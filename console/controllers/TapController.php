<?php
/**
 * Created by PhpStorm.
 * User: gaoyong
 * Date: 2017/7/25
 * Time: 下午12:28
 *
 * 参考文档:
 *  http://www.yiiframework.com/doc-2.0/guide-tutorial-console.html
 *  http://www.yiichina.com/doc/guide/2.0/tutorial-console
 */

//增加用户,故事点击数
//从mnsQueueTapsIncreaseName队列中,读出需要增加点击数的用户,故事,点击数后,更新用户及故事表中的点击数

namespace console\controllers;

use common\components\MnsQueue;
use common\models\Story;
use common\models\User;
use Yii;
use yii\base\Exception;
use yii\console\Controller;

class TapController extends Controller
{
    public $message;

    //暂时不使用
    public function options()
    {
        return ['message'];
    }

    //暂时不使用
    public function optionAliases()
    {
        return ['m' => 'message'];
    }

    /**
     * 接收用户点击消息
     * @see https://stackoverflow.com/questions/41427048/yii2-console-command-pass-arguments-with-name
     */
    public function actionReceiveMessage()
    {

        //单进程-进程锁处理
        $lock_file = dirname(__FILE__) . "/tap-receive-message.lock";
        $lock_file_handle = fopen($lock_file, 'w');
        if ($lock_file_handle === false)
            die("Can not create lock file {$lock_file}\n");
        if (!flock($lock_file_handle, LOCK_EX + LOCK_NB)) {
            die(date("Y-m-d H:i:s") . "[tap/receive-message] Process already exists.\n");
        }

        //接收消息
        $mnsQueue = new MnsQueue();
        $queueName = Yii::$app->params['mnsQueueTapsIncreaseName'];
        while (true) {
            $data = $mnsQueue->receiveMessage($queueName);
            $messageBody = $data['messageBody'];
            $receiptHandle = $data['receiptHandle'];
            $messageBody = \GuzzleHttp\json_decode($messageBody, true);
            if (!empty($messageBody) && is_array($messageBody)) {

                switch ($messageBody['action']) {

                    //增加用户,故事点击数
                    case "taps_increase":
                        $uid = $messageBody['data']['uid'];
                        $storyId = $messageBody['data']['story_id'];
                        $taps = $messageBody['data']['taps'];
                        $ret = $this->receiveTapsIncrease($uid, $storyId, $taps);
                        if($ret) {
                            $mnsQueue->deleteMessage($receiptHandle,$queueName);
                        }
                        break;
                }
            }
        }
    }

    /**
     * 处理队列消息,增加用户,故事点击数
     * @param $uid
     * @param $storyId
     * @param $taps
     * @return bool
     */
    public function receiveTapsIncrease($uid,$storyId,$taps) {

        $userCondition = array(
            'uid' => $uid,
        );
        $userModel = User::find()->where($userCondition)->one();

        $storyCondition = array(
            'story_id' => $storyId,
        );
        $storyModel = Story::find()->where($storyCondition)->one();
        if(!is_null($userModel) && !is_null($storyModel) && !empty($taps)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {

                $userModel->taps = $userModel->taps + $taps;
                $isUserSaved = $userModel->save(false,['taps']);
                if(!$isUserSaved) {
                    //TODO:这里应该把getErrors抛出去,在外部接收然后在记录
                    Yii::error($userModel->getErrors());
                    throw new Exception('增加用户点击数保存失败');
                }

                $storyModel->taps = $storyModel->taps + $taps;
                $isStorySaved = $storyModel->save(false,['taps']);
                if(!$isStorySaved) {
                    Yii::error($storyModel->getErrors());
                    throw new Exception('增加故事点击数保存失败');
                }
                $transaction->commit();
                return true;

            }catch (Exception $e) {

                $transaction->rollBack();
                $error = $e->getMessage();  //获取抛出的错误
                Yii::error($error);
                return false;
            }
        }
    }
}