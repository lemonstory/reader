<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ChapterMessageContentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '消息内容');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chapter-message-content-index">

<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '新增消息内容'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php
        $storyActorArr = ArrayHelper::index($storyActorArr, null, 'story_id');
    ?>

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
                'attribute' => 'chapter_id',
                'contentOptions' => ['style' => 'width:10px;'],
                'format' => 'raw',
                'value' => function ($m) {
                    $ret =  Html::a($m->chapter_id,
                        ['chapter/update', 'id' => $m->chapter_id,]);

                    return $ret;
                },
            ],
            [
                'attribute' => 'message_id',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            [
                'attribute' => 'number',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            [
                'attribute' => 'actor_id',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:50px'],
                'contentOptions' => ['class' => 'box-body box-profile'],
                'value' => function ($m) use ($storyActorArr) {

                    $storyId = $m->story_id;
                    $actorId = $m->actor_id;
                    $name = '';
                    $avatar = '';
                    $ret = '';

                    if(!empty($storyActorArr[$storyId])) {
                        foreach ($storyActorArr[$storyId] as $actorArr) {
                            if($actorArr['actor_id'] == $actorId) {
                                $name = $actorArr['name'];
                                $avatar = $actorArr['avatar'];
                            }
                        }
                    }

                    if(!empty($avatar)) {
                        $img = Html::img($avatar,
                            ['class' => 'img-circle img-bordered-sm', 'style' => 'width: 40px;height: 40px;margin: 0 auto;display: block;','alt' => "角色"]
                        );
                        $ret = Html::a($img,
                            ['story-actor/update', 'id' => $m->actor_id]);

                    }
                    if(!empty($name)) {
                        $ret .= Html::tag('p', $name, ['class' => 'text-muted text-center','style' => 'font-size: 13px; margin-top: 5px;']);
                    }
                    return $ret;
                }
            ],
            [
                'attribute' => 'img',
                'headerOptions' => ['style' => 'width:100px'],
                'format' => 'raw',
                'value' => function ($m) {

                    $ret = '';
                    if(!empty($m->img)) {
                        $img = Html::img($m->img,
                            ['class' => 'img-rounded', 'width' => 90]
                        );
                        $ret = Html::a($img,
                            ['chapter-message-content/update', 'id' => $m->message_id]);
                    }
                    return $ret;
                }
            ],
//            'voice_over:ntext',
//             'text:ntext',
            [
                'attribute' => 'voice_over',
                'contentOptions' => ['style' => 'width:150px; white-space: normal;font-style:italic;font-size: 80%;color: #777;'],
                'value' => function ($model) {
                    $ret = "";
                    if(!empty($model->voice_over)) {
                        $ret = $model->voice_over;
                    }
                    return $ret;
                },
            ],

            [
                'attribute' => 'text',
                'contentOptions' => ['style' => 'width:300px; white-space: normal;font-size: 90%;'],
            ],
            [
                'attribute' => 'is_loading',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:10px'],
                'value' => function ($model) {
                    $state = [
                        '0' => '无',
                        '1' => '有',
                    ];
                    if(!isset($state[$model->is_loading])) {
                        $ret = Html::tag('span', '未知', ['class' => 'not-set']);
                    }else{
                        $ret = Html::tag('span', $state[$model->is_loading]);
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
            ],
            [
                'attribute' => 'last_modify_time',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
