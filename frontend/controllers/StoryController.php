<?php
namespace frontend\controllers;

use common\models\ChapterMessageContent;
use common\models\Story;
use frontend\models\SignupForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;


class StoryController extends Controller
{
    public $modelClass = 'common\models\Story';

    public function actionView($story_id,$chapter_id)
    {
        //数据处理
        $data = array();
        $storyId = $story_id;
        $chapterId = $chapter_id;

        $storyCondition = array(
            'story.story_id' => $storyId,
            'story.status' => Yii::$app->params['STATUS_ACTIVE']
        );
        $storyModel = Story::findOne($storyCondition);
        if(!empty($storyModel)) {

            //角色信息
            $actorCondition = array(
                'story_id' => $storyId,
                'status' => Yii::$app->params['STATUS_ACTIVE'],
                'is_visible' => Yii::$app->params['STATUS_ACTIVE']
            );
            $actorNames = array('actor_id', 'name', 'avatar', 'number', 'location');
            $data['actor'] = $storyModel->getActors()->select($actorNames)->andWhere($actorCondition)->orderBy(['number' => SORT_ASC])->asArray()->all();
            $data['actor'] = ArrayHelper::index($data['actor'],'actor_id');

            //故事消息内容
            $storyCondition = array(
                'story_id' => $storyId,
                'chapter_id' => $chapterId,
                'status' => Yii::$app->params['STATUS_ACTIVE']
            );
            $chapterMessageContentArr = ChapterMessageContent::find()
                ->where($storyCondition)
                ->orderBy('number')
                ->asArray()
                ->all();

            $data['storyId'] = $storyId;
            $data['chapterId'] = $chapterId;
            $data['chapterMessageContent'] = $chapterMessageContentArr;
        }

        $this->layout='@frontend/views/layouts/site.php';
        return $this->render('view',$data);
    }
}
