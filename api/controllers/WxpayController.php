<?php

namespace api\controllers;

use Carbon\Carbon;
use common\components\MnsQueue;
use common\components\QueueMessageHelper;
use GuzzleHttp\Client;
use SimpleXMLElement;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\UrlManager;

class WxpayController extends ActiveController
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
            'except' => ['pay-notify'],
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
     * 请求生成支付订单
     *
     * @see https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1#
     * @param $deviceInfo 终端设备号(门店号或收银设备ID)，默认请传"WEB"
     * @param $totalFee 订单总金额，单位为分
     * @param string $body 商品描述交易字段格式根据不同的应用场景按照以下格式：APP——需传入应用市场上的APP名字-实际商品名称，天天爱消除-游戏充值。
     * @return array
     */
    public function actionGeneratePayOrder($deviceInfo, $totalFee, $body = "有味读书-会员充值")
    {
        $userModel = Yii::$app->user->identity;
        if (!is_null($userModel)) {
            $uid = $userModel->uid;
            $attach['uid'] = $uid;

            //应用ID
            $params['appid'] = Yii::$app->params['weixinPayAppId'];

            //商户号
            $params['mch_id'] = Yii::$app->params['weixinPayMchId'];

            //设备号
            $params['device_info'] = $deviceInfo;

            //随机字符串(随机字符串，不长于32位)
            $params['nonce_str'] = Yii::$app->security->generateRandomString(6);

            //商品描述
            $params['body'] = $body;

            //商品详情
            $params['detail'] = "";

            //附加数据
            $params['attach'] = http_build_query($attach);

            //商户订单号
            $params['out_trade_no'] = $this->generateOutTradeNo();

            //货币类型
            $params['fee_type'] = "CNY";

            //总金额(单位为分)
            $params['total_fee'] = $totalFee;

            //终端IP
            $params['spbill_create_ip'] = Yii::$app->request->getUserIP();

            //交易起始时间
            $params['time_start'] = date("YmdHis", time());

            //交易结束时间(最短失效时间间隔必须大于5分钟)
            $params['time_expire'] = Carbon::now()->addMinutes(Yii::$app->params['weixinPayTimeExpire'])->format("YmdHis");

            //订单优惠标记
            $params['goods_tag'] = "";

            //通知地址
            $params['notify_url'] = Yii::$app->urlManager->createAbsoluteUrl('wxpay/pay-notify');;

            //交易类型
            $params['trade_type'] = "APP";

            //支付方式限制
            $params['limit_pay'] = "";

            //场景信息
            $params['scene_info'] = "";

            //签名类型
            $params['sign_type'] = "MD5";

            //签名
            $params['sign'] = $this->generateSign($params);
            $xml = $this->paramsToXml($params);

            //POST提交
            $client = new Client();
            $response = $client->post(
                Yii::$app->params['weixinPayUnifiedorderUrl'],
                array(
                    'body' => $xml
                )
            );

            //处理响应结果
            $ret = array();
            if (200 == $response->getStatusCode()) {

                $body = $response->getBody();
                $stringBody = (string)$body;

//            <xml>
//                <return_code><![CDATA[FAIL]]></return_code>
//                <return_msg><![CDATA[签名错误]]></return_msg>
//            </xml>
//
//            <xml>
//                <return_code><![CDATA[SUCCESS]]></return_code>
//                <return_msg><![CDATA[OK]]></return_msg>
//                <appid><![CDATA[wx23f40d0badebdb9a]]></appid>
//                <mch_id><![CDATA[1488212012]]></mch_id>
//                <device_info><![CDATA[1111]]></device_info>
//                <nonce_str><![CDATA[H3gY3HULT7tYf83Y]]></nonce_str>
//                <sign><![CDATA[A20DB45B9891B4FB8D4F7D97CA86501A]]></sign>
//                <result_code><![CDATA[SUCCESS]]></result_code>
//                <prepay_id><![CDATA[wx201709032138589ee8dab0030453506984]]></prepay_id>
//                <trade_type><![CDATA[APP]]></trade_type>
//            </xml>

                $resObj = simplexml_load_string($stringBody, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
                if (is_object($resObj)) {

                    if (0 == strcmp("SUCCESS", $resObj->return_code)) {

                        //https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=8_5
                        //商户服务器生成支付订单，先调用【统一下单API】生成预付单，获取到prepay_id后将参数再次签名传输给APP发起支付。以下是调起微信支付的关键代码：
                        //参与签名的字段名为appId，partnerId，prepayId，nonceStr，timeStamp，package
                        $resParams['appId'] = (string)$resObj->appid;
                        $resParams['partnerId'] = (string)$resObj->mch_id;
                        $resParams['prepayId'] = (string)$resObj->prepay_id;
                        $resParams['nonceStr'] = (string)$resObj->nonce_str;
                        $resParams['timeStamp'] = time();
                        $resParams['package'] = "Sign=WXPay";
                        //二次签名
                        $resParams['sign'] = $this->generateSign($resParams);

                        $ret['status'] = 200;
                        $ret['message'] = (string)$resObj->return_msg;
                        $ret['data'] = $resParams;

                    } else {

                        $ret['status'] = 500;
                        $ret['message'] = (string)$resObj->return_msg;
                        $ret['data'] = array();
                    }
                } else {

                    $ret['status'] = 500;
                    $ret['message'] = "xml解析失败";
                    $ret['data'] = array();
                }
            } else {

                $ret['status'] = $response->getStatusCode();
                $ret['message'] = $response->getReasonPhrase();
                $ret['data'] = array();
            }
        } else {
            $ret['status'] = 400;
            $ret['message'] = '用户不存在';
        }

        return $ret;
    }


    /**
     * 支付结果通知(接收微信支付异步通知回调地址,通知url必须为直接可访问的url)
     * @see https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_7&index=3
     */
    public function actionPayNotify()
    {

//        $body = Yii::$app->request->getRawBody();
        $xml = <<<EOF
<xml>
  <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
  <attach><![CDATA[支付测试]]></attach>
  <bank_type><![CDATA[CFT]]></bank_type>
  <fee_type><![CDATA[CNY]]></fee_type>
  <is_subscribe><![CDATA[Y]]></is_subscribe>
  <mch_id><![CDATA[10000100]]></mch_id>
  <nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>
  <openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid>
  <out_trade_no><![CDATA[1409811653]]></out_trade_no>
  <result_code><![CDATA[SUCCESS]]></result_code>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <sign><![CDATA[B552ED6B279343CB493C5DD0D78AB241]]></sign>
  <sub_mch_id><![CDATA[10000100]]></sub_mch_id>
  <time_end><![CDATA[20140903131540]]></time_end>
  <total_fee>1</total_fee>
  <trade_type><![CDATA[JSAPI]]></trade_type>
  <transaction_id><![CDATA[1004400740201409030005092168]]></transaction_id>
</xml>

EOF;
        $postObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        $postParams['appid'] = (string)$postObj->appid;
        $postParams['attach'] = (string)$postObj->attach;
        $postParams['bank_type'] = (string)$postObj->bank_type;
        $postParams['fee_type'] = (string)$postObj->fee_type;
        $postParams['is_subscribe'] = (string)$postObj->is_subscribe;
        $postParams['mch_id'] = (string)$postObj->mch_id;
        $postParams['nonce_str'] = (string)$postObj->nonce_str;
        $postParams['openid'] = (string)$postObj->openid;
        $postParams['out_trade_no'] = (string)$postObj->out_trade_no;
        $postParams['result_code'] = (string)$postObj->result_code;
        $postParams['sub_mch_id'] = (string)$postObj->sub_mch_id;
        $postParams['time_end'] = (string)$postObj->time_end;
        $postParams['total_fee'] = (string)$postObj->total_fee;
        $postParams['trade_type'] = (string)$postObj->trade_type;
        $postParams['transaction_id'] = (string)$postObj->transaction_id;
        $postParams['return_code'] = (string)$postObj->return_code;

//        var_dump($postParams);

        $generatePostSign = $this->generateSign($postParams);
        $postSign = (string)$postObj->sign;

        //获取uid
        $uid = 0;
        $attachQueryStr = urldecode($postParams['attach']);
        if (!empty($attachQueryStr)) {
            parse_str($attachQueryStr, $attach);
            $uid = $attach['uid'];
        }

        //签名验证
        //TODO:校验返回的订单金额是否与商户侧的订单金额一致
        if (0 == strcmp($generatePostSign, $postSign)) {
            $resParams['return_code'] = "SUCCESS";
            $resParams['return_msg'] = "OK";

        } else {

            $resParams['return_code'] = "FAIL";
            //签名失败
            //参数格式校验错误
            $resParams['return_msg'] = "签名失败";
        }

        $resXml = $this->paramsToXml($resParams);
        echo $resXml;
    }


    /**
     * 查询订单
     * @see https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_2&index=4
     * @param $transactionId 微信订单号
     * @param string $outTradeNo 商户订单号
     * @return array
     */
    public function actionOrderQuery($transactionId, $outTradeNo = '')
    {
        $userModel = Yii::$app->user->identity;
        if (!is_null($userModel)) {
            $uid = $userModel->uid;
            if (!empty($transactionId) && !empty($outTradeNo)) {

                $params['appid'] = Yii::$app->params['weixinPayAppId'];
                $params['mch_id'] = Yii::$app->params['weixinPayMchId'];
                //二选一
                //微信的订单号，优先使用
                if (!empty($transactionId)) {
                    $params['transaction_id'] = $transactionId;
                } else {
                    $params['out_trade_no'] = $outTradeNo;
                }
                $params['nonce_str'] = Yii::$app->security->generateRandomString(6);
                $params['sign'] = $this->generateSign($params);

                $xml = $this->paramsToXml($params);
                //POST提交
                $client = new Client();
                $response = $client->post(
                    Yii::$app->params['weixinPayOrderQueryUrl'],
                    array(
                        'body' => $xml
                    )
                );

                //处理响应结果
                $ret = array();
                if (200 == $response->getStatusCode()) {

                    $body = $response->getBody();
                    $stringBody = (string)$body;
                    $resObj = simplexml_load_string($stringBody, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
                    if (is_object($resObj)) {

                        if (0 == strcmp("SUCCESS", $resObj->return_code)) {

                            $resParams['appId'] = (string)$resObj->appid;
                            $resParams['mch_id'] = (string)$resObj->mch_id;
                            $resParams['nonceStr'] = (string)$resObj->nonce_str;
                            $resParams['sign'] = (string)$resObj->sign;
                            $resParams['result_code'] = (string)$resObj->result_code;
                            $resParams['err_code'] = (string)$resObj->err_code;
                            $resParams['err_code_des'] = (string)$resObj->err_code_des;

                            if (0 == strcmp("SUCCESS", $resParams['result_code'])) {

                                $resParams['device_info'] = (string)$resObj->device_info;
                                $resParams['openid'] = (string)$resObj->openid;
                                $resParams['is_subscribe'] = (string)$resObj->is_subscribe;
                                $resParams['trade_type'] = (string)$resObj->trade_type;
                                $resParams['trade_state'] = (string)$resObj->trade_state;
                                $resParams['bank_type'] = (string)$resObj->bank_type;
                                $resParams['total_fee'] = (string)$resObj->total_fee;

                                $resParams['fee_type'] = (string)$resObj->fee_type;
                                $resParams['cash_fee'] = (string)$resObj->cash_fee;
                                $resParams['cash_fee_type'] = (string)$resObj->cash_fee_type;
                                $resParams['settlement_total_fee'] = (string)$resObj->settlement_total_fee;
                                $resParams['coupon_fee'] = (string)$resObj->coupon_fee;
                                $resParams['coupon_count'] = (string)$resObj->coupon_count;
                                //TODO:里面有$符
//                            $resParams['coupon_id_$n'] = (string)$resObj->coupon_id_$n;
//                            $resParams['coupon_type_$n'] = (string)$resObj->coupon_type_$n;
//                            $resParams['coupon_fee_$n'] = (string)$resObj->coupon_fee_$n;

                                $resParams['transaction_id'] = (string)$resObj->transaction_id;
                                $resParams['out_trade_no'] = (string)$resObj->out_trade_no;
                                $resParams['attach'] = (string)$resObj->attach;
                                $resParams['time_end'] = (string)$resObj->time_end;
                                $resParams['trade_state_desc'] = (string)$resObj->trade_state_desc;

                                //结果返回至App(将微信返回的数据全部返回至客户端)
                                //TODO:服务器存储的数据修改
                                $ret['status'] = 200;
                                $ret['message'] = (string)$resObj->return_msg;
                                $ret['data'] = $resParams;
                            } else {

                                $ret['status'] = 500;
                                $ret['message'] = (string)$resObj->err_code_des;
                                $ret['data'] = $resParams;
                            }

                        } else {

                            $ret['status'] = 500;
                            $ret['message'] = (string)$resObj->return_msg;
                            $ret['data'] = array();
                        }
                    } else {

                        $ret['status'] = 500;
                        $ret['message'] = "xml解析失败";
                        $ret['data'] = array();
                    }

                } else {

                    $ret['status'] = $response->getStatusCode();
                    $ret['message'] = $response->getReasonPhrase();
                    $ret['data'] = array();
                }
            } else {

                $ret['status'] = 400;
                $ret['message'] = "transactionId(或)outTradeNo不能为空";
                $ret['data'] = array();
            }
        } else {
            $ret['status'] = 400;
            $ret['message'] = '用户不存在';
        }
        return $ret;
    }

    /**
     * 关闭订单
     * 以下情况需要调用关单接口：商户订单支付失败需要生成新单号重新发起支付，要对原订单号调用关单，避免重复支付；
     * 系统下单后，用户支付超时，系统退出不再受理，避免用户继续，请调用关单接口。
     * 注意：订单生成后不能马上调用关单接口，最短调用时间间隔为5分钟。
     * @see https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_3&index=5
     * @param $outTradeNo
     * @return array
     */
    public function actionCloseOrder($outTradeNo)
    {

        $userModel = Yii::$app->user->identity;
        if (!is_null($userModel)) {
            $uid = $userModel->uid;
            if (!empty($outTradeNo)) {

                $params['appid'] = Yii::$app->params['weixinPayAppId'];
                $params['mch_id'] = Yii::$app->params['weixinPayMchId'];
                $params['out_trade_no'] = $outTradeNo;
                $params['nonce_str'] = Yii::$app->security->generateRandomString(6);
                $params['sign'] = $this->generateSign($params);

                $xml = $this->paramsToXml($params);
                //POST提交
                $client = new Client();
                $response = $client->post(
                    Yii::$app->params['weixinPayOrderCloseUrl'],
                    array(
                        'body' => $xml
                    )
                );

                //处理响应结果
                $ret = array();
                if (200 == $response->getStatusCode()) {

                    $body = $response->getBody();
                    $stringBody = (string)$body;
                    $resObj = simplexml_load_string($stringBody, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
                    if (is_object($resObj)) {

                        if (0 == strcmp("SUCCESS", $resObj->return_code)) {

                            $resParams['appId'] = (string)$resObj->appid;
                            $resParams['mch_id'] = (string)$resObj->mch_id;
                            $resParams['nonceStr'] = (string)$resObj->nonce_str;
                            $resParams['sign'] = (string)$resObj->sign;
                            $resParams['result_code'] = (string)$resObj->result_code;
                            $resParams['err_code'] = (string)$resObj->err_code;
                            $resParams['err_code_des'] = (string)$resObj->err_code_des;

                            if (0 == strcmp("SUCCESS", $resParams['result_code'])) {

                                //TODO:服务器端关闭订单
                                $ret['status'] = 200;
                                $ret['message'] = (string)$resObj->return_msg;
                                $ret['data'] = $resParams;

                            } else {

                                //TODO:服务器端处理错误
                                //TODO:见下面文档中 错误码
                                //  https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=61
                                $ret['status'] = 500;
                                $ret['message'] = (string)$resObj->err_code_des;
                                $ret['data'] = $resParams;
                            }

                        } else {

                            $ret['status'] = 500;
                            $ret['message'] = (string)$resObj->return_msg;
                            $ret['data'] = array();
                        }
                    } else {

                        $ret['status'] = 500;
                        $ret['message'] = "xml解析失败";
                        $ret['data'] = array();
                    }

                } else {

                    $ret['status'] = $response->getStatusCode();
                    $ret['message'] = $response->getReasonPhrase();
                    $ret['data'] = array();
                }

            } else {

                $ret['status'] = 400;
                $ret['message'] = "outTradeNo不能为空";
                $ret['data'] = array();
            }
        } else {
            $ret['status'] = 400;
            $ret['message'] = '用户不存在';
        }
        return $ret;
    }


    /**
     * 生成签名
     * @see https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=4_3
     * @param $params
     * @return string
     */
    public function generateSign($params)
    {

        //参数名ASCII码从小到大排序（字典序）
        ksort($params);
        //如果参数的值为空不参与签名；
        ArrayHelper::removeValue($params, "");
        $paramsStr = urldecode(http_build_query($params));
        $weixinPayKeyValue = Yii::$app->params['weixinPayKey'];
        //在string后加入KEY
        $stringSignTemp = $paramsStr . "&key=" . $weixinPayKeyValue;
        $hashMd5 = md5($stringSignTemp);
        $sign = strtoupper($hashMd5);
        return $sign;
    }

    /**
     * 生成商户订单号
     * 当前系统时间加随机4位序列生成订单号
     * @see https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=4_2
     * @return string
     */
    public function generateOutTradeNo()
    {

        $outTradeNo = date("YmdHis") . rand(1111, 9999);
        return $outTradeNo;
    }

    /**
     * 输出xml字符
     * @param 参数名称
     * @return string
     */
    public function paramsToXml($params)
    {

        $xml = "<xml>";
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $key => $val) {
                if (is_numeric($val)) {
                    $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
                } else {
                    $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
                }
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
}
