<?php
/**
 * Created by PhpStorm.
 * User: gaoyong
 * Date: 2017/7/31
 * Time: 下午12:24
 */

/**
 * 备注：如果sender相同,
 */

namespace console\controllers;

use common\components\MnsQueue;
use common\components\NotifyMessageHelper;
use common\models\Chapter;
use common\models\Comment;
use common\models\Story;
use common\models\User;
use common\models\UserNotify;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class NotifyController extends Controller
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
        $queueName = Yii::$app->params['mnsQueueNotifyName'];
        $isDeleteReceivedMessage = true;
        while (true) {

            $messageBody = $mnsQueue->receiveMessage($isDeleteReceivedMessage, $queueName);
            $messageBody = \GuzzleHttp\json_decode($messageBody,true);
            if (!empty($messageBody) && is_array($messageBody)) {

                switch ($messageBody['action']) {

                    //我发布故事
                    case "post_story":
                        $uid = $messageBody['data']['uid'];
                        $storyId = $messageBody['data']['story_id'];
                        $this->receivePostStory($uid, $storyId);
                        break;

                    //我发布章节
                    case "post_chapter":
                        $uid = $messageBody['data']['uid'];
                        $storyId = $messageBody['data']['story_id'];
                        $chapterId = $messageBody['data']['chapter_id'];
                        $this->receivePostChapter($uid, $storyId, $chapterId);
                        break;

                    //用户评论故事
                    case "comment_story":

                        $authorUid = $messageBody['data']['author_uid'];
                        $storyId = $messageBody['data']['story_id'];
                        $commentUid = $messageBody['data']['comment_uid'];
                        $commentId = $messageBody['data']['comment_id'];
                        $this->receiveCommentStory($authorUid, $storyId, $commentUid, $commentId);
                        break;

                    //回复评论
                    case "reply_comment":

                        $storyId = $messageBody['data']['story_id'];
                        $commentUid = $messageBody['data']['comment_uid'];
                        $commentId = $messageBody['data']['comment_id'];
                        $replyUid = $messageBody['data']['reply_uid'];
                        $replyId = $messageBody['data']['reply_id'];
                        $this->receiveReplyComment($storyId, $commentUid, $commentId, $replyUid, $replyId);
                        break;

                    //用户对故事点赞
                    case "like_story":

                        $authorUid = $messageBody['data']['author_uid'];
                        $storyId = $messageBody['data']['story_id'];
                        $likeUid = $messageBody['data']['like_uid'];
                        $this->receiveLikeStory($authorUid, $storyId, $likeUid);
                        break;

                    //用户对评论点赞
                    case "like_comment":

                        $commentUid = $messageBody['data']['comment_uid'];
                        $commentId = $messageBody['data']['comment_id'];
                        $likeUid = $messageBody['data']['like_uid'];
                        $this->receiveLikeComment($commentUid, $commentId, $likeUid);
                        break;

                    //用户对回复点赞
                    case "like_reply":

                        $replyUid = $messageBody['data']['reply_uid'];
                        $replyId = $messageBody['data']['reply_id'];
                        $likeUid = $messageBody['data']['like_uid'];
                        $this->receiveLikeReply($replyUid, $replyId, $likeUid);
                        break;
                }
            }

            //删除历史数据
            $validTime = strtotime("-6 month");
            $this->deleteHistoryUserNofity($validTime);
        }
    }

    public function actionSendTest()
    {


        $mnsQueue = new MnsQueue();
        $queueName = Yii::$app->params['mnsQueueNotifyName'];
//        //1)用户发布新故事
//        $uid = 2;
//        $storyId = 2;
//        $messageBody = NotifyMessageHelper::postStory($uid, $storyId);
//        $mnsQueue->sendMessage($messageBody, $queueName);

//        //2)用户发布新章节
//        $uid = 2;
//        $storyId = 2;
//        $chapterId = 1;
//        $messageBody = NotifyMessageHelper::postChapter($uid, $storyId, $chapterId);
//        $mnsQueue->sendMessage($messageBody, $queueName);

        //3)用户评论故事
//        $authorUid = 2;
//        $storyId = 2;
//        $commentUid = 2;
//        $commentId = 2;
//        $messageBody = NotifyMessageHelper::commentStory($authorUid, $storyId, $commentUid, $commentId);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        //4)回复评论
//        $storyId = 2;
//        $commentUid = 2;
//        $commentId = 2;
//        $replyUid = 4;
//        $replyId = 18;
//        $messageBody = NotifyMessageHelper::replyComment($storyId, $commentUid, $commentId, $replyUid, $replyId);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//        //聚合
//        $storyId = 2;
//        $commentUid = 2;
//        $commentId = 2;
//        $replyUid = 5;
//        $replyId = 19;
//        $messageBody = NotifyMessageHelper::replyComment($storyId, $commentUid, $commentId, $replyUid, $replyId);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
        //5)用户对故事点赞
//        $uid = 2;
//        $storyId = 2;
//        $likeUid = 6;
//        $messageBody = NotifyMessageHelper::likeStory($uid, $storyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $uid = 2;
//        $storyId = 2;
//        $likeUid = 7;
//        $messageBody = NotifyMessageHelper::likeStory($uid, $storyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $uid = 2;
//        $storyId = 2;
//        $likeUid = 8;
//        $messageBody = NotifyMessageHelper::likeStory($uid, $storyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $uid = 2;
//        $storyId = 2;
//        $likeUid = 9;
//        $messageBody = NotifyMessageHelper::likeStory($uid, $storyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $uid = 2;
//        $storyId = 2;
//        $likeUid = 10;
//        $messageBody = NotifyMessageHelper::likeStory($uid, $storyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $uid = 2;
//        $storyId = 2;
//        $likeUid = 11;
//        $messageBody = NotifyMessageHelper::likeStory($uid, $storyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
        //6)用户对评论点赞
//        $commentUid = 2;
//        $commentId = 2;
//        $likeUid = 5;
//        $messageBody = NotifyMessageHelper::likeComment($commentUid, $commentId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $commentUid = 2;
//        $commentId = 2;
//        $likeUid = 6;
//        $messageBody = NotifyMessageHelper::likeComment($commentUid, $commentId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $commentUid = 2;
//        $commentId = 2;
//        $likeUid = 7;
//        $messageBody = NotifyMessageHelper::likeComment($commentUid, $commentId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        //7) 用户对回复点赞
//        $replyUid = 4;
//        $replyId = 18;
//        $likeUid = 6;
//        $messageBody = NotifyMessageHelper::likeReply($replyUid, $replyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $replyUid = 4;
//        $replyId = 18;
//        $likeUid = 7;
//        $messageBody = NotifyMessageHelper::likeReply($replyUid, $replyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $replyUid = 4;
//        $replyId = 18;
//        $likeUid = 8;
//        $messageBody = NotifyMessageHelper::likeReply($replyUid, $replyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $replyUid = 4;
//        $replyId = 18;
//        $likeUid = 9;
//        $messageBody = NotifyMessageHelper::likeReply($replyUid, $replyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $replyUid = 4;
//        $replyId = 18;
//        $likeUid = 10;
//        $messageBody = NotifyMessageHelper::likeReply($replyUid, $replyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);
//
//        $replyUid = 4;
//        $replyId = 18;
//        $likeUid = 11;
//        $messageBody = NotifyMessageHelper::likeReply($replyUid, $replyId, $likeUid);
//        $mnsQueue->sendMessage($messageBody, $queueName);

    }

    /**
     * 接收通知-我发布故事
     * 向关注作者的用户发送作者发布新故事的通知
     * @param $uid
     * @param $storyId
     */
    public function receivePostStory($uid, $storyId)
    {

        echo "receivePostStory RUN!!!\n";
        //TODO:获取关注$uid的用户列表,前期用户规模比较小全量分发
        $uidArr = User::find()
            ->select('uid')
            ->where(['status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->all();
        $uidArr = ArrayHelper::getColumn($uidArr,'uid');

        //取作者信息
        //TODO:需要对用户信息做cache
        $userInfo = User::find()
            ->where(['uid' => $uid,'status' => Yii::$app->params['STATUS_ACTIVE'],])
            ->asArray()
            ->one();

        //取故事信息
        //TODO:需要对用户信息做cache
        $storyInfo = Story::find()
            ->where(['story_id' => $storyId,'status' => Yii::$app->params['STATUS_ACTIVE'],])
            ->asArray()
            ->one();

        //故事信息不为空且用户正常
        if(!empty($storyInfo) && !empty($userInfo)) {
            //组合content信息
            $contentParam = array();
            //作者姓名
            $contentParam['username'] = $userInfo['username'];
            //作者头像
            $contentParam['avatar'] = $userInfo['avatar'];
            //故事标题
            $contentParam['story_name'] = $storyInfo['name'];
            //故事封面
            $contentParam['story_cover'] = $storyInfo['cover'];
            $content = \GuzzleHttp\json_encode($contentParam);

            $columns = ['uid', 'category', 'topic_id', 'content', 'senders', 'count', 'is_read'];
            $rows = array();
            $count = 1;
            $isRead = 0;
            foreach ($uidArr as $uidItem) {
                $rows[] = [$uidItem, 'post_story', $storyId, $content, $uid, $count, $isRead];
            }

            //执行批量添加
            try {
                $ret = Yii::$app->db->createCommand()->batchInsert(UserNotify::tableName(), $columns, $rows)->execute();
            } catch (Exception $e) {
                echo "Batchinsert user_notify Failed: " . $e->getMessage();
            }
        }
        return;
    }

    /**
     * 接收通知-我发布新章节
     * 向关注作者的用户发送作者更新新章节的通知
     * @param $uid
     * @param $storyId
     * @param $chapterId
     */
    public function receivePostChapter($uid, $storyId, $chapterId)
    {

        //TODO:获取关注$uid的用户列表,前期用户规模比较小全量分发
        $uidArr = User::find()
            ->select('uid')
            ->where(['status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->all();
        $uidArr = ArrayHelper::getColumn($uidArr,'uid');

        //取作者信息
        //TODO:需要对用户信息做cache
        $userInfo = User::find()
            ->where(['uid' => $uid,'status' => Yii::$app->params['STATUS_ACTIVE'],])
            ->asArray()
            ->one();

        //取故事信息
        //TODO:需要对用户信息做cache
        $storyInfo = Story::find()
            ->where(['story_id' => $storyId,'status' => Yii::$app->params['STATUS_ACTIVE'],])
            ->asArray()
            ->one();

        $chapterInfo = Chapter::find()
            ->where(['story_id' => $storyId, 'chapter_id' => $chapterId,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        if(!empty($storyInfo) && !empty($chapterInfo) && !empty($userInfo['status'])) {

            //组合content信息
            $contentParam = array();
            //作者姓名
            $contentParam['username'] = $userInfo['username'];
            //作者头像
            $contentParam['avatar'] = $userInfo['avatar'];
            //故事id
            $contentParam['story_id'] = $storyInfo['story_id'];
            //故事标题
            $contentParam['story_name'] = $storyInfo['name'];
            //故事封面
            $contentParam['story_cover'] = $storyInfo['cover'];
            //章节名称
            if(!empty($chapterInfo['name'])) {
                $contentParam['chapter_name'] = $chapterInfo['name'];
            }else {
                $contentParam['chapter_name'] = "第".$chapterInfo['number']."章";
            }

            $content = \GuzzleHttp\json_encode($contentParam);

            $columns = ['uid', 'category', 'topic_id', 'content', 'senders', 'count', 'is_read'];
            $rows = array();
            $count = 1;
            $isRead = 0;
            foreach ($uidArr as $uid) {
                $rows[] = [$uid, 'post_chapter', $chapterId, $content, $uid, $count, $isRead];
            }

            //执行批量添加
            try {
                $ret = Yii::$app->db->createCommand()->batchInsert(UserNotify::tableName(), $columns, $rows)->execute();
            } catch (Exception $e) {
                echo "receivePostChapter user_notify Failed: " . $e->getMessage();
            }
        }
        return;
    }


    /**
     * 接收通知-用户评论故事
     * 向故事作者发送故事被评论的通知(有聚合处理)
     * @param $authorUid
     * @param $storyId
     * @param $commentUid
     * @param $commentId
     */
    public function receiveCommentStory($authorUid, $storyId, $commentUid, $commentId)
    {

        //取作者信息
        //TODO:需要对用户信息做cache
        $commentUserInfo = User::find()
            ->where(['uid' => $commentUid,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        //取故事信息
        //TODO:需要对故事信息做cache
        $storyInfo = Story::find()
            ->where(['story_id' => $storyId,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        //取评论信息
        //TODO:还需要处理评论被删除的情况
        $commentInfo = Comment::find()
            ->where(['comment_id' => $commentId,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        if(!empty($commentUserInfo) && !empty($storyInfo) && !empty($commentInfo)) {

            //根据uid=uid,category=评论故事,topic_id=story_id取出uid(我)的通知信息
            $notifyInfoModel = UserNotify::find()
                ->where(['uid' => $authorUid, 'category' => 'comment_story', 'topic_id' => $storyId])
                ->one();

            if (!empty($notifyInfoModel) && 1 != $notifyInfoModel->is_read) {

                //如果is_read=未读,则追加数据
                //发送者处理
                $senders = $this->getFutureSendersData($notifyInfoModel->senders, $commentUid);
                $count = $notifyInfoModel->count + 1;
                $isRead = 0;

                //内容处理
                $content['senders'] = $this->getFutureDataSendersFieldData($notifyInfoModel->content, $commentUserInfo);
                $content = \GuzzleHttp\json_encode($content);

            } else {

                if(empty($notifyInfoModel)) {
                    $notifyInfoModel = new UserNotify();
                }

                //如果is_read=已读,则覆盖数据
                //如果不存在,则增加
                $count = 1;
                $isRead = 0;
                $senders = $commentUid;
                $content['senders'][]['uid'] = $commentUserInfo['uid'];
                $content['senders'][]['username'] = $commentUserInfo['username'];
                $content['senders'][]['avatar'] = $commentUserInfo['avatar'];
            }

            $content['story_name'] = $storyInfo['name'];
            $content['story_cover'] = $storyInfo['cover'];
            $content['comment_id'] = $commentInfo['comment_id'];
            $content['comment_content'] = $commentInfo['content'];
            $content = \GuzzleHttp\json_encode($content);

            //数据保存
            $notifyInfoModel->uid = $authorUid;
            $notifyInfoModel->category = "comment_story";
            $notifyInfoModel->topic_id = $storyId;
            $notifyInfoModel->content = $content;
            $notifyInfoModel->senders = (string)$senders;
            $notifyInfoModel->count = $count;
            $notifyInfoModel->is_read = $isRead;
            $isSaved = $notifyInfoModel->save();
            if (!$isSaved) {
                print_r($notifyInfoModel->getErrors());
                echo "notifyCommentStory save user_notify Fail " ;
            }
        }
        return;
    }

    /**
     * 接收通知-回复评论
     * @param $storyId
     * @param $commentUid
     * @param $commentId
     * @param $replyUid
     * @param $replyId
     */
    public function receiveReplyComment($storyId, $commentUid, $commentId, $replyUid, $replyId)
    {

        $content = array();
        //取回复者用户信息
        //TODO:需要对用户信息做cache
        $replyUserInfo = User::find()
            ->where(['uid' => $replyUid,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        //取评论信息及回复信息
        //TODO:还需要处理评论被删除的情况
        $commentAndReplyInfo = Comment::find()
            ->where(['comment_id' => [$commentId, $replyId],])
            ->asArray()
            ->all();

        $commentAndReplyInfo = ArrayHelper::index($commentAndReplyInfo,'comment_id');

        if(!empty($replyUserInfo) && !empty($commentAndReplyInfo) && count($commentAndReplyInfo) == 2) {

            $parentCommentContent = $commentAndReplyInfo[$commentId]['content'];
            $replyCommentContent = $commentAndReplyInfo[$replyId]['content'];

            //根据uid=$commentUid,category=回复评论,topic_id=$commentId(我)的通知信息
            $notifyInfoModel = UserNotify::find()
                ->where(['uid' => $commentUid, 'category' => 'reply_comment', 'topic_id' => $commentId])
                ->one();

            //评论内容
            $content['parent_comment_content'] = $parentCommentContent;
            if (!empty($notifyInfoModel) && 1 != $notifyInfoModel->is_read) {

                //如果is_read=未读,则追加数据
                //发送者处理
                $senders = $this->getFutureSendersData($notifyInfoModel->senders, $replyUid);
                $count = $notifyInfoModel->count + 1;
                $isRead = 0;
                //发送者
                $content['senders'] = $this->getFutureDataSendersFieldData($notifyInfoModel->content, $replyUserInfo);

            } else {

                if(empty($notifyInfoModel)) {
                    $notifyInfoModel = new UserNotify();
                }
                //如果is_read=已读,则覆盖数据
                //如果不存在,则增加
                $senders = $replyUid;
                $count = 1;
                $isRead = 0;
                //TODO:多个用户的时候,这里感觉有些奇怪
                $sender['uid'] = $replyUserInfo['uid'];
                $sender['username'] = $replyUserInfo['username'];
                $sender['avatar'] = $replyUserInfo['avatar'];
                $content['senders'][] = $sender;
            }

            //当发送者只有一个时:消息中含回复内容
            //当发送者大于一个时:消息中不含回复内容
            if (count($content['senders']) == 1) {
                //回复id
                $content['comment_id'] = $replyId;
                //回复内容
                $content['comment_content'] = $replyCommentContent;
            }
            $content = \GuzzleHttp\json_encode($content);

            //数据保存
            $notifyInfoModel->uid = $commentUid;
            $notifyInfoModel->category = "reply_comment";
            $notifyInfoModel->topic_id = $commentId;
            $notifyInfoModel->content = $content;
            $notifyInfoModel->senders = (string)$senders;
            $notifyInfoModel->count = $count;
            $notifyInfoModel->is_read = $isRead;
            $isSaved = $notifyInfoModel->save();
            if (!$isSaved) {
                print_r($notifyInfoModel->getErrors());
                echo "notifyReplyComment save user_notify Fail";
            }
        }
        return;
    }

    /**
     * 接收通知-用户对故事点赞
     * @param $authorUid
     * @param $storyId
     * @param $likeUid
     */
    public function receiveLikeStory($authorUid, $storyId, $likeUid)
    {

        $content = array();
        //获取点赞用户信息
        //TODO:需要对用户信息做cache
        $likeUserInfo = User::find()
            ->where(['uid' => $likeUid,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        //取故事信息
        //TODO:需要对用户信息做cache
        $storyInfo = Story::find()
            ->where(['story_id' => $storyId,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        if(!empty($likeUserInfo) && !empty($storyInfo)) {

            //根据uid=$authorUid,category=点赞故事,topic_id=story_id取出uid(我)的通知信息
            $notifyInfoModel = UserNotify::find()
                ->where(['uid' => $authorUid, 'category' => 'like_story', 'topic_id' => $storyId])
                ->one();

            if (!empty($notifyInfoModel) && 1 != $notifyInfoModel->is_read) {

                //如果is_read=未读,则追加数据
                //发送者处理
                $senders = $this->getFutureSendersData($notifyInfoModel->senders, $likeUid);
                $count = $notifyInfoModel->count + 1;
                $isRead = 0;
                //发送者
                $content['senders'] = $this->getFutureDataSendersFieldData($notifyInfoModel->content, $likeUserInfo);

            } else {

                if(empty($notifyInfoModel)) {
                    $notifyInfoModel = new UserNotify();
                }
                //如果is_read=已读,则覆盖数据
                //如果不存在,则增加
                $senders = $likeUid;
                $count = 1;
                $isRead = 0;
                //TODO:多个用户的时候,这里感觉有些奇怪
                $sender['uid'] = $likeUserInfo['uid'];
                $sender['username'] = $likeUserInfo['username'];
                $sender['avatar'] = $likeUserInfo['avatar'];
                $content['senders'][] = $sender;
            }

            //故事标题
            $content['story_name'] = $storyInfo['name'];
            //故事封面
            $content['story_cover'] = $storyInfo['cover'];
            $content = \GuzzleHttp\json_encode($content);
            //数据保存
            $notifyInfoModel->uid = $authorUid;
            $notifyInfoModel->category = "like_story";
            $notifyInfoModel->topic_id = $storyId;
            $notifyInfoModel->content = $content;
            $notifyInfoModel->senders = (string)$senders;
            $notifyInfoModel->count = $count;
            $notifyInfoModel->is_read = $isRead;
            $isSaved = $notifyInfoModel->save();
            if (!$isSaved) {
                echo "notifyLikeStory save user_notify Fail";
            }
        }
        return;
    }

    /**
     * 接收通知-用户对评论点赞
     * @param $commentUid
     * @param $commentId
     * @param $likeUid
     */
    public function receiveLikeComment($commentUid, $commentId, $likeUid)
    {

        $content = array();
        //获取点赞用户信息
        //TODO:需要对用户信息做cache
        $likeUserInfo = User::find()
            ->where(['uid' => $likeUid,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        //获取评论信息
        //TODO:还需要处理评论被删除的情况
        $commentInfo = Comment::find()
            ->where(['comment_id' => $commentId,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        if(!empty($likeUserInfo) && !empty($commentInfo)) {

            //根据uid=$commentUid,category=点赞评论,topic_id=$commentId取出uid(我)的通知信息
            $notifyInfoModel = UserNotify::find()
                ->where(['uid' => $commentUid, 'category' => 'like_comment', 'topic_id' => $commentId])
                ->one();

            if (!empty($notifyInfoModel) && 1 != $notifyInfoModel->is_read) {

                //如果is_read=未读,则追加数据
                //发送者处理
                $senders = $this->getFutureSendersData($notifyInfoModel->senders, $likeUid);
                $count = $notifyInfoModel->count + 1;
                $isRead = 0;
                //发送者
                $content['senders'] = $this->getFutureDataSendersFieldData($notifyInfoModel->content, $likeUserInfo);

            } else {

                if(empty($notifyInfoModel)) {
                    $notifyInfoModel = new UserNotify();
                }

                //如果is_read=已读,则覆盖数据
                //如果不存在,则增加
                $senders = $likeUid;
                $count = 1;
                $isRead = 0;
                //TODO:多个用户的时候,这里感觉有些奇怪
                $sender['uid'] = $likeUserInfo['uid'];
                $sender['username'] = $likeUserInfo['username'];
                $sender['avatar'] = $likeUserInfo['avatar'];
                $content['senders'][] = $sender;
            }
            //评论内容
            $content['comment_content'] = $commentInfo['content'];
            $content = \GuzzleHttp\json_encode($content);
            //数据保存
            $notifyInfoModel->uid = $commentUid;
            $notifyInfoModel->category = "like_comment";
            $notifyInfoModel->topic_id = $commentId;
            $notifyInfoModel->content = $content;
            $notifyInfoModel->senders = (string)$senders;
            $notifyInfoModel->count = $count;
            $notifyInfoModel->is_read = $isRead;
            $isSaved = $notifyInfoModel->save();
            if (!$isSaved) {
                echo "notifyLikeComment save user_notify Fail";
            }
        }
        return;
    }

    /**
     * 接收通知-用户对回复点赞
     * @param $replyUid
     * @param $replyId
     * @param $likeUid
     */
    public function receiveLikeReply($replyUid, $replyId, $likeUid)
    {

        $content = array();
        //获取点赞用户信息
        //TODO:需要对用户信息做cache
        $likeUserInfo = User::find()
            ->where(['uid' => $likeUid,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        //获取回复信息
        //TODO:还需要处理评论被删除的情况
        $replyInfo = Comment::find()
            ->where(['comment_id' => $replyId,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->asArray()
            ->one();

        if(!empty($likeUserInfo) && !empty($replyInfo)) {

            //根据uid=$commentUid,category=点赞评论,topic_id=$commentId取出uid(我)的通知信息
            $notifyInfoModel = UserNotify::find()
                ->where(['uid' => $replyUid, 'category' => 'like_reply', 'topic_id' => $replyId])
                ->one();

            if (!empty($notifyInfoModel) && 1 != $notifyInfoModel->is_read) {

                //如果is_read=未读,则追加数据
                //发送者处理
                $senders = $this->getFutureSendersData($notifyInfoModel->senders, $likeUid);
                $count = $notifyInfoModel->count + 1;
                $isRead = 0;
                //发送者
                $content['senders'] = $this->getFutureDataSendersFieldData($notifyInfoModel->content, $likeUserInfo);

            } else {

                if(empty($notifyInfoModel)) {

                    $notifyInfoModel = new UserNotify();
                }

                //如果is_read=已读,则覆盖数据
                //如果不存在,则增加
                $senders = $likeUid;
                $count = 1;
                $isRead = 0;
                //TODO:多个用户的时候,这里感觉有些奇怪
                $sender['uid'] = $likeUserInfo['uid'];
                $sender['username'] = $likeUserInfo['username'];
                $sender['avatar'] = $likeUserInfo['avatar'];
                $content['senders'][] = $sender;
            }
            //回复内容
            $content['comment_content'] = $replyInfo['content'];
            $content = \GuzzleHttp\json_encode($content);
            //数据保存
            $notifyInfoModel->uid = $replyUid;
            $notifyInfoModel->category = "like_reply";
            $notifyInfoModel->topic_id = $replyId;
            $notifyInfoModel->content = $content;
            $notifyInfoModel->senders = (string)$senders;
            $notifyInfoModel->count = $count;
            $notifyInfoModel->is_read = $isRead;
            $isSaved = $notifyInfoModel->save();
            if (!$isSaved) {
                echo "notifyLikeReply save user_notify Fail";
            }
        }
        return;
    }

    /**
     * 根据当前的发送者得到未来的消息发送者列表数据
     * @param $currentSenders
     * @param $senderUid
     * @return array|string
     */
    private function getFutureSendersData($currentSenders, $senderUid)
    {

        //发送者处理
        $senders = explode(",", $currentSenders);
        if (count($senders) >= 4) {
            $senders = array_slice($senders, 0, 3);
        }
        array_unshift($senders, $senderUid);
        $senders = implode(",", $senders);
        return $senders;
    }

    /**
     * 根据当前的发送者得到未来消息内容的senders字段数据
     * @param $content
     * @param $senderInfo
     * @return max
     */
    private function getFutureDataSendersFieldData($content, $senderInfo)
    {

        $content = \GuzzleHttp\json_decode($content,true);
        //senders最多4个
        $sendersArr = $content['senders'];
        $sendersItem = array(
            'uid' => $senderInfo['uid'],
            'username' => $senderInfo['username'],
            'avatar' => $senderInfo['avatar'],
        );

        if (count($sendersArr) >= 4) {
            $sendersArr = array_slice($sendersArr, 0, 3);
        }
        array_unshift($sendersArr, $sendersItem);
        return $sendersArr;
    }

    /**
     * 删除用户历史通知
     * @param $validTime
     */
    private function deleteHistoryUserNofity($validTime) {

        $deletedRows = UserNotify::deleteAll('create_time < :validTime', [':validTime' => $validTime]);
    }
}

?>