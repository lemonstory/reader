<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\UserOauth */

$this->title = $model->user_oauth_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Oauths'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-oauth-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->user_oauth_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->user_oauth_id], [
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
            'user_oauth_id',
            'uid',
            'oauth_name',
            'oauth_id',
            'oauth_access_token',
            'oauth_expire',
            'create_time',
            'last_modify_time',
            'status',
        ],
    ]) ?>

</div>
