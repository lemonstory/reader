<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\Comment;
use common\models\Story;
use common\models\User;
use common\models\UserOauth;
use common\models\UserReadStoryRecord;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use Yii;
use common\models\UploadForm;
use yii\web\ServerErrorHttpException;
use common\components\DateTimeHelper;

class CommentController extends ActiveController
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

    public function actionView()
    {
        return $this->render('view');
    }

    /**
     * 获取消息的评论(投票)数据
     * @param $story_id
     * @param $chapter_id
     * @param $message_id
     * @return array
     */
    public function actionVotes($story_id,$chapter_id,$message_id) {

       $condition = array(

            'message_id' => $message_id,
            'chapter_id' => $chapter_id,
            'story_id' => $story_id,
            'is_vote' =>Yii::$app->params['STATUS_IS_VOTE'],
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        );
        $votesCount = Comment::find()
            ->select(['content','COUNT(*) AS count'])
            ->where($condition)
            ->groupBy(['content'])
            ->asArray()
            ->all();
        $votesCount = ArrayHelper::map($votesCount,'content','count');
        $ret = array();
        $data = array();
        foreach ($votesCount as $content => $count) {

            $dataItem = array();
            $dataItem['content'] = $content;
            $dataItem['count'] = $count;
            $data[] = $dataItem;
        }

        $ret['data'] = $data;
        $ret['code'] = 200;
        $ret['msg'] = 'OK';
        return $ret;
    }

    /**
     * 提交消息的评论(投票)数据
     * @return array
     */
    public function actionVoteCommit() {

        $response = Yii::$app->getResponse();
        $uid = Yii::$app->getRequest()->post('uid',null);
        $storyId = Yii::$app->getRequest()->post('story_id',null);
        $chapterId = Yii::$app->getRequest()->post('chapter_id',null);
        $messageId = Yii::$app->getRequest()->post('message_id',null);
        $content = Yii::$app->getRequest()->post('content',null);

        //TODO:输入检查
        //content做枚举值检查
        $data = array();
        try {
            $commentModel = new Comment();
            $commentModel->uid = $uid;
            $commentModel->story_id = $storyId;
            $commentModel->chapter_id = $chapterId;
            $commentModel->message_id = $messageId;
            $commentModel->is_vote = Yii::$app->params['STATUS_IS_NOTE_VOTE'];
            $commentModel->content = $content;
            $commentModel->save();
            if ($commentModel->hasErrors()) {

                Yii::error($commentModel->getErrors());
                throw new ServerErrorHttpException('消息投票保存失败');
            }
            $data['comment_id'] = $commentModel->comment_id;
        }catch (\Exception $e){

            Yii::error($e->getMessage());
            $response->statusCode = 400;
            $response->statusText = '消息投票保存失败';
        }

        $ret['data'] = $data;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }

    /**
     * 获取故事[及章节]的评论内容
     * @param $story_id
     * @param $chapter_id
     * @param $page
     * @param $pre_page
     * @return array
     */
    public function actionIndex($story_id,$chapter_id,$page,$pre_page) {

        //TODO:chapter_id,message_id可以不传,则取整个故事的评论

        $response = Yii::$app->getResponse();
        $offset = ($page - 1) * $pre_page;

        $condition = array(

            'comment.story_id' => $story_id,
            'comment.chapter_id' => $chapter_id,
            'comment.is_vote' => Yii::$app->params['STATUS_IS_NOTE_VOTE'],
            'comment.status' => Yii::$app->params['STATUS_ACTIVE'],
        );
        $columns = array(

            'comment.comment_id as comment_id',
            'comment.message_id as message_id',
            'comment.chapter_id as chapter_id',
            'comment.story_id as story_id',
            'comment.content as content',
            'comment.create_time as create_time',
            'comment.last_modify_time as last_modify_time',
            'user.uid as uid',
            'user.name as name',
            'user.avatar as avatar',
            'user.signature as signature'
        );
        //SELECT `comment`.`comment_id` AS `comment_id`, `comment`.`message_id` AS `message_id`, `comment`.`chapter_id` AS `chapter_id`, `comment`.`story_id` AS `story_id`, `comment`.`content` AS `content`, `comment`.`create_time` AS `create_time`, `comment`.`last_modify_time` AS `last_modify_time`, `user`.`uid` AS `uid`, `user`.`name` AS `name`, `user`.`avatar` AS `avatar`, `user`.`signature` AS `signature` FROM `comment` LEFT JOIN `user` ON `comment`.`uid` = `user`.`uid` WHERE ((`comment`.`story_id`='1') AND (`comment`.`chapter_id`='1') AND (`comment`.`is_vote`=0) AND (`comment`.`status`=1)) AND (`user`.`status`=1) ORDER BY `comment`.`last_modify_time` DESC LIMIT 10
        $query = Comment::find()
                ->select($columns)
                ->joinWith([
                    'user'=> function (ActiveQuery $query)  {
                        $query->andWhere(['user.status' => Yii::$app->params['STATUS_ACTIVE']]);
                    },
                ])

                ->where($condition)
                ->offset($offset)
                ->limit($pre_page)
                ->orderBy(['comment.last_modify_time' => SORT_DESC]);

        $provider =  new ActiveDataProvider([
            'query' =>$query,
            'pagination' => [
                'pageSize' => $pre_page,
            ],
        ]);

        $commentModels = $provider->getModels();
        $ret = array();
        $ret['data']['commentList'] = array();
        foreach ($commentModels as $commentModelItem) {

            $comment = array();
            $comment['comment_id'] = $commentModelItem->comment_id;
            $comment['message_id'] = $commentModelItem->message_id;
            $comment['chapter_id'] = $commentModelItem->chapter_id;
            $comment['story_id'] = $commentModelItem->story_id;
            $comment['content'] = $commentModelItem->content;
            $comment['create_time'] = $commentModelItem->create_time;
            $comment['last_modify_time'] = $commentModelItem->last_modify_time;

            //user
            $userModel = $commentModelItem->user;
            $comment['uid'] = $userModel['uid'];
            $comment['name'] = $userModel['name'];
            $comment['avatar'] = $userModel['avatar'];
            $comment['signature'] = $userModel['signature'];

            $ret['data']['commentList'][] = $comment;
        }

        $pagination = $provider->getPagination();
        $ret['data']['totalCount'] = $pagination->totalCount;
        $ret['data']['pageCount'] = $pagination->getPageCount();
        $ret['data']['currentPage'] = $pagination->getPage() + 1;
        $ret['data']['perPage'] = $pagination->getPageSize();
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }

    public function actionCommit() {

        $response = Yii::$app->getResponse();
        $uid = Yii::$app->getRequest()->post('uid',null);
        $storyId = Yii::$app->getRequest()->post('story_id',null);
        $chapterId = Yii::$app->getRequest()->post('chapter_id',null);
        $messageId = Yii::$app->getRequest()->post('message_id',null);
        $content = Yii::$app->getRequest()->post('content',null);

        //TODO:输入检查
        $data = array();
        try {
            $commentModel = new Comment();
            $commentModel->uid = $uid;
            $commentModel->story_id = $storyId;
            $commentModel->chapter_id = $chapterId;
            $commentModel->message_id = $messageId;
            $commentModel->is_vote = Yii::$app->params['STATUS_IS_NOTE_VOTE'];
            $commentModel->content = $content;
            $commentModel->save();
            if ($commentModel->hasErrors()) {

                Yii::error($commentModel->getErrors());
                throw new ServerErrorHttpException('评论保存失败');
            }
            $data['comment_id'] = $commentModel->comment_id;
        }catch (\Exception $e){

            Yii::error($e->getMessage());
            $response->statusCode = 400;
            $response->statusText = '评论保存失败';
        }

        $ret['data'] = $data;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;

    }

}
