<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StoryUpdateSubscription */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Story Update Subscription',
]) . $model->uid;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Story Update Subscriptions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->uid, 'url' => ['view', 'uid' => $model->uid, 'story_id' => $model->story_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="story-update-subscription-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
