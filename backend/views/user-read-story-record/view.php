<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\UserReadStoryRecord */

$this->title = $model->uid;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Read Story Records'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-read-story-record-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'uid' => $model->uid, 'story_id' => $model->story_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'uid' => $model->uid, 'story_id' => $model->story_id], [
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
            'uid',
            'story_id',
            'last_chapter_id',
            'last_message_id',
            'create_time',
            'last_modify_time',
            'status',
        ],
    ]) ?>

</div>
