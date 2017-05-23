<?php

namespace api\controllers;

use common\models\Story;
use Yii;
use yii\rest\ActiveController;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;


/**
 * Class StoryController
 * @package api\controllers
 *
 * 1）创建故事
 * 2）修改故事封面
 * 3）修改故事信息(标题,简介,标签)
 * 4）发布故事
 * 5）删除故事
 * 6）列出用户所有故事
 */
class StoryController extends ActiveController
{
    public $modelClass = 'common\models\Story';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
    public $viewAction = 'view';

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 动作
        unset($actions['delete'], $actions['create']);

        // 使用"prepareDataProvider()"方法自定义数据provider
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }
//
//    public function prepareDataProvider()
//    {
//        // 为"index"动作准备和返回数据provider
//    }


    public function actionCreate()
    {
        $body = Yii::$app->getRequest()->getBodyParams();
        $body['status'] = Yii::$app->params->get('unpublished');
        $body['create_time'] = time();
        $body['views'] = 0;
        $body['message_count'] = 0;
        $body['chapter_count'] = 0;
        $
        $story = new Story();
        if ($story->create($body)) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($story->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        } elseif (!$story->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        $data['code'] = $response->statusCode;
        $data['msg'] = $response->statusText;
        $data['data'] = $story->getAttributes();
        return $data;
    }

    /**
     * 获取用户的故事
     * @param $uid
     * @return array
     */
    public function actionUserStorys($uid, $perPage)
    {
        return array(1, 2, 3);
    }
}