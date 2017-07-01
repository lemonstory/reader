<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\Comment;
use common\models\Like;
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
     * @param $message_id
     * @return array
     */
    public function actionMessageVotes($message_id) {

       $condition = array(
            'target_id' => $message_id,
            'target_type' => Yii::$app->params['COMMENT_TARGET_TYPE_MESSAGE'],
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        );
        $votesCount = Comment::find()
            ->select(['content','COUNT(*) AS count'])
            ->where($condition)
            ->groupBy(['content'])
            ->asArray()
            ->all();
        $votesCount = ArrayHelper::map($votesCount,'content','count');
        foreach (Yii::$app->params['COMMENT_MESSAGE_VOTE_CONTENT'] as $content) {

            if(!array_key_exists($content,$votesCount)) {
                $votesCount[$content] = 0;
            }
        }

        $ret = array();
        $data = array();
        foreach ($votesCount as $content => $count) {

            $dataItem = array();
            $dataItem['content'] = $content;
            $dataItem['count'] = intval($count);
            $data[] = $dataItem;
        }

        $ret['data'] = $data;
        $ret['code'] = 200;
        $ret['msg'] = 'OK';
        return $ret;
    }

    /**
     * 获取故事的评论(投票)汇总数据
     * @param $story_id
     * @return array
     */
    public function actionStoryVotes($story_id) {

        //select count(*) as count, content from `comment` LEFT JOIN chapter_message_content ON `comment`.target_id=`chapter_message_content`.message_id
        //where chapter_message_content.story_id=1  and `comment`.target_type=3 GROUP BY `comment`.content

        $storyId = $story_id;
        $condition = array(
            'chapter_message_content.story_id' => $storyId,
            'comment.target_type' => Yii::$app->params['COMMENT_TARGET_TYPE_MESSAGE'],
            'chapter_message_content.status' => Yii::$app->params['STATUS_ACTIVE'],
            'comment.status' => Yii::$app->params['STATUS_ACTIVE'],

        );
        $votesCount = Comment::find()
            ->select(['content','COUNT(*) AS count'])
            ->leftJoin('chapter_message_content','comment.target_id=chapter_message_content.message_id')
            ->where($condition)
            ->groupBy(['comment.content'])
            ->asArray()
            ->all();
        $votesCount = ArrayHelper::map($votesCount,'content','count');

        foreach (Yii::$app->params['COMMENT_MESSAGE_VOTE_CONTENT'] as $content) {

            if(!array_key_exists($content,$votesCount)) {
                $votesCount[$content] = 0;
            }
        }

        $ret = array();
        $data = array();
        foreach ($votesCount as $content => $count) {

            $dataItem = array();
            $dataItem['content'] = $content;
            $dataItem['count'] = intval($count);
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
        $ownerUid = Yii::$app->getRequest()->post('uid',null);
        $messageId = Yii::$app->getRequest()->post('message_id',null);
        $content = Yii::$app->getRequest()->post('content',null);

        //TODO:输入检查
        //content做枚举值检查
        $data = array();
        try {
            $commentModel = new Comment();
            $commentModel->owner_uid = $ownerUid;
            $commentModel->target_id = $messageId;
            $commentModel->target_type = Yii::$app->params['COMMENT_TARGET_TYPE_MESSAGE'];
            $commentModel->content = $content;
            $commentModel->save();
            if ($commentModel->hasErrors()) {

                Yii::error($commentModel->getErrors());
                throw new ServerErrorHttpException('111消息投票保存失败');
            }
            $data['comment_id'] = $commentModel->comment_id;
        }catch (\Exception $e){

            Yii::error($e->getMessage());
            $response->statusCode = 400;
            $response->statusText = '222消息投票保存失败';
        }

        $ret['data'] = $data;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }

    /**
     * 获取故事的评论内容
     * @param $story_id
     * @param $page
     * @param $pre_page
     * @return array
     */
    public function actionIndex($story_id,$page,$pre_page) {

        $response = Yii::$app->getResponse();
        $ret = array();
        $ret['data']['commentList'] = array();
        $ret['data']['commentList']['new'] = array();

        $commentCondition = array(
            'comment.target_id' => $story_id,
            'comment.target_type' => Yii::$app->params['COMMENT_TARGET_TYPE_STORY'],
            'comment.status' => Yii::$app->params['STATUS_ACTIVE'],
        );

        //获取最新评论总数量
        $ret['data']['totalCount'] = Comment::find()
            ->where($commentCondition)
            ->count();
        $ret['data']['currentPage'] = $page;
        $ret['data']['perPage'] = $pre_page;
        $ret['data']['pageCount'] = intval(ceil($ret['data']['totalCount'] / $ret['data']['perPage']));
        $ret['data']['pageCount'] = 1;

        if($ret['data']['totalCount'] > 0) {

            //第一页返回热门评论
            if($ret['data']['currentPage'] == 1) {
                $ret['data']['commentList']['hot'] = array();
                $commentHotCondition = ['>', 'comment.like_count', 0];
                //热门评论
                $commentHotIdArr = Comment::find()
                    ->select('comment.comment_id as comment_id,comment.parent_comment_id as parent_comment_id')
                    ->where($commentCondition)
                    ->andWhere($commentHotCondition)
                    ->orderBy('comment.like_count',SORT_DESC)
                    ->limit(Yii::$app->params['COMMENT_HOT_MAX_COUNT'])
                    ->asArray()
                    ->all();

                $commentHotCommentPairIdArr = ArrayHelper::map($commentHotIdArr,'comment_id','parent_comment_id');
                $commentHotCommentIdArr = ArrayHelper::getColumn($commentHotIdArr,'comment_id');
                $commentHotParentCommentIdArr = ArrayHelper::getColumn($commentHotIdArr,'parent_comment_id');

                //过滤parent_comment_id=0的值
                $commentHotFilterParentCommentIdArr = array_filter($commentHotParentCommentIdArr, function($item){
                    return !empty($item);
                });

                $commentHotAllCommentIdArr = array_merge($commentHotCommentIdArr,$commentHotFilterParentCommentIdArr);

                //获取评论内容
                $commentHotAllCommentContentArr = Comment::find()
                    ->where(['comment_id' => $commentHotAllCommentIdArr])
                    ->joinWith([
                        'user'=> function (ActiveQuery $query)  {
                            $query->andWhere(['user.status' => Yii::$app->params['STATUS_ACTIVE']]);
                        },
                    ])
                    ->asArray()
                    ->all();
                $commentHotAllCommentContentArr = ArrayHelper::index($commentHotAllCommentContentArr,'comment_id');

                //组织热门评论数据
                $ret['data']['commentList']['hot'] = $this->processCommentHierarchy($commentHotCommentPairIdArr,$commentHotAllCommentContentArr);
                //热门评论按照赞数倒序排列
                ArrayHelper::multisort($ret['data']['commentList']['hot'], 'like_count',SORT_DESC,SORT_NUMERIC);
            }

            //最新评论
            $offset = ($page - 1) * $pre_page;
            $commentNewIdArr = Comment::find()
                ->select('comment.comment_id as comment_id,comment.parent_comment_id as parent_comment_id')
                ->where($commentCondition)
                ->orderBy('comment.create_time',SORT_DESC)
                ->offset($offset)
                ->limit($pre_page)
                ->asArray()
                ->all();

            $commentNewCommentPairIdArr = ArrayHelper::map($commentNewIdArr,'comment_id','parent_comment_id');
            $commentNewCommentIdArr = ArrayHelper::getColumn($commentNewIdArr,'comment_id');
            $commentNewParentCommentIdArr = ArrayHelper::getColumn($commentNewIdArr,'parent_comment_id');

            //过滤parent_comment_id=0的值
            $commentNewFilterParentCommentIdArr = array_filter($commentNewParentCommentIdArr, function($item){
                return !empty($item);
            });
            $commentNewAllCommentIdArr = array_merge($commentNewCommentIdArr,$commentNewFilterParentCommentIdArr);
            //获取评论内容
            $commentNewAllCommentContentArr = Comment::find()
                ->where(['comment_id' => $commentNewAllCommentIdArr])
                ->joinWith([
                    'user'=> function (ActiveQuery $query)  {
                        $query->andWhere(['user.status' => Yii::$app->params['STATUS_ACTIVE']]);
                    },
                ])
                ->asArray()
                ->all();
            $commentNewAllCommentContentArr = ArrayHelper::index($commentNewAllCommentContentArr,'comment_id');
            //组织最新评论数据
            $ret['data']['commentList']['new'] = $this->processCommentHierarchy($commentNewCommentPairIdArr,$commentNewAllCommentContentArr);

        }
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }

    /** 处理评论的层级关系
     * @param $commentPairIdArr
     * @param $commentContentArr
     * @return array
     */
    private function processCommentHierarchy($commentPairIdArr,$commentContentArr) {

            $commentHierarchyList = array();
            if(!empty($commentPairIdArr) && !empty($commentContentArr)) {

                foreach ($commentPairIdArr as $commentId => $parentCommentId) {
                    $comment = array();
                    $comment['comment_id'] = $commentContentArr[$commentId]['comment_id'];
                    $comment['parent_comment_id'] = $commentContentArr[$commentId]['parent_comment_id'];
                    $comment['target_id'] = $commentContentArr[$commentId]['target_id'];
                    $comment['target_type'] = $commentContentArr[$commentId]['target_type'];
                    $comment['content'] = $commentContentArr[$commentId]['content'];
                    $comment['target_uid'] = $commentContentArr[$commentId]['target_uid'];
                    $comment['like_count'] = $commentContentArr[$commentId]['like_count'];
                    $comment['create_time'] = $commentContentArr[$commentId]['create_time'];
                    $comment['last_modify_time'] = $commentContentArr[$commentId]['last_modify_time'];

                    //user
                    $comment['owner_uid'] = $commentContentArr[$commentId]['owner_uid'];
                    $comment['owner_name'] = $commentContentArr[$commentId]['user']['name'];
                    $comment['owner_avatar'] = $commentContentArr[$commentId]['user']['avatar'];
                    $comment['owner_signature'] = $commentContentArr[$commentId]['user']['signature'];

                    if(!empty($parentCommentId)) {
                        $comment['parent'] = array();
                        if($commentContentArr[$parentCommentId]['status'] == Yii::$app->params['STATUS_ACTIVE']) {
                            $comment['parent']['comment_id'] = $commentContentArr[$parentCommentId]['comment_id'];
                            $comment['parent']['parent_comment_id'] = $commentContentArr[$parentCommentId]['parent_comment_id'];
                            $comment['parent']['target_id'] = $commentContentArr[$parentCommentId]['target_id'];
                            $comment['parent']['target_type'] = $commentContentArr[$parentCommentId]['target_type'];
                            $comment['parent']['content'] = $commentContentArr[$parentCommentId]['content'];
                            $comment['parent']['target_uid'] = $commentContentArr[$parentCommentId]['target_uid'];
                            $comment['parent']['like_count'] = $commentContentArr[$parentCommentId]['like_count'];
                            $comment['parent']['create_time'] = $commentContentArr[$parentCommentId]['create_time'];
                            $comment['parent']['last_modify_time'] = $commentContentArr[$parentCommentId]['last_modify_time'];
                            $comment['parent']['status'] = $commentContentArr[$parentCommentId]['status'];

                            //user
                            $comment['parent']['owner_uid'] = $commentContentArr[$parentCommentId]['owner_uid'];
                            $comment['parent']['owner_name'] = $commentContentArr[$parentCommentId]['user']['name'];
                            $comment['parent']['owner_avatar'] = $commentContentArr[$parentCommentId]['user']['avatar'];
                            $comment['parent']['owner_signature'] = $commentContentArr[$parentCommentId]['user']['signature'];

                        }else{
                            $comment['parent']['status'] = $commentContentArr[$parentCommentId]['status'];
                        }

                    }
                    $commentHierarchyList[] = $comment;
                }
            }
            return $commentHierarchyList;
    }


    /**
     * 提交评论
     * @return mixed
     */
    public function actionCommit() {

        $response = Yii::$app->getResponse();
        $parentCommentId = Yii::$app->getRequest()->post('parent_comment_id',0);
        $ownerUid = Yii::$app->getRequest()->post('uid',null);
        $storyId = Yii::$app->getRequest()->post('story_id',null);
        $content = Yii::$app->getRequest()->post('content',null);
        $targetUid = 0;

        //TODO:输入检查
        $data = array();
        try {
            if(!empty($parentCommentId)) {

                $parentCommentModel = Comment::findOne(['comment_id' => $parentCommentId]);
                if(!is_null($parentCommentModel)) {
                    if($parentCommentModel->status != Yii::$app->params['STATUS_DELETED']){
                        $targetUid = $parentCommentModel->owner_uid;
                    }else{
                        throw new ServerErrorHttpException('父级评论被删除');
                    }
                }else{
                    throw new ServerErrorHttpException('父级评论不存在');
                }
            }

            $commentModel = new Comment();
            $commentModel->owner_uid = $ownerUid;
            $commentModel->target_id = $storyId;
            $commentModel->target_type = Yii::$app->params['COMMENT_TARGET_TYPE_STORY'];;
            $commentModel->content = $content;
            $commentModel->parent_comment_id = $parentCommentId;
            $commentModel->target_id = $targetUid;
            $commentModel->save();
            if ($commentModel->hasErrors()) {

                Yii::error($commentModel->getErrors());
                throw new ServerErrorHttpException('评论保存失败');
            }
            $data['comment_id'] = $commentModel->comment_id;
        }catch (\Exception $e){

            Yii::error($e->getMessage());
            $response->statusCode = 400;
            $response->statusText = $e->getMessage();
        }

        $ret['data'] = $data;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;

    }

}
