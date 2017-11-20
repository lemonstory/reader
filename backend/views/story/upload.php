<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?php

$this->title = Yii::t('app', '上传故事');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php

$listBoxItems = array();
if (!empty($allTagArr)) {
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
    <h5 class="box-title">第一步：选择标签</h5>
    <div class="row">
        <div class="col-sm-2">
            <?php
                echo Html::listBox('Story[tags]', null, $listBoxItems, ['multiple' => 'true', 'required' => true, 'class' => 'form-control']);
            ?>
        </div>
        <div class="help-block"></div>
    </div>
</div>
<h5 class="box-title">第二步：选择故事txt文件</h5>
<?= $form->field($model, 'file')->fileInput(['required' => true])->label('') ?>
<button class="btn btn-success btn-sm">完成</button>
<?php ActiveForm::end(); ?>
