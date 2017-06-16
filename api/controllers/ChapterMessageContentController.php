<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use Yii;
use common\models\UploadForm;
use yii\web\ServerErrorHttpException;
use common\components\DateTimeHelper;

class ChapterMessageContentController extends ActiveController
{
    public $modelClass = 'common\models\ChapterMessageContent';
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
        return $actions;
    }

    public function actionView($story_id,$chapter_id)
    {
        $data = array();
        $condition = array(
            'story_id' => $story_id,
            'chapter_id' => $chapter_id,
            'status' => Yii::$app->params['STATUS_ACTIVE']
        );
        $names = array('story_id','chapter_id','message_content','create_time','last_modify_time');
        $chapterMessageContentModel = ChapterMessageContent::find()->select($names)->where($condition)->one();
        if(!empty($chapterMessageContentModel)) {
            $data = $chapterMessageContentModel->getAttributes($names);
        }

        $ret['data'] = $data;
        $ret['code'] = 200;
        $ret['message'] = 'OK';
        return $ret;
    }

    public function actionTest()
    {
        echo "######TEST#######";
//        $data = array();
//        $condition = array(
//            'story_id' => $story_id,
//            'chapter_id' => $chapter_id,
//            'status' => Yii::$app->params['STATUS_ACTIVE']
//        );
//        $names = array('story_id','chapter_id','message_content','create_time','last_modify_time');
//        $chapterMessageContentModel = ChapterMessageContent::find()->select($names)->where($condition)->one();
//        if(!empty($chapterMessageContentModel)) {
//            $data = $chapterMessageContentModel->getAttributes();
//       }
//
//        $ret['data'] = $data;
//        $ret['code'] = 200;
//        $ret['message'] = 'OK';
//        return $ret;
    }

}
