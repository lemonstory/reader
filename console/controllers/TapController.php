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

    public function actionReceiveMessage()
    {
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