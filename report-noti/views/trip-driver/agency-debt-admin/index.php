<?php

use yii\web\YiiAsset;

$this->title = 'Danh sách đại lý nợ tổng đài';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('/js/pages/trip-debt-agency.js', ['depends' => [YiiAsset::class]]);
?>

<div class="trip-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Bộ lọc tìm kiếm</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $this->render('search', ['model' => $searchModel]); ?>
        </div>
    </div>

    <div class="table-view-list js-ajax-table">
        <?php $agencyList = $dataProvider->getModels(); ?>
        <?php echo $this->render('table', ['dataProvider' => $dataProvider, 'agencyList' => $agencyList]) ?>
    </div>
</div>