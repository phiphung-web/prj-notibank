<?php

use yii\grid\GridViewAsset;

GridViewAsset::register($this);

$this->title = 'Thống kê marketing yêu cầu gọi lại';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pay-transaction-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Bộ lọc</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i></button>
            </div>
        </div>

        <div class="box-body">
            <?php echo $this->render('search', [
                'model' => $searchModel,
            ]) ?>
        </div>
    </div>

    <div class="table-view-list js-ajax-table table-statistic">
        <?php
        $callbackList = $dataProvider->getModels();

        echo $this->render('table', compact(['searchModel', 'dataProvider', 'callbackList']));
        ?>
    </div>
</div>