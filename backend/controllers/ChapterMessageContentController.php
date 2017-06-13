<?php

namespace backend\controllers;

use Yii;
use common\models\ChapterMessageContent;
use common\models\ChapterMessageContentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ChapterMessageContentController implements the CRUD actions for ChapterMessageContent model.
 */
class ChapterMessageContentController extends Controller
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
     * Lists all ChapterMessageContent models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ChapterMessageContentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ChapterMessageContent model.
     * @param integer $chapter_id
     * @param integer $story_id
     * @return mixed
     */
    public function actionView($chapter_id, $story_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($chapter_id, $story_id),
        ]);
    }

    /**
     * Creates a new ChapterMessageContent model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ChapterMessageContent();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'chapter_id' => $model->chapter_id, 'story_id' => $model->story_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ChapterMessageContent model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $chapter_id
     * @param integer $story_id
     * @return mixed
     */
    public function actionUpdate($chapter_id, $story_id)
    {
        $model = $this->findModel($chapter_id, $story_id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'chapter_id' => $model->chapter_id, 'story_id' => $model->story_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ChapterMessageContent model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $chapter_id
     * @param integer $story_id
     * @return mixed
     */
    public function actionDelete($chapter_id, $story_id)
    {
        $this->findModel($chapter_id, $story_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ChapterMessageContent model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $chapter_id
     * @param integer $story_id
     * @return ChapterMessageContent the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($chapter_id, $story_id)
    {
        if (($model = ChapterMessageContent::findOne(['chapter_id' => $chapter_id, 'story_id' => $story_id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
