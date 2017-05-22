<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\StoryActor */

$this->title = Yii::t('app', 'Create Story Actor');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Story Actors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="story-actor-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
