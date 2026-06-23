<?php

use app\helpers\MyStringHelper;
use yii\helpers\Html;

?>
<table class="table table-striped table-bordered <?= $class ?>">
    <thead>
        <tr>
            <th style="width: 23%">Chuyến xe</th>
            <th style="width: 23%">Khách hàng</th>
            <th style="width: 23%">Lái xe</th>
            <th class="text-center">Nguồn</th>
            <th style="width: 8%"></th>
        </tr>
    <tbody>
        <?php
        foreach ($tripList as $tripItem) :
            $trip_group = $tripItem['tripGroup'];
            $group_zalo = [];
            if ($trip_group !== null) {
                $group_zalo = $trip_group->attributes;
            }
        ?>
            <tr data-key="<?= $tripItem->id ?>">
                <td>
                    <div>
                        <span class="text-danger"><?= date('d/m/Y H:i', strtotime($tripItem->pickup_time)) ?></span>
                    </div>

                    <div>
                        <span class="text-primary"><?= $tripItem->pickup_address ?></span>
                        <span style="font-size: 15px;">➜</span>
                        <span class="text-danger"><?= $tripItem->destination_address ?></span>
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
                        }
                        ?>
                    </div>

                    <?php if (! empty($tripItem->description)) { ?>
                        <div class="text-left">Mô tả: <span>
                                <?= $tripItem->description ?>
                            </span></div>
                    <?php } ?>
                </td>

                <td>
                    <div>
                        Tên: <span class="text-primary"><?= $tripItem->customer_name ?></span>
                    </div>

                    <div>
                        SĐT: <span class="text-primary"><?= $tripItem->customer_phone ?></span>
                    </div>
                    <?php if (! empty($tripItem)) { ?>
                        <div>
                            Loại xe: <span
                                class="text-primary"><?= isset(TYPE_OF_CAR_LIST[$tripItem->type_of_car]) ? TYPE_OF_CAR_LIST[$tripItem->type_of_car] : 'Không xác định' ?></span>
                        </div>
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
                    echo $html;
                    ?>
                </td>

                <td>
                    <?php
                    $bid = $tripItem['bid'];
                    $driver = $tripItem['bid']['driver'];
                    if (isset($driver)) {
                    ?>
                        <div class="text-left">
                            Tên: <span class="text-primary"><?= $driver->display_name ?></span>
                            <?= ($driver->driver_ban == 1 ? '<span class="text-danger">(Nhiều xe)</span>' : '') ?>
                        </div>

                        <div class="text-left">
                            SĐT: <span class="text-primary"><?= $driver->username ?></span>
                        </div>

                        <?php if ($driver->driver_ban != 1) { ?>
                            <?php
                            $car = $tripItem['bid']['driver']['car'];
                            if (isset($car)) {
                            ?>
                                <div class="text-left">
                                    Hãng xe: <span class="text-primary"><?= $car->type ?></span>
                                </div>

                                <div class="text-left">
                                    BKS: <span class="text-primary"><?= $car->bks ?></span>
                                    <?= ! empty($car->color) ? ' ( ' . $car->color . ' )' : '' ?>
                                </div>
                            <?php
                            }
                        } else { ?>
                            <?php if ($tripItem['driver_sub_phone'] !== '' && $tripItem['driver_sub_phone'] !== null && strlen($tripItem['driver_sub_phone']) > 0) { ?>
                                <hr style="margin: 5px 0">
                                <div class="text-left">
                                    Tên lái xe phụ: <span class="text-primary"><?= $tripItem['driver_sub_name'] ?></span>
                                </div>

                                <div class="text-left">
                                    SĐT: <span class="text-primary"><?= $tripItem['driver_sub_phone'] ?></span>
                                </div>

                                <div class="text-left">
                                    BKS: <span class="text-primary"><?= $tripItem['driver_sub_bks'] ?></span>
                                </div>

                                <div class="text-left">
                                    Hãng xe: <span class="text-primary"><?= $tripItem['driver_sub_type'] ?></span>
                                </div>
                            <?php } else { ?>
                                <div class="text-left text-danger">
                                    Cần nhập thông tin tài xế phụ
                                </div>
                            <?php } ?>
                        <?php } ?>
                    <?php
                    } else { ?>
                        <div class="text-left">
                            Tên: <span class="text-primary"><?= $trip_group->driver_name ?></span>
                        </div>

                        <div class="text-left">
                            SĐT: <span class="text-primary"><?= $trip_group->driver_phone ?></span>
                        </div>
                    <?php } ?>
                </td>

                <td class="text-center">
                    <div>
                        <span
                            class='text-primary'><?= isset(SOURCE_TRIP_TYPE_LIST[$tripItem['source_trip']]) ? SOURCE_TRIP_TYPE_LIST[$tripItem['source_trip']] : '' ?></span>
                        <?php
                        if ($tripItem['source_trip'] == SOURCE_TRIP_TYPE_AGENCY) {
                            echo "<span class='text-primary'>: " . (isset($tripItem->agency->name) ? $tripItem->agency->name : '') . ' </span>';
                        }
                        ?>
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
                                        <span
                                            class="text-primary"><?= MyStringHelper::convertIntegerToPrice($trip_group->price) ?>₫</span>
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
                        }
                        ?>
                    </div>
                </td>

                <td>
                    <?php
                    $commonButtons = [
                        'collection' => Html::button(
                            '<span>Xác nhận</span>',
                            [
                                'title' => 'Xác nhận',
                                'class' => 'btn-collection-call-driver btn-primary btn',
                                'data-id' => $tripItem->id,
                            ]
                        ),
                    ];
                    $template = $commonButtons['collection'];
                    ?>
                    <?= $template ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
