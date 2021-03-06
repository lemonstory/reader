<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\StoryActor */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="story-actor-form">

    <?php
        $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'],
            'fieldConfig'=>[
                'template'=> "{label}<div class=\"row\"><div class=\"col-sm-2\">{input}</div>{error}</div>",
                'labelOptions' => [ 'class' => 'col-sm-1 control-label' ]
            ]
        ]);
    ?>

    <?= $form->field($model, 'story_id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php
        $avatar = "";
        if(!empty($model->avatar)) {
            $avatar =  Html::img($model->avatar, ['class' => 'profile-user-img img-circle', 'width' => 90]);
        }
    ?>
    <?php

        $fileInputOptions = [];
        if(empty($model->avatar)) {
            $fileInputOptions = ['required'=>true];
        }

        echo $form->field($model, 'avatar', [
            'template'=> "{label}<div class=\"row\"><div class=\"col-sm-6\"><p>{$avatar}</p>{input}</div>{error}</div>",
            'labelOptions' => [ 'class' => 'col-sm-1 control-label' ],
        ])->fileInput($fileInputOptions)
    ?>


    <?= $form->field($model, 'number')->textInput() ?>

    <?= $form->field($model, 'location')->radioList(
        ['0'=>'左','1'=>'右'],
        ['item' => function($index, $label, $name, $checked, $value) {

            $return = '<label class="modal-radio">';
            $return .= '<input type="radio" name="' . $name . '" value="' . $value . '" tabindex="3"';
            if($checked) {
                $return .= 'checked';
            }
            $return .= '>';
            $return .= '<i></i>';
            if($value == '0') {
                $return .= '<span class="not-set">' . $label . '</span>';
            }else {
                $return .= '<span>' . $label . '</span>';
            }
            $return .= '</label>';

            return $return;
        }]);
    ?>

<!--    --><?//= $form->field($model, 'is_visible')->textInput()->textInput(['disabled' => true]) ?>

<!--    --><?//= $form->field($model, 'is_visible')->radioList([1=>'可见','0'=>'不可见']); ?>
    <?= $form->field($model, 'is_visible')->radioList(
        ['1'=>'可见','0'=>'不可见'],
        ['item' => function($index, $label, $name, $checked, $value) {

            $return = '<label class="modal-radio">';
            $return .= '<input type="radio" name="' . $name . '" value="' . $value . '" tabindex="3"';
            if($checked) {
                $return .= 'checked';
            }
            $return .= '>';
            $return .= '<i></i>';
            if($value == '0') {
                $return .= '<span class="not-set">' . $label . '</span>';
            }else {
                $return .= '<span>' . $label . '</span>';
            }
            $return .= '</label>';

            return $return;
        }]);
    ?>

    <?= $form->field($model, 'status')->radioList(
        ['1'=>'正常','0'=>'删除'],
        ['item' => function($index, $label, $name, $checked, $value) {

            $return = '<label class="modal-radio">';
            $return .= '<input type="radio" name="' . $name . '" value="' . $value . '" tabindex="3"';
            if($checked) {
                $return .= 'checked';
            }
            $return .= '>';
            $return .= '<i></i>';
            if($value == '0') {
                $return .= '<span class="text-red">' . $label . '</span>';
            }else {
                $return .= '<span>' . $label . '</span>';
            }
            $return .= '</label>';

            return $return;
        }]);
    ?>

    <?= $form->field($model, 'create_time')->textInput(['disabled' => true, 'value' => date('Y-m-d H:i:s', $model->create_time)]) ?>
    <?= $form->field($model, 'last_modify_time')->textInput(['disabled' => true, 'value' => date('Y-m-d H:i:s', $model->create_time)]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '新建') : Yii::t('app', '修改'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
