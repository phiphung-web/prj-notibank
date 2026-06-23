<?php
$this->title = 'Thêm khu vực';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách khu vực', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="driver-create">
    <?= $this->render('_form', [
        'model' => $model,
        'areaClone' => $areaClone,
    ]) ?>
</div>