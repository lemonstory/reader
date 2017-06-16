<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\Story;
use common\models\Tag;
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

class TagController extends ActiveController
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

    /**
     * 标签列表
     * @return mixed
     */
    public function actionIndex() {

        $condition = array(
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        );
        $columns = array('tag_id','number','name');
        $tagArr = Tag::find()->select($columns)->where($condition)->orderBy(['number' => SORT_ASC])->asArray()->all();
        $response = Yii::$app->getResponse();
        $ret['data'] = $tagArr;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }

}
