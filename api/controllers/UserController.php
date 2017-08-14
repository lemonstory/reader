<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\SignupForm;
use common\models\Story;
use common\models\User;
use common\models\UserOauth;
use common\models\UserReadStoryRecord;
use yii\base\Response;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use Yii;
use common\models\UploadForm;
use yii\web\ServerErrorHttpException;
use common\components\DateTimeHelper;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

class UserController extends ActiveController
{
    public $modelClass = 'common\models\User';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        //用户认证
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            //部分action需要access-token认证，部分action不需要
            'except' => ['qq-login', 'signup', 'others-storys'],
            'authMethods' => [
//                HttpBasicAuth::className(),
//                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 动作
        unset($actions['delete'], $actions['create'], $actions['view']);

        // 使用"prepareDataProvider()"方法自定义数据provider
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

    }

    public function actionView()
    {
        return $this->render('view');
    }

    /**
     * 获取用户自己发布的故事
     * @param $uid
     * @param $page
     * @param $pre_page
     * @return ActiveDataProvider
     */
    public function actionStorys($uid, $page, $pre_page)
    {

        $userModel = Yii::$app->user->identity;
        $ret['data'] = array();
        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {

                $response = Yii::$app->getResponse();
                $offset = ($page - 1) * $pre_page;
                $story = Story::find()
                    ->with([
                        'actors' => function (ActiveQuery $query) {
                            $query->andWhere(['is_visible' => Yii::$app->params['STATUS_ACTIVE'], 'status' => Yii::$app->params['STATUS_ACTIVE']]);
                        },
                        'tags' => function (ActiveQuery $query) {
                            $query->andWhere(['status' => Yii::$app->params['STATUS_ACTIVE']]);
                        },
                    ])
                    ->where(['uid' => $uid, 'status' => Yii::$app->params['STATUS_ACTIVE']])
                    ->offset($offset)
                    ->limit($pre_page)
                    ->orderBy(['last_modify_time' => SORT_DESC]);

                $provider = new ActiveDataProvider([
                    'query' => $story,
                    'pagination' => [
                        'pageSize' => $pre_page,
                    ],
                ]);

                $storyModels = $provider->getModels();
                $ret = array();
                foreach ($storyModels as $storyModelItem) {

                    $story = array();
                    $story['story_id'] = $storyModelItem->story_id;
                    $story['name'] = $storyModelItem->name;
                    $story['description'] = $storyModelItem->description;
                    $story['cover'] = $storyModelItem->cover;
                    $story['uid'] = $storyModelItem->uid;
                    $story['chapter_count'] = $storyModelItem->chapter_count;
                    $story['message_count'] = $storyModelItem->message_count;
                    $story['taps'] = $storyModelItem->taps;
                    $story['is_published'] = $storyModelItem->is_published;
                    $story['create_time'] = $storyModelItem->create_time;
                    $story['last_modify_time'] = $storyModelItem->last_modify_time;

                    //actor
                    $actorModels = $storyModelItem->actors;
                    $actorList = array();
                    foreach ($actorModels as $actorModelItem) {
                        $actor = array();
                        $actor['actor_id'] = $actorModelItem->actor_id;
                        $actor['name'] = $actorModelItem->name;
                        $actor['avatar'] = $actorModelItem->avatar;
                        $actor['number'] = $actorModelItem->number;
                        $actor['is_visible'] = $actorModelItem->is_visible;
                        $actorList[] = $actor;
                    }
                    $story['actor'] = $actorList;

                    //tag
                    $tagModels = $storyModelItem->tags;
                    $tagList = array();
                    foreach ($tagModels as $tagModelItem) {
                        $tag = array();
                        $tag['tag_id'] = $tagModelItem->tag_id;
                        $tag['name'] = $tagModelItem->name;
                        $tag['number'] = $tagModelItem->number;
                        $tagList[] = $tag;
                    }
                    $story['tag'] = $tagList;
                    $ret['data']['storyList'][] = $story;
                }

                $pagination = $provider->getPagination();
                $ret['data']['totalCount'] = $pagination->totalCount;
                $ret['data']['pageCount'] = $pagination->getPageCount();
                $ret['data']['currentPage'] = $pagination->getPage() + 1;
                $ret['data']['perPage'] = $pagination->getPageSize();
                $ret['code'] = $response->statusCode;
                $ret['msg'] = $response->statusText;

            } else {
                $ret['code'] = 400;
                $ret['msg'] = 'uid与token不相符';
            }
        } else {
            $ret['code'] = 400;
            $ret['msg'] = '用户不存在';
        }

