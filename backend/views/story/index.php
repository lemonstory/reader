<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '故事');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="story-index">

<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '新增故事'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'story_id',
                'contentOptions' => ['style' => 'width:10px;'],
                'format' => 'raw',
                'value' => function ($m) {
                    $ret =  Html::a($m->story_id,
                        ['story/update', 'id' => $m->story_id,]);
                    return $ret;
                },
            ],
            [
                'attribute' => 'cover',
                'format' => 'raw',
                'value' => function ($m) {
                    $cover = Html::img($m->cover,
                        ['class' => 'img-rounded', 'width' => 90]
                    );
                    return Html::a($cover,
                        ['story/update', 'id' => $m->story_id]);
                }
            ],
            [

                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($m) use ($storyTagArr) {
                    $ret = Html::a($m->name,
                        ['story/update', 'id' => $m->story_id]);

                    if(isset($storyTagArr[$m->story_id]) && !empty($storyTagArr[$m->story_id])) {
                        {
                            //TODO:下面的tag_id是硬编码,需要更改
                            //'tag_id' => 'bg color'
                            $bg = [
                                '1' => 'bg-yellow',
                                '2' => 'bg-green',
                                '3' => 'bg-red',
                            ];
                            $ret .= "<p >";
                            foreach ($storyTagArr[$m->story_id] as $tag) {

                                $tagNameUrl = Html::a($tag['tag_name'],
                                    ['tag/view', 'id' => $tag['tag_id']], ['style' => 'color:#fff']);
                                $ret .= " <small class=\"label {$bg[$tag['tag_id']]}\">{$tagNameUrl}</small>";

                            }
                            $ret .= "</p>";
                        }
                    }
                    return $ret;
                },
            ],
            'sub_name',
//            'description',
            [
                'attribute' => 'uid',
                'headerOptions' => ['style' => 'width:100px'],
                'format' => 'raw',
                'value' => function ($m) use ($storyUserArr){
                    $ret = '';
                    if(!empty($m->uid)) {
                        $ret = Html::a($storyUserArr[$m->uid]['name'],
                            ['user/view', 'id' => $m->uid]);
                    }
                    return $ret;
                },
            ],

            [
                'attribute' => 'chapter_count',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'value' => function ($model) {
                    $ret = Html::a($model->chapter_count."章",
                        ['chapter/index', 'ChapterSearch[story_id]' => $model->story_id]);
                    return $ret;
                },
            ],
            [
                'attribute' => 'message_count',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'value' => function ($model) {
                    $ret = Html::a($model->message_count."条",
                        ['chapter-message-content/index', 'ChapterMessageContentSearch[story_id]' => $model->story_id]);
                    return $ret;
                },
            ],
            [
                'attribute' => 'taps',
                'headerOptions' => ['style' => 'width:100px'],
                'value' => function ($model) {
                    return $model->taps."次";
                },
            ],
            [
                'attribute' => 'is_published',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'contentOptions' => ['style' => 'width:10px;'],
                'value' => function ($model) {
                    $state = [
                        '0' => '未发布',
                        '1' => '已发布',
                    ];
                    if ($model->is_published == 0){
                        $ret = Html::tag('span', $state[$model->is_published], ['class' => 'not-set']);
                    }else {
                        $ret = $state[$model->is_published];
                    }
                    return $ret;
                },
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:10px'],
                'value' => function ($model) {
                    $state = [
                        '0' => '已删除',
                        '1' => '正常',
                    ];

                    if(!isset($state[$model->status])) {
                        $ret = Html::tag('span', '未知', ['class' => 'not-set']);
                    }else if ($model->status == 0){
                        $ret = Html::tag('span', $state[$model->status], ['class' => 'not-set']);
                    }else {
                        $ret = $state[$model->status];
                    }
                    return $ret;
                },
            ],
            'create_time',
            'last_modify_time',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?></div>
