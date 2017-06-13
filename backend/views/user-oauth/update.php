<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UserOauth */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'User Oauth',
]) . $model->user_oauth_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Oauths'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->user_oauth_id, 'url' => ['view', 'id' => $model->user_oauth_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="user-oauth-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
