<?php

use yii\web\YiiAsset;

$this->title = 'Danh sách xác nhận tài xế';
$this->params['breadcrumbs'][] = $this->title;

// include js
$this->registerJsFile('/js/pages/call-driver.js', ['depends' => [YiiAsset::class]]);

?>
<div class="call-drive-warp">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i></button>
            </div>
        </div>

        <?php echo $this->render('components/search-filter'); ?>
    </div>

    <div class="table-view-list mt-10">
        <div class="row">
            <div class="col-lg-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Chuyến xe bình thường</h3>
                    </div>
                    <div class="box-body wrap-table-normal">
                        <?php echo $this->render('components/table', ['class' => 'table-trip-normal', 'tripList' => $tripList['normal']]); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Chuyến xe muộn sau 23h</h3>
                    </div>
                    <div class="box-body wrap-table-late">
                        <?php echo $this->render('components/table', ['class' => 'table-trip-late', 'tripList' => $tripList['late']]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>