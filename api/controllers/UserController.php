<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\User;
use common\models\UserReadStoryRecord;
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

    public function actionReadStoryRecord($uid,$page,$per_page) {

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

        $with = 'story';
        $findRetArr = UserReadStoryRecord::find()
                        ->select($storyNames)
                        ->innerJoinWith($with)
                        ->andWhere($storyCondition)
                        ->orderBy(['user_read_story_record.last_modify_time' => SORT_DESC])
                        ->asArray()
                        ->all();
        $uidArr = array();
        foreach ($findRetArr as $item) {
            $uidArr[] = $item['story']['uid'];
        }


        $uidArr = array(1);
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
        $data = array();
        $dataItem = array();
        foreach ($findRetArr as $item) {

            $dataItem['story_id'] = $item['story_id'];
            $dataItem['name'] = $item['name'];
            $dataItem['description'] = $item['description'];
            $dataItem['cover'] = $item['cover'];
            $dataItem['chapter_count'] = $item['chapter_count'];
            $dataItem['message_count'] = $item['message_count'];
            $dataItem['taps'] = $item['taps'];
            $dataItem['is_published'] = $item['is_published'];
            $dataItem['story_create_time'] = $item['story_create_time'];
            $dataItem['story_last_modify_time'] = $item['story_last_modify_time'];
            $dataItem['last_chapter_id'] = $item['last_chapter_id'];
            $dataItem['last_message_id'] = $item['last_message_id'];
            $dataItem['create_time'] = $item['create_time'];
            $dataItem['last_modify_time'] = $item['last_modify_time'];

            if(!empty($userInfoList[$item['uid']])) {
                $dataItem['user'] = $userInfoList[$item['uid']];
            }else {
                $dataItem['user'] = array();
            }

            $data[] = $dataItem;
        }

        $ret['data'] = $data;
        $ret['code'] = 200;
        $ret['message'] = 'OK';
        return $ret;
    }

}
