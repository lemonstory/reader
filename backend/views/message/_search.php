<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\MessageSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="message-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'message_id') ?>

    <?= $form->field($model, 'chapter_id') ?>

    <?= $form->field($model, 'story_id') ?>

    <?= $form->field($model, 'from_actor_name') ?>

    <?= $form->field($model, 'from_actor_avatar') ?>

    <?php // echo $form->field($model, 'to_actor_name') ?>

    <?php // echo $form->field($model, 'to_actor_avatar') ?>

    <?php // echo $form->field($model, 'content') ?>

    <?php // echo $form->field($model, 'number') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'last_modify_time') ?>

    <?php // echo $form->field($model, 'status') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
