<?php

namespace api\controllers;

use common\components\MnsQueue;
use common\components\QueueMessageHelper;
use common\models\UserNotify;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;

class NotifyController extends ActiveController
{
    public $modelClass = 'common\models\UserNotify';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        //用户认证
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            //部分action需要access-token认证，部分action不需要
            'except' => [],
            'authMethods' => [
//                HttpBasicAuth::className(),
//                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 动作
        unset($actions['delete'], $actions['create'], $actions['view']);

        // 使用"prepareDataProvider()"方法自定义数据provider
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
    }

    public function actionIndex($uid, $page, $pre_page)
    {
        $userModel = Yii::$app->user->identity;
        $ret = array();
        $ret["code"] = 200;
        $ret["msg"] = "OK";
        $ret['data'] = array();
        if (!is_null($userModel)) {
            if ($uid == $userModel->uid) {

                $response = Yii::$app->getResponse();
                $offset = ($page - 1) * $pre_page;
                $notify = UserNotify::find()
                    ->where(['uid' => $uid,
                        'is_read' => 0])
                    ->offset($offset)
                    ->limit($pre_page)
                    ->orderBy(['last_modify_time' => SORT_DESC]);

                $provider = new ActiveDataProvider([
                    'query' => $notify,
                    'pagination' => [
                        'pageSize' => $pre_page,
                    ],
                ]);

                $notifyList = array();
                $notifyModels = $provider->getModels();
                $notifyIdsArr = array();
                if (!empty($notifyModels)) {

                    foreach ($notifyModels as $notifyModelItem) {

                        //组织数据
                        $id = $notifyModelItem->id;
                        $notifyIdsArr[] = $id;
                        $uid = $notifyModelItem->uid;
                        $category = $notifyModelItem->category;
                        $topicId = $notifyModelItem->topic_id;
                        $content = $notifyModelItem->content;
                        $contentArr = \GuzzleHttp\json_decode($content, true);
                        $senders = $notifyModelItem->senders;
                        $count = $notifyModelItem->count;
                        $createTime = $notifyModelItem->create_time;

                        switch ($category) {

                            //发布故事
                            case "post_story":
                                $dataItem = array();
                                $dataItem['id'] = $id;
                                $dataItem['category'] = $category;
                                $dataItem['senders'] = array(array(
                                    'uid' => $senders,
                                    'username' => $contentArr['username'],
                                    'avatar' => $contentArr['avatar'],
                                ));
                                $dataItem['count'] = $count;
                                $dataItem['story_id'] = $topicId;
                                $dataItem['story_name'] = $contentArr['story_name'];
                                $dataItem['story_cover'] = $contentArr['story_cover'];
                                $dataItem['create_time'] = $createTime;
                                $notifyList[] = $dataItem;
                                break;

                            //发布章节
                            case "post_chapter":
                                $dataItem = array();
                                $dataItem['id'] = $id;
                                $dataItem['category'] = $category;
                                $dataItem['senders'] = array(array(
                                    'uid' => $senders,
                                    'username' => $contentArr['username'],
                                    'avatar' => $contentArr['avatar'],
                                ));
                                $dataItem['count'] = $count;
                                $dataItem['story_id'] = $contentArr['story_id'];
                                $dataItem['story_name'] = $contentArr['story_name'];
                                $dataItem['story_cover'] = $contentArr['story_cover'];
                                $dataItem['chapter_id'] = $topicId;
                                $dataItem['chapter_name'] = $contentArr['chapter_name'];
                                $dataItem['create_time'] = $createTime;
                                $notifyList[] = $dataItem;
                                break;

                            //用户评论故事
                            case "comment_story":
                                $dataItem = array();
                                $dataItem['id'] = $id;
                                $dataItem['category'] = $category;
                                $dataItem['senders'] = $contentArr['senders'];
                                $dataItem['count'] = $count;
                                $dataItem['story_id'] = $topicId;
                                $dataItem['story_name'] = $contentArr['story_name'];
                                $dataItem['story_cover'] = $contentArr['story_cover'];
                                $dataItem['comment_id'] = $contentArr['comment_id'];
                                $dataItem['comment_content'] = $contentArr['comment_content'];
                                $dataItem['create_time'] = $createTime;
                                $notifyList[] = $dataItem;
                                break;

                            //回复评论
                            case "reply_comment":
                                $dataItem = array();
                                $dataItem['id'] = $id;
                                $dataItem['category'] = $category;
                                $dataItem['senders'] = $contentArr['senders'];
                                $dataItem['count'] = $count;
                                $dataItem['parent_comment_id'] = $topicId;
                                $dataItem['parent_comment_content'] = $contentArr['parent_comment_content'];
                                $dataItem['comment_id'] = $contentArr['comment_id'];
                                $dataItem['comment_content'] = $contentArr['comment_content'];
                                $dataItem['create_time'] = $createTime;
                                $notifyList[] = $dataItem;
                                break;

                            //用户对故事点赞
                            case "like_story":
                                $dataItem = array();
                                $dataItem['id'] = $id;
                                $dataItem['category'] = $category;
                                $dataItem['senders'] = $contentArr['senders'];
                                $dataItem['count'] = $count;
                                $dataItem['story_id'] = $topicId;
                                $dataItem['story_name'] = $contentArr['story_name'];
                                $dataItem['story_cover'] = $contentArr['story_cover'];
                                $dataItem['create_time'] = $createTime;
                                $notifyList[] = $dataItem;
                                break;

                            //用户对评论点赞
                            case "like_comment":
                                $dataItem = array();
                                $dataItem['id'] = $id;
                                $dataItem['category'] = $category;
                                $dataItem['senders'] = $contentArr['senders'];
                                $dataItem['count'] = $count;
                                $dataItem['comment_id'] = $topicId;
                                $dataItem['comment_content'] = $contentArr['comment_content'];
                                $dataItem['create_time'] = $createTime;
                                $notifyList[] = $dataItem;
                                break;

                            //用户对回复点赞
                            case "like_reply":
                                $dataItem = array();
                                $dataItem['id'] = $id;
                                $dataItem['category'] = $category;
                                $dataItem['senders'] = $contentArr['senders'];
                                $dataItem['count'] = $count;
                                $dataItem['comment_id'] = $topicId;
                                $dataItem['comment_content'] = $contentArr['comment_content'];
                                $dataItem['create_time'] = $createTime;
                                $notifyList[] = $dataItem;
                                break;
                        }
                    }

                    //设置通知消息为已读
                    //TODO:测试期间暂时关闭
                    $notifyIds = implode(",", $notifyIdsArr);
//            $updatedRows = UserNotify::updateAll(['is_read' => 1], 'uid=:uid AND id IN(' . $notifyIds . ")", ['uid' => $uid]);
                }

                $ret['data']['notifyList'] = $notifyList;
                $pagination = $provider->getPagination();
                $ret['data']['totalCount'] = $pagination->totalCount;
                $ret['data']['pageCount'] = $pagination->getPageCount();
                $ret['data']['currentPage'] = $pagination->getPage() + 1;
                $ret['data']['perPage'] = $pagination->getPageSize();
                $ret['code'] = $response->statusCode;
                $ret['msg'] = $response->statusText;

            } else {
                $ret['code'] = 400;
                $ret['msg'] = 'uid与token不相符';
            }
        } else {
            $ret['code'] = 400;
            $ret['msg'] = '用户不存在';
        }
        return $ret;
    }
}