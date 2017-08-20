<?php

namespace api\controllers;

use DefaultAcsClient;
use DefaultProfile;
use Sts\Request\V20150401\AssumeRoleRequest;
use Yii;
use yii\rest\ActiveController;

class StsController extends ActiveController
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

    /**
     * 移动端获取oss Token
     * @return array
     */
    public function actionToken() {

        include_once Yii::$app->vendorPath.'/sts-server/aliyun-php-sdk-core/Config.php';
        function read_file($fname)
        {
            $content = '';
            if (!file_exists($fname)) {
//                echo "The file $fname does not exist\n";
//                exit (0);
                return 0;
            }
            $handle = fopen($fname, "rb");
            while (!feof($handle)) {
                $content .= fread($handle, 10000);
            }
            fclose($handle);
            return $content;
        }

        //配置文件说明
        //https://help.aliyun.com/document_detail/31920.html?spm=5176.product31815.6.623.KYJRp1
        $configJson = Yii::$app->vendorPath.'/sts-server/config.json';
        $content = read_file($configJson);
        if(0 !== $content) {

            $myjsonarray = json_decode($content);
            $accessKeyID = $myjsonarray->AccessKeyID;
            $accessKeySecret = $myjsonarray->AccessKeySecret;
            $roleArn = $myjsonarray->RoleArn;
            $tokenExpire = $myjsonarray->TokenExpireTime;

            $policyFile = Yii::$app->vendorPath.'/sts-server/'.$myjsonarray->PolicyFile;
            $policy = read_file($policyFile);
            if(0 !== $policy) {
                $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $accessKeyID, $accessKeySecret);
                $client = new DefaultAcsClient($iClientProfile);

                $request = new AssumeRoleRequest();
                $request->setRoleSessionName("external-username");
                $request->setRoleArn($roleArn);
                $request->setPolicy($policy);
                $request->setDurationSeconds($tokenExpire);
                $response = $client->doAction($request);
//                var_dump($response);
//                exit;

                $rows = array();
                $body = $response->getBody();
                $content = json_decode($body);
                $rows['status'] = $response->getStatus();

                if ($response->getStatus() == 200)
                {
                    $rows['AccessKeyId'] = $content->Credentials->AccessKeyId;
                    $rows['AccessKeySecret'] = $content->Credentials->AccessKeySecret;
                    $rows['Expiration'] = $content->Credentials->Expiration;
                    $rows['SecurityToken'] = $content->Credentials->SecurityToken;
                }
                else
                {
                    $rows['AccessKeyId'] = "";
                    $rows['AccessKeySecret'] = "";
                    $rows['Expiration'] = "";
                    $rows['SecurityToken'] = "";
                }

//                echo json_encode($rows);
//                exit;

                $ret['status'] = $rows['status'];
                if($rows['status'] == 200) {
                    $ret['message'] = "OK";
                }else{
                    $ret['message'] = "Code : " . $content->Code . " ; " . "Message : " . $content->Message;
                }

                $ret['data']['AccessKeyId'] = $rows['AccessKeyId'];
                $ret['data']['AccessKeySecret'] = $rows['AccessKeySecret'];
                $ret['data']['Expiration'] = $rows['Expiration'];
                $ret['data']['SecurityToken'] = $rows['SecurityToken'];

            }else {
                $ret['status'] = 500;
                $ret['message'] = $policy ." 文件不存在";
            }
        }else {
            $ret['status'] = 500;
            $ret['message'] = $configJson ." 文件不存在";
        }
        return $ret;
    }
}
