<?php

namespace backend\controllers;

use common\models\UploadForm;
use Yii;
use common\models\User;
use common\models\UserSearch;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
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
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
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
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->uid]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing User model.
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
                $uploadFormModel->file = UploadedFile::getInstanceByName('User[avatar]');

                if (!empty($uploadFormModel->file)) {
                    $avatarUrl = $uploadFormModel->uploadAvatarOss($model->uid, Yii::$app->params['ossAvatarObjectUserPrefix']);
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
                    return $this->redirect(['view', 'id' => $model->uid]);
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
     * Deletes an existing User model.
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
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
