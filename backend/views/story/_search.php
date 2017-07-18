<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\StorySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="story-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'story_id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'sub_name') ?>

    <?= $form->field($model, 'description') ?>

    <?= $form->field($model, 'cover') ?>

    <?php // echo $form->field($model, 'uid') ?>

    <?php // echo $form->field($model, 'chapter_count') ?>

    <?php // echo $form->field($model, 'message_count') ?>

    <?php // echo $form->field($model, 'comment_count') ?>

    <?php // echo $form->field($model, 'taps') ?>

    <?php // echo $form->field($model, 'is_published') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'last_modify_time') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
