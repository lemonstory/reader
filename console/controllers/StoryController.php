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
    public function options()
    {
        return ['message'];
    }

    //暂时不使用
    public function optionAliases()
    {
        return ['m' => 'message'];
    }

    public function actionHot()
    {
        $redis = Yii::$app->redis;
        //获取所有的故事
        $condition = ['status' => Yii::$app->params['STATUS_ACTIVE'],'is_published' => Yii::$app->params['STATUS_PUBLISHED']];
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
                $rank = $storyModel->hot($taps,$commentCount,1,0,$item['last_modify_time'],$lastCommentTime);
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
            }
        }else {
            echo "故事数组不能为空\n";
            return 1;
        }

        echo "故事全局排序更新成功\n";
        return 0;
    }
}