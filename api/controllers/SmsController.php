<?php
namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use common\components\Sms;

//阿里云-短信服务-SDK demo
//// 调用示例：
//
//header('Content-Type: text/plain; charset=utf-8');
//
//$demo = new SmsDemo(
//    "yourAccessKeyId",
//    "yourAccessKeySecret"
//);
//
//echo "SmsDemo::sendSms\n";
//$response = $demo->sendSms(
//    "短信签名", // 短信签名
//    "SMS_0000001", // 短信模板编号
//    "12345678901", // 短信接收者
//    Array(  // 短信模板中字段的值
//        "code"=>"12345",
//        "product"=>"dsd"
//    ),
//    "123"
//);
//print_r($response);
//
//echo "SmsDemo::queryDetails\n";
//$response = $demo->queryDetails(
//    "12345678901",  // phoneNumbers 电话号码
//    "20170718", // sendDate 发送时间
//    10, // pageSize 分页大小
//    1 // currentPage 当前页码
//// "abcd" // bizId 短信发送流水号，选填
//);
//
//print_r($response);


class SmsController extends ActiveController {


    public $modelClass = '';
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

    //$phoneNumbers
    public function actionSendSms()
    {

        $request = Yii::$app->request;
//        $phoneNumbers = $request->get('phoneNumbers');
        $phoneNumbers = '18600024911';
        $sms = new Sms();
        $signName = Yii::$app->params['smsSignName'];
        $templateCode = Yii::$app->params['smsTemplateCode'];
        $templateParam = array('number' => '1234');
        $outId = "123";
        $response = $sms->sendSms($signName,$templateCode,$phoneNumbers,$templateParam,$outId);

//        stdClass Object ( [Message] => OK [RequestId] => ACA1E730-8BDC-4C47-9F63-E9311F9BC992 [BizId] => 179315102107980174^0 [Code] => OK )
//        print_r($response);
    }

}

