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
        <?= Html::a(Yii::t('app', '新增标签'), ['create'], ['class' => 'btn btn-success']) ?>
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
                    if(isset(Yii::$app->params['tagNameBg'][$m->tag_id])) {
                        $bg = Yii::$app->params['tagNameBg'][$m->tag_id];
                    }else {
                        $bg = Yii::$app->params['defaultTagNameBg'];
                    }

                    $ret .= "<p >";
                        $tagNameUrl = Html::a($m->name,
                            ['tag/view', 'id' => $m->tag_id], ['style' => 'color:#fff']);
                        $ret .= " <small class=\"label {$bg}\">{$tagNameUrl}</small>";
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
