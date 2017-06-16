<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\Story;
use common\models\User;
use common\models\UserReadStoryRecord;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use Yii;
use common\models\UploadForm;
use yii\web\ServerErrorHttpException;
use common\components\DateTimeHelper;

class UserReadStoryRecordController extends ActiveController
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
     * 阅读记录列表
     * @param $uid
     * @param $page
     * @param $per_page
     * @return mixed
     */
    public function actionIndex($uid,$page,$per_page) {

        //章节信息
        $storyCondition = array(
            'story.status' => Yii::$app->params['STATUS_ACTIVE'],
            'user_read_story_record.status' => Yii::$app->params['STATUS_ACTIVE'],
            'user_read_story_record.uid' => $uid,
        );
        $storyNames = array(
            'story.story_id',
            'story.name',
            'story.description',
            'story.cover',
            'story.uid',
            'story.chapter_count',
            'story.message_count',
            'story.taps',
            'story.is_published',
            'story.create_time AS story_create_time',
            'story.last_modify_time AS story_last_modify_time',
            'user_read_story_record.last_chapter_id',
            'user_read_story_record.last_message_id',
            'user_read_story_record.create_time',
            'user_read_story_record.last_modify_time'
        );

        $offset = ($page - 1) * $per_page;
        $with = 'story';
        $query = UserReadStoryRecord::find()
            ->select($storyNames)
            ->innerJoinWith($with)
            ->andWhere($storyCondition)
            ->offset($offset)
            ->limit($per_page)
            ->orderBy(['user_read_story_record.last_modify_time' => SORT_DESC]);

        $provider =  new ActiveDataProvider([
            'query' =>$query,
            'pagination' => [
                'pageSize' => $per_page,
            ],
        ]);

        $data = array();
        //getModels还是按照UserReadStoryRecord(Model类)的结构返回数据,虽然上面有SELECT name的定义
        //关系'story'会是$userReadStoryRecordModels的Item的属性
        $userReadStoryRecordModels = $provider->getModels();
        $pagination = $provider->getPagination();
        $data['totalCount'] = $pagination->totalCount;
        $data['pageCount'] = $pagination->getPageCount();
        $data['currentPage'] = $pagination->getPage() + 1;
        $data['perPage'] = $pagination->getPageSize();


        if(!empty($userReadStoryRecordModels)) {
            $uidArr = array();
            foreach ($userReadStoryRecordModels as $item) {
                $uidArr[] = $item['story']['uid'];
            }

            //作者信息
            $userCondition = array(
                'uid' => $uidArr,
                'status' => Yii::$app->params['STATUS_ACTIVE'],
            );

            $userNames = array(
                'uid',
                'name',
                'avatar',
                'signature'
            );
            $userInfoList = User::find()->select($userNames)->where($userCondition)->asArray()->all();
            $userInfoList = ArrayHelper::index($userInfoList, 'uid');

            //合并故事和作者数据
            $dataItem = array();
            foreach ($userReadStoryRecordModels as $item) {

                $dataItem['story_id'] = $item->story->story_id;
                $dataItem['name'] = $item->story->name;
                $dataItem['description'] = $item->story->description;
                $dataItem['cover'] = $item->story->cover;
                $dataItem['chapter_count'] = $item->story->chapter_count;
                $dataItem['message_count'] = $item->story->message_count;
                $dataItem['taps'] = $item->story->taps;
                $dataItem['is_published'] = $item->story->is_published;
                $dataItem['story_create_time'] = $item->story->create_time;
                $dataItem['story_last_modify_time'] = $item->story->last_modify_time;
                $dataItem['last_chapter_id'] = $item->last_chapter_id;
                $dataItem['last_message_id'] = $item->last_message_id;
                $dataItem['create_time'] = $item->create_time;
                $dataItem['last_modify_time'] = $item->last_modify_time;

                if(!empty($userInfoList[$item['uid']])) {
                    $dataItem['user'] = $userInfoList[$item['uid']];
                }else {
                    $dataItem['user'] = array();
                }
                $data['storyList'][] = $dataItem;
            }
        }
        $ret['data'] = $data;
        $ret['code'] = 200;
        $ret['message'] = 'OK';
        return $ret;
    }

}
