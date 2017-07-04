<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\TagSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '标签');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tag-index">

<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '新建标签'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'tag_id',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            [
                'attribute' => 'number',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            [

                'attribute' => 'name',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:50px'],
                'value' => function ($m) {

                    $ret = "";
                    //TODO:下面的tag_id是硬编码,需要更改
                    //'tag_id' => 'bg color'
                    $bg = [
                        '1' => 'bg-yellow',
                        '2' => 'bg-green',
                        '3' => 'bg-red',
                    ];
                    $ret .= "<p >";

                        $tagNameUrl = Html::a($m->name,
                            ['tag/view', 'id' => $m->tag_id], ['style' => 'color:#fff']);
                        $ret .= " <small class=\"label {$bg[$m->tag_id]}\">{$tagNameUrl}</small>";
                    $ret .= "</p>";
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
