<?php
/**
 * Created by PhpStorm.
 * User: gaoyong
 * Date: 2017/7/31
 * Time: 下午2:38
 */

namespace common\components;

use AliyunMNS\Client;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\SendMessageRequest;
use Yii;
use yii\base\Component;

require(Yii::$app->vendorPath.'/aliyun-mns-php-sdk/mns-autoloader.php');

class MnsQueue extends Component
{
    private $accessId;
    private $accessKey;
    private $endPoint;
    private $client;

    public function __construct($accessId="", $accessKey="", $endPoint="")
    {
        $this->accessId = !empty($accessId) ? $accessId : Yii::$app->params['mnsAccessKeyId'];
        $this->accessKey = !empty($accessKey) ? $accessKey : Yii::$app->params['mnsAccessKeySecret'];
        $this->endPoint = !empty($endPoint) ? $endPoint :  Yii::$app->params['mnsEndpoint'];
        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);
    }


    public function sendMessage($messageBody,$queueName) {

//        echo "sendMessage Run!!! \n";
//        echo "messageBody : " . $messageBody . "\n";

        $queue = $this->client->getQueueRef($queueName);
        // 2. send message
//        $messageBody = "test";
        // as the messageBody will be automatically encoded
        // the MD5 is calculated for the encoded body
//        $bodyMD5 = md5(base64_encode($messageBody));
        $request = new SendMessageRequest($messageBody);
        try
        {
            //SendMessageResponse: containing the messageId and bodyMD5
            $res = $queue->sendMessage($request);
//            echo "MessageSent! \n";
            return true;
        }
        catch (MnsException $e)
        {
            $msg = "SendMessage Failed: " . $e;
            Yii::error($msg);
            return false;
        }
    }

    /**
     * 接收队列消息
     * @param $queueName
     * @return array|bool $data['messageBody']:消息体,$data['receiptHandle']:消息临时句柄
     * @see https://help.aliyun.com/document_detail/27477.html
     */
    public function receiveMessage($queueName) {

        $data = array();
        $queue = $this->client->getQueueRef($queueName);
        // 3. receive message
        $receiptHandle = NULL;
        try
        {
            // when receiving messages, it's always a good practice to set the waitSeconds to be 30.
            // it means to send one http-long-polling request which lasts 30 seconds at most.
            $res = $queue->receiveMessage(30);
//            echo "ReceiveMessage Succeed! \n";
//            if (strtoupper($bodyMD5) == $res->getMessageBodyMD5())
//            {
                $messageBody =  $res->getMessageBody();
//            echo "messageBody : " . $messageBody . "\n";

//            }
            $receiptHandle = $res->getReceiptHandle();
            $data['messageBody'] = $messageBody;
            $data['receiptHandle'] = $receiptHandle;
            return $data;
        }
        catch (MnsException $e)
        {
            $msg =  "ReceiveMessage Failed: " . $e;
            Yii::error($msg);
            return false;
        }
    }

    public function deleteMessage($receiptHandle,$queueName) {

        //队列名称默认值
        if(empty($queueName)) {
            $queueName = Yii::$app->params['mnsQueueNotifyName'];
        }

        $queue = $this->client->getQueueRef($queueName);
        // 4. delete message
        try
        {
            $res = $queue->deleteMessage($receiptHandle);
//            echo "DeleteMessage Succeed! \n";
            return true;
        }
        catch (MnsException $e)
        {
            $msg = "DeleteMessage Failed: " . $e;
            Yii::error($msg);
            return false;
        }
    }
}