<?php

namespace api\controllers;

use Carbon\Carbon;
use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\Story;
use common\models\User;
use common\models\UserReadStoryRecord;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use Yii;
use common\models\UploadForm;
use yii\web\ServerErrorHttpException;
use common\components\DateTimeHelper;

class UserReadStoryRecordController extends ActiveController
{
    public $modelClass = 'common\models\User';
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
            'except' => [],
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

    public $columnStoryNames = array(
        'story.story_id',
        'story.name',
        'story.description',
        'story.cover',
        'story.uid',
        'story.chapter_count',
        'story.message_count',
        'story.taps',
        'story.is_published',
        'story.last_modify_time AS story_update_time',
        'user_read_story_record.last_chapter_id',
        'user_read_story_record.last_message_id',
        'user_read_story_record.create_time',
        'user_read_story_record.last_modify_time'
    );

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
     * 获取图书更新
     * @param $uid
     * @param $story_ids //以逗号分隔的故事id列表
     * @return mixed
     */
    public function actionStoriesUpdate($uid, $story_ids)
    {

        $userModel = Yii::$app->user->identity;
        $data = array();
        $ret['data'] = $data;
        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {

                $data['storyList'] = array();
                $pattern = "/^\d+(,\d+)*$/";
                $time = preg_match_all($pattern, $story_ids, $matches);
                if (intval($time) > 0) {

                    //获取故事信息
                    $storyCondition = [
                        "and",
                        "story.status=" . Yii::$app->params['STATUS_ACTIVE'],
                        "story.story_id in (" . $story_ids . ")",
                        "story.last_modify_time>user_read_story_record.last_modify_time",
                        "user_read_story_record.status=" . Yii::$app->params['STATUS_ACTIVE'],
                        "user_read_story_record.uid=" . $uid,
                    ];

//        $offset = ($page - 1) * $per_page;
                    $with = 'story';
                    $userReadStoryRecordArr = UserReadStoryRecord::find()
                        ->select($this->columnStoryNames)
                        ->innerJoinWith($with)
                        ->Where($storyCondition)
//            ->offset($offset)
//            ->limit($per_page)
                        ->orderBy(['user_read_story_record.last_modify_time' => SORT_ASC])
                        ->asArray()
                        ->all();

                    if (!empty($userReadStoryRecordArr)) {
                        $data['storyList'] = $this->composeUserReadStoryRecordData($userReadStoryRecordArr);
                    }

                    $ret['status'] = 200;
                    $ret['message'] = 'OK';

                } else {

                    $ret['status'] = 400;
                    $ret['message'] = 'story_ids输入错误';
                }
                $ret['data'] = $data;

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
     * 获取阅读记录
     * @param $uid
     * @param $time //阅读记录最后更新时间
     * @param $page
     * @param $per_page
     * @return mixed
     */
    public function actionIndex($uid, $time, $page, $per_page)
    {

        $userModel = Yii::$app->user->identity;
        $data = array();
        $ret['data'] = $data;
        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {
                $storyCondition = [
                    "and",
                    "story.status=" . Yii::$app->params['STATUS_ACTIVE'],
                    "user_read_story_record.status=" . Yii::$app->params['STATUS_ACTIVE'],
                    "user_read_story_record.uid=" . $uid,
                    "user_read_story_record.last_modify_time>" . $time,
                ];

                $offset = ($page - 1) * $per_page;
                $with = 'story';
                $query = UserReadStoryRecord::find()
                    ->select($this->columnStoryNames)
                    ->innerJoinWith($with)
                    ->Where($storyCondition)
                    ->offset($offset)
                    ->limit($per_page)
                    ->orderBy(['user_read_story_record.last_modify_time' => SORT_ASC]);

                $provider = new ActiveDataProvider([
                    'query' => $query,
                    'pagination' => [
                        'pageSize' => $per_page,
                    ],
                ]);

                $data = array();
                //getModels还是按照UserReadStoryRecord(Model类)的结构返回数据,虽然上面有SELECT name的定义
                //关系'story'会是$userReadStoryRecordModels的Item的属性
                $userReadStoryRecordModels = $provider->getModels();
                $pagination = $provider->getPagination();
                $data['totalCount'] = $pagination->totalCount;
                $data['pageCount'] = $pagination->getPageCount();
                $data['currentPage'] = $pagination->getPage() + 1;
                $data['perPage'] = $pagination->getPageSize();

                if (!empty($userReadStoryRecordModels)) {
                    $data['storyList'] = $this->composeUserReadStoryRecordData($userReadStoryRecordModels);
                }

                $ret['data'] = $data;
                $ret['status'] = 200;
                $ret['message'] = 'OK';
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
     * 组装阅读记录数据
     * @param $userReadStoryRecords
     * @return array
     */
    private function composeUserReadStoryRecordData($userReadStoryRecords)
    {

        $uidArr = array();
        $messageIdArr = array();
        $storyList = array();
        $storyReadPositionData = array();
        foreach ($userReadStoryRecords as $item) {
            $uidArr[] = $item['story']['uid'];
            $messageIdArr[] = $item['last_message_id'];
            $storyIdArr[] = $item['story_id'];

            //按照story_id为索引记录用户阅读位置数据
            $storyReadPositionData[$item['story_id']]['message_count'] = $item['story']['message_count'];
            $storyReadPositionData[$item['story_id']]['last_chapter_id'] = $item['last_chapter_id'];
            $storyReadPositionData[$item['story_id']]['last_message_id'] = $item['last_message_id'];
        }

        //作者信息
        $userCondition = array(
            'uid' => $uidArr,
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        );

        $userNames = array(
            'uid',
            'username',
            'avatar',
            'signature'
        );
        $userInfoList = User::find()->select($userNames)->where($userCondition)->asArray()->all();
        $userInfoList = ArrayHelper::index($userInfoList, 'uid');

        //消息内容序号
        $messageNumberArr = ChapterMessageContent::find()->select(['message_id', 'number'])->where(['message_id' => $messageIdArr])->asArray()->all();
        $messageNumberArr = ArrayHelper::map($messageNumberArr, 'message_id', 'number');

        //章节信息
        $chapterCondition = array(
            'story_id' => $storyIdArr,
            'status' => Yii::$app->params['STATUS_ACTIVE'],
            'is_published' => Yii::$app->params['STATUS_PUBLISHED'],
        );

        $chapterNames = array(
            'story_id',
            'chapter_id',
            'number',
            'message_count',
        );

        $chapterArr = Chapter::find()->select($chapterNames)->where($chapterCondition)->asArray()->all();
        //阅读进度计算
        $userReadProgressArr = array();
        foreach ($storyIdArr as $storyId) {

            $readMessageCount = 0;
            //故事消息总数量
            $totalMessageCount = $storyReadPositionData[$storyId]['message_count'];
            $lastChapterId = $storyReadPositionData[$storyId]['last_chapter_id'];
            $lastMessageId = $storyReadPositionData[$storyId]['last_message_id'];
            $lastMessageNumber = 0;
            if (isset($messageNumberArr[$lastMessageId]) && !empty($messageNumberArr[$lastMessageId])) {
                $lastMessageNumber = $messageNumberArr[$lastMessageId];
            }

            if ($totalMessageCount > 0) {
                foreach ($chapterArr as $chapterItem) {

                    //章节id是自增
                    if ($chapterItem['story_id'] == $storyId && $chapterItem['chapter_id'] <= $lastChapterId) {
                        if ($chapterItem['chapter_id'] != $lastChapterId) {
                            $readMessageCount = $readMessageCount + $chapterItem['message_count'];
                        } else if ($chapterItem['chapter_id'] == $lastChapterId) {
                            $readMessageCount = $readMessageCount + $lastMessageNumber;
                        }
                    }
                }

                $val = $readMessageCount / $totalMessageCount;
                $userReadProgressArr[$storyId] = round($val, 2);
            } else {
                $userReadProgressArr[$storyId] = 0;
            }
        }

        //组装故事数据
        $dataItem = array();
        foreach ($userReadStoryRecords as $item) {

            $dataItem['story_id'] = $item['story']['story_id'];
            $dataItem['name'] = $item['story']['name'];
            $dataItem['description'] = $item['story']['description'];
            $dataItem['cover'] = $item['story']['cover'];
            $dataItem['chapter_count'] = $item['story']['chapter_count'];
            $dataItem['message_count'] = $item['story']['message_count'];
            $dataItem['taps'] = $item['story']['taps'];
            $dataItem['is_published'] = $item['story']['is_published'];
            $dataItem['story_update_time'] = $item['story']['last_modify_time'];
            $dataItem['user_read_progress'] = $userReadProgressArr[$item['story']['story_id']];
            $dataItem['last_chapter_id'] = $item['last_chapter_id'];
            $dataItem['last_message_id'] = $item['last_message_id'];

            //组装消息内容序号
            if (isset($messageNumberArr[$dataItem['last_message_id']]) && !empty($messageNumberArr[$dataItem['last_message_id']])) {
                $dataItem['last_message_number'] = $messageNumberArr[$dataItem['last_message_id']];
            }

            $dataItem['create_time'] = Carbon::createFromTimestamp($item['create_time'])->toDateTimeString();
            $dataItem['last_modify_time'] = Carbon::createFromTimestamp($item['last_modify_time'])->toDateTimeString();

            //合并故事和作者数据
            if (!empty($userInfoList[$item['uid']])) {
                $dataItem['user'] = $userInfoList[$item['uid']];
            } else {
                $dataItem['user'] = array();
            }
            $storyList[] = $dataItem;
        }
        return $storyList;
    }

    /**
     * 提交阅读记录更改(新增,修改,删除)
     * {该方法的命名不是特别满意}
     * @param $uid 用户uid
     * @return mixed
     */
    public function actionBatchProcess($uid)
    {

        $response = Yii::$app->getResponse();
        $inputReadStoryRecords = Yii::$app->getRequest()->post('read_story_records');
//        $uid = Yii::$app->getRequest()->post('uid');
        $ret = array();
        $data = array();
        $hasError = false;
        $userModel = Yii::$app->user->identity;
        $ret['data'] = $data;
        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {

                if (!empty($inputReadStoryRecords)) {
                    foreach ($inputReadStoryRecords as $readStoryRecordItem) {
                        $transaction = Yii::$app->db->beginTransaction();
                        try {

                            $readStoryRecordItem['uid'] = $uid;
                            $condition = array(
                                'uid' => $readStoryRecordItem['uid'],
                                'story_id' => $readStoryRecordItem['story_id']
                            );
                            $readStoryRecordModel = UserReadStoryRecord::findOne($condition);
                            if (is_null($readStoryRecordModel)) {
                                $readStoryRecordModel = new UserReadStoryRecord();
                                $readStoryRecordModel->loadDefaultValues();
                            }

                            $readStoryRecordModel->setAttributes($readStoryRecordItem);
                            $readStoryRecordItem['create_time'] = $readStoryRecordItem['create_time'];
                            $readStoryRecordItem['last_modify_time'] = $readStoryRecordItem['last_modify_time'];

                            $readStoryRecordModel->save();
                            if ($readStoryRecordModel->hasErrors()) {
                                Yii::error($readStoryRecordModel->getErrors());
                                throw new ServerErrorHttpException('操作失败');
                            }

                            $transaction->commit();
                            $data['userReadStoryRecordList'][] = $readStoryRecordModel->getAttributes();

                        } catch (\Exception $e) {

                            //如果抛出错误则进入catch，先callback，然后捕获错误，返回错误
                            $hasError = true;
                            $transaction->rollBack();
//                    Yii::error($e->getMessage());
                            $response->statusCode = 400;
                            $response->statusText = '系统出现错误';
                        }
                    }

                    if (count($data) > 0 && $hasError) {
                        $response->statusCode = 206;
                        $response->statusText = '成功处理部分数据';
                    }
                } else {
                    $response->statusCode = 400;
                    $response->statusText = '输入参数错误';
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
}