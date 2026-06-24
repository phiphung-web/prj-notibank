<?php


/* @var $this yii\web\View */
/* @var $model app\models\Admin */
/* @var $dataAgency */

$this->title = 'Update : ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Danh sách tài khoản', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="admin-update">
    <?= $this->render('_form', [
        'model' => $model,
        'bankTransaction' => $bankTransaction,
        'dataAgency' => $dataAgency,
    ]) ?>
</div>
