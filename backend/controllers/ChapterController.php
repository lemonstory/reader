<?php

namespace backend\controllers;

use common\models\ChapterMessageContent;
use common\models\Story;
use common\models\StoryActor;
use common\models\UploadForm;
use Yii;
use common\models\Chapter;
use common\models\ChapterSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * ChapterController implements the CRUD actions for Chapter model.
 */
class ChapterController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Chapter models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ChapterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Chapter model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Chapter model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Chapter();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->chapter_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Chapter model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        //TODO:获取用户UID
        $uid = 0;
        $model = $this->findModel($id);
        $uploadFormModel = new UploadForm();

        if (Yii::$app->request->isPost) {

            $background = $model->background;
            $model->load(Yii::$app->request->post());
            $uploadFormModel->file = UploadedFile::getInstanceByName('Chapter[background]');

            if (!empty($uploadFormModel->file)) {
                $backgroundUrl = $uploadFormModel->uploadPicOss($uid, Yii::$app->params['ossPicObjectBackgroundPrefix']);
                if (!empty($backgroundUrl)) {
                    $model->background = $backgroundUrl;
                }
            }

            //Yii2 会自动生成一个hidden的cover,但是value却未空
            //导致什么都不做更改的情况下,cover会被2次设置为空
            //原因没有找到.通过下面的方法规避一下
            //https://stackoverflow.com/questions/34593023/yii-2-file-input-renders-hidden-file-input-tag
            if (empty($model->background)) {
                $model->background = $background;
            }

            if ($model->save()) {
                return $this->redirect(['update', 'id' => $model->chapter_id]);
            } else {
                print $model->getErrors();
            }

        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Chapter model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * 上传txt格式的章节内容
     * @return string
     */
    public function actionUpload()
    {

        $uploadFormModel = new UploadForm();
        if (Yii::$app->request->isPost) {
            $storyId = intval(Yii::$app->request->post('story_id', 0));
            if ($storyId > 0) {

                $uploadFormModel->file = UploadedFile::getInstance($uploadFormModel, 'file');
                if ($uploadFormModel->validate()) {
                    $file = Yii::getAlias('@backend/web/uploads/') . $uploadFormModel->file->baseName . '.' . $uploadFormModel->file->extension;
                    if ($uploadFormModel->file->saveAs($file)) {

                        //解析处理故事文件
                        $storyCondition = ['story_id' => $storyId];
                        $storyModel = Story::find()->where($storyCondition)->one();
                        $story = $storyModel->parseFile($file, Story::FILE_PARSE_TYPE_CHAPTER);
                        if (!empty($storyId)) {
                            //获取角色信息
                            $actorCondition = array(
                                'story_id' => $storyId,
                                'status' => Yii::$app->params['STATUS_ACTIVE'],
                                'is_visible' => Yii::$app->params['STATUS_ACTIVE'],
                            );
                            //角色字段
                            $actorColumns = array(
                                'actor_id',
                                'name',
                                'number',
                                'location',
                            );
                            $story['actorArr'] = StoryActor::find()->select($actorColumns)->where($actorCondition)->asArray()->all();

                            //获取章节信息
                            $chapterCondition = array(
                                'story_id' => $storyId,
                                'status' => Yii::$app->params['STATUS_ACTIVE'],
                            );
                            //章节字段
                            $chapterColumns = array(
                                'chapter_id',
                                'name',
                                'number',
                            );
                            $story['chapterArr'] = Chapter::find()->select($chapterColumns)->where($chapterCondition)->asArray()->all();
                        }

                        //数据存储
                        if (!empty($story) && is_array($story)) {
                            $transaction = Yii::$app->db->beginTransaction();
                            try {

                                //故事
                                $storyModel->chapter_count = $storyModel->chapter_count + $story['add_chapter_count'];
                                $storyModel->message_count = $storyModel->message_count + $story['add_message_count'];
                                if ($storyModel->save()) {
                                    $storyId = $storyModel->story_id;
                                } else {
                                    print_r($storyModel->getErrors());
                                    throw new ServerErrorHttpException('修改故事章节数量及消息数量失败');
                                }

                                //章节
                                $chapterNumberIdPair = array();
                                if (!empty($story['addChapterArr']) && is_array($story['addChapterArr'])) {

                                    foreach ($story['addChapterArr'] as $chapterItem) {

                                        //检查章节序号是否已经存在
                                        $existChapterNumberIdPair = ArrayHelper::map($story['chapterArr'], 'number', 'chapter_id');
                                        if (!ArrayHelper::keyExists($chapterItem['number'], $existChapterNumberIdPair, false)) {
                                            $messageCount = 0;
                                            if (isset($story['addMessageArr'][$chapterItem['number']]) && is_array($story['addMessageArr'][$chapterItem['number']])) {
                                                $messageCount = count($story['addMessageArr'][$chapterItem['number']]);
                                            }
                                            $chapterModel = new Chapter();
                                            $chapterModel->story_id = $storyId;
                                            $chapterModel->name = $chapterItem['name'];
                                            $chapterModel->number = $chapterItem['number'];
                                            $chapterModel->message_count = $messageCount;
                                            $chapterModel->status = Yii::$app->params['STATUS_ACTIVE'];
                                            $chapterModel->is_published = Yii::$app->params['STATUS_UNPUBLISHED'];
                                            if ($chapterModel->save()) {
                                                $chapterId = $chapterModel->chapter_id;
                                                $chapterNumberIdPair[$chapterItem['number']] = $chapterId;
                                            } else {
                                                print_r($chapterModel->getErrors());
                                                throw new ServerErrorHttpException('新建章节失败');
                                            }
                                        } else {
                                            throw new ServerErrorHttpException('章节序号出现冲突: #' . $chapterItem['number'] . '已存在');
                                        }
                                    }
                                } else {
                                    throw new ServerErrorHttpException('没有章节信息');
                                }

                                //角色
                                $actorNameIdPair = ArrayHelper::map($story['actorArr'], 'name', 'actor_id');

                                //消息
                                if (!empty($story['addMessageArr'])
                                    && is_array($story['addMessageArr'])
                                    && !empty($actorNameIdPair)
                                    && is_array($actorNameIdPair)
                                    && !empty($chapterNumberIdPair)
                                    && is_array($chapterNumberIdPair)
                                ) {

                                    $messageRows = array();
                                    foreach ($story['addMessageArr'] as $chapterNumber => $chapterMessageArr) {

                                        $chapterId = $chapterNumberIdPair[$chapterNumber];
                                        if (!empty($chapterId)) {
                                            foreach ($chapterMessageArr as $index => $messageItem) {

                                                $messageRow['story_id'] = $storyId;
                                                $messageRow['chapter_id'] = $chapterId;
                                                $actorId = 0;
                                                if (!empty($messageItem['actorName'])) {
                                                    if (isset($actorNameIdPair[$messageItem['actorName']]) && !empty($actorNameIdPair[$messageItem['actorName']])) {
                                                        $actorId = $actorNameIdPair[$messageItem['actorName']];
                                                    } else {
                                                        throw new ServerErrorHttpException($messageItem['actorName'] . ': 角色不存在');
                                                    }
                                                }
                                                $messageRow['actor_id'] = $actorId;
                                                $messageRow['text'] = $messageItem['text'];
                                                $messageRow['voice_over'] = $messageItem['voiceOver'];
                                                $number = $index + 1;
                                                $messageRow['number'] = $number;
                                                $messageRow['status'] = Yii::$app->params['STATUS_ACTIVE'];
                                                $messageRows[] = $messageRow;
                                            }

                                        } else {
                                            throw new ServerErrorHttpException('消息内容没有章节Id');
                                        }
                                    }

                                    $messageColumns = ['story_id', 'chapter_id', 'actor_id', 'text', 'voice_over', 'number', 'status'];
                                    $sql = Yii::$app->db->createCommand()->batchInsert(ChapterMessageContent::tableName(), $messageColumns, $messageRows);
                                    $messageAffectedRows = $sql->execute();
                                } else {
                                    throw new ServerErrorHttpException('没有消息内容(或)角色名称-角色id对为空(或)章节序号-章节id对为空');
                                }
                                $transaction->commit();
                                echo "成功";
                            } catch (\Exception $e) {
                                $transaction->rollBack();
                                echo $e->getMessage();
                                echo "失败";
                            }
                        }
                    }
                }
            }
        }
        return $this->render('upload', ['model' => $uploadFormModel]);
    }

    /**
     * Finds the Chapter model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Chapter the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Chapter::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
