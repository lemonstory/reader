<?php
/**
 * Created by PhpStorm.
 * User: gaoyong
 * Date: 2017/7/25
 * Time: 下午12:28
 *
 * 参考文档:
 *  http://www.yiiframework.com/doc-2.0/guide-tutorial-console.html
 *  http://www.yiichina.com/doc/guide/2.0/tutorial-console
 */

//存储:
//故事全局Rank
//Redis -> SortSet key:youwei_stories_hot_rank, member:story_id,score:故事在全局Rank中的排序值
//每x分钟(假设2分钟)更新一次,以此更新首页排序

namespace console\controllers;

use common\models\Comment;
use common\models\Story;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\ArrayHelper;


class StoryController extends Controller
{
    public $message;

    //暂时不使用
//    public function options()
//    {
//        return ['message'];
//    }
//
//    //暂时不使用
//    public function optionAliases()
//    {
//        return ['m' => 'message'];
//    }

    /**
     * 按热度对全网内容进行排序
     * @return int
     */
    public function actionHot()
    {
        $redis = Yii::$app->redis;

        //从最热列表中删除已不合法的故事
        //例如: 有故事被后台审核人员已经删除
        $inHotStoryIdArr = $redis->zrevrange(Yii::$app->params['cacheKeyYouweiStoriesHotRank'], 0, -1);
        $inHotStoryIdArrCount = count($inHotStoryIdArr);
        if($inHotStoryIdArrCount > 0) {

            $inHotCondition  = ['and',
                ['story.status' => Yii::$app->params['STATUS_ACTIVE']],
                ['story.is_published' => Yii::$app->params['STATUS_PUBLISHED']],
                ['>' , 'story.message_count' , Yii::$app->params['homeStoryMinMessageCount']],
                ['>' , 'story.taps' , Yii::$app->params['homeStoryMinTapsCount']],
                ['story.story_id' => $inHotStoryIdArr],
            ];

            $stillInHotStoryArr = Story::find()->select('story_id')->where($inHotCondition)->asArray()->all();
            $stillInHotStoryArrCount = count($stillInHotStoryArr);

            //存在不合法的数据
            if($inHotStoryIdArrCount != $stillInHotStoryArrCount) {

                $stillInHotStoryIdArr = ArrayHelper::getColumn($stillInHotStoryArr,'story_id');
                $invalidHotStoryIdArr =  array_diff($inHotStoryIdArr,$stillInHotStoryIdArr);
                $invalidHotStoryIdArrCount = count($invalidHotStoryIdArr);
                if($invalidHotStoryIdArrCount > 0) {
                    foreach ($invalidHotStoryIdArr as $key => $item) {
                        $invalidStoryId = intval($item);
                        $redis->zrem(Yii::$app->params['cacheKeyYouweiStoriesHotRank'],$invalidStoryId);
                    }
                }
            }
        }

        //获取所有的故事
        $condition  = ['and',
            ['story.status' => Yii::$app->params['STATUS_ACTIVE']],
            ['story.is_published' => Yii::$app->params['STATUS_PUBLISHED']],
            ['>' , 'story.message_count' , Yii::$app->params['homeStoryMinMessageCount']],
            ['>' , 'story.taps' , Yii::$app->params['homeStoryMinTapsCount']],
        ];
        $storyArr = Story::find()->where($condition)->asArray()->all();
        $storyModel =  new Story();

        //所有的故事Id
        $storyIdArr = ArrayHelper::getColumn($storyArr,'story_id');

        //获取所以故事的评论时间
        $commentConditon = ['target_id' => $storyIdArr,'target_type' => 1,'status' =>  1];
        $lastCommentArr = Comment::find()->where($commentConditon)->orderBy(['last_modify_time'=>SORT_DESC])->limit(1)->asArray()->all();
        $lastCommentArr = ArrayHelper::index($lastCommentArr,'target_id');

        if(!empty($storyArr) && is_array($storyArr)) {
            foreach ($storyArr as $key=> $item) {

                $storyId = $item['story_id'];

                //TODO:这里的$taps和$commentCount为0时,默认值设置为1不合理
                $taps = intval($item['taps']) > 0 ? intval($item['taps']) : 1 ;
                $commentCount = intval($item['comment_count']) > 0 ? intval($item['comment_count']) : 1 ;

                if(ArrayHelper::keyExists($item['story_id'],$lastCommentArr)) {
                    $lastCommentTime = $lastCommentArr[$item['story_id']]['last_modify_time'];
                }else {
                    $lastCommentTime = 0;
                }
                //计算每个故事的排序值
                //TODO:Qscore,Ascores这两个参数都未做处理
                if($item['last_modify_time'] > time()) {
                    $dateAsk = date('Y-m-d H:i:s',time());
                }else {
                    $dateAsk = date('Y-m-d H:i:s',$item['last_modify_time']);
                }

                if($lastCommentTime > time()) {
                    $dateActive = date('Y-m-d H:i:s',time());
                }else {
                    $dateActive = date('Y-m-d H:i:s',$lastCommentTime);
                }
                $rank = $storyModel->hot($taps,$commentCount,1,0,$dateAsk,$dateActive);
                if(!is_nan($rank)) {
                    //向Redis中写入Rank
                    try{
                        $isAdded = $redis->zadd(Yii::$app->params['cacheKeyYouweiStoriesHotRank'], $rank, strval($storyId));
                        if(strcmp($isAdded,"0") != 0 && strcmp($isAdded,"1") != 0) {
                            echo "redis 写入失败\n";
                            return 1;
                        }
                    }catch (Exception $e) {
                        echo $e->getMessage();
                        return 1;
                    }
                }else {
                    echo "rank 计算出现错误\n";
                    return 1;
                }
            }
        }else {
            echo "故事数组不能为空\n";
            return 1;
        }

        echo "故事全局排序更新成功\n";
        return 0;
    }


    /**
     * 随机对全网内容进行排序
     * @return int
     */
    public function actionShuffle(){

        $redis = Yii::$app->redis;
        //获取所有的故事
        $condition  = ['and',
            ['story.status' => Yii::$app->params['STATUS_ACTIVE']],
            ['story.is_published' => Yii::$app->params['STATUS_PUBLISHED']],
            ['>' , 'story.message_count' , Yii::$app->params['homeStoryMinMessageCount']],
            ['>' , 'story.taps' , Yii::$app->params['homeStoryMinTapsCount']],
        ];
        $storyArr = Story::find()->where($condition)->asArray()->all();

        //所有的故事Id
        $storyIdArr = ArrayHelper::getColumn($storyArr,'story_id');
        shuffle($storyIdArr);
        if(!empty($storyArr) && is_array($storyArr)) {
            foreach ($storyIdArr as $rank => $storyId) {
                //向Redis中写入Rank
                try {
                    $isAdded = $redis->zadd(Yii::$app->params['cacheKeyYouweiStoriesHotRank'], $rank, strval($storyId));
                    if (strcmp($isAdded, "0") != 0 && strcmp($isAdded, "1") != 0) {
                        echo "redis 写入失败\n";
                        return 1;
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                    return 1;
                }
            }
        }else {
            echo "故事数组不能为空\n";
            return 1;
        }

        echo "故事全局排序更新成功\n";
        return 0;
    }
}