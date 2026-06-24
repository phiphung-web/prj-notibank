<?php


/* @var $this yii\web\View */
/* @var $model app\models\Customer */

$this->title = 'Chỉnh sửa thông tin khách hàng: ' . $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Danh sách khách hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->display_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Chỉnh Sửa';
?>
<div class="customer-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>