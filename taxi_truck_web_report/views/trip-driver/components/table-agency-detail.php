<?php

use app\helpers\MyStringHelper;
use app\models\Agency;
use yii\helpers\Html;

$agency = Agency::findOne($agency_id);
if (isset($tripList) && is_array($tripList) && count($tripList)) {
    foreach ($tripList as $key => $tripItem) {
        $trip_group = $tripItem['tripGroup'];
        $group_zalo = [];
        if ($trip_group !== null) {
            $group_zalo = $trip_group->attributes;
        } ?>
        <tr data-key="<?= $tripItem->id ?>" role="row" class="tr-agency-debt-detail">
            <td class="text-center"><?php echo $key + 1 ?></td>
            <td style="max-width: 300px">
                <div><span class="text-danger">
                        <?= date('d/m/Y H:i', strtotime($tripItem->pickup_time)) ?>
                    </span></div>
                <div>
                    <span class="text-primary">
                        <?= $tripItem->pickup_address ?>
                    </span>
                    <span style="font-size: 15px;">➜</span>
                    <span class="text-danger">
                        <?= $tripItem->destination_address ?>
                    </span>
                </div>
                <div class="text-bold">
                    <span class="text-success">(<?= SCHEDULE_LIST_TRIP[$tripItem->round_trip] ?>)</span>
                    <?= ($tripItem->is_have_bill ? ' - <span class="text-primary">(Hóa đơn)</span>' : '') ?>
                    <?= ($tripItem->is_collect_money ? ' - <span class="text-danger">(Thu tiền)</span>' : ' - <span class="text-danger">(Không thu tiền)</span>') ?>
                </div>
                <div class="js-collected-money-<?= $tripItem->id ?>">
                    <?php
                    if ($tripItem->collected_money > 0 && $tripItem->collected_money_at != null) {
                        echo 'Thu tiền : <span class="text-success">' . date('d/m/Y H:i', strtotime($tripItem->collected_money_at)) . '</span>';
                    } ?>
                </div>
                <?php if (! empty($tripItem->description)) { ?>
                    <div class="text-left">Mô tả: <span>
                            <?= $tripItem->description ?>
                        </span></div>
                <?php } ?>
            </td>
            <td>
                <div>Tên: <span class="text-primary">
                        <?= $tripItem->customer_name ?>
                    </span></div>
                <div>SĐT: <span class="text-primary">
                        <?= $tripItem->customer_phone ?>
                    </span></div>
                <?php if (! empty($tripItem)) { ?>
                    <div>Loại xe: <span class="text-primary">
                            <?= isset(TYPE_OF_CAR_LIST[$tripItem->type_of_car]) ? TYPE_OF_CAR_LIST[$tripItem->type_of_car] : 'Không xác định' ?>
                        </span></div>
                <?php } ?>
                <?php
                $html = '';
                $tripReturn = $tripItem['tripReturn'];
                if (! empty($tripReturn)) {
                    $driverTripReturn = $tripReturn['driver'];
                    $html .= "<div class='text-danger'>Trả lịch (" . ($tripReturn->refund == 0 ? 'Không hoàn tiền' : 'Hoàn tiền') . '): <span>' . $tripReturn->note . '</span> </div>';
                    if (isset($driverTripReturn)) {
                        $html .= '<div>Tài xế: ' . $driverTripReturn->display_name . ' - ' . $driverTripReturn->username . ' </div>';
                    }
                }
                echo $html; ?>
            </td>
            <td>
                <?php
                $bid = $tripItem['bid'];
                $driver = $tripItem['bid']['driver'];
                if (isset($driver)) {
                ?>
                    <div class="text-left">Tên: <span class="text-primary">
                            <?= $driver->display_name ?>
                        </span>
                        <?= ($driver->driver_ban == 1 ? '<span class="text-danger">(Nhiều xe)</span>' : '') ?>
                    </div>
                    <div class="text-left">SĐT: <span class="text-primary">
                            <?= $driver->username ?>
                        </span></div>
                    <?php if ($driver->driver_ban != 1) { ?>
                        <?php
                        $car = $tripItem['bid']['driver']['car'];
                        if (isset($car)) {
                        ?>
                            <div class="text-left">Hãng xe: <span class="text-primary">
                                    <?= $car->type ?>
                                </span></div>
                            <div class="text-left">BKS: <span class="text-primary">
                                    <?= $car->bks . ' ( ' . $car->color . ' ) ' ?>
                                </span></div>
                        <?php
                        }
                    } else { ?>
                        <?php if ($tripItem['driver_sub_phone'] !== '' && $tripItem['driver_sub_phone'] !== null && strlen($tripItem['driver_sub_phone']) > 0) { ?>
                            <hr style="margin: 5px 0">
                            <div class="text-left">Tên lái xe phụ: <span class="text-primary">
                                    <?= $tripItem['driver_sub_name'] ?>
                                </span></div>
                            <div class="text-left">SĐT: <span class="text-primary">
                                    <?= $tripItem['driver_sub_phone'] ?>
                                </span></div>
                            <div class="text-left">BKS: <span class="text-primary">
                                    <?= $tripItem['driver_sub_bks'] ?>
                                </span></div>
                            <div class="text-left">Hãng xe: <span class="text-primary">
                                    <?= $tripItem['driver_sub_type'] ?>
                                </span></div>
                        <?php } else { ?>
                            <div class="text-left text-danger">Cần nhập thông tin tài xế phụ</div>
                        <?php } ?>
                    <?php } ?>
                <?php
                } else { ?>
                    <div class="text-left">Tên: <span class="text-primary">
                            <?= isset($trip_group->driver_name) ? $trip_group->driver_name : '' ?>
                        </span></div>
                    <div class="text-left">SĐT: <span class="text-primary">
                            <?= isset($trip_group->driver_phone) ? $trip_group->driver_phone : '' ?>
                        </span></div>
                <?php } ?>
            </td>
            <td class="text-center text-bold">
                <?= MyStringHelper::convertIntegerToPrice((isset($tripItem->price_customer) ? $tripItem->price_customer : 0)) ?>₫
            </td>
            <td class="text-center text-bold">
                <?= MyStringHelper::convertIntegerToPrice((isset($bid->price) ? $bid->price : 0)) ?>₫
            </td>
            <td class="text-center text-bold text-danger">
                <?php
                $moneyDept = -$tripItem->money_debt_agency;
                if ($tripItem->price_customer - $tripItem->bid->price - $tripItem->money_debt_agency > ($tripItem->price_customer - $tripItem->money_debt_agency) / 100 * $agency->percent && ! empty($agency->percent)) {
                    $moneyDept += ($tripItem->price_customer - $tripItem->money_debt_agency) / 100 * $agency->percent;
                } elseif ($tripItem->price_customer - $tripItem->bid->price - $tripItem->money_debt_agency > $agency->price) {
                    $moneyDept += $agency->price;
                }
                echo MyStringHelper::convertIntegerToPrice($moneyDept) . 'đ<br>';
                if ($moneyDept < 0) {
                    echo "<span class='text-success'>Tổng đài trả</span>";
                } elseif ($moneyDept > 0) {
                    echo "<span class='text-primary'>Đại lý trả</span>";
                } ?>
            </td>
            <td class="text-center">
                <?php
                $html = '';
                if ($tripItem['status'] == STATUS_TRIP_OPEN && $tripItem['sell_start_time'] > gmdate('Y-m-d H:i:s', time() + 7 * 3600)) {
                    $html .= '<div><span class="text-primary">' . STATUS_TRIP[STATUS_TRIP_CREATE] . '</span></div>';
                } elseif ($tripItem['status'] == STATUS_TRIP_EXPIRE) {
                    $html .= '<div><span class="text-danger text-bold">' . STATUS_TRIP[$tripItem['status']] . '</span></div>';
                } else {
                    $html .= '<div><span class="text-primary">' . STATUS_TRIP[$tripItem['status']] . '</span></div>';
                }
                $html .= '<div><span class="text-success">' . ($tripItem['is_called_for_cus'] == 1 && ($tripItem['status'] == 'DONE' || $tripItem['status'] == 'COMPLETE') ? 'Đã liên hệ với khách hàng' : '') . '</span></div>';
                echo $html; ?>
            </td>
            <td class="text-center">
                <div>
                    <span class='text-primary'>
                        <?= isset(SOURCE_TRIP_TYPE_LIST[$tripItem['source_trip']]) ? SOURCE_TRIP_TYPE_LIST[$tripItem['source_trip']] : '' ?>
                    </span>
                    <?php
                    if ($tripItem['source_trip'] == SOURCE_TRIP_TYPE_AGENCY) {
                        echo "<span class='text-primary'>: " . (isset($tripItem->agency->name) ? $tripItem->agency->name : '') . ' </span>';
                    } ?>
                    <?php
                    if (isset($trip_group)) {
                        $group_zalo = $trip_group['groupZalo'];
                        $group_zalo_seller = $trip_group['groupZaloSeller'];

                        if (isset($group_zalo->name) && $group_zalo->name != '') {
                    ?>
                            <div>
                                Nhóm bán:
                                <span class="text-primary"><?= $group_zalo->name ?></span>
                            </div>

                            <?php
                            if ($trip_group->type == 1 || $trip_group->type == 2) :
                            ?>
                                <div class="">
                                    Thu:
                                    <span class="text-primary"><?= MyStringHelper::convertIntegerToPrice($trip_group->price) ?>₫</span>
                                </div>
                            <?php
                            endif;
                        }

                        if (isset($group_zalo_seller->name) && $group_zalo_seller->name != '') :
                            ?>
                            <div>
                                Người bán:
                                <span class="text-primary"><?= $group_zalo_seller->name ?></span>
                            </div>
                    <?php
                        endif;
                    } ?>
                </div>
            </td>
            <td>
                <?php echo Html::a('<span class="fa fa-fas fa-money" aria-hidden="true"></span>', [''], [
                    'title' => 'Thanh toán',
                    'class' => 'btn-success btn mb2 btn-accept-debt',
                    'data-id' => $tripItem['id'],
                    'data-agency-id' => $agency->id,
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
