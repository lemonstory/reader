<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Like */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Like',
]) . $model->target_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Likes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->target_id, 'url' => ['view', 'target_id' => $model->target_id, 'target_type' => $model->target_type, 'uid' => $model->uid]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="like-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
