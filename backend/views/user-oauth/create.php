<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\UserOauth */

$this->title = Yii::t('app', 'Create User Oauth');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Oauths'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-oauth-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
