<?php


/* @var $this yii\web\View */
/* @var $model app\models\Driver */

$this->title = 'Sửa thông tin tài xế: "' . $model->display_name . '"';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách tài xế', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->display_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Sửa';
?>
<div class="driver-update">

    <?= $this->render('_form', [
        'model' => $model,
        'carModel' => $carModel,
    ]) ?>

</div>