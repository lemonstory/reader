<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ChapterSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '章节');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chapter-index">

<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '新增章节'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'story_id',
                'contentOptions' => ['style' => 'width:10px;'],
                'format' => 'raw',
                'value' => function ($model) {
                    $ret =  Html::a($model->story_id,
                        ['story/update', 'id' => $model->story_id,]);

                    return $ret;
                },
            ],
            [
                'attribute' => 'number',
                'headerOptions' => ['style' => 'width:50px'],
                'value' => function ($model) {
                    $ret =  '#'.$model->number;
                    return $ret;
                },
            ],
            [
                'attribute' => 'chapter_id',
                'contentOptions' => ['style' => 'width:10px;'],
                'format' => 'raw',
                'value' => function ($model) {
                    $ret =  Html::a($model->chapter_id,
                        ['chapter/update', 'id' => $model->chapter_id,]);
                    return $ret;
                },
            ],
            [
                'attribute' => 'background',
                'headerOptions' => ['style' => 'width:100px'],
                'format' => 'raw',
                'value' => function ($model) {
                    $cover = Html::img($model->background,
                        ['class' => 'img-rounded', 'width' => 90]
                    );
                    return Html::a($cover,
                        ['chapter/update', 'id' => $model->chapter_id]);
                    ;
                }
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'contentOptions' => ['style' => 'width:10px;'],
                'value' => function ($model) {
                    $ret = "";
                    if(!empty($model->name)) {
                        $ret = $model->name;
                    }
                    return $ret;
                },
            ],
            [
                'attribute' => 'message_count',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'value' => function ($model) {
                    $ret = Html::a($model->message_count."条",
                        ['chapter-message-content/index',
                                'ChapterMessageContentSearch[story_id]' => $model->story_id,
                                'ChapterMessageContentSearch[chapter_id]' => $model->chapter_id
                        ]);
                    return $ret;
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
            [
                'attribute' => 'create_time',
                'headerOptions' => ['style' => 'width:50px'],
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],
            [
                'attribute' => 'last_modify_time',
                'headerOptions' => ['style' => 'width:50px'],
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
