<?php

namespace backend\controllers;

use common\models\UploadForm;
use Yii;
use common\models\StoryActor;
use common\models\StoryActorSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * StoryActorController implements the CRUD actions for StoryActor model.
 */
class StoryActorController extends Controller
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
                            if (!empty($uid) && ArrayHelper::isIn($uid, Yii::$app->params['adminUidWhiteList'])) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all StoryActor models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StoryActorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StoryActor model.
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
     * Creates a new StoryActor model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new StoryActor();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->actor_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing StoryActor model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $uploadFormModel = new UploadForm();

        if (Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {

                $avatar = $model->avatar;
                $model->load(Yii::$app->request->post());
                $uploadFormModel->file = UploadedFile::getInstanceByName('StoryActor[avatar]');

                if (!empty($uploadFormModel->file)) {
                    $avatarUrl = $uploadFormModel->uploadAvatarOss($model->actor_id, Yii::$app->params['ossAvatarObjectActorPrefix']);
                    if (!empty($avatarUrl)) {
                        $model->avatar = $avatarUrl;
                    }
                }

                //Yii2 会自动生成一个hidden的cover,但是value却未空
                //导致什么都不做更改的情况下,cover会被2次设置为空
                //原因没有找到.通过下面的方法规避一下
                //https://stackoverflow.com/questions/34593023/yii-2-file-input-renders-hidden-file-input-tag
                if (empty($model->avatar)) {
                    $model->avatar = $avatar;
                }

                $isSaved = $model->save();
                $transaction->commit();
                if ($isSaved) {
                    return $this->redirect(['view', 'id' => $model->actor_id]);
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                print $e->getMessage();
                //print_r($e->getTrace());
            }
        } else {

            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing StoryActor model.
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
     * Finds the StoryActor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return StoryActor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StoryActor::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
