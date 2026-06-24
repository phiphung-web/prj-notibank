<?php

use app\helpers\MyStringHelper;
use yii\web\YiiAsset;

$this->title = 'Danh sách khách đang nợ';
$this->params['breadcrumbs'][] = $this->title;

// include js
$this->registerJsFile('/js/pages/trip-debt-customers.js', ['depends' => [YiiAsset::class]]);

/* @var $dataProvider */
/* @var $searchModel */
/* @var $debtType */
?>

<div class="trip-index">
  <div class="box box-green">
      <div class="box-header with-border">
          <h3 class="box-title">Filter</h3>
          <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse">
                  <i class="fa fa-minus"></i>
              </button>
          </div>
      </div>

      <?php echo $this->render('components/search-filter', compact(['debtType'])); ?>
  </div>

  <?php
    $totalPriceCustomer = $money['total_price_customer'] ? $money['total_price_customer'] : 0;
    $totalBidPrice = $money['total_bid_price'] ? $money['total_bid_price'] : 0;
  ?>
    <div class="row">
        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3 class="customer-month fsc-sm-24">
                      <?= MyStringHelper::convertIntegerToPrice((isset($totalPriceCustomer) ? $totalPriceCustomer : 0)) ?>₫
                    </h3>
                    <p>Tổng số tiền thu khách.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3 class="customer-booked fsc-sm-24">
                      <?= MyStringHelper::convertIntegerToPrice((isset($totalBidPrice) ? $totalBidPrice : 0)) ?>₫
                    </h3>
                    <p>Tổng số tiền lái xe nhận.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3 class="customer-canceled fsc-sm-24">
                      <?= MyStringHelper::convertIntegerToPrice($totalPriceCustomer - $totalBidPrice) ?>₫
                    </h3>
                    <p>Lợi nhuận.</p>
                </div>
            </div>
        </div>
    </div>

  <div class="table-view-list js-ajax-table">
      <?php $tripList = $dataProvider->getModels(); ?>

      <?php echo $this->render('components/table-debt-customers', compact(['searchModel', 'tripList', 'dataProvider'])) ?>
  </div>
</div>
