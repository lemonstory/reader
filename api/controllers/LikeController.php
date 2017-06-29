<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
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
        $uid = Yii::$app->getRequest()->get('uid',null);

        $data = array();
        try {
            $condition = array(
              
                'like.object_id' => $commentId,
                'like.object_type' => Yii::$app->params['LIKE_TYPE_COMMENT'],
                'like.uid' => $uid,
            );
            
            $likeModel = Like::findOne($condition);
            if(is_null($likeModel)) {
                $likeModel = new Like();
            }

            $likeModel->uid = $uid;
            $likeModel->object_id = $commentId;
            $likeModel->object_type = Yii::$app->params['LIKE_TYPE_COMMENT'];
            $likeModel->status = Yii::$app->params['STATUS_ACTIVE'];
            $likeModel->save();
            if ($likeModel->hasErrors()) {

                Yii::error($likeModel->getErrors());
                throw new ServerErrorHttpException('评论点赞保存失败');
            }
        }catch (\Exception $e){

            Yii::error($e->getMessage());
            $response->statusCode = 400;
            $response->statusText = '评论点赞保存失败';
        }

        $ret['data'] = $data;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }


    public function actionCommentDislike($comment_id,$uid) {

        $response = Yii::$app->getResponse();
        $commentId = Yii::$app->getRequest()->get('comment_id',null);
        $uid = Yii::$app->getRequest()->get('uid',null);
        $data = array();
        try {
            $condition = array(

                'like.object_id' => $commentId,
                'like.object_type' => Yii::$app->params['LIKE_TYPE_COMMENT'],
                'like.uid' => $uid,
            );

            $likeModel = Like::findOne($condition);
            if(!is_null($likeModel)) {
                $likeModel->uid = $uid;
                $likeModel->object_id = $commentId;
                $likeModel->object_type = Yii::$app->params['LIKE_TYPE_COMMENT'];
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


        }catch (\Exception $e){

            Yii::error($e->getMessage());
            $response->statusCode = 400;
            $response->statusText = '评论点赞保存失败';
        }

        $ret['data'] = $data;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }


}
