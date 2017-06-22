<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
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

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 动作
        unset($actions['delete'], $actions['create'], $actions['view']);

        // 使用"prepareDataProvider()"方法自定义数据provider
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    //新建章节: 新建章节信息,写入章节消息内容
    //修改章节: 修改章节内容
    //删除章节: 消息内容已同步->章节信息及内容状态设置为删除,消息内容未同步->写入章节消息内容,章节信息及内容状态设置为删除
    public function actionUploadMessageContent()
    {
        $response = Yii::$app->getResponse();
        $data = array();
        $uploadFormModel = new UploadForm();

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
                $file = SITE_ROOT.'/../uploads/' . $uploadFormModel->file->baseName . '.' . $uploadFormModel->file->extension;
                $uploadFormModel->file->saveAs($file);
                $messageContent = file_get_contents($file);

                if(!empty($messageContent)) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                            $chapterModel = new Chapter();
                            $chapterModel->loadDefaultValues();
                            foreach ($chapterModel->attributes as $attName => $attValue) {
                                if(!empty($input[$attName])) {
                                    $chapterModel[$attName] = $input[$attName];
                                }
                            }

                            $chapterModel->save();
                            if($chapterModel->hasErrors()) {
                                Yii::error($chapterModel->getErrors());
                                throw new ServerErrorHttpException('章节操作失败');
                            }

                            $chapterMessageContentModel = new ChapterMessageContent();
                            $chapterMessageContentModel->chapter_id = $chapterModel->chapter_id;
                            $chapterMessageContentModel->story_id = $chapterModel->story_id;
                            $chapterMessageContentModel->message_content = $messageContent;
                            $chapterMessageContentModel->status = $chapterModel->status;
                            $chapterMessageContentModel->create_time = $chapterModel->create_time;
                            $chapterMessageContentModel->last_modify_time = $chapterModel->last_modify_time;
                            $chapterMessageContentModel->save();
                            if($chapterMessageContentModel->hasErrors()) {
                                Yii::error($chapterModel->getErrors());
                                throw new ServerErrorHttpException('消息内容操作失败');
                            }

                            unlink($file);
                            $transaction->commit();

                            $data['story_id'] = $chapterModel->story_id;
                            $data['chapter_id'] = $chapterModel->chapter_id;
                            $data['status'] = $chapterModel->status;
                            $data['create_time'] = $chapterModel->create_time;
                            $data['last_modify_time'] = $chapterModel->last_modify_time;
                            $data['message_content'] = $chapterMessageContentModel->message_content;


                    }catch (\Exception $e){

                        //如果抛出错误则进入catch，先callback，然后捕获错误，返回错误
                        $transaction->rollBack();
                        Yii::error($e->getMessage());
                        $response->statusCode = 400;
                        $response->statusText = $e->getMessage();
                    }
                }else{
                    $response->statusCode = 400;
                    $response->statusText = '消息内容为空';
                }
            }else {
                $response->statusCode = 400;
                $response->statusText = '参数错误';
            }

        }else {
            $response->statusCode = 400;
            $response->statusText = '参数错误';
        }

        $ret['data'] = $data;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }




}
