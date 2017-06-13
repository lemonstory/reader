<?php

namespace api\controllers;

use common\models\Story;
use common\models\StoryTag;
use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yii\rest\Serializer;
use Parsedown;
use api\controllers\MessageParsedown;


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
        unset($actions['delete'], $actions['create'], $actions['view']);

        // 使用"prepareDataProvider()"方法自定义数据provider
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }
//
//    public function prepareDataProvider()
//    {
//        // 为"index"动作准备和返回数据provider
//    }


    /**
     * 获取用户发布的故事
     * @param $uid
     * @param $page
     * @param $page_size
     * @return ActiveDataProvider
     */
    public function actionUserStoryList($uid,$page,$page_size) {

        $response = Yii::$app->getResponse();
        $offset = ($page - 1) * $page_size;
        $story = Story::find()
            ->with([
                'actors' => function (\yii\db\ActiveQuery $query) {
                    $query->andWhere(['is_visible' => Yii::$app->params['STATUS_ACTIVE'],'status' => Yii::$app->params['STATUS_ACTIVE']]);
                },
                'tags'=> function (\yii\db\ActiveQuery $query) {
                    $query->andWhere(['status' => Yii::$app->params['STATUS_ACTIVE']]);
                },
            ])

            ->where(['uid' => $uid,'status' => Yii::$app->params['STATUS_ACTIVE']])
            ->offset($offset)
            ->limit($page_size)
            ->orderBy(['last_modify_time' => SORT_DESC]);

        $provider =  new ActiveDataProvider([
            'query' =>$story,
            'pagination' => [
                'pageSize' => $page_size,
            ],
        ]);

        $storyModels = $provider->getModels();
        $data = array();
        foreach ($storyModels as $storyModelItem) {

            $story = array();
            $story['story_id'] = $storyModelItem->story_id;
            $story['name'] = $storyModelItem->name;
            $story['description'] = $storyModelItem->description;
            $story['cover'] = $storyModelItem->cover;
            $story['uid'] = $storyModelItem->uid;
            $story['chapter_count'] = $storyModelItem->chapter_count;
            $story['message_count'] = $storyModelItem->message_count;
            $story['taps'] = $storyModelItem->taps;
            $story['is_published'] = $storyModelItem->is_published;
            $story['create_time'] = $storyModelItem->create_time;
            $story['last_modify_time'] = $storyModelItem->last_modify_time;

            //actor
            $actorModels = $storyModelItem->actors;
            $actorList = array();
            foreach ($actorModels as $actorModelItem) {
                $actor = array();
                $actor['actor_id'] = $actorModelItem->actor_id;
                $actor['name'] = $actorModelItem->name;
                $actor['avator'] = $actorModelItem->avator;
                $actor['number'] = $actorModelItem->number;
                $actor['is_visible'] = $actorModelItem->is_visible;
                $actorList[] = $actor;
            }
            $story['actor'] = $actorList;

            //tag
            $tagModels = $storyModelItem->tags;
            $tagList = array();
            foreach ($tagModels as $tagModelItem) {
                $tag = array();
                $tag['tag_id'] = $tagModelItem->tag_id;
                $tag['name'] = $tagModelItem->name;
                $tag['number'] = $tagModelItem->number;
                $tagList[] = $tag;
            }
            $story['tag'] = $tagList;
            $data['storyList'][] = $story;
        }


        $pagination = $provider->getPagination();
        $data['page'] = array(
            'totalCount' => $pagination->totalCount,
            'pageCount' => $pagination->getPageCount(),
            'currentPage' => $pagination->getPage() + 1,
            'perPage' => $pagination->getPageSize(),
        );

        $data['code'] = $response->statusCode;
        $data['message'] = $response->statusText;
        return $data;
    }


    public function actionCreate()
    {
        $body = Yii::$app->getRequest()->getBodyParams();
        $body['create_time'] = time();
        $body['views'] = 0;
        $body['message_count'] = 0;
        $body['chapter_count'] = 0;
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
        $data['message'] = $response->statusText;
        $data['data'] = $story->getAttributes();
        return $data;
    }

    public function actionView($id)
    {

        $data = array();
        $storyId = $id;
        $story = new Story();
        $story = $story->getStory($storyId);
        $data['data'] = $story;
        $data['code'] = 200;
        $data['message'] = 'OK';
        return $data;
    }


    public function actionAddTags()
    {

        $storyId = Yii::$app->request->post('storyId');
        $tagIds = explode(',', Yii::$app->request->post('tagIds'));
        if (count($tagIds) > 0) {
            $story = new Story();
            $rowsAffected = $story->addTags($storyId, $tagIds);
        }
        $data['data'] = $rowsAffected;
        $data['code'] = 200;
        $data['message'] = 'OK';
        return $data;
    }


    /**
     * 获取用户的故事
     * @param $uid
     * @return array
     */
    public function actionStorys($uid)
    {

        $provider =  new ActiveDataProvider([
            'query' => Story::find(),
            'pagination' => [
                'pageSize' => 2,
            ],
        ]);
        return $provider;
    }

    public function actionTest()
    {

        $str = <<<EOD
#标题
**>![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=40)陈明**：请问您是\t心理咨询处的老师吗？这里很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长
**<![](https://avatars1.githubusercontent.com/u/7226606?v=3&s=40)李洁**：我是，有什么可以帮的到你的？
_星期三 下午_
...
**>![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=40)陈明**：您好，我是医学院的学生，我身边发生了一些很诡异的事情，我觉得自己快被逼疯了！希望能和您倾诉一下。
**<![](https://avatars1.githubusercontent.com/u/7226606?v=3&s=40)李洁**：你别着急，慢慢说，有什么问题我们一起解决。
**>![]()陈明**：先给您介绍一下我的室友，所有事情都是由他引起的。
    **<![](https://avatars1.githubusercontent.com/u/7226606?v=3&s=40)李洁**：好的。
**>![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=40)陈明**：我是研究生，
研究生的宿舍都是
两人间，
您知道吧？
**<![](https://avatars1.githubusercontent.com/u/7226606?v=3&s=40)李洁**：没错。
**>![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=40)陈明**：我的室友来自东部的一个农村，并不是我歧视农村人，但是我和他的关系十分不合。
**>![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=40)陈明**：在上面说
![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=100)发生大发发
EOD;


        $MessageParsedown = new MessageParsedown();

        $MessageParsedown->setBreaksEnabled(true);
        echo $MessageParsedown->text($str); # prints: <p>Hello <em>Parsedown</em>!</p>

    }
}
?>