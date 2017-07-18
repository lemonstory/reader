<?php

use yii\helpers\ArrayHelper;
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

    <?= $form->field($model, 'name')->textInput(['maxlength' => true,'required'=>true]) ?>
    <?= $form->field($model, 'sub_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description', [
            'template'=> "{label}<div class=\"row\"><div class=\"col-sm-6\">{input}</div>{error}</div>",
            'labelOptions' => [ 'class' => 'col-sm-1 control-label' ]
        ])->textarea(['maxlength' => true,'required'=>true]) ?>

    <?php
        $coverImg =  Html::img($model->cover, ['class' => 'img-rounded', 'width' => 90]);
    ?>

    <?php

        $fileInputOptions = [];
        if(empty($model->cover)) {
            $fileInputOptions = ['required'=>true];
        }

        echo $form->field($model, 'cover', [
            'template'=> "{label}<div class=\"row\"><div class=\"col-sm-6\"><p>{$coverImg}</p>{input}</div>{error}</div>",
            'labelOptions' => [ 'class' => 'col-sm-1 control-label' ],
        ])->fileInput($fileInputOptions)
    ?>

    <?= $form->field($model, 'uid')->textInput() ?>
    <?= $form->field($model, 'chapter_count')->textInput(['disabled' => true]) ?>
    <?= $form->field($model, 'message_count')->textInput(['disabled' => true]) ?>
    <?= $form->field($model, 'comment_count')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'taps')->textInput(['maxlength' => true]) ?>
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

    <?php

        $listBoxItems = array();
        if(!empty($allTagArr)) {
            foreach ($allTagArr as $tag) {
                $tag_id = (string)$tag['tag_id'];
                $tag_name = (string)$tag['name'];
                $listBoxItems[$tag_id] = $tag_name;
            }
        }
    ?>

    <!-- TODO:下面写得有点撮, $form->field($model...)里面的$model是一个对象,但是tag关系findAll后拿到的是个数组,所以无法使用 $form->field..这种简洁的写法-->
    <!-- $form->field($model, 'xxx')->listBox($listBoxItems,['multiple' => 'true'])-->
    <div class="form-group field-story-tags">
        <label class="col-sm-1 control-label" for="story-tags">标签</label>
        <div class="row">
            <div class="col-sm-2">
                <?php
                    $selection = null;
                    if(!empty($checkTagArr)) {
                        $selection = ArrayHelper::getColumn($checkTagArr, 'tag_id');
                        echo Html::listBox('Story[tags]', $selection, $listBoxItems, ['multiple' => 'true', 'class' => 'form-control']);
                    }else {
                        echo Html::listBox('Story[tags]', $selection, $listBoxItems, ['multiple' => 'true', 'class' => 'form-control']);
                    }
                ?>
            </div>
            <div class="help-block"></div>
        </div>
    </div>

    <?= $form->field($model, 'create_time')->textInput(['disabled' => true]) ?>
    <?= $form->field($model, 'last_modify_time')->textInput(['disabled' => true]) ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '新建') : Yii::t('app', '修改'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
