<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ChapterMessageContent */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Chapter Message Content',
]) . $model->chapter_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Chapter Message Contents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->chapter_id, 'url' => ['view', 'chapter_id' => $model->chapter_id, 'story_id' => $model->story_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="chapter-message-content-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
