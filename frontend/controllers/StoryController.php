<?php

    namespace frontend\controllers;
use yii\rest\ActiveController;

//class StoryController extends \yii\web\Controller
//{
//    public $modelClass = 'common\models\Story';
//
//    public function actionIndex()
//    {
//        return $this->render('index');
//    }
//
//    public function actionTest() {
//
//        echo "aaaa";
//        exit;
//    }
//
//}

class StoryController extends ActiveController
{
    public $modelClass = 'common\models\Story';

    public function actionView()
    {
//        return $this->render('index');

        $array = array(1,2,3);

    }

}
