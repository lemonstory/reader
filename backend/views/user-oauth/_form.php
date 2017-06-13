<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UserOauth */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-oauth-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'uid')->textInput() ?>

    <?= $form->field($model, 'oauth_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'oauth_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'oauth_access_token')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'oauth_expire')->textInput() ?>

    <?= $form->field($model, 'create_time')->textInput() ?>

    <?= $form->field($model, 'last_modify_time')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
