<?php

namespace api\controllers;

use Carbon\Carbon;
use common\components\CountHelper;
use common\components\CoverHelper;
use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\Story;
use common\models\StoryActor;
use common\models\Tag;
use common\models\User;
use common\models\UserReadStoryRecord;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use Yii;
use common\models\UploadForm;
use yii\web\ServerErrorHttpException;
use common\components\DateTimeHelper;

class TagController extends ActiveController
{
    public $modelClass = 'common\models\Tag';
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

    }

    public function init()
    {
        parent::init();
        Carbon::setLocale('zh');
    }

    /**
     * 标签列表
     * @return mixed
     */
    public function actionIndex() {

        $condition = array(
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        );
        $columns = array('tag_id','number','name');
        $tagArr = Tag::find()->select($columns)->where($condition)->orderBy(['number' => SORT_ASC])->asArray()->all();
        $response = Yii::$app->getResponse();

        //全部作品tag_id=0且number=0
        $allTagStorys = array(
            'tag_id' => 0,
            'name' => '全部作品',
            'number' => 0,
        );
        $tagArr[] = $allTagStorys;
        ArrayHelper::multisort($tagArr,'number');

        $ret['data'] = $tagArr;
        $ret['status'] = $response->statusCode;
        $ret['message'] = $response->statusText;
        return $ret;
    }

    /**
     * 标签下的故事列表
     * @param $tag_id
     * @param $page
     * @param $pre_page
     * @return mixed
     */
    public function actionStorys($tag_id,$page,$pre_page) {

        $response = Yii::$app->getResponse();
        $offset = ($page - 1) * $pre_page;

        //SELECT `story`.* FROM `story` INNER JOIN `story_tag_relation` ON `story`.`story_id` = `story_tag_relation`.`story_id` INNER JOIN `tag` ON `story_tag_relation`.`tag_id` = `tag`.`tag_id` WHERE (`story`.`status`=1) AND ((`tag`.`tag_id`='1') AND (`tag`.`status`=1))
        $query = Story::find()
            ->innerJoinWith([
                'tags'=> function (ActiveQuery $query) use ($tag_id) {
                    $query->andWhere(['tag.tag_id' => $tag_id,'tag.status' => Yii::$app->params['STATUS_ACTIVE']]);
                },
            ])
            ->where(['and',['story.status' => Yii::$app->params['STATUS_ACTIVE']], ['story.is_published' => Yii::$app->params['STATUS_PUBLISHED']], ['>' , 'story.message_count' , Yii::$app->params['tagStoryMinMessageCount']]])
            ->offset($offset)
            ->limit($pre_page)
            ->orderBy(['story.last_modify_time' => SORT_DESC]);

        $provider =  new ActiveDataProvider([
            'query' =>$query,
            'pagination' => [
                'pageSize' => $pre_page,
            ],
        ]);

        $storyModels = $provider->getModels();
        $ret = array();
        $storyIdArr = array();
        $ret['data']['storyList'] = array();
        foreach ($storyModels as $storyModelItem) {

            $story = array();
            $storyIdArr[] = $storyModelItem->story_id;
            $story['story_id'] = $storyModelItem->story_id;
            $story['name'] = $storyModelItem->name;
            $story['description'] = $storyModelItem->description;

            //获取图片宽高
            $coverUrl = $storyModelItem->cover;
            $coverImageInfo = CoverHelper::imageInfo($coverUrl);
            $cover = array();
            $cover['url'] = $coverUrl;
            $cover['width'] = $coverImageInfo['width'];
            $cover['height'] = $coverImageInfo['height'];
            $cover['format'] = $coverImageInfo['format'];
            $story['cover'] = $cover;

            $story['uid'] = $storyModelItem->uid;
            $story['chapter_count'] = $storyModelItem->chapter_count;
            $story['message_count'] = $storyModelItem->message_count;
            $story['taps'] = CountHelper::humanize($storyModelItem->taps);
            $story['is_published'] = $storyModelItem->is_published;
            $story['create_time'] = Carbon::createFromTimestamp($storyModelItem->create_time)->toDateTimeString();
            $story['last_modify_time'] = Carbon::createFromTimestamp($storyModelItem->last_modify_time)->toDateTimeString();

            //tag
            $tagList = array();
            $tagModels = $storyModelItem->tags;
            foreach ($tagModels as $tagModelItem) {
                $tag = array();
                $tag['tag_id'] = $tagModelItem->tag_id;
                $tag['name'] = $tagModelItem->name;
                $tag['number'] = $tagModelItem->number;
                $tagList[] = $tag;
            }
            $story['tag'] = $tagList;
            $ret['data']['storyList'][] = $story;
        }

        //角色信息
        $columns = array(
            'actor_id',
            'name',
            'avatar',
            'number',
            'story_id',
        );
        $condition = array(
            'story_actor.is_visible' => Yii::$app->params['STATUS_ACTIVE'],
            'story_actor.status' => Yii::$app->params['STATUS_ACTIVE'],
            'story_actor.story_id' => $storyIdArr,
        );
        $storyActorFindArr = StoryActor::find()->select($columns)->andWhere($condition)->orderBy(['number' => SORT_ASC])->asArray()->all();
        $storyActorArr = array();
        foreach ($storyActorFindArr as $item) {

            $storyId = $item['story_id'];
            unset($item['story_id']);
            $storyActorArr[$storyId][] = $item;
        }

        //将角色信息添加至$data
        if(is_array($ret['data']['storyList']) && count($ret['data']['storyList']) > 0) {
            foreach ($ret['data']['storyList'] as $key => $retStory) {

                if(array_key_exists($retStory['story_id'],$storyActorArr)) {
                    $ret['data']['storyList'][$key]['actor'] = $storyActorArr[$retStory['story_id']];
                }else {
                    $ret['data']['storyList'][$key]['actor'] = array();
                }
            }
        }
        $pagination = $provider->getPagination();
        $ret['data']['totalCount'] = $pagination->totalCount;
        $ret['data']['pageCount'] = $pagination->getPageCount();
        $ret['data']['currentPage'] = $pagination->getPage() + 1;
        $ret['data']['perPage'] = $pagination->getPageSize();
        $ret['status'] = $response->statusCode;
        $ret['message'] = $response->statusText;
        return $ret;
    }

}
