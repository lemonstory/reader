<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\LikeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="like-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'target_id') ?>

    <?= $form->field($model, 'target_type') ?>

    <?= $form->field($model, 'uid') ?>

    <?= $form->field($model, 'status') ?>

    <?= $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'last_modify_time') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
