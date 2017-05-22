<?php

namespace backend\controllers;

use Yii;
use common\models\StoryUpdateSubscription;
use common\models\StoryUpdateSubscriptionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * StoryUpdateSubscriptionController implements the CRUD actions for StoryUpdateSubscription model.
 */
class StoryUpdateSubscriptionController extends Controller
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
     * Lists all StoryUpdateSubscription models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StoryUpdateSubscriptionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StoryUpdateSubscription model.
     * @param integer $uid
     * @param integer $story_id
     * @return mixed
     */
    public function actionView($uid, $story_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($uid, $story_id),
        ]);
    }

    /**
     * Creates a new StoryUpdateSubscription model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new StoryUpdateSubscription();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'uid' => $model->uid, 'story_id' => $model->story_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing StoryUpdateSubscription model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $uid
     * @param integer $story_id
     * @return mixed
     */
    public function actionUpdate($uid, $story_id)
    {
        $model = $this->findModel($uid, $story_id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'uid' => $model->uid, 'story_id' => $model->story_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing StoryUpdateSubscription model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $uid
     * @param integer $story_id
     * @return mixed
     */
    public function actionDelete($uid, $story_id)
    {
        $this->findModel($uid, $story_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the StoryUpdateSubscription model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $uid
     * @param integer $story_id
     * @return StoryUpdateSubscription the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($uid, $story_id)
    {
        if (($model = StoryUpdateSubscription::findOne(['uid' => $uid, 'story_id' => $story_id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
