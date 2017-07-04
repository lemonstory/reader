<?php

namespace backend\controllers;

use common\models\User;
use Yii;
use common\models\Like;
use common\models\LikeSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * LikeController implements the CRUD actions for Like model.
 */
class LikeController extends Controller
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
     * Lists all Like models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LikeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        //批量获取用户信息
        $models = $dataProvider->getModels();
        $UidArr = array();
        if(!empty($models)) {

            foreach ($models as $model) {
                $UidArr[] = $model->owner_uid;
                $UidArr[] = $model->target_uid;
            }
        }

        $userCondition = array(
            'uid' => $UidArr,
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        );

        $userArr = User::find()->where($userCondition)->asArray()->all();
        $userArr = ArrayHelper::index($userArr,'uid');

        //批量获取被赞对象内容
        //被赞对象为故事：内容=故事标题
        //被赞对象为评论：内容=故事标题




        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'userArr' => $userArr,
        ]);
    }

    /**
     * Displays a single Like model.
     * @param integer $target_id
     * @param integer $target_type
     * @param integer $owner_uid
     * @param integer $target_uid
     * @return mixed
     */
    public function actionView($target_id, $target_type, $owner_uid, $target_uid)
    {
        return $this->render('view', [
            'model' => $this->findModel($target_id, $target_type, $owner_uid, $target_uid),
        ]);
    }

    /**
     * Creates a new Like model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Like();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'target_id' => $model->target_id, 'target_type' => $model->target_type, 'owner_uid' => $model->owner_uid, 'target_uid' => $model->target_uid]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Like model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $target_id
     * @param integer $target_type
     * @param integer $owner_uid
     * @param integer $target_uid
     * @return mixed
     */
    public function actionUpdate($target_id, $target_type, $owner_uid, $target_uid)
    {
        $model = $this->findModel($target_id, $target_type, $owner_uid, $target_uid);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'target_id' => $model->target_id, 'target_type' => $model->target_type, 'owner_uid' => $model->owner_uid, 'target_uid' => $model->target_uid]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Like model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $target_id
     * @param integer $target_type
     * @param integer $owner_uid
     * @param integer $target_uid
     * @return mixed
     */
    public function actionDelete($target_id, $target_type, $owner_uid, $target_uid)
    {
        $this->findModel($target_id, $target_type, $owner_uid, $target_uid)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Like model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $target_id
     * @param integer $target_type
     * @param integer $owner_uid
     * @param integer $target_uid
     * @return Like the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($target_id, $target_type, $owner_uid, $target_uid)
    {
        if (($model = Like::findOne(['target_id' => $target_id, 'target_type' => $target_type, 'owner_uid' => $owner_uid, 'target_uid' => $target_uid])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
