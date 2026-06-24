<?php
/* @var $this yii\web\View */
?>

<div class="col-md-4 col-xs-12">
    <div class="box box-success" data-select2-id="select2-data-11-15ak">
        <div class="box-header with-border">
            <h3 class="box-title">Tìm kiếm số điện thoại</h3>
        </div>
        <div class="box-body" data-select2-id="select2-data-10-5ijr">
            <form class="d-flex form-search-phone">
                <input type="text" class="form-control input-search-phone" placeholder="Nhập số điện thoại cần tìm kiếm..." value="<?php echo (!empty($_GET['phone']) ? $_GET['phone'] : ''); ?>">
                <button class="btn btn-success mt-mb-2" type="submit" style="border-radius:0">Tìm kiếm</button>
            </form>
        </div>
    </div>
    <div id="list-call">
    </div>
</div>
