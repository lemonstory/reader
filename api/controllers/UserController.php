<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\Story;
use common\models\User;
use common\models\UserOauth;
use common\models\UserReadStoryRecord;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use Yii;
use common\models\UploadForm;
use yii\web\ServerErrorHttpException;
use common\components\DateTimeHelper;

class UserController extends ActiveController
{
    public $modelClass = 'common\models\User';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

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
     * 获取用户发布的故事
     * @param $uid
     * @param $page
     * @param $pre_page
     * @return ActiveDataProvider
     */
    public function actionStorys($uid,$page,$pre_page) {

        $response = Yii::$app->getResponse();
        $offset = ($page - 1) * $pre_page;
        $story = Story::find()
            ->with([
                'actors' => function (ActiveQuery $query) {
                    $query->andWhere(['is_visible' => Yii::$app->params['STATUS_ACTIVE'],'status' => Yii::$app->params['STATUS_ACTIVE']]);
                },
                'tags'=> function (ActiveQuery $query) {
                    $query->andWhere(['status' => Yii::$app->params['STATUS_ACTIVE']]);
                },
            ])

            ->where(['uid' => $uid,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->offset($offset)
            ->limit($pre_page)
            ->orderBy(['last_modify_time' => SORT_DESC]);

        $provider =  new ActiveDataProvider([
            'query' =>$story,
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
    public function actionQqLogin() {

        $accessToken = Yii::$app->request->get('accessToken','');
        $openId = Yii::$app->request->get('openId','');
        $userInfo = array();

        if(!empty($accessToken) && !empty($openId)) {


            $oauthCondition = ['oauth_id' => $openId];
            $userOauthModel = UserOauth::find()->where($oauthCondition);
            $count = $userOauthModel->count();

            if($count == 1) {

                $userOauthModel->oauth_access_token = $accessToken;
                $userOauthModel->save();

                $uid = $userOauthModel->uid;
                $userCondition = ['uid' => $uid];
                $userModel = User::findOne($userCondition);

                $userInfo['uid'] = $userModel->uid;
                $userInfo['name'] = $userModel->name;
                $userInfo['cellphone'] = $userModel->cellphone;
                $userInfo['avatar'] = $userModel->avatar;
                $userInfo['signature'] = $userModel->signature;
                $userInfo['status'] = $userModel->status;

            }elseif ($count == 0) {

                $qcObj = null;
                $getInfo = $qcObj->get_user_info();


            }else {

                //TODO:系统出现错误
            }


        }

        return $userInfo;

    }


}
