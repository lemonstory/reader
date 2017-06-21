<?php

namespace api\controllers;

use common\models\Chapter;
use common\models\ChapterMessageContent;
use common\models\Story;
use common\models\User;
use common\models\UserReadStoryRecord;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseJson;
use yii\rest\ActiveController;
use yii\web\JsonParser;
use yii\web\UploadedFile;
use Yii;
use common\models\UploadForm;
use yii\web\ServerErrorHttpException;
use common\components\DateTimeHelper;
use OpenSearch\Client\OpenSearchClient;
use OpenSearch\Client\SearchClient;
use OpenSearch\Util\SearchParamsBuilder;

class SearchController extends ActiveController
{
    public $modelClass = 'common\models\User';
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



    public function actionView()
    {
        return $this->render('view');
    }

    /**
     * 搜索故事
     * @param $keyword
     * @param $page
     * @param $per_page
     * @return array
     */
    public function actionStories($keyword,$page,$per_page) {

        $ret = array();
        $ret['data']['totalCount'] = 0;
        $ret['data']['pageCount'] = 0;
        $ret['data']['currentPage'] = $page;
        $ret['data']['perPage'] = $per_page;
        $ret['data']['storyList'] = array();
        if(!empty($keyword)) {
            $start = ($page - 1) * $per_page;
            $query = "story:'" . $keyword . "' OR sws_story:'" . $keyword . "'";
            $searchRet = $this->getOpenSearchRet($query,$start,$per_page);
            $ret['data']['totalCount'] = $searchRet['result']['total'];
            $ret['data']['pageCount'] = ceil($searchRet['result']['total'] / $per_page);
            if(0 === strcasecmp($searchRet['status'],"OK")) {

                $ret['code'] = 200;
                $ret['msg'] = "OK";

                foreach ($searchRet['result']['items'] as $item) {
                    $story['story_id'] = $item['fields']['story_id'];
                    //story_name的飘红会有bug,所以这里是有story_sws_name
                    //在为某个字段配有摘要的情况下，该字段不可以创建2种不同类型的分词,会触发个别文档有可能会飘红，而有些则不会飘红.
                    //目前该bug暂时还不好解，您这边目前需要避免同一个字段进入不同分词组合索引的情况进行避免，或者再针对这些部分字段单独创建对应分词索引，通过AND,OR之类的逻辑操作进行查询
                    $story['name'] = $item['fields']['story_sws_name'];
                    $story['description'] = $item['fields']['story_description'];
                    $story['cover'] = $item['fields']['story_cover'];
                    $story['uid'] = $item['fields']['story_uid'];
                    $story['chapter_count'] = $item['fields']['story_chapter_count'];
                    $story['message_count'] = $item['fields']['story_message_count'];
                    $story['taps'] = $item['fields']['story_taps'];
                    $story['is_published'] = $item['fields']['story_is_published'];
                    $story['status'] = $item['fields']['story_status'];
                    $story['create_time'] = $item['fields']['story_create_time'];
                    $story['last_modify_time'] = $item['fields']['story_last_modify_time'];
                    $ret['data']['storyList'][] = $story;
                }
            }else {

                $ret['code'] = $searchRet['errors']['code'];
                $ret['msg'] = "搜索系统出现错误";
                //TODO:记录错误日志
//                $errorMessage = $searchRet['errors']['message'];
//                $errorTracer = $searchRet['tracer'];
            }
        }

        return $ret;
    }

    /**
     * 用户搜索(未发布过故事的用户,不会被搜索出来)
     * @param $keyword
     * @param $page
     * @param $per_page
     * @return array
     */
    public function actionUsers($keyword,$page,$per_page) {

        $ret = array();
        $ret['data']['totalCount'] = 0;
        $ret['data']['pageCount'] = 0;
        $ret['data']['currentPage'] = $page;
        $ret['data']['perPage'] = $per_page;
        $ret['data']['userList'] = array();
        if(!empty($keyword)) {
            $start = ($page - 1) * $per_page;
//            $query = "user:'小'&&distinct=dist_key:story_uid,dist_count:1,dist_times:1, reserved:false&&kvpairs=duniqfield:story_uid";
            $query = "user:'".$keyword."'&&distinct=dist_key:story_uid,dist_count:1,dist_times:1, reserved:false&&kvpairs=duniqfield:story_uid";
            $searchRet = $this->getOpenSearchRet($query,$start,$per_page);
            $ret['data']['totalCount'] = $searchRet['result']['total'];
            $ret['data']['pageCount'] = ceil($searchRet['result']['total'] / $per_page);
            if(0 === strcasecmp($searchRet['status'],"OK")) {

                $ret['code'] = 200;
                $ret['msg'] = "OK";

                foreach ($searchRet['result']['items'] as $item) {

                    $story['uid'] = $item['fields']['story_uid'];
                    $story['name'] = $item['fields']['user_name'];
                    $story['avatar'] = $item['fields']['user_avatar'];
                    $story['signature'] = $item['fields']['user_signature'];
                    $story['status'] = $item['fields']['user_status'];
                    $story['create_time'] = $item['fields']['user_create_time'];
                    $story['last_modify_time'] = $item['fields']['user_last_modify_time'];
                    $ret['data']['userList'][] = $story;
                }
            }else {

                $ret['code'] = $searchRet['errors']['code'];
                $ret['msg'] = "搜索系统出现错误";
                //TODO:记录错误日志
//                $errorMessage = $searchRet['errors']['message'];
//                $errorTracer = $searchRet['tracer'];
            }
        }

        return $ret;


    }


    private function getOpenSearchRet($query,$start,$hit) {

        $searchRet = array();
        if(!empty($query)) {

            $start = intval($start);
            //创建OpenSearchClient客户端对象
            $client = new OpenSearchClient(
                Yii::$app->params['accessKeyID'],
                Yii::$app->params['accessKeySecret'],
                Yii::$app->params['openSearchEndPoint'],
                Yii::$app->params['openSearchOptions']
            );
            // 实例化一个搜索类
            $searchClient = new SearchClient($client);
            // 实例化一个搜索参数类
            $params = new SearchParamsBuilder();
            //设置config子句的start值
            $params->setStart($start);
            //设置config子句的hit值
            $params->setHits($hit);
            // 指定一个应用用于搜索
            $params->setAppName(Yii::$app->params['openSearchAppName']);
            // 指定搜索关键词
            $params->setQuery($query);
            // 指定返回的搜索结果的格式为json
            $params->setFormat("fulljson");
            //添加排序字段
            $params->addSort('RANK', SearchParamsBuilder::SORT_DECREASE);
            // 执行搜索，获取搜索结果
            $searchRet = $searchClient->execute($params->build())->result;
            $searchRet = BaseJson::decode($searchRet);
        }
        return $searchRet;
    }




}
