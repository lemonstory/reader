<?php

namespace frontend\controllers;
use yii\rest\ActiveController;

//class UserController extends \yii\web\Controller
//{
//    public function actionIndex()
//    {
//        return $this->render('index');
//    }
//
//}

class UserController extends ActiveController
{
    public $modelClass = 'common\models\User';
}