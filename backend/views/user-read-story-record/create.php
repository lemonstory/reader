<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\UserReadStoryRecord */

$this->title = Yii::t('app', 'Create User Read Story Record');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Read Story Records'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-read-story-record-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
