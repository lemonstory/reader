<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', '上传章节');
$this->params['breadcrumbs'][] = $this->title;
$form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data'],
    'fieldConfig'=>[
        'template'=> "{label}<div class=\"row\"><div class=\"col-sm-2\">{input}</div>{error}</div>",
        'labelOptions' => [ 'class' => 'col-sm-1 control-label' ]
    ]
]);
?>
<div class="form-group field-story-tags">
    <label class="col-sm-1 control-label" for="story-tags">故事Id</label>
    <div class="row">
        <div class="col-sm-2">
            <?= Html::input('text', 'story_id', '', ['class' => 'form-control','required'=>true]) ?>
        </div>
        <div class="help-block"></div>
    </div>
</div>

<?= $form->field($model, 'file')->fileInput(['required'=>true])->label('章节txt文件') ?>

<button class="btn btn-success btn-sm">上传文件</button>

<?php ActiveForm::end(); ?>
