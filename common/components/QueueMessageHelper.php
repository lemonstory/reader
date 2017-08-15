<?php
/**
 * mnsQueue队列处理的消息结构
 * User: gaoyong
 * Date: 2017/7/31
 * Time: 下午4:40
 */
namespace common\components;
use yii\base\Component;

class QueueMessageHelper extends Component
{
    /**
     * 用户发布新故事
     * @param $uid 故事作者Uid
     * @param $storyId 新故事id
     * @return string
     */
    public static function postStory($uid,$storyId) {

        $message = array();
        $message['action'] = "post_story";
        $message['data']['uid'] = $uid;
        $message['data']['story_id'] = $storyId;
        $messageBody = \GuzzleHttp\json_encode($message);
        return $messageBody;
    }

    /**
     * 用户发布新章节
     * @param $uid 故事作者Uid
     * @param $storyId 故事id
     * @param $chapterId 新章节id
     * @return string
     */
    public static function postChapter($uid,$storyId,$chapterId) {

        $message = array();
        $message['action'] = "post_chapter";
        $message['data']['uid'] = $uid;
        $message['data']['story_id'] = $storyId;
        $message['data']['chapter_id'] = $chapterId;
        $messageBody = \GuzzleHttp\json_encode($message);
        return $messageBody;
    }


    /**
     * 用户评论故事
     * @param $authorUid 故事作者Uid
     * @param $storyId 故事id
     * @param $commentUid 发布评论者Uid
     * @param $commentId 新评论id
     * @return string
     */
    public static function commentStory($authorUid,$storyId,$commentUid,$commentId) {

        $message = array();
        $message['action'] = "comment_story";
        $message['data']['author_uid'] = $authorUid;
        $message['data']['story_id'] = $storyId;
        $message['data']['comment_uid'] = $commentUid;
        $message['data']['comment_id'] = $commentId;
        $messageBody = \GuzzleHttp\json_encode($message);
        return $messageBody;
    }


    /**
     * 回复评论
     * @param $storyId 故事id
     * @param $commentUid 评论者Uid
     * @param $commentId 评论id
     * @param $replyUid 发布回复者Uid
     * @param $replyId 新回复id
     * @return string
     */
    public static function replyComment($storyId,$commentUid,$commentId,$replyUid,$replyId) {

        $message = array();
        $message['action'] = "reply_comment";
        $message['data']['story_id'] = $storyId;
        $message['data']['comment_uid'] = $commentUid;
        $message['data']['comment_id'] = $commentId;
        $message['data']['reply_uid'] = $replyUid;
        $message['data']['reply_id'] = $replyId;
        $messageBody = \GuzzleHttp\json_encode($message);
        return $messageBody;
    }

    /**
     * 用户对故事点赞
     * @param $authorUid 故事作者Uid
     * @param $storyId 故事id
     * @param $likeUid 点赞用户Uid
     * @return string
     */
    public static function likeStory($authorUid,$storyId,$likeUid) {

        $message = array();
        $message['action'] = "like_story";
        $message['data']['author_uid'] = $authorUid;
        $message['data']['story_id'] = $storyId;
        $message['data']['like_uid'] = $likeUid;
        $messageBody = \GuzzleHttp\json_encode($message);
        return $messageBody;
    }

    /**
     * 用户对评论点赞
     * @param $commentUid 评论者Uid
     * @param $commentId 评论id
     * @param $likeUid 点赞用户Uid
     * @return string
     */
    public static function likeComment($commentUid,$commentId,$likeUid) {

        $message = array();
        $message['action'] = "like_comment";
        $message['data']['comment_uid'] = $commentUid;
        $message['data']['comment_id'] = $commentId;
        $message['data']['like_uid'] = $likeUid;
        $messageBody = \GuzzleHttp\json_encode($message);
        return $messageBody;
    }

    /**
     * 用户对回复点赞
     * @param $replyUid 回复者Uid
     * @param $replyId 新回复id
     * @param $likeUid 点赞用户Uid
     * @return string
     */
    public static function likeReply($replyUid,$replyId,$likeUid) {

        $message = array();
        $message['action'] = "like_reply";
        $message['data']['reply_uid'] = $replyUid;
        $message['data']['reply_id'] = $replyId;
        $message['data']['like_uid'] = $likeUid;
        $messageBody = \GuzzleHttp\json_encode($message);
        return $messageBody;
    }

    /**
     * 用户,故事点击数增加
     * @param $uid 点击用户uid
     * @param $storyId 被点击故事id
     * @param $taps 点击数
     * @return string
     */
    public static function tapsIncrease($uid,$storyId,$taps) {

        $message = array();
        $message['action'] = "taps_increase";
        $message['data']['uid'] = $uid;
        $message['data']['story_id'] = $storyId;
        $message['data']['taps'] = $taps;
        $messageBody = \GuzzleHttp\json_encode($message);
        return $messageBody;
    }

    /**
     * 解析Json格式的消息体为数组
     * @param $messageBody
     * @return mixed
     */
    public static function parseJsonMessageBody($messageBody) {

        $messageBody = \GuzzleHttp\json_decode($messageBody);
        return $messageBody;
    }
}