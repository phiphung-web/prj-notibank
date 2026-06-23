<?php

use kartik\daterange\DateRangePicker;

app\assets\DashboardAsset::register($this);
$this->title = 'DashBoard';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('/datetimepicker/jquery.datetimepicker.full.min.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerCssFile('/datetimepicker/jquery.datetimepicker.css');
$this->registerJsFile('/js/pages/dashboard.js', ['depends' => [\yii\web\YiiAsset::class]]);
?>
<style>
    .fs-14 {
        font-size: 14px;
    }
    .w-50 {
        width: 50%;
    }
</style>
<!-- Small boxes (Stat box) -->
<?php
/*
<div class="row">
    <div class="col-lg-6 col-xs-12">
        <div class="small-box bg-blue">
            <div class="inner">
                <div class="d-flex justify-content-center align-items-center header-day-statistic">
                    <h3 class="fsc-sm-24" style="margin: 0 10px 0 0;">Báo cáo thống kê</h3>
                    <div class="drp-container bg-gray" style="margin: 8px 0">
                        <?= DateRangePicker::widget([
                            'name' => 'date_range_2',
                            'presetDropdown' => true,
                            'convertFormat' => true,
                            'includeMonthsFilter' => true,
                            'pluginOptions' => [
                                'locale' => [
                                    'format' => 'Y-m-d',
                                    'separator' => ' to ',
                                ],
                            ],
                            'options' => [
                                'placeholder' => 'Select range...',
                                'name' => 'time',
                                'class' => 'date-picker-dashboard',
                            ]
                        ]);
                        ?>
                    </div>
                </div>
                <table class="table table-bordered mt-10 mb-10 table-day-statistic">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-center">Khách hàng</th>
                            <th class="text-center">Lái xe</th>
                            <th class="text-center">Lợi nhuận</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tổng doanh thu</td>
                            <td class="text-center total_customer_revenue">0</td>
                            <td class="text-center total_driver_revenue">0</td>
                            <td class="text-center total_profit">0</td>
                        </tr>
                        <tr>
                            <td>Đã thu</td>
                            <td class="text-center customers_collected">0</td>
                            <td class="text-center drivers_collected">0</td>
                            <td class="text-center profits_collected">0</td>
                        </tr>
                        <tr>
                            <td>Nợ</td>
                            <td class="text-center customers_debt">0</td>
                            <td class="text-center drivers_debt">0</td>
                            <td class="text-center profits_debt">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xs-12">
        <div class="small-box bg-red">
            <div class="inner">
                <h3 class="text-center fsc-sm-24">Báo cáo thống kê (Toàn bộ)</h3>
                <table class="table table-bordered mt-10 mb-10">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-center">Khách hàng</th>
                            <th class="text-center">Lái xe</th>
                            <th class="text-center">Lợi nhuận</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tổng doanh thu</td>
                            <td class="text-center total_customer_revenue_all">0</td>
                            <td class="text-center total_driver_revenue_all">0</td>
                            <td class="text-center total_profit_all">0</td>
                        </tr>
                        <tr>
                            <td>Đã thu</td>
                            <td class="text-center customers_collected_all">0</td>
                            <td class="text-center drivers_collected_all">0</td>
                            <td class="text-center profits_collected_all">0</td>
                        </tr>
                        <tr>
                            <td>Nợ</td>
                            <td class="text-center customers_debt_all">0</td>
                            <td class="text-center drivers_debt_all">0</td>
                            <td class="text-center profits_debt_all">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
*/
?>
<div class="row">
    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3 class="top-driver">0</h3>
                <p>Tài xế</p>
            </div>
            <a href="/driver" class="small-box-footer">Chi tiết <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="small-box bg-green">
            <div class="inner">
                <h3 class="top-money">0</h3>
                <p>Số tiền dư</p>
            </div>
            <a href="#" class="small-box-footer">Chi tiết <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3 class="top-trip">0</h3>
                <p>Số lịch đã chốt</p>
            </div>
            <a href="#" class="small-box-footer">Chi tiết <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="small-box bg-red">
            <div class="inner">
                <h3 class="top-revenue">0</h3>
                <p>Doanh thu tháng</p>
            </div>
            <a href="#" class="small-box-footer">Chi tiết <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 col-xs-12">
        <div class="small-box bg-blue">
            <div class="inner">
                <div class="d-flex justify-content-center align-items-center header-day-statistic">
                    <h3 class="fsc-sm-24 bold" style="margin: 0 10px 0 0;">Báo cáo thống kê lịch xe</h3>
                    <div class="drp-container bg-gray" style="margin: 8px 0">
                      <?= DateRangePicker::widget([
                        'name' => 'date_range_2',
                        'presetDropdown' => true,
                        'convertFormat' => true,
                        'includeMonthsFilter' => true,
                        'pluginOptions' => [
                          'locale' => [
                            'format' => 'Y-m-d',
                            'separator' => ' to ',
                          ],
                        ],
                        'options' => [
                          'placeholder' => 'Select range...',
                          'name' => 'time',
                          'class' => 'date-picker-trip',
                        ],
                      ]);
                      ?>
                    </div>
                </div>
                <hr>
                <div class="d-flex">
                    <div class="w-50">
                        <div class="d-flex align-items-center">
                            <div class="count-trip fs-14" style="width: auto">Tổng số đơn: 0</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="count-trip-success fs-14">Số đơn thành công: 0</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="count-trip-fail fs-14">Số đơn thất bại: 0</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="source-mail fs-14">Nguồn mail: 0</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="source-call fs-14">Nguồn gọi điện: 0</div>
                        </div>
                    </div>
                    <div class="w-50">
                        <div class="d-flex align-items-center">
                            <div class="source-zalo fs-14">Nguồn zalo: 0</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="source-fb fs-14">Nguồn facebook: 0</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="source-agency fs-14">Nguồn đại lý: 0</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="source-customer-rollback fs-14">Nguồn khách quay đầu: 0</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="source-customer-rollback-service fs-14">Nguồn khách quay đầu cskh: 0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$script = <<< JS

var topInteval = 10000;

updateTop();
setInterval(function(){ 
    updateTop();
}, topInteval);


function updateTop(){
    $.ajax({
         url: '/dash-board/db-statistic',
         success: function(data) {
            $('h3.top-driver').html(data.numDriver);
            $('h3.top-money').html(data.totalMoney);
            $('h3.top-trip').html(data.totalTrip);
            $('h3.top-revenue').html(data.revenue);
         }
    });
}

$(document).on('change', '.date-picker-dashboard', function() {
    let html_loading = `<div class="loading-area">
                            <div class="loader">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </div>`;
    $('.table-day-statistic tbody').html(html_loading);
    $.ajax({
        type: "POST",
        url: "/dash-board/get-statistic-day",
        data: { date: $(this).val() },
        success: function(response) {
            let json = JSON.parse(response);
            let html = "";
            html += '<tr>';
            html += '<td>Tổng doanh thu</td>';
            html += '<td class="text-center">' + numberWithCommas(json.total_customer_revenue) + '</td>';
            html += '<td class="text-center">' + numberWithCommas(json.total_driver_revenue) + '</td>';
            html += '<td class="text-center">' + numberWithCommas(json.total_profit) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Đã thu</td>';
            html += '<td class="text-center">' + numberWithCommas(json.customers_collected) + '</td>';
            html += '<td class="text-center">' + numberWithCommas(json.drivers_collected) + '</td>';
            html += '<td class="text-center">' + numberWithCommas(json.profits_collected) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Nợ</td>';
            html += '<td class="text-center">' + numberWithCommas(json.customers_debt) + '</td>';
            html += '<td class="text-center">' + numberWithCommas(json.drivers_debt) + '</td>';
            html += '<td class="text-center">' + numberWithCommas(json.profits_debt) + '</td>';
            html += '</tr>';
            $('.table-day-statistic tbody').html(html);
        }
    });
});

$(document).on('change', '.date-picker-trip', function() {
    $.ajax({
        type: "POST",
        url: "/dash-board/get-total-trip",
        data: { date: $(this).val() },
        success: function(response) {
            let json = JSON.parse(response);
            $('.count-trip').html('Tổng số đơn: ' + numberWithCommas(json.tong));
            $('.count-trip-success').html('Số đơn thành công: ' + numberWithCommas(json.thanh_cong));
            $('.count-trip-fail').html('Số đơn thất bại: ' + numberWithCommas(json.that_bai));
            $('.source-mail').html('Nguồn mail: ' + numberWithCommas(json.nguon_mail));
            $('.source-call').html('Nguồn gọi điện: ' + numberWithCommas(json.nguon_call));
            $('.source-zalo').html('Nguồn zalo: ' + numberWithCommas(json.nguon_zalo));
            $('.source-fb').html('Nguồn facebook: ' + numberWithCommas(json.nguon_fb));
            $('.source-agency').html('Nguồn đại lý: ' + numberWithCommas(json.nguon_dai_ly));
            $('.source-customer-rollback').html('Nguồn khách quay đầu: ' + numberWithCommas(json.nguon_khach_quay_dau));
            $('.source-customer-rollback-service').html('Nguồn khách quay đầu cskh: ' + numberWithCommas(json.nguon_khach_quay_dau_cskh));
        }
    });
});

function numberWithCommas(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
 
JS;
$position = \yii\web\View::POS_READY;
$this->registerJs($script, $position);
?>