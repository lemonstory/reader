<?php

namespace api\controllers;
use yii\rest\ActiveController;

//class UserController extends \yii\web\Controller
//{
//    public function actionIndex()
//    {
//        return $this->render('index');
//    }
//
//}

class StoryController extends ActiveController
{
    public $modelClass = 'common\models\Story';
}