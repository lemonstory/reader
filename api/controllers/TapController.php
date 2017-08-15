<?php

namespace api\controllers;

use common\components\MnsQueue;
use common\components\QueueMessageHelper;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;

class TapController extends ActiveController
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
            'except' => ['taps-increase'],
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

    public function actionIndex()
    {
        return $this->render('index');
    }


    /**
     * 增加用户,故事点击数
     * @param $uid
     * @param $storyId
     * @param $taps
     * @return mixed
     */
    public function actionTapsIncrease($uid,$storyId,$taps) {

        $ret['data'] = array();
        if(!empty($uid) && !empty($storyId) && !empty($taps)) {
            $mnsQueue = new MnsQueue();
            $queueName = Yii::$app->params['mnsQueueTapsIncreaseName'];
            $messageBody = QueueMessageHelper::tapsIncrease($uid, $storyId, $taps);
            $isSent = $mnsQueue->sendMessage($messageBody, $queueName);
            if($isSent) {
                $ret['code'] = 200;
                $ret['msg'] = 'OK';
            }else {
                $ret['code'] = 200;
                $ret['msg'] = 'OK';
            }
        }else {
            $ret['code'] = 400;
            $ret['msg'] = '参数错误';
        }

        return $ret;
    }

}
