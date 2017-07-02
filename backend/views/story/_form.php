<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Story */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="story-form">

    <?php
        $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'],
            'fieldConfig'=>[
                'template'=> "{label}<div class=\"row\"><div class=\"col-sm-2\">{input}</div>{error}</div>",
                'labelOptions' => [ 'class' => 'col-sm-1 control-label' ]
            ]
        ]);
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description', [
            'template'=> "{label}<div class=\"row\"><div class=\"col-sm-6\">{input}</div>{error}</div>",
            'labelOptions' => [ 'class' => 'col-sm-1 control-label' ]
        ])->textarea(['maxlength' => true]) ?>

    <?php
        $coverImg =  Html::img($model->cover, ['class' => 'img-rounded', 'width' => 90]);
    ?>

    <?= $form->field($model, 'cover', [
        'template'=> "{label}<div class=\"row\"><div class=\"col-sm-6\"><p>{$coverImg}</p>{input}</div>{error}</div>",
        'labelOptions' => [ 'class' => 'col-sm-1 control-label' ],
//        'inputOptions' => [ 'name' => 'file' ]
    ])->fileInput() ?>


    <?= $form->field($model, 'uid')->textInput() ?>

    <?= $form->field($model, 'chapter_count')->textInput(['disabled' => true]) ?>

    <?= $form->field($model, 'message_count')->textInput(['disabled' => true]) ?>

    <?= $form->field($model, 'taps')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_published')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'create_time')->textInput(['disabled' => true]) ?>

    <?= $form->field($model, 'last_modify_time')->textInput(['disabled' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '新建') : Yii::t('app', '修改'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
