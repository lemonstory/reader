<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Like */

$this->title = $model->target_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Likes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="like-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'target_id' => $model->target_id, 'target_type' => $model->target_type, 'owner_uid' => $model->owner_uid, 'target_uid' => $model->target_uid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'target_id' => $model->target_id, 'target_type' => $model->target_type, 'owner_uid' => $model->owner_uid, 'target_uid' => $model->target_uid], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'target_id',
            'target_type',
            'owner_uid',
            'target_uid',
            'status',
            'create_time',
            'last_modify_time',
        ],
    ]) ?>

</div>
