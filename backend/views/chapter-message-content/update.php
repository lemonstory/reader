<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ChapterMessageContent */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Chapter Message Content',
]) . $model->message_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Chapter Message Contents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->message_id, 'url' => ['view', 'id' => $model->message_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="chapter-message-content-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
