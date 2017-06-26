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

class ChapterMessageContentController extends ActiveController
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

    public function actionView($story_id,$chapter_id)
    {

        $data = array();
        $storyId = $story_id;
        $chapterId = $chapter_id;
        $storyCondition = array(
            'story_id' => $storyId,
            'chapter_id' => $chapterId,
            'status' => Yii::$app->params['STATUS_ACTIVE']
        );
        $chapterMessageContentArr = ChapterMessageContent::find()
                                    ->where($storyCondition)
                                    ->orderBy('number')
                                    ->asArray()
                                    ->all();

        $data['storyId'] = $storyId;
        $data['chapterId'] = $chapterId;
        $data['chapterMessageContent'] = $chapterMessageContentArr;
        return $this->renderPartial('view',$data);
    }

}
