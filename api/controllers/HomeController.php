<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\Story;
use common\models\StoryActor;
use common\models\Tag;
use common\models\User;
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

class HomeController extends ActiveController
{
    public $modelClass = 'common\models\Tag';
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

    //TODO:这里的数据需要管理
    /**
     * 首页-按照更新时间偏序的故事
     * @param $page
     * @param $pre_page
     * @return array
     */
    public function actionIndex($page,$pre_page) {

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

            ->where(['status' => Yii::$app->params['STATUS_ACTIVE']])
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

}
