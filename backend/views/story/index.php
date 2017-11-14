<?php

use yii\helpers\ArrayHelper;
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
    <!--    <h1>--><? //= Html::encode($this->title) ?><!--</h1>-->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="callout callout-warning">
        <p>首页：故事最少消息数量 <?= Yii::$app->params['tagStoryMinMessageCount'] ?>;     标签页：故事最少消息数量 <?= Yii::$app->params['homeStoryMinMessageCount']  ?></p>

    </div>

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
                    $ret = Html::a($m->story_id,
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
                'value' => function ($model) {
                    $ret = Html::a($model->name,
                        ['story/update', 'id' => $model->story_id]);
                    return $ret;
                },
            ],

            [
                'label' => '标签',
                'format' => 'raw',
                'attribute'=>'tag_id',
                'value' => function($model) {
                    $ret = "<p >";
                    foreach ($model->tags as $tag) {

                        if (isset(Yii::$app->params['tagNameBg'][$tag->tag_id])) {
                            $bg = Yii::$app->params['tagNameBg'][$tag->tag_id];
                        } else {
                            $bg = Yii::$app->params['defaultTagNameBg'];
                        }
                        $tagNameUrl = Html::a($tag->name,
                            ['story/index', 'StorySearch[tag_id]' => $tag->tag_id], ['style' => 'color:#fff']);
                        $ret .= " <small class=\"label {$bg}\">{$tagNameUrl}</small>";
                    }
                    $ret .= "</p>";
                    return $ret;
                },
            ],


            [
                'attribute' => 'uid',
                'format' => 'raw',
                'value' => function ($model) {
                    $ret = '';
                    if (!empty($model->user)) {
                        $ret = Html::a($model->user->username,
                            ['user/update', 'id' => $model->uid]);
                    }
                    return $ret;
                },
            ],

            [
                'attribute' => 'chapter_count',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'value' => function ($model) {
                    $ret = Html::a($model->chapter_count . "章",
                        ['chapter/index', 'ChapterSearch[story_id]' => $model->story_id]);
                    return $ret;
                },
            ],

            [
                'attribute' => 'message_count',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'value' => function ($model) {

                    $ret = Html::a($model->message_count . "条",
                        ['chapter-message-content/index', 'ChapterMessageContentSearch[story_id]' => $model->story_id]);
                    return $ret;
                },
            ],

            [
                'attribute' => 'comment_count',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'value' => function ($model) {

                    $commentTargetType = ArrayHelper::index(Yii::$app->params['COMMENT_TARGET_TYPE'], 'alias');
                    $ret = Html::a($model->comment_count . "条",
                        ['comment/index', 'CommentSearch[target_type]' => $commentTargetType['story']['value'], 'CommentSearch[target_id]' => $model->story_id]);
                    return $ret;
                },
            ],
            [
                'attribute' => 'taps',
                'headerOptions' => ['style' => 'width:100px'],
                'value' => function ($model) {
                    return $model->taps . "次";
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
                    if ($model->is_published == 0) {
                        $ret = Html::tag('span', $state[$model->is_published], ['class' => 'not-set']);
                    } else {
                        $ret = $state[$model->is_published];
                    }
                    return $ret;
                },
            ],
            [
                'attribute' => 'is_serialized',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'contentOptions' => ['style' => 'width:10px;'],
                'value' => function ($model) {
                    $state = [
                        '0' => '已完本',
                        '1' => '连载中',
                    ];
                    if ($model->is_serialized == 0) {
                        $ret = Html::tag('span', $state[$model->is_serialized], ['class' => 'not-set']);
                    } else {
                        $ret = $state[$model->is_serialized];
                    }
                    return $ret;
                },
            ],
            [
                'attribute' => 'is_pay',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'contentOptions' => ['style' => 'width:10px;'],
                'value' => function ($model) {
                    $state = [
                        '0' => '免费',
                        '1' => '收费',
                    ];
                    if ($model->is_pay == 0) {
                        $ret = Html::tag('span', $state[$model->is_pay], ['class' => 'not-set']);
                    } else {
                        $ret = $state[$model->is_pay];
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

                    if (!isset($state[$model->status])) {
                        $ret = Html::tag('span', '未知', ['class' => 'not-set']);
                    } else if ($model->status == 0) {
                        $ret = Html::tag('span', $state[$model->status], ['class' => 'not-set']);
                    } else {
                        $ret = $state[$model->status];
                    }
                    return $ret;
                },
            ],
            [
                'attribute' => 'create_time',
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],
            [
                'attribute' => 'last_modify_time',
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?></div>
