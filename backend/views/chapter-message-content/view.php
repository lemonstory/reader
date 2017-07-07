<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ChapterMessageContent */

$this->title = $model->message_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Chapter Message Contents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chapter-message-content-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->message_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->message_id], [
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
            'message_id',
            'story_id',
            'chapter_id',
            'number',
            'voice_over:ntext',
            'actor_id',
            'text:ntext',
            'img',
            'is_loading',
            'create_time',
            'last_modify_time',
            'status',
        ],
    ]) ?>

</div>
