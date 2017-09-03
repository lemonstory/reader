<?php

namespace api\controllers;

use api\controllers\MessageParsedown;
use Carbon\Carbon;
use common\components\DateTimeHelper;
use common\components\MnsQueue;
use common\components\QueueMessageHelper;
use common\models\Story;
use common\models\StoryActor;
use common\models\StoryTag;
use common\models\StoryTagRelation;
use common\models\UserReadStoryRecord;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\BaseJson;
use yii\rest\ActiveController;
use yii\web\ServerErrorHttpException;


class StoryController extends ActiveController
{
    public $modelClass = 'common\models\Story';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        //用户认证
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            //部分action需要access-token认证，部分action不需要
            'except' => ['view', 'chapters'],
            'authMethods' => [
//                HttpBasicAuth::className(),
//                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
        ];
        return $behaviors;
    }

    public function init()
    {
        parent::init();
        Carbon::setLocale('zh');
    }

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 动作
        unset($actions['delete'], $actions['create'], $actions['view']);

        // 使用"prepareDataProvider()"方法自定义数据provider
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }
//
//    public function prepareDataProvider()
//    {
//        // 为"index"动作准备和返回数据provider
//    }

    /**
     * 批量新建故事
     * @param $uid 作者Uid
     * @return mixed
     * @throws ServerErrorHttpException
     */
    public function actionBatchCreate($uid)
    {
        $userModel = Yii::$app->user->identity;
        $ret['data'] = array();
        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {

                $response = Yii::$app->getResponse();
                $inputStories = Yii::$app->getRequest()->post();
                $ret = array();
                $data = array();
                $hasError = false;

                if (!empty($inputStories['storys'])) {

                    foreach ($inputStories['storys'] as $storyItem) {
                        $transaction = Yii::$app->db->beginTransaction();
                        try {

                            //保存故事
                            $storyItem['uid'] = $uid;
                            $storyModel = new Story();
                            $storyModel->loadDefaultValues();
                            $storyModel->setAttributes($storyItem);
                            $storyModel->save();
                            if ($storyModel->hasErrors()) {
                                Yii::error($storyModel->getErrors());
                                throw new ServerErrorHttpException('保存故事失败');
                            }
                            $storyId = $storyModel->story_id;

                            //保存角色
                            //接收到的角色值:[{"number":"角色序号-1","name":"角色姓名-1","avatar":"角色头像-2","is_visible":"是否可见"},{"number":"角色序号-2","name":"角色姓名-2","avatar":"角色头像-2","is_visible":"是否可见"}];
                            $actorAffectedRows = 0;
                            if (isset($storyItem['actor']) && !empty($storyItem['actor'])) {
                                $actorJson = $storyItem['actor'];
                                $actorRows = $this->parseActorJson($actorJson, $storyId);

                                //TODO:角色信息格式输入检查
                                $actorColumns = ['actor_id', 'story_id', 'name', 'avatar', 'number', 'is_visible','location'];
                                $actorAffectedRows = Yii::$app->db->createCommand()->batchInsert(StoryActor::tableName(), $actorColumns, $actorRows)->execute();
                            }

                            //保存标签
                            //接收到的标签值:[{"tag_id":1, "status":"1"},{"tag_id":2, "status":"1"}];
                            $tagAffectedRows = 0;
                            if (isset($storyItem['tag']) && !empty($storyItem['tag'])) {
                                $tagJson = $storyItem['tag'];
                                $tagRows = $this->parseTagJson($tagJson, $storyId);
                                //TODO:标签信息格式输入检查
                                $tagColumns = ['story_id', 'tag_id', 'status'];
                                $tagAffectedRows = Yii::$app->db->createCommand()->batchInsert(StoryTagRelation::tableName(), $tagColumns, $tagRows)->execute();
                            }

                            //消息通知->用户发布新故事
                            $mnsQueue = new MnsQueue();
                            $queueName = Yii::$app->params['mnsQueueNotifyName'];
                            $messageBody = QueueMessageHelper::postStory($uid, $storyId);
                            $mnsQueue->sendMessage($messageBody, $queueName);

                            $transaction->commit();

                            if ($storyId > 0 && $actorAffectedRows >= 0 && $tagAffectedRows >= 0) {

                                $dataItem = $this->getStoryInfoWithModel($storyModel);
                                $dataItem['local_story_id'] = $storyItem['local_story_id'];
                                $data[] = $dataItem;
                            }
                        } catch (\Exception $e) {

                            //如果抛出错误则进入catch，先callback，然后捕获错误，返回错误
                            $hasError = true;
                            $transaction->rollBack();
                            //Yii::error($e->getMessage());
                            $response->statusCode = 400;
                            $response->statusText = '新建故事失败';
                        }
                    }

                    if (count($data) > 0 && $hasError) {

                        $response->statusCode = 206;
                        $response->statusText = '成功新建部分故事';
                    }

                } else {
                    $response->statusCode = 400;
                    $response->statusText = '参数错误';
                }
                $ret['data'] = $data;
                $ret['status'] = $response->statusCode;
                $ret['message'] = $response->statusText;

            } else {
                $ret['status'] = 400;
                $ret['message'] = 'uid与token不相符';
            }
        } else {
            $ret['status'] = 400;
            $ret['message'] = '用户不存在';
        }
        return $ret;
    }

    /**
     * 批量更新故事
     * @param $uid 作者Uid
     * @return mixed
     * @throws ServerErrorHttpException
     */
    public function actionBatchUpdate($uid)
    {
        $userModel = Yii::$app->user->identity;
        $ret['data'] = array();
        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {

                $response = Yii::$app->getResponse();
                $inputStories = Yii::$app->getRequest()->post();
                $ret = array();
                $data = array();
                $hasError = false;
                if (!empty($inputStories['storys'])) {
                    foreach ($inputStories['storys'] as $storyItem) {
                        $transaction = Yii::$app->db->beginTransaction();
                        try {
                            //保存故事
                            $storyModel = Story::findOne($storyItem['story_id']);
                            if (!empty($storyModel)) {

                                //检查故事发布状态
                                $isUpdatePublished = false;
                                $isPublished = $storyModel->is_published;
                                if ($isPublished == Yii::$app->params['STATUS_UNPUBLISHED'] && $storyItem['is_published'] == Yii::$app->params['STATUS_PUBLISHED']) {
                                    $isUpdatePublished = true;
                                }

                                $currentTaps = $storyModel->taps;
                                $storyModel->setAttributes($storyItem);
                                //点击数递增
                                if (!empty($storyItem['taps'])) {
                                    $storyModel->taps = $currentTaps + $storyItem['taps'];
                                }
                                $storyModel->save();
                                if ($storyModel->hasErrors()) {

                                    Yii::error($storyModel->getErrors());
                                    throw new ServerErrorHttpException('编辑故事输入错误');
                                }
                                $storyId = $storyModel->story_id;

                                //删除故事则阅读记录也一并删除
                                if ($storyItem['status'] == Yii::$app->params['STATUS_DELETED']) {
                                    $userReadStoryRecordCondition = array(
                                        'uid' => $uid,
                                        'story_id' => $storyId,
                                    );
                                    $userReadStoryRecordModel = UserReadStoryRecord::find()->where($userReadStoryRecordCondition)->one();
                                    $userReadStoryRecordModel->status = Yii::$app->params['STATUS_DELETED'];
                                    $userReadStoryRecordModel->save(false, ['status']);
                                    if ($userReadStoryRecordModel->hasErrors()) {
                                        Yii::error($userReadStoryRecordModel->getErrors());
                                        throw new ServerErrorHttpException('阅读记录删除失败');
                                    }
                                }

                                //更新角色
                                //接收到的角色值:[{"actor_id":1, "number":"1","name":"姓名-1","avatar":"","is_visible":1},{"actor_id":2,"number":"2","name":"姓名-2","avatar":"","is_visible":1}];
                                if (isset($storyItem['actor']) && !empty($storyItem['actor'])) {
                                    $actorJson = $storyItem['actor'];
                                    $actorRows = $this->parseActorJson($actorJson, $storyId);
                                    if (!empty($actorRows)) {
                                        foreach ($actorRows as $actorItem) {
                                            $storyActorModel = StoryActor::findOne($actorItem['actor_id']);
                                            if ($storyActorModel === null) {
                                                $storyActorModel = new StoryActor();
                                            }
                                            $storyActorModel->setAttributes($actorItem);
                                            $storyActorModel->save();
                                            if ($storyActorModel->hasErrors()) {
                                                Yii::error($storyActorModel->getErrors());
                                                throw new ServerErrorHttpException('角色修改失败');
                                            }
                                        }
                                    }
                                }

                                //更新标签
                                //接收到的标签值:[{"tag_id":1, "status":"1"},{"tag_id":2, "status":"1"}];
                                if (isset($storyItem['tag']) && !empty($storyItem['tag'])) {
                                    $tagJson = $storyItem['tag'];
                                    $tagRows = $this->parseTagJson($tagJson, $storyId);

                                    if (!empty($tagRows)) {
                                        foreach ($tagRows as $tagItem) {

                                            $condition = array(
                                                'story_id' => $tagItem['story_id'],
                                                'tag_id' => $tagItem['tag_id']
                                            );
                                            $storyTagRelationModel = StoryTagRelation::findOne($condition);
                                            if ($storyTagRelationModel === null) {
                                                $storyTagRelationModel = new StoryTagRelation();
                                            }
                                            $storyTagRelationModel->setAttributes($tagItem);
                                            $storyTagRelationModel->save();
                                            if ($storyTagRelationModel->hasErrors()) {
                                                Yii::error($storyTagRelationModel->getErrors());
                                                throw new ServerErrorHttpException('标签修改失败');
                                            }
                                        }
                                    }
                                }

                                //修改故事发布状态,发送通知
                                //消息通知->用户发布新故事
                                if ($isUpdatePublished) {
                                    $mnsQueue = new MnsQueue();
                                    $queueName = Yii::$app->params['mnsQueueNotifyName'];
                                    $messageBody = QueueMessageHelper::postStory($storyModel->uid, $storyModel->story_id);
                                    $mnsQueue->sendMessage($messageBody, $queueName);
                                }

                            } else {
                                throw new ServerErrorHttpException('故事信息为空');
                            }
                            $transaction->commit();
                            $dataItem['local_story_id'] = null;
                            $dataItem = $this->getStoryInfoWithModel($storyModel);
                            if (isset($storyItem['local_story_id'])) {
                                $dataItem['local_story_id'] = $storyItem['local_story_id'];
                            }

                            $data[] = $dataItem;

                        } catch (\Exception $e) {

                            //如果抛出错误则进入catch，先callback，然后捕获错误，返回错误
                            $hasError = true;
                            $transaction->rollBack();
                            Yii::error($e->getMessage());
//                            print_r($e->getMessage());
//                    print_r($e->getTrace());
                            $response->statusCode = 400;
                            $response->statusText = '编辑故事失败';
                        }
                    }

                    if (count($data) > 0 && $hasError) {
                        $response->statusCode = 206;
                        $response->statusText = '成功编辑部分故事';
                    }
                } else {
                    $response->statusCode = 400;
                    $response->statusText = '参数错误';
                }
                $ret['data'] = $data;
                $ret['status'] = $response->statusCode;
                $ret['message'] = $response->statusText;

            } else {
                $ret['status'] = 400;
                $ret['message'] = 'uid与token不相符';
            }
        } else {
            $ret['status'] = 400;
            $ret['message'] = '用户不存在';
        }
        return $ret;
    }

    public function actionView($id)
    {
        $data = array();
        $storyId = $id;
        $storyCondition = array(
            'story.story_id' => $storyId,
            'story.status' => Yii::$app->params['STATUS_ACTIVE']
        );

        $storyModel = Story::find()
            ->joinWith([
                'user' => function (ActiveQuery $query) {
                    $query->andWhere(['user.status' => Yii::$app->params['STATUS_ACTIVE']]);
                },])
            ->where($storyCondition)
            ->limit(1)
            ->all();

        if (isset($storyModel[0]) && !empty($storyModel[0])) {
            $data = $this->getStoryInfoWithModel($storyModel[0]);
        }
        $ret['data'] = $data;
        $ret['status'] = 200;
        $ret['message'] = 'OK';
        return $ret;
    }

    public function actionChapters($id)
    {

        $data = array();
        $storyId = $id;
        $storyCondition = array(
            'story_id' => $storyId,
            'status' => Yii::$app->params['STATUS_ACTIVE']
        );
        $storyModel = Story::findOne($storyCondition);
        if (!empty($storyModel)) {
            //章节信息
            $chapterCondition = array(
                'story_id' => $storyId,
                'status' => Yii::$app->params['STATUS_ACTIVE']
            );
            $chapterNames = array('chapter_id', 'name', 'background', 'message_count', 'number', 'is_published', 'create_time', 'last_modify_time');
            $data = $storyModel->getChapters()->select($chapterNames)->andWhere($chapterCondition)->orderBy(['number' => SORT_ASC])->asArray()->all();

            if (is_array($data) && !empty($data)) {
                foreach ($data as $key => $item) {
                    //格式化时间
                    $data[$key]['create_time'] = Carbon::createFromTimestamp($data[$key]['create_time'])->toDateTimeString();
                    $data[$key]['last_modify_time'] = Carbon::createFromTimestamp($data[$key]['last_modify_time'])->toDateTimeString();
                }
            }
        }
        $ret['data'] = $data;
        $ret['status'] = 200;
        $ret['message'] = 'OK';
        return $ret;
    }


    /**
     * 拼接角色数组
     * @param $actorJson
     * @param $storyId
     * @return array
     */
    private function parseActorJson($actorJson, $storyId)
    {

        $actorArr = BaseJson::decode($actorJson);
        $actorRows = array();
        if (!empty($actorArr) && !empty($storyId)) {
            foreach ($actorArr as $actorItem) {
                $actorRow['actor_id'] = isset($actorItem['actor_id']) ? $actorItem['actor_id'] : null;
                $actorRow['story_id'] = $storyId;
                $actorRow['name'] = $actorItem['name'];
                $actorRow['avatar'] = $actorItem['avatar'];
                $actorRow['number'] = $actorItem['number'];
                $actorRow['is_visible'] = isset($actorItem['is_visible']) ? $actorItem['is_visible'] : 1;
                $actorRow['location'] = $actorItem['location'];
                $actorRows[] = $actorRow;
            }
        }
        return $actorRows;
    }

    /**
     * 拼接标签数组
     * @param $tagJson
     * @param $storyId
     * @return array
     */
    private function parseTagJson($tagJson, $storyId)
    {

        $tagArr = BaseJson::decode($tagJson);
        $tagRows = array();
        if (!empty($tagArr) && !empty($storyId)) {
            foreach ($tagArr as $tagItem) {
                $tagRow['story_id'] = $storyId;
                $tagRow['tag_id'] = $tagItem['tag_id'];
                $tagRow['status'] = isset($tagItem['status']) ? $tagItem['status'] : 1;
                $tagRows[] = $tagRow;
            }
        }
        return $tagRows;
    }

    private function getStoryInfoWithModel($storyModel)
    {

        $data = array();
        if (!empty($storyModel)) {
            $data = $storyModel->getAttributes();
            $data['create_time'] = Carbon::createFromTimestamp($data['create_time'])->toDateTimeString();
            $data['last_modify_time'] = Carbon::createFromTimestamp($data['last_modify_time'])->toDateTimeString();
            $storyId = $storyModel->story_id;

            //角色信息
            $actorCondition = array(
                'story_id' => $storyId,
                'status' => Yii::$app->params['STATUS_ACTIVE'],
                'is_visible' => Yii::$app->params['STATUS_ACTIVE']
            );
            $actorNames = array('actor_id', 'name', 'avatar', 'number', 'location');
            $data['actor'] = $storyModel->getActors()->select($actorNames)->andWhere($actorCondition)->orderBy(['number' => SORT_ASC])->asArray()->all();

            //标签信息
            $tagCondition = array(
                'status' => Yii::$app->params['STATUS_ACTIVE']
            );
            $tagNames = array('tag_id', 'name', 'number');
            $data['tag'] = $storyModel->getTags()->select($tagNames)->andWhere($tagCondition)->orderBy(['number' => SORT_ASC])->asArray()->all();

            //故事分享链接
            $data['share_url'] = $this->getShareUrl($storyId);

            //作者信息
            if (isset($storyModel->user) && !empty($storyModel->user)) {

                unset($data['uid']);
                $data['user'] = array();
                $data['user']['uid'] = $storyModel->user->uid;
                $data['user']['username'] = $storyModel->user->username;
                $data['user']['avatar'] = $storyModel->user->avatar;
                $data['user']['signature'] = $storyModel->user->signature;
            }
        }
        return $data;
    }


    /**
     * 获取故事分享的地址
     * @param $storyId
     * @return string
     */
    public function getShareUrl($storyId)
    {

        //TODO:上线前要修改
        $shareUrlArr = Yii::$app->params['storyShareUrl'];
        $key = array_rand($shareUrlArr);
        $shareUrl = $shareUrlArr[$key];
        return $shareUrl;
    }

}

?>