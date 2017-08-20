<?php

namespace api\controllers;

use common\components\MnsQueue;
use common\components\QueueMessageHelper;
use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\Story;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use Yii;
use common\models\UploadForm;
use yii\web\ServerErrorHttpException;
use common\components\DateTimeHelper;

class ChapterController extends ActiveController
{
    public $modelClass = 'common\models\Chapter';
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

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 动作
        unset($actions['delete'], $actions['create'], $actions['view']);

        // 使用"prepareDataProvider()"方法自定义数据provider
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    /**
     * 新建章节: 新建章节信息,写入章节消息内容
     * 修改章节: 修改章节内容
     * 删除章节: 消息内容已同步->章节信息及内容状态设置为删除,消息内容未同步->写入章节消息内容,章节信息及内容状态设置为删除
     * @param $uid 作者Uid
     * @return mixed
     */
    public function actionCommitMessageContent($uid)
    {
        $userModel = Yii::$app->user->identity;
        $response = Yii::$app->getResponse();
        $data = array();
        $uploadFormModel = new UploadForm();
        $ret['data'] = array();

        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {
                if (Yii::$app->request->isPost) {
                    $input['local_story_id'] = Yii::$app->request->post('local_story_id');
                    $input['story_id'] = Yii::$app->request->post('story_id');
                    $input['local_chapter_id'] = Yii::$app->request->post('local_chapter_id');
                    $input['chapter_id'] = Yii::$app->request->post('chapter_id');
                    $input['status'] = Yii::$app->request->post('status');
                    $input['create_time'] = DateTimeHelper::inputCheck(Yii::$app->request->post('create_time'));
                    $input['create_time'] = $input['create_time'] = DateTimeHelper::convert($input['create_time'], 'datetime');
                    $input['last_modify_time'] = DateTimeHelper::inputCheck(Yii::$app->request->post('last_modify_time'));
                    $input['last_modify_time'] = $input['last_modify_time'] = DateTimeHelper::convert($input['last_modify_time'], 'datetime');
                    $data['local_story_id'] = $input['local_story_id'];
                    $data['local_chapter_id'] = $input['local_chapter_id'];
                    $uploadFormModel->file = UploadedFile::getInstanceByName( 'chapter_message_content');
                    if ($uploadFormModel->validate()) {

                        //TODO:目录定义需要更改
                        define ('SITE_ROOT', realpath(dirname(__FILE__)));
                        $file = Yii::getAlias('@api/web/uploads/') . $uploadFormModel->file->baseName . '.' . $uploadFormModel->file->extension;
                        $uploadFormModel->file->saveAs($file);
                        $transaction = Yii::$app->db->beginTransaction();
                        try {

                            if(!empty($input['story_id']) && !empty($input['chapter_id'])) {
                                $chapterCondition = array(
                                    'chapter_id' => $input['chapter_id'],
                                    'story_id' => $input['story_id'],
                                    'status' => Yii::$app->params['STATUS_ACTIVE'],
                                );
                                $chapterModel = Chapter::findOne($chapterCondition);
                            }

                            if($chapterModel === null) {
                                $chapterModel = new Chapter();
                                $chapterModel->loadDefaultValues();
                            }

                            foreach ($chapterModel->attributes as $attName => $attValue) {
                                if(!empty($input[$attName])) {
                                    $chapterModel[$attName] = $input[$attName];
                                }
                            }

                            //处理章节消息内容
                            $messageCount = 0;
                            if (file_exists($file)) {
                                $messageContentXml = simplexml_load_file($file, null, LIBXML_NOCDATA);
                                if ($messageContentXml) {
                                    $messageCount = count($messageContentXml->chapter_message_content->message);
                                }
                            }

                            $chapterModel->message_count = $messageCount;
                            $chapterModel->save();
                            if($chapterModel->hasErrors()) {
                                foreach ($chapterModel->getErrors() as $attribute => $error) {
                                    foreach ($error as $message) {
                                        throw new ServerErrorHttpException($attribute.": ".$message);
                                    }
                                }
                            }
                            $chapterId = $chapterModel->chapter_id;
                            $storyId = $chapterModel->story_id;
                            //这里可能有新增,或修改
                            if($messageCount > 0) {
                                foreach ($messageContentXml->chapter_message_content->message as $messageItem) {

                                    $messageId = (string)$messageItem->message_id;
                                    $chapterMessageContentModel = null;
                                    if(!empty($messageId)) {
                                        $messageCondition = array(
                                            'message_id' => $messageId,
                                            'chapter_id' => $chapterId,
                                            'story_id' => $chapterModel->story_id,
                                        );
                                        $chapterMessageContentModel = ChapterMessageContent::findOne($messageCondition);
                                    }
                                    if($chapterMessageContentModel === null) {
                                        $chapterMessageContentModel = new ChapterMessageContent();
                                    }
                                    $chapterMessageContentModel->chapter_id = $chapterId;
                                    $chapterMessageContentModel->story_id = $chapterModel->story_id;
                                    $chapterMessageContentModel->message_id = $messageId;
                                    $chapterMessageContentModel->number = (string)$messageItem->number;
                                    $chapterMessageContentModel->voice_over = (string)$messageItem->voice_over;
                                    $chapterMessageContentModel->actor_id = (string)$messageItem->actor->actor_id;
                                    $chapterMessageContentModel->text = (string)$messageItem->text;
                                    $chapterMessageContentModel->img = (string)$messageItem->img;
                                    $chapterMessageContentModel->status = (string)$messageItem->status;
                                    $chapterMessageContentModel->save();
                                    if($chapterMessageContentModel->hasErrors()) {

                                        foreach ($chapterMessageContentModel->getErrors() as $attribute => $error) {
                                            foreach ($error as $message) {
                                                throw new ServerErrorHttpException($attribute.": ".$message);
                                            }
                                        }
                                    }
                                }
                                //处理成功的文件会被删除
                                unlink($file);
                            }

                            //修改故事章节总数量,消息总数量
                            $storyCondition = array(
                                'story_id' => $storyId,
                                'status' => Yii::$app->params['STATUS_ACTIVE'],
                            );
                            $chapterCount = Chapter::find()->where($storyCondition)->count();
                            $messageCount = ChapterMessageContent::find()->where($storyCondition)->count();

                            $storyModel = Story::find()->where($storyCondition)->one();
                            $storyModel->chapter_count = $chapterCount;
                            $storyModel->message_count = $messageCount;
                            $storyModel->save();
                            if($storyModel->hasErrors()) {
                                foreach ($storyModel->getErrors() as $attribute => $error) {
                                    foreach ($error as $message) {
                                        throw new ServerErrorHttpException($attribute.": ".$message);
                                    }
                                }
                            }

                            $transaction->commit();
                            $data['story_id'] = $chapterModel->story_id;
                            $data['chapter_id'] = $chapterModel->chapter_id;
                            $data['status'] = $chapterModel->status;
                            $data['create_time'] = $chapterModel->create_time;
                            $data['last_modify_time'] = $chapterModel->last_modify_time;
                            $data['message_count'] = $messageCount;

                            //消息通知->用户发布新章节
                            $mnsQueue = new MnsQueue();
                            $queueName = Yii::$app->params['mnsQueueNotifyName'];
                            $messageBody = QueueMessageHelper::postChapter($uid, $chapterModel->story_id, $chapterModel->chapter_id);
                            $mnsQueue->sendMessage($messageBody, $queueName);

                        }catch (\Exception $e){

                            //如果抛出错误则进入catch，先callback，然后捕获错误，返回错误
                            $transaction->rollBack();
                            Yii::error($e->getMessage());
                            $response->statusCode = 400;
                            $response->statusText = $e->getMessage();
                        }

                    }else {

                        foreach ($uploadFormModel->getErrors() as $attribute => $error) {
                            foreach ($error as $message) {
                                //throw new Exception($attribute.": ".$message);
                                $response->statusCode = 400;
                                $response->statusText = $message;
                            }
                        }
                    }

                } else {
                    $response->statusCode = 400;
                    $response->statusText = '参数为空';
                }

                $ret['data'] = $data;
                $ret['status'] = $response->statusCode;
                $ret['message'] = $response->statusText;
            }else {
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
