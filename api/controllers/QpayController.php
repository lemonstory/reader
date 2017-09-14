<?php

namespace api\controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;

class QpayController extends ActiveController
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
     * @see https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=58
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
            $params['appid'] = Yii::$app->params['qpayAppId'];

            //商户号
            $params['mch_id'] = Yii::$app->params['qpayMchId'];

            //随机字符串(随机字符串，不长于32位)
            $params['nonce_str'] = Yii::$app->security->generateRandomString(6);

            //商品描述
            $params['body'] = $body;

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
            $params['time_expire'] = Carbon::now()->addMinutes(Yii::$app->params['qpayTimeExpire'])->format("YmdHis");

            //支付方式限制
            $params['limit_pay'] = "";

            //代扣签约序列号(商户侧记录的用户代扣协议序列号，支付中开通代扣必)
            $params['contract_code'] = "";

            //QQ钱包活动标识
            $params['promotion_tag'] = "";

            //支付场景
            $params['trade_type'] = "APP";

            //通知地址
            $params['notify_url'] = Yii::$app->urlManager->createAbsoluteUrl('qpay/pay-notify');

            //设备号
            $params['device_info'] = $deviceInfo;

            //签名(签名类型：md5)
            //https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=57
            $params['sign'] = $this->generateSign($params);
            $xml = $this->paramsToXml($params);

            //POST提交
            $client = new Client();
            $response = $client->post(
                Yii::$app->params['qpayUnifiedorderUrl'],
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
//            <return_code><![CDATA[SUCCESS]]></return_code>
//            <return_msg><![CDATA[SUCCESS]]></return_msg>
//            <retcode><![CDATA[0]]></retcode>
//            <retmsg><![CDATA[ok]]></retmsg>
//            <appid><![CDATA[101405801]]></appid>
//            <mch_id><![CDATA[1488219851]]></mch_id>
//            <nonce_str><![CDATA[4cab398176dfb3c4fff996b5d7365c33]]></nonce_str>
//            <prepay_id><![CDATA[6V62a6ef387814905f21fb6dbbacb381]]></prepay_id>
//            <result_code><![CDATA[SUCCESS]]></result_code>
//            <sign><![CDATA[08D61D9B0358E9FC91013A2521C27227]]></sign>
//            <trade_type><![CDATA[APP]]></trade_type>
//            </xml>

                $resObj = simplexml_load_string($stringBody, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
                if (is_object($resObj)) {

                    if (0 == strcmp("SUCCESS", $resObj->return_code)) {

                        //https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=165
                        $resParams['appid'] = (string)$resObj->appid;
                        $resParams['nonce'] = (string)$resObj->nonce_str;
                        $resParams['timeStamp'] = time();

                        //手Q公众帐号，暂时未对外开放申请。
                        //注：所有参与签名的参数，如果value为空, 生成格式如“pubAcc=”
                        $resParams['pubAcc'] = "";
                        $resParams['pubAccHint'] = "";
                        $resParams['bargainorId'] = (string)$resObj->mch_id;

                        if (0 == strcmp("SUCCESS", $resObj->result_code)) {
                            //prepay_id:QQ钱包的预支付会话标识，用于后续接口调用中使用，该值有效期为2小时
                            $resParams['tokenId'] = (string)$resObj->prepay_id;

                            //数字签名
                            //sig,sigType不参与签名
                            $resParams['sig'] = $this->generateDigitalSign($resParams);
                            $resParams['sigType'] = "HMAC-SHA1"; //不参与签名

                            $ret['status'] = 200;
                            $ret['message'] = (string)$resObj->return_msg;
                            $ret['data'] = $resParams;
                        } else {

                            $ret['status'] = 500;
                            $ret['message'] = (string)$resObj->retmsg;
                            $ret['data'] = array();
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
            $ret['message'] = '用户不存在';
        }
        return $ret;
    }


    /**
     * 支付结果通知(接收微信支付异步通知回调地址,通知url必须为直接可访问的url)
     * 支付完成后，QQ钱包会把相关支付结果和用户信息发送给商户，商户需要接收处理，并返回应答，如果QQ钱包收到商户的应答不是成功或超时，QQ钱包则认为通知失败，会通过一定的策略定期重新发起通知，尽可能提高通知的成功率，但QQ钱包不保证通知最终能成功。
     * @see https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=59
     */
    public function actionPayNotify()
    {

//        $xml = Yii::$app->request->getRawBody();
        $xml = <<<EOF
<xml>
    <appid><![CDATA[1104606907]]></appid>
    <attach><![CDATA[ATTACHEND=&END]]></attach>
    <bank_type><![CDATA[BALANCE]]></bank_type> 
    <cash_fee><![CDATA[1]]></cash_fee>
    <device_info><![CDATA[WP00000001]]></device_info>
    <fee_type><![CDATA[CNY]]></fee_type>
    <mch_id><![CDATA[1900000109]]></mch_id>
    <nonce_str><![CDATA[7b14db232445d79c5c86d22bbd8898d3]]></nonce_str>
    <openid><![CDATA[D60EFFA28D0698EF57CFC9118C149E94]]></openid>
    <out_trade_no><![CDATA[20161025_qpay_unified_order_A]]></out_trade_no>
    <sign><![CDATA[DE4335434F33C065C449E261DCE08BCF]]></sign>
    <time_end><![CDATA[20161025094946]]></time_end>
    <total_fee><![CDATA[1]]></total_fee>
    <trade_state><![CDATA[SUCCESS]]></trade_state>
    <trade_type><![CDATA[NATIVE]]></trade_type>
    <transaction_id><![CDATA[1900000109471610251307259064]]></transaction_id>
</xml>


EOF;
        $postObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        $postParams['appid'] = (string)$postObj->appid;
        $postParams['attach'] = (string)$postObj->attach;
        $postParams['bank_type'] = (string)$postObj->bank_type;
        $postParams['cash_fee'] = (string)$postObj->cash_fee;
        $postParams['device_info'] = (string)$postObj->device_info;
        $postParams['fee_type'] = (string)$postObj->fee_type;
        $postParams['mch_id'] = (string)$postObj->mch_id;
        $postParams['nonce_str'] = (string)$postObj->nonce_str;
        $postParams['openid'] = (string)$postObj->openid;
        $postParams['out_trade_no'] = (string)$postObj->out_trade_no;
        $postParams['time_end'] = (string)$postObj->time_end;
        $postParams['total_fee'] = (string)$postObj->total_fee;
        $postParams['trade_state'] = (string)$postObj->trade_state;
        $postParams['trade_type'] = (string)$postObj->trade_type;
        $postParams['transaction_id'] = (string)$postObj->transaction_id;
        //var_dump($postParams);
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
            $resParams['return_msg'] = "";

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
     * @see https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=60
     * @param $transactionId QQ钱包订单号
     * @param string $outTradeNo 商户订单号
     * @return array
     */
    public function actionOrderQuery($transactionId, $outTradeNo = '')
    {

        $userModel = Yii::$app->user->identity;
        if (!is_null($userModel)) {
            $uid = $userModel->uid;
            if (!empty($transactionId) && !empty($outTradeNo)) {

                $params['appid'] = Yii::$app->params['qpayAppId'];
                $params['mch_id'] = Yii::$app->params['qpayMchId'];
                //二选一
                //QQ钱包订单号，优先使用
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
                    Yii::$app->params['qpayOrderQueryUrl'],
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

                            $resParams['appid'] = (string)$resObj->appid;
                            $resParams['mch_id'] = (string)$resObj->mch_id;
                            $resParams['sign'] = (string)$resObj->sign;
                            $resParams['result_code'] = (string)$resObj->result_code;
                            $resParams['err_code'] = (string)$resObj->err_code;
                            $resParams['err_code_des'] = (string)$resObj->err_code_des;
                            $resParams['nonceStr'] = (string)$resObj->nonce_str;

                            if (0 == strcmp("SUCCESS", $resParams['result_code'])) {

                                $resParams['device_info'] = (string)$resObj->device_info;
                                $resParams['trade_type'] = (string)$resObj->trade_type;
                                $resParams['trade_state'] = (string)$resObj->trade_state;
                                $resParams['bank_type'] = (string)$resObj->bank_type;
                                $resParams['fee_type'] = (string)$resObj->fee_type;
                                $resParams['total_fee'] = (string)$resObj->total_fee;
                                $resParams['cash_fee'] = (string)$resObj->cash_fee;
                                $resParams['coupon_fee'] = (string)$resObj->coupon_fee;
                                $resParams['transaction_id'] = (string)$resObj->transaction_id;
                                $resParams['out_trade_no'] = (string)$resObj->out_trade_no;
                                $resParams['attach'] = (string)$resObj->attach;
                                $resParams['time_end'] = (string)$resObj->time_end;
                                $resParams['trade_state_desc'] = (string)$resObj->trade_state_desc;
                                $resParams['openid'] = (string)$resObj->openid;
                            }
                            //结果返回至App(将QQ支付返回的数据全部返回至客户端)
                            //TODO:服务器存储的数据修改
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
     *  以下情况需要调用关单接口：
     *      商户订单支付失败需要生成新单号重新发起支付，要对原订单号调用关单，避免重复支付；
     *      系统下单后，用户支付超时，系统退出不再受理，避免用户继续，请调用关单接口。
     *  注意：
     *      订单生成后不能马上调用关单接口，最短调用时间间隔为5分钟
     * @see https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=61
     * @param 商户订单号|string $outTradeNo
     * @param 订单金额|int $totaFee (可选)
     * @return array
     */
    public function actionCloseOrder($outTradeNo, $totaFee=0)
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
                    Yii::$app->params['qpayOrderCloseUrl'],
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

                            $resParams['appid'] = (string)$resObj->appid;
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
     * 生成签名 - 商户后台与QQ钱包支付后台的签名机制
     * @see https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=57
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
        $qpayKeyValue = Yii::$app->params['qpayKey'];
        //在string后加入KEY
        $stringSignTemp = $paramsStr . "&key=" . $qpayKeyValue;
        $hashMd5 = md5($stringSignTemp);
        $sign = strtoupper($hashMd5);
        return $sign;
    }


    /**
     * 密钥构造 - 数字签名需要
     * @see https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=165
     * @return string
     */
    public function generateSecretKey()
    {

        $secretKey = Yii::$app->params['qpayAppKey'] . "&";
        return $secretKey;
    }


    /**
     * 数字签名-生成签名值方法
     * 商户App调用QQ钱包支付时签名机制
     * @see https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=165
     * @param $params
     * @return string
     */
    public function generateDigitalSign($params)
    {

        //将需要参与签名的所有参数按key进行字典升序排列
        ksort($params);
        //将第1步中排序后的参数(key=value)用&拼接起来
        $paramsStr = urldecode(http_build_query($params));

        //密钥
        $secretKey = $this->generateSecretKey();
        $hash = hash_hmac("sha1", $paramsStr, $secretKey);
        $sign = base64_encode($hash);
        return $sign;
    }

    /**
     * 生成商户订单号
     * 当前系统时间加随机4位序列生成订单号
     * @see https://qpay.qq.com/qpaywiki/showdocument.php?pid=38&docid=56
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
