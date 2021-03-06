<?php
namespace api\controllers;

use common\models\User;
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


    /**
     * 发送验证码
     * 阿里云流量限制：对同一个手机号码发送短信验证码，1条/分钟，5条/小时，累计10条/天
     * @param $mobilePhone
     * @return mixed
     * @see https://help.aliyun.com/document_detail/55284.html?spm=5176.product44282.4.4.sVbOok
     */
    public function actionSendSms($mobilePhone)
    {
        //检测手机号是否已注册
        $mobilePhone = trim($mobilePhone);
        $userCondition = ['mobile_phone' => $mobilePhone];
        $count = (int)User::find()->where($userCondition)->count();
        if(0 == $count) {
            $sms = new Sms();
            $signName = Yii::$app->params['smsSignName'];
            $templateCode = Yii::$app->params['smsTemplateCode'];
            $number = rand(1000,9999);
            $templateParam = array('number' => $number);
            //redis存储
            $redis = Yii::$app->redis;
            $key = sprintf(Yii::$app->params['cacheKeyYouweiSmsNumber'],$mobilePhone);
            $seconds = Yii::$app->params['expireSmsNumberTime'];
            $value = $number;
            $isSet = $redis->setex($key, $seconds, $value);
            if($isSet) {
                //外部流水扩展字段
                $outId = "";
                //$response -> stdClass Object ( [Message] => OK [RequestId] => ACA1E730-8BDC-4C47-9F63-E9311F9BC992 [BizId] => 179315102107980174^0 [Code] => OK )
                $response = $sms->sendSms($signName,$templateCode,$mobilePhone,$templateParam,$outId);

                //格式化输出
                $ret = array();
                $data = array();
                $data['RequestId'] = $response->RequestId;
                $data['BizId'] = $response->BizId;
                $ret['data'] = $data;
                if(0 == strcmp("OK",$response->Code)) {
                    $ret['status'] = 200;
                }else {
                    $ret['status'] = $response->Code;
                }
                $ret['message'] = $response->Message;
            }else {
                $ret['data'] = array();
                $ret['status'] = 500;
                //redis写入失败
                $ret['message'] = '系统出现错误';
            }
        }else {

            $ret['data'] = array();
            $ret['status'] = 400;
            $ret['message'] = '该手机号已经被注册';
        }
        return $ret;
    }

    /**
     * 短信发送记录查询
     * @param $mobilePhone
     * @param $bizId
     * @return mixed
     * @see https://help.aliyun.com/document_detail/55452.html?spm=5176.doc55451.6.557.ylPC1q
     */
    public function actionQuerySendDetails($mobilePhone,$bizId) {

        $sms = new Sms();
        $pageSize = 10;
        $currentPage = 1;
        $sendDate = date("Ymd",time());
        $response = $sms->queryDetails($mobilePhone,$sendDate,$pageSize,$currentPage,$bizId);

        //格式化输出
        $ret = array();
        if(0 == strcmp("OK",$response->Code)) {
            $ret['status'] = 200;
        }else {
            $ret['status'] = $response->Code;
        }
        $ret['message'] = $response->Message;
        if(1 == $response->TotalCount) {

            $SmsSendDetailDTO = $response->SmsSendDetailDTOs->SmsSendDetailDTO;
            $ret['data']['SendDate'] = $SmsSendDetailDTO[0]->SendDate;
            $ret['data']['SendStatus'] = $SmsSendDetailDTO[0]->SendStatus;
            $ret['data']['ReceiveDate'] = $SmsSendDetailDTO[0]->ReceiveDate;
            $ret['data']['ErrCode'] = $SmsSendDetailDTO[0]->ErrCode;
            $ret['data']['TemplateCode'] = $SmsSendDetailDTO[0]->TemplateCode;
            $ret['data']['Content'] = $SmsSendDetailDTO[0]->Content;
            $ret['data']['PhoneNum'] = $SmsSendDetailDTO[0]->PhoneNum;
        }
        return $ret;
    }

    /**
     * 验证码校验
     * @param $mobilePhone
     * @param $number
     * @return array
     */
    public function actionVerifySms($mobilePhone,$number) {

        $redis = Yii::$app->redis;
        $key = sprintf(Yii::$app->params['cacheKeyYouweiSmsNumber'],$mobilePhone);
        $value = $redis->get($key);
        $ret = array();
        $ret['data'] = array();
        if(!empty($value) &&  $value == $number) {

            $ret['status'] = 200;
            $ret['message'] = 'OK';

        }else {
            $ret['status'] = 400;
            $ret['message'] = '验证码已过期(或)输入错误';
        }
        return $ret;
    }

}

