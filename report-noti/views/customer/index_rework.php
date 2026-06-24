<?php

use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model */
/* @var $statistical */

$this->title = 'Khách hàng';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-index">
    <div class="box box-green" style="margin-bottom: 20px;">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $this->render('_search', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

    <?php $customerList = $dataProvider->getModels(); ?>

    <div class="dashboard">
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-blue">
                    <div class="inner">
                        <h3 class="customer-month">
                            <?= ! empty($statistical['totalTripMonth']) ? $statistical['totalTripMonth'] : 0 ?>
                        </h3>
                        <p>Tổng số lượt KH đã đi trong tháng.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 class="customer-booked">
                            <?= ! empty($statistical['totalTripBooked']) ? $statistical['totalTripBooked'] : 0 ?>
                        </h3>
                        <p>Tổng số chuyến mà KH đã đặt.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 class="customer-canceled">
                            <?= ! empty($statistical['totalTripCancel']) ? $statistical['totalTripCancel'] : 0 ?>
                        </h3>
                        <p>Tổng số chuyến mà KH đã hủy.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 class="customer-successfully">
                            <?= ! empty($statistical['totalSuccessfulTrips']) ? $statistical['totalSuccessfulTrips'] : 0 ?>
                        </h3>
                        <p>Tổng số chuyến mà KH đã đi thành công.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <table id="datatables_w0" class="table table-striped table-bordered" width="100%" cellspacing="0" style="background: #fff;">
        <thead>
            <tr>
                <th>
                    <?= $model->attributeLabels()['display_name'] ?>
                </th>
                <th>
                    <?= $model->attributeLabels()['phone'] ?>
                </th>
                <th class="text-center w155">
                    <?= $model->attributeLabels()['customer_booked'] ?>
                </th>
                <th class="text-center w155">
                    <?= $model->attributeLabels()['customer_canceled'] ?>
                </th>
                <th class="text-center w155">
                    <?= $model->attributeLabels()['customer_successfully'] ?>
                </th>
                <th>
                    <?= $model->attributeLabels()['created_on'] ?>
                </th>
                <th>
                    <?= $model->attributeLabels()['modified_on'] ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if the customer list is not empty, is an array, and contains items
            if (! empty($customerList) && is_array($customerList) && count($customerList)) {
                foreach ($customerList as $customer) {
                    $customerId = $customer['id'];
                    // Get the customer's statistics from the precomputed map, or use an empty array if not available
                    $customerStats = isset($customerStatsMap[$customerId]) ? $customerStatsMap[$customerId] : []; ?>
                    <tr data-key="<?= $customerId ?>" role="row">
                        <td>
                            <?= $customer['customer_name'] ?>
                        </td>
                        <td>
                            <?= $customer['customer_phone'] ?>
                        </td>
                        <td class="text-center">
                            <?= ! empty($customer['total']) ? $customer['total'] : 0 ?>
                        </td>
                        <td class="text-center">
                            <?= ! empty($customer['customer_canceled']) ? $customer['customer_canceled'] : 0 ?>
                        </td>
                        <td class="text-center">
                            <?= ! empty($customer['customer_successfully']) ? $customer['customer_successfully'] : 0 ?>
                        </td>
                        <td>
                            <?= $customer['created_on'] ?>
                        </td>
                        <td>
                            <?= $customer['modified_on'] ?>
                        </td>
                    </tr>
                <?php
                }
            } else { ?>
                <tr>
                    <td colspan="100%"><span class="text-danger">Không có dữ liệu phù hợp...</span></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php $pagination = $dataProvider->getPagination(); ?>
    <div class="d-flex" style="justify-content: space-between;">
        <div>
            <?php
            $startIndex = $pagination->getPage() * $pagination->getPageSize() + 1;
            $endIndex = $startIndex + count($dataProvider->getModels()) - 1;
            $totalCount = $dataProvider->getTotalCount();

            echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries';

            ?>
        </div>
        <?=
        LinkPager::widget([
            'pagination' => $pagination,
            'prevPageLabel' => 'Previous',
            'nextPageLabel' => 'Next',
            'options' => ['class' => 'pagination', 'style' => 'margin:0'],
            'linkOptions' => ['class' => 'page-link'],
            'maxButtonCount' => 5,
        ]);
        ?>
    </div>

</div>
<?php
$script = <<<JS

var topInteval = 10000;

updateCustomer();
setInterval(function(){ 
    updateCustomer();
}, topInteval);

function updateCustomer(){
    $.ajax({
        url: '/api/db-customer',
        success: function(data) {
            $('.customer-month').html(data.customerMonth);
            $('.customer-booked').html(data.customerBooked);
            $('.customer-canceled').html(data.customerCanceled);
            $('.customer-successfully').html(data.customerSuccessfully);
        }
    });
}
JS;
$position = \yii\web\View::POS_READY;
$this->registerJs($script, $position);
?>