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

class HomeController extends ActiveController
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
     * 首页-按照评论数量,最后评论时间,点击量升序,按照最后修改时间降序
     * @param $page
     * @param $pre_page
     * @return array
     */
    public function actionIndex($page,$pre_page) {

        $ret = array();
        $redis = Yii::$app->redis;
        $response = Yii::$app->getResponse();
        $start = ($page - 1) * $pre_page;
        $stop = ($page - 1) * $pre_page + ($pre_page -1);

        $currentPage = $page;
        $perPage = $pre_page;
        $totalCount = $redis->zcount(Yii::$app->params['cacheKeyYouweiStoriesHotRank'], '-inf', '+inf');
        $pageCount = ceil($totalCount / $perPage);

        $ret['data']['totalCount'] = $totalCount;
        $ret['data']['pageCount'] = $pageCount;
        $ret['data']['currentPage'] = $currentPage;
        $ret['data']['perPage'] = $perPage;
        $ret['data']['storyList'] = array();
        $ret['status'] = $response->statusCode;
        $ret['message'] = $response->statusText;

        $storyIdArr = $redis->zrevrange(Yii::$app->params['cacheKeyYouweiStoriesHotRank'], $start, $stop);
        if(!empty($storyIdArr) && is_array($storyIdArr)) {

            $storyArr = Story::find()
                ->with([
                    'actors' => function (ActiveQuery $query) {
                        $query->andWhere(['is_visible' => Yii::$app->params['STATUS_ACTIVE'],'status' => Yii::$app->params['STATUS_ACTIVE']]);
                    },
                    'tags'=> function (ActiveQuery $query) {
                        $query->andWhere(['status' => Yii::$app->params['STATUS_ACTIVE']]);
                    },
                ])
                ->where(['status' => Yii::$app->params['STATUS_ACTIVE'],'is_published' => Yii::$app->params['STATUS_PUBLISHED'],'story_id' => $storyIdArr])
                ->asArray()
                ->all();

            if(!empty($storyArr) && is_array($storyArr)) {
                foreach ($storyArr as $storyItem) {
                    $story = array();
                    $story['story_id'] = $storyItem['story_id'];
                    $story['name'] = $storyItem['name'];
                    $story['description'] = $storyItem['description'];

                    //获取图片宽高
                    $coverUrl = $storyItem['cover'];
                    $coverImageInfo = CoverHelper::imageInfo($coverUrl);
                    $cover = array();
                    $cover['url'] = $coverUrl;
                    $cover['width'] = $coverImageInfo['width'];
                    $cover['height'] = $coverImageInfo['height'];
                    $cover['format'] = $coverImageInfo['format'];
                    $story['cover'] = $cover;

                    $story['uid'] = $storyItem['uid'];
                    $story['chapter_count'] = $storyItem['chapter_count'];
                    $story['message_count'] = $storyItem['message_count'];
                    $story['taps'] = CountHelper::humanize($storyItem['taps']);
                    $story['is_published'] = $storyItem['is_published'];
                    $story['create_time'] = Carbon::createFromTimestamp($storyItem['create_time'])->toDateTimeString();
                    $story['last_modify_time'] = Carbon::createFromTimestamp($storyItem['last_modify_time'])->toDateTimeString();

                    //actor
                    $actorArr = $storyItem['actors'];
                    $actorList = array();
                    foreach ($actorArr as $actorItem) {
                        $actor = array();
                        $actor['actor_id'] = $actorItem['actor_id'];
                        $actor['name'] = $actorItem['name'];
                        $actor['avatar'] = $actorItem['avatar'];
                        $actor['number'] = $actorItem['number'];
                        $actor['is_visible'] = $actorItem['is_visible'];
                        $actorList[] = $actor;
                    }
                    $story['actor'] = $actorList;

                    //tag
                    $tagArr = $storyItem['tags'];
                    $tagList = array();
                    foreach ($tagArr as $tagItem) {
                        $tag = array();
                        $tag['tag_id'] = $tagItem['tag_id'];
                        $tag['name'] = $tagItem['name'];
                        $tag['number'] = $tagItem['number'];
                        $tagList[] = $tag;
                    }
                    $story['tag'] = $tagList;
                    $ret['data']['storyList'][] = $story;
                }
            }
        }
        return $ret;
    }
}
