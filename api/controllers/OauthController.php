<?php

namespace api\controllers;

use QC;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;

class OauthController extends ActiveController
{
    public $modelClass = 'common\models\User';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
    public $likeCommentTargetType = '';

    public function init()
    {
        parent::init();
    }

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 动作
        unset($actions['delete'], $actions['create'], $actions['view']);

        // 使用"prepareDataProvider()"方法自定义数据provider
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

    }



    public function actionCallback()
    {
        include_once Yii::$app->vendorPath.'/qqconnect-server-sdk-php/API/qqConnectAPI.php';
        $qc = new QC();
        echo $qc->qq_callback();
        echo $qc->get_openid();
    }

    //http://open.weibo.com/apps/1766319045/info/advanced
    //高级设置->OAuth2.0 授权设置->授权回调页
    public function actionWeiboCallback() {
       exit("Weibo Callback RUN!");
    }

    //http://open.weibo.com/apps/1766319045/info/advanced
    //高级设置->OAuth2.0 授权设置->取消授权回调页
    public function actionWeiboCallbackCancel() {
        exit("Weibo Callback Cancel RUN!");
    }



}
