<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ChapterMessageContentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="chapter-message-content-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'message_id') ?>

    <?= $form->field($model, 'story_id') ?>

    <?= $form->field($model, 'chapter_id') ?>

    <?= $form->field($model, 'number') ?>

    <?= $form->field($model, 'voice_over') ?>

    <?php // echo $form->field($model, 'actor_id') ?>

    <?php // echo $form->field($model, 'text') ?>

    <?php // echo $form->field($model, 'img') ?>

    <?php // echo $form->field($model, 'is_loading') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'last_modify_time') ?>

    <?php // echo $form->field($model, 'status') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
