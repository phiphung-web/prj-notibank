<?php

use yii\helpers\Url;

?>
<div class="d-flex align-items-center mb-20">
    <div class="wrap-btn mr-20">
        <a href="<?= Url::to(['driver/driver-many-trips']) ?>" class="btn btn-primary">Lái xe đi nhiều chuyến</a>
    </div>
    <div class="wrap-btn mr-20">
        <a href="<?= Url::to(['driver/driver-no-trips']) ?>" class="btn btn-danger">Lái xe chưa nhận chuyến theo ngày</a>
    </div>
    <div class="wrap-btn mr-20">
        <a href="<?= Url::to(['driver/driver-not-active']) ?>" class="btn btn-warning">Lái xe tạo tài khoản nhưng chưa hoạt động</a>
    </div>
</div>