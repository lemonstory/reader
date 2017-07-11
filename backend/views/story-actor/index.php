<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\StoryActorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Story Actors');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="story-actor-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create Story Actor'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'actor_id',
                'contentOptions' => ['style' => 'width:10px;'],
                'format' => 'raw',
                'value' => function ($model) {
                    $ret =  Html::a($model->actor_id,
                        ['story-actor/update', 'id' => $model->actor_id,]);
                    return $ret;
                },
            ],
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
            ],
            [
                'attribute' => 'avatar',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:120px'],
                'contentOptions' => ['class' => 'box-body box-profile'],
                'value' => function ($m) {
                    $actor_id = $m->actor_id;
                    $avatar = $m->avatar;
                    $ret = '';
                    if(!empty($avatar)) {
                        $img = Html::img($avatar,
                            ['class' => 'img-circle img-bordered-sm', 'style' => 'width: 40px;height: 40px;margin: 0 auto;display: block;','alt' => "评论者"]
                        );
                        $ret = Html::a($img,
                            ['story-actor/update', 'id' => $actor_id]);

                    }
                    return $ret;
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
                        $ret = Html::a($model->name,
                            ['story-actor/update', 'id' => $model->actor_id,]);
                    }
                    return $ret;
                },
            ],
            [
                'attribute' => 'location',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:10px'],
                'value' => function ($model) {
                    $state = [
                        '0' => '左',
                        '1' => '右',
                    ];

                    if(!isset($state[$model->location])) {
                        $ret = Html::tag('span', '未知', ['class' => 'not-set']);
                    }else if ($model->location == 0){
                        $ret = Html::tag('span', $state[$model->location], ['class' => 'not-set']);
                    }else {
                        $ret = $state[$model->location];
                    }
                    return $ret;
                },
            ],
            [
                'attribute' => 'is_visible',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:10px'],
                'value' => function ($model) {
                    $state = [
                        '0' => '不可见',
                        '1' => '可见',
                    ];

                    if(!isset($state[$model->is_visible])) {
                        $ret = Html::tag('span', '未知', ['class' => 'not-set']);
                    }else if ($model->is_visible == 0){
                        $ret = Html::tag('span', $state[$model->is_visible], ['class' => 'not-set']);
                    }else {
                        $ret = $state[$model->is_visible];
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
