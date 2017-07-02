<?php

namespace backend\controllers;

use common\models\UploadForm;
use Yii;
use common\models\Story;
use common\models\StorySearch;
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

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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
     * Updates an existing Story model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {

        $uid = 0;
        $model = $this->findModel($id);
        $uploadFormModel = new UploadForm();

        if (Yii::$app->request->isPost) {

            $cover = $model->cover;
            $model->load(Yii::$app->request->post());
            $uploadFormModel->file = UploadedFile::getInstanceByName('Story[cover]');

            if(!empty($uploadFormModel->file)) {
                $coverUrl = $uploadFormModel->uploadPicOss($uid);
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

            if($model->save()) {
                return $this->redirect(['view', 'id' => $model->story_id]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
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
