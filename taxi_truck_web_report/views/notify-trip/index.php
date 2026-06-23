<?php

use app\helpers\MyStringHelper;
use yii\web\YiiAsset;

$this->title = 'Danh sách chuyến VIP và Gold';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('/js/pages/notify-trip.js', ['depends' => [YiiAsset::class]]);

?>

<script>
    var typeOfCarList = JSON.parse('<?php echo json_encode(TYPE_OF_CAR_LIST) ?>');
    var scheduleList = JSON.parse('<?php echo json_encode(SCHEDULE_LIST_TRIP) ?>');
    var minuteVip = <?php echo $systemVip ?>;
    var minuteGold = <?php echo $systemGold ?>;
</script>

<div class="request-call-back-warp">
    <div class="row">
        <div class="col-lg-6 col-12 hidden">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Danh sách chuyến đã mở bán cho GOLD</h3>
                </div>
                <div class="box-body">
                    <div class="table-view-list mt-10">
                        <table class="table table-striped table-bordered table-trip-gold" style="background-color: #fff">
                            <thead>
                                <tr>
                                    <th class="phone">Thông tin chuyến xe</th>
                                    <th class="status">Giá thu khách</th>
                                    <th class="type-reject">Giá bid</th>
                                    <th class="note">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($tripsGold) && is_array($tripsGold) && count($tripsGold)) {
                                    foreach ($tripsGold as $tripGold) :
                                ?>
                                        <tr data-id="<?= $tripGold['id'] ?>"  data-type="GOLD">
                                            <td class="copy-info-trip">
<div>Thành viên GOLD đã được quyền mua trước chuyến này</div>
<div>Giờ đi: <?= date('d/m/Y H:i', strtotime($tripGold['pickup_time'])) ?></div>
<div>Lịch trình: <?= $tripGold['pickup_address'] ?> ➜ <?= $tripGold['destination_address'] ?></div>
<div>Loại lịch: (<?= SCHEDULE_LIST_TRIP[$tripGold['round_trip']] ?>)<?= ($tripGold['is_have_bill'] ? ' - (Hóa đơn)' : '') ?><?= ($tripGold['is_collect_money'] ? ' - (Thu tiền)' : ' - (Không thu tiền)') ?></div>
<?php if (! empty($tripGold['description'])) { ?>
<div>Mô tả: <?= $tripGold['description'] ?></div>
<?php } ?>
<div>Loại xe: <?= isset(TYPE_OF_CAR_LIST[$tripGold['type_of_car']]) ? TYPE_OF_CAR_LIST[$tripGold['type_of_car']] : 'Không xác định' ?></div>
<div>Giờ mở bán: <?= date('d/m/Y H:i', strtotime($tripGold['new_time'])) ?></div>
                                            </td>
                                            <td class="text-center text-bold">
                                                <?= MyStringHelper::convertIntegerToPrice((isset($tripGold['price_customer']) ? $tripGold['price_customer'] : 0)) ?>₫
                                            </td>
                                            <td class="text-center text-bold">
                                                <?= MyStringHelper::convertIntegerToPrice((isset($tripGold['price_bid']) ? $tripGold['price_bid'] : 0)) ?>₫
                                            </td>
                                            <td>
                                                <button class="copy-button btn btn-primary" ><i class="fa fa-copy"></i></button>
                                                <button class="accept-button btn btn-success" ><i class="fa fa-check"></i></button>
                                            </td>
                                        </tr>
                                <?php
                                    endforeach;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Danh sách chuyến chuẩn bị mở bán</h3>
                </div>
                <div class="box-body">
                    <div class="table-view-list mt-10">
                        <table class="table table-striped table-bordered table-trip-vip" style="background-color: #fff">
                            <thead>
                                <tr>
                                    <th class="phone">Thông tin chuyến xe</th>
                                    <th class="status">Giá thu khách</th>
                                    <th class="type-reject">Giá bid</th>
                                    <th class="note">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($tripsVip) && is_array($tripsVip) && count($tripsVip)) {
                                    foreach ($tripsVip as $tripVip) :
                                ?>
                                        <tr data-id="<?= $tripVip['id'] ?>" data-type="VIP">
                                            <td class="copy-info-trip">
<div>Còn 15 phút nữa lịch này sẽ được mở bán trên app Hùng Dương mời anh em vào app để mua chuyến.</div>
<div>Giờ đi: <?= date('d/m/Y H:i', strtotime($tripVip['pickup_time'])) ?></div>
<div>Lịch trình: <?= $tripVip['pickup_address'] ?> ➜ <?= $tripVip['destination_address'] ?></div>
<div>Loại lịch: (<?= SCHEDULE_LIST_TRIP[$tripVip['round_trip']] ?>)<?= ($tripVip['is_have_bill'] ? ' - (Hóa đơn)' : '') ?><?= ($tripVip['is_collect_money'] ? ' - (Thu tiền)' : ' - (Không thu tiền)') ?></div>
<?php if (! empty($tripVip['description'])) { ?>
<div>Mô tả: <?= $tripVip['description'] ?></div>
<?php } ?>
<div>Loại xe: <?= isset(TYPE_OF_CAR_LIST[$tripVip['type_of_car']]) ? TYPE_OF_CAR_LIST[$tripVip['type_of_car']] : 'Không xác định' ?></div>
<div>Giờ mở bán: <?= date('d/m/Y H:i', strtotime($tripVip['new_time'])) ?></div>
                                            </td>
                                            <td class="text-center text-bold">
                                                <?= MyStringHelper::convertIntegerToPrice((isset($tripVip['price_customer']) ? $tripVip['price_customer'] : 0)) ?>₫
                                            </td>
                                            <td class="text-center text-bold">
                                                <?= MyStringHelper::convertIntegerToPrice((isset($tripVip['price_bid']) ? $tripVip['price_bid'] : 0)) ?>₫
                                            </td>
                                            <td>
                                                <button class="copy-button btn btn-primary" ><i class="fa fa-copy"></i></button>
                                                <button class="accept-button btn btn-success" ><i class="fa fa-check"></i></button>
                                            </td>
                                        </tr>
                                <?php
                                    endforeach;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
