<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ChapterMessageContent */

$this->title = $model->chapter_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Chapter Message Contents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chapter-message-content-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'chapter_id' => $model->chapter_id, 'story_id' => $model->story_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'chapter_id' => $model->chapter_id, 'story_id' => $model->story_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'chapter_id',
            'story_id',
            'message_content:ntext',
            'create_time',
            'last_modify_time',
            'status',
        ],
    ]) ?>

</div>
