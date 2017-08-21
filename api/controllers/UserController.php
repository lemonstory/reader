<?php

namespace api\controllers;

use api\models\LoginForm;
use common\components\DateTimeHelper;
use common\models\Oauth;
use common\models\SignupForm;
use common\models\Story;
use common\models\User;
use DateTime;
use QC;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\ServerErrorHttpException;

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
            'except' => ['qq-login', 'signup', 'mobile-phone-login', 'others-storys', 'others-info'],
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
                $ret['status'] = $response->statusCode;
                $ret['message'] = $response->statusText;

            } else {
                $ret['status'] = 400;
                $ret['message'] = 'uid与token不相符';
            }
        } else {
            $ret['status'] = 400;
            $ret['message'] = '用户不存在';
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
        $ret['status'] = $response->statusCode;
        $ret['message'] = $response->statusText;


        return $ret;
    }


    /**
     * QQ登录
     * @return array 用户个人信息
     * @see http://wiki.connect.qq.com/get_user_info
     */
    public function actionQqLogin()
    {

        $accessToken = Yii::$app->request->get('accessToken', '');
        $openId = Yii::$app->request->get('openId', '');
        $userInfo = array();

        if (!empty($accessToken) && !empty($openId)) {

            $oauthCondition = ['source' => 'qq', 'source_id' => $openId];
            $oauthQueryObj = Oauth::find()->where($oauthCondition);
            $count = $oauthQueryObj->count();
            $oauthModel = $oauthQueryObj->one();
            if ($count == 1) {

                //TODO:开发授权用户的accessToken需要存储
                $uid = $oauthModel->uid;
                $userCondition = ['uid' => $uid];
                $userModel = User::findOne($userCondition);
                $ret['data'] = $this->retUserInfoData($userModel);
                $ret['status'] = 200;
                $ret['message'] = 'OK';

            } elseif ($count == 0) {

                include_once Yii::$app->vendorPath . "/qqconnect-server-sdk-php/API/qqConnectAPI.php";
                $qcObj = new QC($accessToken, $openId);
                $qqUserInfo = $qcObj->get_user_info();
//                var_dump($qqUserInfo);
//                exit;

                //获取用户成功
                if (is_array($qqUserInfo) && !empty($qqUserInfo) && isset($qqUserInfo['ret']) && 0 == $qqUserInfo['ret']) {

                    $transaction = Yii::$app->db->beginTransaction();
                    try {

                        //存储用户信息
                        $username = $qqUserInfo['nickname'];
                        if (!empty($qqUserInfo['figureurl_qq_2'])) {
                            $avatar = $qqUserInfo['figureurl_qq_2'];
                        } else {
                            $avatar = $qqUserInfo['figureurl_qq_1'];
                        }

                        //性别 女=0; 男=1
                        $gender = 0;
                        if (!empty($qqUserInfo['gender'])) {
                            if (0 == strcmp($qqUserInfo['gender'], "男")) {
                                $gender = 1;
                            } else {
                                $gender = 0;
                            }
                        }

                        //TODO:省市这里数据存储需要修改
                        $city = $qqUserInfo['city'];
                        $province = $qqUserInfo['province'];

                        $birthday = null;
                        if (!empty($qqUserInfo['year'])) {

                            $year = sprintf('%s-01-01 00:00:00', $qqUserInfo['year']);
                            $birthday = $year;
                        }

                        //user数据表存储
                        $userModel = new User();
                        $userModel->username = $username;
                        $userModel->avatar = $avatar;
                        $userModel->gender = $gender;
                        $userModel->city = $city;
                        $userModel->province = $province;
                        $userModel->birthday = $birthday;
                        $userModel->status = Yii::$app->params['STATUS_ACTIVE'];
                        $userModel->register_ip = Yii::$app->request->getUserIP();
                        $userModel->register_time = time();
                        $userModel->last_login_ip = Yii::$app->request->getUserIP();
                        $userModel->last_login_time = time();
                        $userModel->generateAuthKey();
                        $userModel->generateAccessToken();
                        if ($userModel->save(false)) {

                            $uid = $userModel->uid;
                            //Oauth数据表存储
                            $oauthModel = new Oauth();
                            $oauthModel->uid = $uid;
                            $oauthModel->source = 'qq';
                            $oauthModel->source_id = $openId;
                            $oauthModel->status = Yii::$app->params['STATUS_ACTIVE'];

                            if ($oauthModel->save()) {

                                //返回用户信息
                                $ret['data'] = $this->retUserInfoData($userModel);
                                $ret['status'] = 200;
                                $ret['message'] = 'OK';

                            } else {

                                //错误处理
                                if ($oauthModel->hasErrors()) {
                                    Yii::error($oauthModel->getErrors());
                                    throw new ServerErrorHttpException('开放授权信息保存失败');
                                }
                            }
                        } else {

                            //错误处理
                            if ($userModel->hasErrors()) {
                                //错误处理
                                if ($userModel->hasErrors()) {

                                    Yii::error($userModel->getErrors());
                                    throw new ServerErrorHttpException('用户信息保存失败');
                                }
                            }
                        }

                        $transaction->commit();

                    } catch (\Exception $e) {

                        //如果抛出错误则进入catch，先callback，然后捕获错误，返回错误
                        $transaction->rollBack();
                        Yii::error($e->getMessage());
                        $ret['status'] = 500;
                        $ret['message'] = $e->getMessage();
                    }

                } else {
                    $ret['data'] = array();
                    $ret['status'] = 500;
                    $ret['message'] = '调用QQ get_user_info接口获取用户信息失败';
                }

            } else {
                $ret['data'] = array();
                $ret['status'] = 500;
                $ret['message'] = '系统错误,多个用户拥有相同的openId';
            }
        }
        return $ret;
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
                    $ret['status'] = 400;
                    $ret['message'] = $message;
                }
            }
        } else {

            //注册成功返回用户信息
            $ret['data'] = $this->retUserInfoData($userModel);
            $ret['status'] = 200;
            $ret['message'] = 'OK';
        }

        return $ret;
    }


    /**
     * 手机号登录
     * @return string|\yii\web\Response
     * @internal param $mobilePhone
     * @internal param $password
     */
    public function actionMobilePhoneLogin()
    {

        $mobilePhone = Yii::$app->request->post('mobilePhone');
        $password = Yii::$app->request->post('password');
        $ret['data'] = array();
        if (Yii::$app->user->isGuest) {

            $loginFormModel = new LoginForm();
            $loginFormModel->mobilePhone = $mobilePhone;
            $loginFormModel->password = $password;

            if ($loginFormModel->login()) {
                //登录成功
                $userModel = Yii::$app->user->identity;
                $ret['data'] = $this->retUserInfoData($userModel);
                $ret['status'] = 200;
            } else {
                //登录失败
                if ($loginFormModel->hasErrors()) {
                    foreach ($loginFormModel->getErrors() as $attribute => $error) {
                        foreach ($error as $message) {
                            //throw new Exception($attribute.": ".$message);
                            $ret['status'] = 400;
                            $ret['message'] = $message;
                        }
                    }
                }
            }
        } else {
            //不应该执行到这里
            $ret['status'] = 500;
            $ret['message'] = '系统出现错误';
        }
        return $ret;
    }

    public function actionLogout() {

        $ret = array();
        $ret['data'] = array();
        $isLogout = Yii::$app->user->logout(true);
        if($isLogout) {

            $ret['status'] = 200;
            $ret['message'] = 'OK';
        }else {

            $ret['status'] = 500;
            $ret['message'] = '系统出现错误';
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
                            $ret['status'] = 400;
                            $ret['message'] = $message;
                        }
                    }
                } else {
                    $ret['data'] = $this->retUserInfoData($userModel);
                    $ret['status'] = 200;
                    $ret['message'] = 'OK';
                }
            } else {
                $ret['status'] = 400;
                $ret['message'] = 'uid与token不相符';
            }

        } else {
            $ret['status'] = 400;
            $ret['message'] = '用户不存在';
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
                            $ret['status'] = 400;
                            $ret['message'] = $message;
                        }
                    }
                } else {
                    $ret['data'] = $this->retUserInfoData($userModel);
                    $ret['status'] = 200;
                    $ret['message'] = 'OK';
                }
            } else {
                $ret['status'] = 400;
                $ret['message'] = 'uid与token不相符';
            }

        } else {
            $ret['status'] = 400;
            $ret['message'] = '用户不存在';
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
                            $ret['status'] = 400;
                            $ret['message'] = $message;
                        }
                    }
                } else {
                    $ret['data'] = $this->retUserInfoData($userModel);
                    $ret['status'] = 200;
                    $ret['message'] = 'OK';
                }
            } else {
                $ret['status'] = 400;
                $ret['message'] = 'uid与token不相符';
            }

        } else {
            $ret['status'] = 400;
            $ret['message'] = '用户不存在';
        }
        return $ret;
    }

    /**
     * 获取他人的用户信息
     * @param $uid
     * @return mixed
     */
    public function actionOthersInfo($uid)
    {

        $condition = array(
            'uid' => $uid,
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        );
        $userModel = User::find()->where($condition)->one();
        $ret['data'] = array();

        if (!is_null($userModel)) {

            $ret['data'] = $this->retUserInfoData($userModel, true);
            $ret['status'] = 200;
            $ret['message'] = 'OK';

        } else {
            $ret['status'] = 400;
            $ret['message'] = '用户不存在';
        }

        return $ret;
    }

    /**
     * 组织返回用户信息数据
     * @param $userModel
     * @param $isOthers //是否是访问他人用户信息
     * @return mixed
     */
    private function retUserInfoData($userModel, $isOthers = false)
    {

        $data = array();
        $data['uid'] = $userModel->uid;
        $data['username'] = $userModel->username;
        $data['avatar'] = $userModel->avatar;
        $data['signature'] = $userModel->signature;
        $data['taps'] = $userModel->taps;
        $data['gender'] = $userModel->gender;
        $data['province'] = $userModel->province;
        $data['city'] = $userModel->city;
        $data['birthday'] = $userModel->birthday;
        $data['status'] = $userModel->status;

        if (!$isOthers) {
            $data['mobile_phone'] = $userModel->mobile_phone;
            $data['email'] = $userModel->email;
            $data['access_token'] = $userModel->access_token;
            $data['register_ip'] = $userModel->register_ip;
            $data['register_time'] = $userModel->register_time;
            $data['last_login_ip'] = $userModel->last_login_ip;
            $data['last_login_time'] = $userModel->last_login_time;
            $data['last_modify_time'] = $userModel->last_modify_time;
        }

        return $data;
    }

}
