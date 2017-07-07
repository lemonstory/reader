<?php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?php

$this->title = Yii::t('app', '上传故事');
$this->params['breadcrumbs'][] = $this->title;
?>
<?= $form->field($model, 'file')->fileInput()->label('故事txt文件') ?>

    <button class="btn btn-success btn-sm">上传文件</button>

<?php ActiveForm::end(); ?>
