<?php

use app\helpers\MyStringHelper;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Customers';
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
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="small-box bg-blue">
                    <div class="inner">
                        <h3 class="customer-month fsc-sm-24">0</h3>
                        <p>Tổng số chuyến khách hàng đã tạo.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 class="customer-booked fsc-sm-24">0</h3>
                        <p>Tổng số chuyến khách hàng đi thành công.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 class="customer-canceled fsc-sm-24">0</h3>
                        <p>Tổng số chuyến khách hàng đã hủy.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 class="customer-successfully fsc-sm-24">0đ</h3>
                        <p>Tổng số tiền khách hàng đã thanh toán.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $pagination = $dataProvider->getPagination(); ?>
    <div class="d-flex" style="justify-content: space-between; align-items:center;margin-bottom: 20px">
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
    <table id="datatables_w0" class="table table-striped table-bordered" width="100%" cellspacing="0" style="background: #fff;">
        <thead>
            <tr>
                <th>
                    Thông tin khách hàng
                </th>
                <th class="text-center">
                    Ngày sinh
                </th>
                <th class="text-center">
                    Giới tính
                </th>
                <th class="text-center">
                    Hạng
                </th>
                <th class="text-center">
                    Tổng số chuyến
                </th>
                <th class="text-center">
                    Tổng thành công
                </th>
                <th class="text-center">
                    Tổng hủy
                </th>
                <th class="text-center">
                    Tổng tiền
                </th>
                <th class="text-center">
                    Chuyến đi gần nhất
                </th>
                <th class="text-center">
                    Thao tác
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
                    $customerStats = $customerStatsMap[$customerId] ?? []; ?>
                    <tr data-key="<?= $customerId ?>" role="row">
                        <td>
                            <div class="customer-info">
                                <?= (! empty($customer['display_name']) ? 'Họ tên: <span class="text-danger">' . $customer['display_name'] . '</span>' : '') ?>
                            </div>
                            <div class="customer-info">
                                <?= (! empty($customer['phone']) ? 'SĐT: <span class="text-danger">' . $customer['phone'] . '</span>' : '') ?>
                            </div>
                            <div class="customer-info">
                                <?= (! empty($customer['address']) ? 'Địa chỉ: <span class="text-danger">' . $customer['address'] . '</span>' : '') ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <?= (! empty($customer['birthday']) ? date('d/m/Y', strtotime($customer['birthday'])) : '-') ?>
                        </td>
                        <td class="text-center">
                            <?= (! empty($customer['gender']) ? GENDER[$customer['gender']] : '-') ?>
                        </td>
                        <td class="text-center">
                            <?= isset($customer['customer_rank']) ? $customer['customer_rank'] : '-' ?>
                        </td>
                        <td class="text-center">
                            <?= MyStringHelper::convertIntegerToPrice($customer['total_trip'] ?? 0) ?>
                        </td>
                        <td class="text-center">
                            <?= MyStringHelper::convertIntegerToPrice($customer['total_complete'] ?? 0) ?>
                        </td>
                        <td class="text-center">
                            <?= MyStringHelper::convertIntegerToPrice($customer['total_cancel'] ?? 0) ?>
                        </td>
                        <td class="text-center">
                            <?= MyStringHelper::convertIntegerToPrice($customer['total_paid'] ?? 0) ?>đ
                        </td>
                        <td class="text-center">
                            <?= (! empty($customer['lastest_trip']) ? date('d/m/Y H:i', strtotime($customer['lastest_trip'])) : '-') ?>
                        </td>
                        <td class="text-center">
                            <?= Html::a('<span class="fa fa-eye" aria-hidden="true"></span> ', ['customer/view', 'id' => $customer['id']], [
                                'title' => 'Xem thông tin chuyến xe',
                                'class' => 'btn-info btn mb2',
                            ]) ?>
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
    <div class="d-flex" style="justify-content: space-between; align-items:center;">
        <div>
            <?php echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries'; ?>
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
        url: '/customer/db-customer',
        success: function(data) {
            $('.customer-month').html(data.total);
            $('.customer-booked').html(data.complete);
            $('.customer-canceled').html(data.cancel);
            $('.customer-successfully').html(data.paid);
        }
    });
}
JS;
$position = \yii\web\View::POS_READY;
$this->registerJs($script, $position);
?>
