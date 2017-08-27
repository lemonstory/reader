<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\CommentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', '评论');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comment-index">

<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '新增评论'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'comment_id',
                'headerOptions' => ['style' => 'width:50px'],
            ],
            [
                'attribute' => 'owner_uid',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'contentOptions' => ['class' => 'box-body box-profile'],
                'value' => function ($m) use ($userArr) {

                    $uid = $m->owner_uid;
                    $username = '';
                    $avatar = '';
                    $ret = '';
                    if(!empty($userArr[$uid])) {
                        $username = $userArr[$uid]['username'];
                        $avatar = $userArr[$uid]['avatar'];
                        if(!empty($avatar)) {
                            $img = Html::img($avatar,
                                ['class' => 'img-circle img-bordered-sm', 'style' => 'width: 40px;height: 40px;margin: 0 auto;display: block;','alt' => "评论者"]
                            );
                            $ret = Html::a($img,
                                ['user/update', 'id' => $uid]);

                        }
                        if(!empty($username)) {
                            $ret .= Html::tag('p', $username, ['class' => 'text-muted text-center','style' => 'font-size: 13px; margin-top: 5px;']);
                        }
                    }
                    return $ret;
                }
            ],
            [
                'attribute' => 'target_uid',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'contentOptions' => ['class' => 'box-body box-profile'],
                'value' => function ($m) use ($userArr) {

                    $uid = $m->target_uid;
                    $username = '';
                    $avatar = '';
                    $ret = '';
                    if(!empty($userArr[$uid])) {
                        $username = $userArr[$uid]['username'];
                        $avatar = $userArr[$uid]['avatar'];
                        if(!empty($avatar)) {
                            $img = Html::img($avatar,
                                ['class' => 'img-circle img-bordered-sm', 'style' => 'width: 40px;height: 40px;margin: 0 auto;display: block;','alt' => "评论者"]
                            );
                            $ret = Html::a($img,
                                ['user/update', 'id' => $uid]);

                        }
                        if(!empty($username)) {
                            $ret .= Html::tag('p', $username, ['class' => 'text-muted text-center','style' => 'font-size: 13px; margin-top: 5px;']);
                        }

                    }
                    return $ret;
                }
            ],
            [
                'attribute' => 'content',
                'contentOptions' => ['style' => 'width:300px; white-space: normal;font-size: 90%;'],
            ],
            [
                'attribute' => 'target_type',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:120px'],
                'value' => function ($model) {

                    $targetTypeArr = Yii::$app->params['COMMENT_TARGET_TYPE'];
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
                'attribute' => 'parent_comment_id',
                'headerOptions' => ['style' => 'width:80px'],
            ],
            [
                'attribute' => 'target_id',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width:100px'],
                'value' => function ($model) {
                    $targetTypeArr = Yii::$app->params['COMMENT_TARGET_TYPE'];
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
            'like_count',
            [
                'attribute' => 'like_count',
                'headerOptions' => ['style' => 'width:50px'],
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