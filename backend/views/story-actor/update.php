<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StoryActor */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Story Actor',
]) . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Story Actors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->story_actor_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="story-actor-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
