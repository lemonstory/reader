<?php

namespace backend\controllers;

use common\models\StoryActor;
use Yii;
use common\models\ChapterMessageContent;
use common\models\ChapterMessageContentSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
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

            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'ips' => Yii::$app->params['adminIps'],
                        'matchCallback' => function ($rule, $action) {
                            $uid = Yii::$app->getUser()->getId();
                            if(!empty($uid) && ArrayHelper::isIn($uid,Yii::$app->params['adminUidWhiteList'])) {
                                return true;
                            }else {
                                return false;
                            }
                        }
                    ],
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

        $storyActorIdPairArr = array();
        $models = $dataProvider->getModels();
        if(!empty($models)) {

            foreach ($models as $model) {
                $storyActorIdPairArr[$model->story_id] = $model->actor_id;
            }
        }

        //获取故事角色
        $storyActorArr = array();
        if(!empty($storyActorIdPairArr)) {

            $storyIdArr = array_keys($storyActorIdPairArr);
            $condition = array(
                'is_visible' => Yii::$app->params['STATUS_ACTIVE'],
                'status' => Yii::$app->params['STATUS_ACTIVE'],
                'story_id' => $storyIdArr,
            );
            $query = $storyActorArr = StoryActor::find()->where($condition);
            foreach ($storyActorIdPairArr as $storyId => $actorId) {

                if(!empty($actorId)) {
                    $orCondition = sprintf("`story_id`=%s AND `actor_id`=%s",$storyId,$actorId);
                    $query->orWhere($orCondition);
                }
            }

            // get the AR raw sql in YII2
//            $commandQuery = clone $query;
//            echo $commandQuery->createCommand()->getRawSql();
            $storyActorArr = $query->asArray()->all();
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'storyActorArr' => $storyActorArr,
        ]);
    }

    /**
     * Displays a single ChapterMessageContent model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
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
            return $this->redirect(['view', 'id' => $model->message_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ChapterMessageContent model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->message_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ChapterMessageContent model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ChapterMessageContent model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ChapterMessageContent the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ChapterMessageContent::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
