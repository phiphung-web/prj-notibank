<?php

use yii\web\YiiAsset;

$this->title = 'Danh sách nạp tiền';
$this->params['breadcrumbs'][] = $this->title;

// include js
$this->registerJsFile('/js/pages/sms-pay-transaction.js', ['depends' => [YiiAsset::class]]);

/* @var $this yii\web\View */
/* @var $searchModel */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<div class="pay-transaction-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
        </div>

        <div class="box-body">
            <?php echo $this->render('_search_sms', [
                'model' => $searchModel,
                'adminList' => $adminList,
                'systemConfiguration' => $systemConfiguration,
            ]) ?>
        </div>
    </div>

    <div class="table-view-list js-ajax-table">
        <?php $smsPayList = $dataProvider->getModels(); ?>

        <?php echo $this->render('table_customer', compact(['searchModel', 'smsPayList', 'dataProvider', 'adminList'])) ?>
    </div>
</div>