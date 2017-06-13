<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UserOauthSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-oauth-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'user_oauth_id') ?>

    <?= $form->field($model, 'uid') ?>

    <?= $form->field($model, 'oauth_name') ?>

    <?= $form->field($model, 'oauth_id') ?>

    <?= $form->field($model, 'oauth_access_token') ?>

    <?php // echo $form->field($model, 'oauth_expire') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'last_modify_time') ?>

    <?php // echo $form->field($model, 'status') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
