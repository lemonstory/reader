<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\LikeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '赞');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="like-index">

<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '新增赞'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'uid',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'contentOptions' => ['class' => 'box-body box-profile'],
                'value' => function ($m) use ($userArr) {
                    $uid = $m->uid;
                    $name = '';
                    $avatar = '';
                    $ret = '';
                    if(!empty($userArr[$uid])) {
                        $name = $userArr[$uid]['name'];
                        $avatar = $userArr[$uid]['avatar'];
                        if(!empty($avatar)) {
                            $img = Html::img($avatar,
                                ['class' => 'img-circle img-bordered-sm', 'style' => 'width: 40px;height: 40px;margin: 0 auto;display: block;','alt' => "评论者"]
                            );
                            $ret = Html::a($img,
                                ['user/update', 'id' => $uid]);

                        }
                        if(!empty($name)) {
                            $ret .= Html::tag('p', $name, ['class' => 'text-muted text-center','style' => 'font-size: 13px; margin-top: 5px;']);
                        }

                    }
                    return $ret;
                }
            ],
            [
                'attribute' => 'target_id',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'value' => function ($model) {
                    $targetTypeArr = Yii::$app->params['LIKE_TARGET_TYPE'];
                    $targetTypeArr = ArrayHelper::index($targetTypeArr,'value');
                    $targetTypeValue = $model->target_type;
                    $ret = '';
                    if(ArrayHelper::keyExists($targetTypeValue, $targetTypeArr, false)) {

                        if(isset($targetTypeArr[$targetTypeValue]['alias'])) {
                            $ret = Html::a($model->target_id, ["{$targetTypeArr[$targetTypeValue]['alias']}/view", 'id' => $model->target_id]);
                        } else {
                            $ret = "系统错误[alias]未设置";
                        }

                    }else {
                        $ret = "系统错误[type]未设置";
                    }
                    return $ret;
                },
            ],
            [
                'attribute' => 'target_type',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:120px'],
                'value' => function ($model) {

                    $targetTypeArr = Yii::$app->params['LIKE_TARGET_TYPE'];
                    $targetTypeArr = ArrayHelper::index($targetTypeArr,'value');
                    $targetTypeValue = $model->target_type;
                    $ret = '';
                    if(ArrayHelper::keyExists($targetTypeValue, $targetTypeArr, false)) {

                        if(isset($targetTypeArr[$targetTypeValue]['label'])) {
                            $ret = $targetTypeArr[$targetTypeValue]['label'];
                            $ret = Html::a($ret, ["{$targetTypeArr[$targetTypeValue]['alias']}/view", 'id' => $model->target_id]);
                        } else {
                            $ret = "系统错误[label]未设置";
                        }
                    } else {
                        $ret = "系统错误[type]未设置";
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
