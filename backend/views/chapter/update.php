<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Chapter */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Chapter',
]) . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Chapters'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->chapter_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="chapter-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