        return $ret;
    }

    /**
     * 获取其他用户已发布的故事
     * @param $uid
     * @param $page
     * @param $pre_page
     * @return ActiveDataProvider
     */
    public function actionOthersStorys($uid, $page, $pre_page)
    {

        $ret['data'] = array();
        $response = Yii::$app->getResponse();
        $offset = ($page - 1) * $pre_page;
        $story = Story::find()
            ->with([
                'actors' => function (ActiveQuery $query) {
                    $query->andWhere(['is_visible' => Yii::$app->params['STATUS_ACTIVE'], 'status' => Yii::$app->params['STATUS_ACTIVE']]);
                },
                'tags' => function (ActiveQuery $query) {
                    $query->andWhere(['status' => Yii::$app->params['STATUS_ACTIVE']]);
                },
            ])
            ->where(['uid' => $uid, 'status' => Yii::$app->params['STATUS_ACTIVE'], 'is_published' => Yii::$app->params['STATUS_PUBLISHED']])
            ->offset($offset)
            ->limit($pre_page)
            ->orderBy(['last_modify_time' => SORT_DESC]);

        $provider = new ActiveDataProvider([
            'query' => $story,
            'pagination' => [
                'pageSize' => $pre_page,
            ],
        ]);

        $storyModels = $provider->getModels();
        $ret = array();
        foreach ($storyModels as $storyModelItem) {

            $story = array();
            $story['story_id'] = $storyModelItem->story_id;
            $story['name'] = $storyModelItem->name;
            $story['description'] = $storyModelItem->description;
            $story['cover'] = $storyModelItem->cover;
            $story['uid'] = $storyModelItem->uid;
            $story['chapter_count'] = $storyModelItem->chapter_count;
            $story['message_count'] = $storyModelItem->message_count;
            $story['taps'] = $storyModelItem->taps;
            $story['is_published'] = $storyModelItem->is_published;
            $story['create_time'] = $storyModelItem->create_time;
            $story['last_modify_time'] = $storyModelItem->last_modify_time;

            //actor
            $actorModels = $storyModelItem->actors;
            $actorList = array();
            foreach ($actorModels as $actorModelItem) {
                $actor = array();
                $actor['actor_id'] = $actorModelItem->actor_id;
                $actor['name'] = $actorModelItem->name;
                $actor['avatar'] = $actorModelItem->avatar;
                $actor['number'] = $actorModelItem->number;
                $actor['is_visible'] = $actorModelItem->is_visible;
                $actorList[] = $actor;
            }
            $story['actor'] = $actorList;

            //tag
            $tagModels = $storyModelItem->tags;
            $tagList = array();
            foreach ($tagModels as $tagModelItem) {
                $tag = array();
                $tag['tag_id'] = $tagModelItem->tag_id;
                $tag['name'] = $tagModelItem->name;
                $tag['number'] = $tagModelItem->number;
                $tagList[] = $tag;
            }
            $story['tag'] = $tagList;
            $ret['data']['storyList'][] = $story;
        }

        $pagination = $provider->getPagination();
        $ret['data']['totalCount'] = $pagination->totalCount;
        $ret['data']['pageCount'] = $pagination->getPageCount();
        $ret['data']['currentPage'] = $pagination->getPage() + 1;
        $ret['data']['perPage'] = $pagination->getPageSize();
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;


        return $ret;
    }


    /**
     * QQ登录
     * @return array 用户个人信息
     */
    public function actionQqLogin()
    {

        $accessToken = Yii::$app->request->get('accessToken', '');
        $openId = Yii::$app->request->get('openId', '');
        $userInfo = array();

        if (!empty($accessToken) && !empty($openId)) {


            $oauthCondition = ['oauth_id' => $openId];
            $userOauthModel = UserOauth::find()->where($oauthCondition);
            $count = $userOauthModel->count();

            if ($count == 1) {

                $userOauthModel->oauth_access_token = $accessToken;
                $userOauthModel->save();

                $uid = $userOauthModel->uid;
                $userCondition = ['uid' => $uid];
                $userModel = User::findOne($userCondition);

                $userInfo['uid'] = $userModel->uid;
                $userInfo['name'] = $userModel->name;
                $userInfo['mobile_phone'] = $userModel->mobile_phone;
                $userInfo['avatar'] = $userModel->avatar;
                $userInfo['signature'] = $userModel->signature;
                $userInfo['status'] = $userModel->status;

            } elseif ($count == 0) {

                $qcObj = null;
                $getInfo = $qcObj->get_user_info();


            } else {

                //TODO:系统出现错误
            }


        }

        return $userInfo;
    }


    /**
     * 手机号注册
     * @param $mobilePhone
     * @param $password
     * @return mixed
     */
    public function actionSignup($mobilePhone, $password)
    {

        $signupFormModel = new SignupForm();
        $signupFormModel->mobile_phone = $mobilePhone;
        $signupFormModel->password = $password;
        $signupFormModel->username = "用户_" . rand(1000, 9999);

        $userModel = $signupFormModel->signup();
        $ret = array();
        if (is_null($userModel)) {

            foreach ($signupFormModel->getErrors() as $attribute => $error) {
                foreach ($error as $message) {
                    //throw new Exception($attribute.": ".$message);
                    $ret['data'] = array();
                    $ret['code'] = 400;
                    $ret['msg'] = $message;
                }
            }
        } else {

            //注册成功返回用户信息
            $ret['data'] = $this->retUserInfoData($userModel);
            $ret['code'] = 200;
            $ret['msg'] = 'OK';
        }

        return $ret;
    }

    /**
     * 修改用户名
     * @param $uid
     * @param $username
     * @return mixed
     */
    public function actionUpdateUsername($uid, $username)
    {

        $userModel = Yii::$app->user->identity;
        $ret['data'] = array();
        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {
                $userModel->username = $username;
                if (!$userModel->save(true, ['username'])) {
                    foreach ($userModel->getErrors() as $attribute => $error) {
                        foreach ($error as $message) {
                            //throw new Exception($attribute.": ".$message);
                            $ret['code'] = 400;
                            $ret['msg'] = $message;
                        }
                    }
                } else {
                    $ret['data'] = $this->retUserInfoData($userModel);
                    $ret['code'] = 200;
                    $ret['msg'] = 'OK';
                }
            } else {
                $ret['code'] = 400;
                $ret['msg'] = 'uid与token不相符';
            }

        } else {
            $ret['code'] = 400;
            $ret['msg'] = '用户不存在';
        }

        return $ret;
    }

    /**
     * 修改用户头像
     * @param $uid
     * @param $avatar
     * @return mixed
     */
    public function actionUpdateAvatar($uid, $avatar)
    {

        $userModel = Yii::$app->user->identity;
        $ret['data'] = array();
        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {
                $userModel->avatar = $avatar;
                if (!$userModel->save(true, ['avatar'])) {
                    foreach ($userModel->getErrors() as $attribute => $error) {
                        foreach ($error as $message) {
                            //throw new Exception($attribute.": ".$message);
                            $ret['code'] = 400;
                            $ret['msg'] = $message;
                        }
                    }
                } else {
                    $ret['data'] = $this->retUserInfoData($userModel);
                    $ret['code'] = 200;
                    $ret['msg'] = 'OK';
                }
            } else {
                $ret['code'] = 400;
                $ret['msg'] = 'uid与token不相符';
            }

        } else {
            $ret['code'] = 400;
            $ret['msg'] = '用户不存在';
        }

        return $ret;
    }


    /**
     * 修改个性签名
     * @param $uid
     * @param $signature
     * @return mixed
     */
    public function actionUpdateSignature($uid, $signature)
    {

        $userModel = Yii::$app->user->identity;
        $ret['data'] = array();

        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {
                $userModel->signature = $signature;
                if (!$userModel->save(true, ['signature'])) {
                    foreach ($userModel->getErrors() as $attribute => $error) {
                        foreach ($error as $message) {
                            //throw new Exception($attribute.": ".$message);
                            $ret['code'] = 400;
                            $ret['msg'] = $message;
                        }
                    }
                } else {
                    $ret['data'] = $this->retUserInfoData($userModel);
                    $ret['code'] = 200;
                    $ret['msg'] = 'OK';
                }
            } else {
                $ret['code'] = 400;
                $ret['msg'] = 'uid与token不相符';
            }

        } else {
            $ret['code'] = 400;
            $ret['msg'] = '用户不存在';
        }
        return $ret;
    }


    /**
     * 组织返回用户信息数据
     * @param $userModel
     * @return mixed
     */
    private function retUserInfoData($userModel)
    {

        $data = array();
        $data['uid'] = $userModel->uid;
        $data['username'] = $userModel->username;
        $data['mobile_phone'] = $userModel->mobile_phone;
        $data['email'] = $userModel->email;
        $data['avatar'] = $userModel->avatar;
        $data['signature'] = $userModel->signature;
        $data['access_token'] = $userModel->access_token;
        $data['status'] = $userModel->status;
        $data['register_ip'] = $userModel->register_ip;
        $data['register_time'] = $userModel->register_time;
        $data['last_login_ip'] = $userModel->last_login_ip;
        $data['last_login_time'] = $userModel->last_login_time;
        $data['last_modify_time'] = $userModel->last_modify_time;
        return $data;
    }

}
