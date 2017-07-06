<?php

namespace backend\controllers;

use common\models\StoryTagRelation;
use common\models\Tag;
use common\models\UploadForm;
use common\models\User;
use Yii;
use common\models\Story;
use common\models\StorySearch;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * StoryController implements the CRUD actions for Story model.
 */
class StoryController extends Controller
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
     * Lists all Story models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        //批量获取标签
        $models = $dataProvider->getModels();
        $storyIdArr = array();
        $storyUidArr = array();
        if(!empty($models)) {

            foreach ($models as $model) {
                $storyIdArr[] = $model->story_id;
                $storyUidArr[] = $model->uid;
            }
        }

        $storyTagRelationCondition = array(
            'story_id' => $storyIdArr,
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        );
        $storyTagidArr = StoryTagRelation::find()->where($storyTagRelationCondition)->asArray()->all();
        $storyTagidArr = ArrayHelper::index($storyTagidArr,null,'story_id');

        $tagCondition = array(
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        );
        $tagArr = Tag::find($tagCondition)->asArray()->all();
        $tagArr = ArrayHelper::index($tagArr,'tag_id');

        $storyTagArr = array();
        if(!empty($storyTagidArr) && !empty($tagArr)) {

            foreach ($storyTagidArr as $storyId => $storyTagRelArr) {
                if(!empty($storyTagRelArr)) {
                    foreach ($storyTagRelArr as $key => $storyTagRelItem) {

                        if(isset($tagArr[$storyTagRelItem['tag_id']]['name'])) {
                            $storyTagRelItem['tag_name'] = $tagArr[$storyTagRelItem['tag_id']]['name'];
                        }else {
                            $storyTagRelItem['tag_name'] = '';
                        }
                        $storyTagArr[$storyId][$key] = $storyTagRelItem;
                    }
                }
            }
        }

        //批量获取作者
        $userCondition = array(
            'uid' => $storyUidArr,
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        );

        $storyUserArr = User::find()->where($userCondition)->asArray()->all();
        $storyUserArr = ArrayHelper::index($storyUserArr,'uid');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'storyTagArr' => $storyTagArr,
            'storyUserArr' => $storyUserArr,
        ]);
    }

    /**
     * Displays a single Story model.
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
     * Creates a new Story model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Story();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->story_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }


    /**
     * 上传txt格式的故事内容
     * @return string
     */
    public function actionUpload()
    {
        $uploadFormModel = new UploadForm();

        if (Yii::$app->request->isPost) {
            $uploadFormModel->file = UploadedFile::getInstance($uploadFormModel, 'file');

            if ($uploadFormModel->validate()) {
                $file = Yii::getAlias('@backend/web/uploads/') . $uploadFormModel->file->baseName . '.' . $uploadFormModel->file->extension;
                if($uploadFormModel->file->saveAs($file)) {

                    //解析处理故事文件
                    $storyModel = new Story();
                    $storyModel->parseFile($file);
                }
            }
        }

        return $this->render('upload', ['model' => $uploadFormModel]);
    }

    /**
     * Updates an existing Story model.
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

        //获取所有的tag
        $tagCondition = array(
            'status' => Yii::$app->params['STATUS_ACTIVE']
        );

        $allTagArr = Tag::find($tagCondition)
            ->orderBy(['number' => SORT_DESC])
            ->asArray()
            ->all();

        //获取故事tag并转为数组
        $checkTagArr = ArrayHelper::toArray($model->tags,[
            'common\models\Tag'=>[
                'tag_id',
                'name',
                'number',
                'create_time',
                'last_modify_time',
                'status'
            ]
        ]);

        if (Yii::$app->request->isPost) {

            $transaction = Yii::$app->db->beginTransaction();
            try{

                $cover = $model->cover;
                $model->load(Yii::$app->request->post());
                $uploadFormModel->file = UploadedFile::getInstanceByName('Story[cover]');

                if(!empty($uploadFormModel->file)) {
                    $coverUrl = $uploadFormModel->uploadPicOss($uid,Yii::$app->params['ossPicObjectCoverPrefix']);
                    if (!empty($coverUrl)) {
                        $model->cover = $coverUrl;
                    }
                }

                //Yii2 会自动生成一个hidden的cover,但是value却未空
                //导致什么都不做更改的情况下,cover会被2次设置为空
                //原因没有找到.通过下面的方法规避一下
                //https://stackoverflow.com/questions/34593023/yii-2-file-input-renders-hidden-file-input-tag
                if(empty($model->cover)) {
                    $model->cover = $cover;
                }

                //批量修改标签
                $storyTagPair = array();
                $storyId = $model->story_id;
                $inputPost = Yii::$app->request->post();
                $checkTagIdArr = ArrayHelper::getColumn($checkTagArr,'tag_id');
                sort($checkTagIdArr);
                if (!empty($inputPost['Story']['tags'])) {

                    sort($inputPost['Story']['tags']);
                    if($checkTagIdArr != $inputPost['Story']['tags']) {

                        //新增的tag
                        foreach ($inputPost['Story']['tags'] as $tagId) {
                            $storyTagPair[] = array($storyId,$tagId,Yii::$app->params['STATUS_ACTIVE']);
                        }

                        //被删除的tag
                        $unCheckTagIdArr = array_diff($checkTagIdArr,$inputPost['Story']['tags']);
                        if(!empty($unCheckTagIdArr)) {

                            foreach ($unCheckTagIdArr as $tagId) {
                                $storyTagPair[] = array($storyId,$tagId,Yii::$app->params['STATUS_DELETED']);
                            }
                        }

                        $command = Yii::$app->getDb()->createCommand()->batchInsert('story_tag_relation',
                            [ 'story_id',  'tag_id','status'],
                            $storyTagPair
                        );

                        $sql = $command->getSql()." ON DUPLICATE KEY UPDATE `status`=VALUES(`status`);";
                        Yii::$app->getDb()->createCommand($sql)->execute();
                    }
                }

                $isSaved = $model->save();
                $transaction->commit();
                if($isSaved) {
                    return $this->redirect(['view', 'id' => $model->story_id]);
                }

            }catch (\Exception $e) {
                $transaction->rollBack();
                print $e->getMessage();
                print $e->getTrace();
            }

        } else {

            return $this->render('update', [
                'model' => $model,
                'allTagArr' => $allTagArr,
                'checkTagArr' => $checkTagArr,
            ]);
        }
    }

    /**
     * Deletes an existing Story model.
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
     * Finds the Story model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Story the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Story::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
