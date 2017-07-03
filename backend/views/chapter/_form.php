<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Chapter */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="chapter-form">

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
    <?= $form->field($model, 'number')->textInput() ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?php
        $backgroundImg =  Html::img($model->background, ['class' => 'img-rounded', 'width' => 90]);
    ?>
    <?= $form->field($model, 'background', [
        'template'=> "{label}<div class=\"row\"><div class=\"col-sm-6\"><p>{$backgroundImg}</p>{input}</div>{error}</div>",
        'labelOptions' => [ 'class' => 'col-sm-1 control-label' ],
    ])->fileInput() ?>
    <?= $form->field($model, 'message_count')->textInput(['disabled' => true]) ?>
    <?= $form->field($model, 'is_published')->radioList([1=>'已发布','0'=>'未发布']); ?>
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

    <?= $form->field($model, 'create_time')->textInput(['disabled' => true]) ?>
    <?= $form->field($model, 'last_modify_time')->textInput(['disabled' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '新建') : Yii::t('app', '修改'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
