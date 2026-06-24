<?php

use yii\bootstrap\Alert;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Message */

$this->title = '';
// $this->params['breadcrumbs'][] = ['label' => 'Messages', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="message-view">

    <?php
        Alert::begin([
            'options' => [
                'class' => $success ? 'alert-success' : 'alert-danger',
            ],
        ]);

        echo $msg;

        Alert::end();
    ?>

    <p>
        <?= Html::a('Gửi thông báo', ['create'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Danh sách thông báo', ['/message'], ['class' => 'btn btn-success']) ?>
    </p>


</div>