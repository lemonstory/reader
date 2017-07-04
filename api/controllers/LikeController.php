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
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use Yii;
use common\models\UploadForm;
use yii\web\ServerErrorHttpException;
use common\components\DateTimeHelper;

class LikeController extends ActiveController
{
    public $modelClass = 'common\models\User';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
    public $likeCommentTargetType = '';

    public function init()
    {
        parent::init();
        if(empty($this->likeTargetType)) {
            $likeTargetTypeArr = Yii::$app->params['LIKE_TARGET_TYPE'];
            $likeTargetTypeArr = ArrayHelper::index($likeTargetTypeArr,'alias');
            $this->likeCommentTargetType = intval($likeTargetTypeArr['comment']['value']);
        }

    }

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 动作
        unset($actions['delete'], $actions['create'], $actions['view']);

        // 使用"prepareDataProvider()"方法自定义数据provider
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

    }


    //评论点赞
    public function actionCommentLike($comment_id,$uid)
    {
        $response = Yii::$app->getResponse();
        $commentId = Yii::$app->getRequest()->get('comment_id',null);
        $ownerUid = Yii::$app->getRequest()->get('uid',null);

        $data = array();
        $transaction = Yii::$app->db->beginTransaction();
        try {

            //记录赞
            $condition = array(
                'like.target_id' => $commentId,
                'like.target_type' => $this->likeCommentTargetType,
                'like.owner_uid' => $ownerUid,
            );
            $likeModel = Like::findOne($condition);
            if(is_null($likeModel)) {
                $likeModel = new Like();
            }

            if($likeModel->getIsNewRecord() || (!$likeModel->getIsNewRecord() && $likeModel->status == Yii::$app->params['STATUS_DELETED'])) {

                //评论赞数+1
                $commentModel = Comment::findOne(['comment_id' => $comment_id]);
                $commentModel->updateCounters(['like_count' => 1]);

                $likeModel->owner_uid = $ownerUid;
                $likeModel->target_uid = $commentModel->owner_uid;
                $likeModel->target_id = $commentId;
                $likeModel->target_type = $this->likeCommentTargetType;
                $likeModel->status = Yii::$app->params['STATUS_ACTIVE'];
                $likeModel->save();
                if ($likeModel->hasErrors()) {

                    Yii::error($likeModel->getErrors());
                    throw new ServerErrorHttpException('评论点赞保存失败');
                }

            }else {
                //不能重复点赞
                $response->statusCode = 400;
                $response->statusText = '不能重复点赞';
            }
            $transaction->commit();

        }catch (\Exception $e){

            $transaction->rollBack();
            Yii::error($e->getMessage());
            $response->statusCode = 400;
            $response->statusText = $e->getMessage();
        }

        $ret['data'] = $data;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }


    public function actionCommentDislike($comment_id,$uid) {

        $response = Yii::$app->getResponse();
        $commentId = Yii::$app->getRequest()->get('comment_id',null);
        $ownerId = Yii::$app->getRequest()->get('uid',null);
        $data = array();
        $transaction = Yii::$app->db->beginTransaction();
        try {

            //删除记录赞
            $condition = array(

                'like.target_id' => $commentId,
                'like.target_type' => $this->likeCommentTargetType,
                'like.owner_uid' => $ownerId,
                'like.status' => Yii::$app->params['STATUS_ACTIVE'],
            );

            $likeModel = Like::findOne($condition);
            if(!is_null($likeModel)) {

                //评论赞数-1
                $commentModel = Comment::findOne(['comment_id' => $comment_id]);
                if($commentModel->like_count > 0) {
                    $commentModel->updateCounters(['like_count' => -1]);
                }

                $likeModel->owner_uid = $uid;
                $likeModel->target_uid = $commentModel->owner_uid;
                $likeModel->target_id = $commentId;
                $likeModel->target_type = $this->likeCommentTargetType;
                $likeModel->status = Yii::$app->params['STATUS_DELETED'];
                $likeModel->save();
                if ($likeModel->hasErrors()) {
                    Yii::error($likeModel->getErrors());
                    throw new ServerErrorHttpException('评论点赞保存失败');
                }

            }else{
                $response->statusCode = 400;
                $response->statusText = '没有点赞记录';
            }
            $transaction->commit();

        }catch (\Exception $e){

            $transaction->rollBack();
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
