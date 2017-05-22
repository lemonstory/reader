<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\StoryUpdateSubscription */

$this->title = Yii::t('app', 'Create Story Update Subscription');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Story Update Subscriptions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="story-update-subscription-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
