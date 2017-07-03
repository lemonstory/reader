<?php

namespace backend\controllers;

use common\models\UploadForm;
use Yii;
use common\models\Chapter;
use common\models\ChapterSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
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

            if(!empty($uploadFormModel->file)) {
                $backgroundUrl = $uploadFormModel->uploadPicOss($uid,Yii::$app->params['ossPicObjectBackgroundPrefix']);
                if (!empty($backgroundUrl)) {
                    $model->background = $backgroundUrl;
                }
            }

            //Yii2 会自动生成一个hidden的cover,但是value却未空
            //导致什么都不做更改的情况下,cover会被2次设置为空
            //原因没有找到.通过下面的方法规避一下
            //https://stackoverflow.com/questions/34593023/yii-2-file-input-renders-hidden-file-input-tag
            if(empty($model->background)) {
                $model->background = $background;
            }

            if($model->save()) {
                return $this->redirect(['update', 'id' => $model->chapter_id]);
            }else {
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
