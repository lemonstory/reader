<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UserReadStoryRecord */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'User Read Story Record',
]) . $model->uid;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Read Story Records'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->uid, 'url' => ['view', 'uid' => $model->uid, 'story_id' => $model->story_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="user-read-story-record-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
