<?php

namespace api\controllers;

use common\models\Story;
use common\models\StoryActor;
use common\models\StoryTag;
use common\models\StoryTagRelation;
use common\models\User;
use common\models\UserReadStoryRecord;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseJson;
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
     * 批量新建故事
     * @return mixed
     * @throws ServerErrorHttpException
     */
    public function actionBatchCreate()
    {
        $response = Yii::$app->getResponse();
        $inputStorys = Yii::$app->getRequest()->post();
        $ret = array();
        $data = array();
        $hasError = false;
        if(!empty($inputStorys['storys'])) {
            
            foreach ($inputStorys['storys'] as $storyItem) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    //保存故事
                    $storyModel = new Story();
                    $storyModel->loadDefaultValues();
                    $storyModel->setAttributes($storyItem);

                    $storyModel->save();
                    if($storyModel->hasErrors()) {
                        Yii::error($storyModel->getErrors());
                        throw new ServerErrorHttpException('新建故事失败');
                    }
                    $storyId = $storyModel->story_id;

                    //保存角色
                    //接收到的角色值:[{"number":"角色序号-1","name":"角色姓名-1","avatar":"角色头像-2","is_visible":"是否可见"},{"number":"角色序号-2","name":"角色姓名-2","avatar":"角色头像-2","is_visible":"是否可见"}];
                    $actorJson = $storyItem['actor'];
                    $actorRows = $this->parseActorJson($actorJson,$storyId);
                    //TODO:角色信息格式输入检查
                    $actorColumns = ['actor_id','story_id', 'name', 'avator', 'number','is_visible'];
                    $actorAffectedRows = Yii::$app->db->createCommand()->batchInsert(StoryActor::tableName(), $actorColumns, $actorRows)->execute();

                    //保存标签
                    //接收到的标签值:[{"tag_id":1, "status":"1"},{"tag_id":2, "status":"1"}];
                    $tagJson = $storyItem['tag'];
                    $tagRows = $this->parseTagJson($tagJson,$storyId);
                    //TODO:标签信息格式输入检查
                    $tagColumns = ['story_id', 'tag_id','status'];
                    $tagAffectedRows = Yii::$app->db->createCommand()->batchInsert(StoryTagRelation::tableName(), $tagColumns, $tagRows)->execute();
                    $transaction->commit();
                    if ($storyId > 0 && $actorAffectedRows > 0 && $tagAffectedRows > 0) {

                        $dataItem['local_story_id'] = $storyItem['local_story_id'];
                        $dataItem['story_id'] = $storyId;
                        $data[] = $dataItem;
                    }
                }catch (\Exception $e){

                    //如果抛出错误则进入catch，先callback，然后捕获错误，返回错误
                    $hasError = true;
                    $transaction->rollBack();
                    Yii::error($e->getMessage());
                    $response->statusCode = 400;
                    $response->statusText = '新建故事失败';
                }
            }

            if(count($data) > 0 && $hasError) {
                $response->statusCode = 206;
                $response->statusText = '成功新建部分故事' ;
            }
        }else{
            $response->statusCode = 400;
            $response->statusText = '参数错误' ;
        }
        $ret['data'] = $data;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }


    /**
     * 批量更新故事
     * @return mixed
     * @throws ServerErrorHttpException
     */
    public function actionBatchUpdate()
    {
        $response = Yii::$app->getResponse();
        $inputStorys = Yii::$app->getRequest()->post();
        $ret = array();
        $data = array();
        $hasError = false;
        if(!empty($inputStorys['storys'])) {

            foreach ($inputStorys['storys'] as $storyItem) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    //保存故事
                    $storyModel = Story::findOne($storyItem['story_id']);
                    $storyModel->setAttributes($storyItem);
                    $storyModel->save();
                    if($storyModel->hasErrors()) {
                        Yii::error($storyModel->getErrors());
                        throw new ServerErrorHttpException('编辑故事失败');
                    }
                    $storyId = $storyModel->story_id;

                    //更新角色
                    //接收到的角色值:[{"actor_id":1, "number":"1","name":"姓名-1","avatar":"","is_visible":1},{"actor_id":2,"number":"2","name":"姓名-2","avatar":"","is_visible":1}];
                    $actorJson = $storyItem['actor'];
                    $actorRows = $this->parseActorJson($actorJson,$storyId);
                    if(!empty($actorRows)) {
                        foreach ($actorRows as $actorItem) {
                            $storyActorModel = StoryActor::findOne($actorItem['actor_id']);
                            if($storyActorModel === null) {
                                $storyActorModel = new StoryActor();
                            }
                            $storyActorModel->setAttributes($actorItem);
                            $storyActorModel->save();
                            if($storyActorModel->hasErrors()) {
                                Yii::error($storyActorModel->getErrors());
                                throw new ServerErrorHttpException('角色修改失败');
                            }
                        }
                    }
                    //更新标签
                    //接收到的标签值:[{"tag_id":1, "status":"1"},{"tag_id":2, "status":"1"}];
                    $tagJson = $storyItem['tag'];
                    $tagRows = $this->parseTagJson($tagJson,$storyId);

                    if(!empty($tagRows)) {
                        foreach ($tagRows as $tagItem) {

                            $condition = array(
                                'story_id' => $tagItem['story_id'],
                                'tag_id' => $tagItem['tag_id']
                            );
                            $storyTagRelationModel = StoryTagRelation::findOne($condition);
                            if($storyTagRelationModel === null) {
                                $storyTagRelationModel = new StoryTagRelation();
                            }
                            $storyTagRelationModel->setAttributes($tagItem);
                            $storyTagRelationModel->save();
                            if($storyTagRelationModel->hasErrors()) {
                                Yii::error($storyTagRelationModel->getErrors());
                                throw new ServerErrorHttpException('标签修改失败');
                            }
                        }
                    }

                    $transaction->commit();
                    $dataItem['local_story_id'] = $storyItem['local_story_id'];
                    $dataItem['story_id'] = $storyId;
                    $data[] = $dataItem;

                }catch (\Exception $e){

                    //如果抛出错误则进入catch，先callback，然后捕获错误，返回错误
                    $hasError = true;
                    $transaction->rollBack();
                    Yii::error($e->getMessage());
                    $response->statusCode = 400;
                    $response->statusText = '编辑故事失败';
                }
            }

            if(count($data) > 0 && $hasError) {
                $response->statusCode = 206;
                $response->statusText = '成功编辑部分故事' ;
            }
        }else{
            $response->statusCode = 400;
            $response->statusText = '参数错误' ;
        }
        $ret['data'] = $data;
        $ret['code'] = $response->statusCode;
        $ret['msg'] = $response->statusText;
        return $ret;
    }

    public function actionView($id)
    {

        $data = array();
        $storyId = $id;
        $storyCondition = array(
            'story_id' => $storyId,
            'status' => Yii::$app->params['STATUS_ACTIVE']
        );
        $storyModel = Story::findOne($storyCondition);
        if(!empty($storyModel)) {
            $data = $storyModel->getAttributes();

            //角色信息
            $actorCondition = array(
                'story_id' => $storyId,
                'status' => Yii::$app->params['STATUS_ACTIVE'],
                'is_visible' => Yii::$app->params['STATUS_ACTIVE']
            );
            $actorNames = array('actor_id','name','avator','number');
            $data['actor'] = $storyModel->getActors()->select($actorNames)->andWhere($actorCondition)->orderBy(['number' => SORT_ASC])->asArray()->all();

            //标签信息
            $tagCondition = array(
                'status' => Yii::$app->params['STATUS_ACTIVE']
            );
            $tagNames = array('tag_id','name','number');
            $data['tag'] = $storyModel->getTags()->select($tagNames)->andWhere($tagCondition)->orderBy(['number' => SORT_ASC])->asArray()->all();
        }

        $ret['data'] = $data;
        $ret['code'] = 200;
        $ret['message'] = 'OK';
        return $ret;
    }

    public function actionChapters($id) {

        $data = array();
        $storyId = $id;
        $storyCondition = array(
            'story_id' => $storyId,
            'status' => Yii::$app->params['STATUS_ACTIVE']
        );
        $storyModel = Story::findOne($storyCondition);
        if(!empty($storyModel)) {

            //章节信息
            $chapterCondition = array(
                'story_id' => $storyId,
                'status' => Yii::$app->params['STATUS_ACTIVE']
            );
            $chapterNames = array('chapter_id','name','background','message_count','number','is_published','create_time','last_modify_time');
            $data = $storyModel->getChapters()->select($chapterNames)->andWhere($chapterCondition)->orderBy(['number' => SORT_ASC])->asArray()->all();
        }

        $ret['data'] = $data;
        $ret['code'] = 200;
        $ret['message'] = 'OK';
        return $ret;
    }


    /**
     * 拼接角色数组
     * @param $actorJson
     * @param $storyId
     * @return array
     */
    private function parseActorJson($actorJson,$storyId) {

        $actorArr = BaseJson::decode($actorJson);
        $actorRows = array();
        if(!empty($actorArr) && !empty($storyId)) {
            foreach ($actorArr as $actorItem) {
                $actorRow['actor_id'] = isset($actorItem['actor_id']) ? $actorItem['actor_id'] : null;
                $actorRow['story_id'] = $storyId;
                $actorRow['name'] = $actorItem['name'];
                $actorRow['avatar'] = $actorItem['avatar'];
                $actorRow['number'] = $actorItem['number'];
                $actorRow['is_visible'] = isset($actorItem['is_visible']) ? $actorItem['is_visible'] : 1;
                $actorRows[] = $actorRow;
            }
        }
        return $actorRows;
    }

    /**
     * 拼接标签数组
     * @param $tagJson
     * @param $storyId
     * @return array
     */
    private function parseTagJson($tagJson,$storyId) {

        $tagArr = BaseJson::decode($tagJson);
        $tagRows = array();
        if(!empty($tagArr) && !empty($storyId)) {
            foreach ($tagArr as $tagItem) {
                $tagRow['story_id'] = $storyId;
                $tagRow['tag_id'] = $tagItem['tag_id'];
                $tagRow['status'] = isset($tagItem['status']) ? $tagItem['status'] : 1;
                $tagRows[] = $tagRow;
            }
        }
        return $tagRows;
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

//        $str = <<<EOD
//#标题
//**>![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=40)陈明**：请问您是\t心理咨询处的老师吗？这里很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长,很长
//**<![](https://avatars1.githubusercontent.com/u/7226606?v=3&s=40)李洁**：我是，有什么可以帮的到你的？
//_星期三 下午_
//...
//**>![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=40)陈明**：您好，我是医学院的学生，我身边发生了一些很诡异的事情，我觉得自己快被逼疯了！希望能和您倾诉一下。
//**<![](https://avatars1.githubusercontent.com/u/7226606?v=3&s=40)李洁**：你别着急，慢慢说，有什么问题我们一起解决。
//**>![]()陈明**：先给您介绍一下我的室友，所有事情都是由他引起的。
//    **<![](https://avatars1.githubusercontent.com/u/7226606?v=3&s=40)李洁**：好的。
//**>![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=40)陈明**：我是研究生，
//研究生的宿舍都是
//两人间，
//您知道吧？
//**<![](https://avatars1.githubusercontent.com/u/7226606?v=3&s=40)李洁**：没错。
//**>![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=40)陈明**：我的室友来自东部的一个农村，并不是我歧视农村人，但是我和他的关系十分不合。
//**>![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=40)陈明**：在上面说
//![](https://avatars0.githubusercontent.com/u/10001124?v=3&s=100)发生大发发
//EOD;
//
//
//        $MessageParsedown = new MessageParsedown();
//
//        $MessageParsedown->setBreaksEnabled(true);
//        echo $MessageParsedown->text($str); # prints: <p>Hello <em>Parsedown</em>!</p>

        phpinfo();
    }
}
?>