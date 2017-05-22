<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Chapter */

$this->title = Yii::t('app', 'Create Chapter');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Chapters'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chapter-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
