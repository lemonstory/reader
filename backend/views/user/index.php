<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '用户');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '新增用户'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'uid',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            [
                'attribute' => 'avatar',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:120px'],
                'contentOptions' => ['class' => 'box-body box-profile'],
                'value' => function ($m) {
                    $uid = $m->uid;
                    $avatar = $m->avatar;
                    $ret = '';
                    if(!empty($avatar)) {
                        $img = Html::img($avatar,
                            ['class' => 'img-circle img-bordered-sm', 'style' => 'width: 40px;height: 40px;margin: 0 auto;display: block;','alt' => "评论者"]
                        );
                        $ret = Html::a($img,
                            ['user/update', 'id' => $uid]);

                    }
                    return $ret;
                }
            ],

            [
                'attribute' => 'name',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:50px'],
                'value' => function ($m) {
                    $uid = $m->uid;
                    $ret = Html::a($m->name,
                        ['user/update', 'id' => $uid]);
                    return $ret;
                }
            ],
            [
                'attribute' => 'signature',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            [
                'attribute' => 'cellphone',
                'headerOptions' => ['style' => 'width:50px'],
                'value' => function ($model) {
                    $ret = "";
                    if(!empty($model->cellphone)) {
                        $ret = $model->cellphone;
                    }
                    return $ret;
                },
            ],
//            'password',
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
                'attribute' => 'register_ip',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            [
                'attribute' => 'register_time',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            [
                'attribute' => 'last_login_ip',
                'headerOptions' => ['style' => 'width:120px'],
            ],
            [
                'attribute' => 'last_login_time',
                'headerOptions' => ['style' => 'width:120px'],
                'value' => function ($model) {
                    $ret = "";
                    if(!empty($model->last_login_time)) {
                        $ret = $model->last_login_time;
                    }
                    return $ret;
                },
            ],
            [
                'attribute' => 'last_modify_time',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
