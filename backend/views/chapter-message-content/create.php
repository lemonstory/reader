<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ChapterMessageContent */

$this->title = Yii::t('app', 'Create Chapter Message Content');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Chapter Message Contents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chapter-message-content-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
