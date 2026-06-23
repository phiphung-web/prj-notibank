<?php

use app\helpers\MyStringHelper;
use app\models\Driver;
use app\services\TripAutoService;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$tripAutoService = new TripAutoService();

$queryTotal = clone $dataProvider->query;
$queryTotal->select([
    'price_customer' => 'trip.price_customer',
    'price_bid' => 'trip.price_bid'
]);

$subQuery = (new \yii\db\Query())->from(['sub' => $queryTotal]);
$totals = $subQuery->select([
    'total_customer' => 'SUM(price_customer)',
    'total_bid' => 'SUM(price_bid)'
])->one();

$totalCustomer = $totals['total_customer'] ?? 0;
$totalBid = $totals['total_bid'] ?? 0;
$totalProfit = $totalCustomer - $totalBid;
?>
<div class="d-flex" style="justify-content: space-between;margin-bottom: 10px;">
    <div>
        <?php
        $startIndex = $dataProvider->getPagination()->getPage() * $dataProvider->getPagination()->getPageSize() + 1;
        $endIndex = $startIndex + count($dataProvider->getModels()) - 1;
        $totalCount = $dataProvider->getTotalCount();
        echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries';
        ?>
    </div>
    <?=
    LinkPager::widget([
        'pagination' => $dataProvider->getPagination(),
        'prevPageLabel' => 'Previous',
        'nextPageLabel' => 'Next',
        'options' => ['class' => 'pagination', 'style' => 'margin:0'],
        'linkOptions' => [
            'class' => 'page-link trip-pagination-item',
            'data-page' => function ($page, $label, $disabled, $active) {
                $options = [];
                $options['tag'] = 'a';
                $options['data-page'] = $page;  // Thêm thuộc tính data-page

                return $options;
            },
        ],
        'maxButtonCount' => 5,
    ]);
    ?>
</div>
<table id="datatables_w0" class="table table-striped table-bordered table-trip" width="100%" cellspacing="0"
    style="background: #fff;">
    <thead>
        <tr>
            <th style="width: 350px; min-width: 160px;">Chuyến xe</th>
            <th style="min-width: 160px">Khách hàng</th>
            <th class="text-center" style="min-width: 100px">Thu khách</th>
            <?php
            if ($searchModel->status == STATUS_TRIP_DONE || $searchModel->status == STATUS_TRIP_COMPLETE || $searchModel->status == STATUS_TRIP_ALL) {
                echo '<th>Lái xe</th>';
            }
            ?>
            <th class="text-center" style="min-width: 100px">Giá bid</th>
            <?php
            if ($searchModel->status == STATUS_TRIP_DONE || $searchModel->status == STATUS_TRIP_COMPLETE) {
                echo '<th class="text-center" style="min-width: 100px">Lái xe nhận</th>';
            }
            ?>
            <th class="text-center" style="min-width: 100px">Trạng thái</th>
            <th class="text-center" style="min-width: 100px">Nguồn</th>
            <th class="text-center" style="min-width: 100px">Ẩn/Hiện</th>
            <th class="text-center" style="min-width: 100px">Người tạo</th>
            <?php
            if ($searchModel->status == STATUS_TRIP_CANCEL) {
                echo '<th class="text-center" style="min-width: 100px">Lý do hủy</th>';
            }
            ?>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($tripList) && is_array($tripList) && count($tripList)) {
            foreach ($tripList as $tripItem) {
                $trip_group = $tripItem['tripGroup'];
                $group_zalo = [];
                if ($trip_group !== null) {
                    $group_zalo = $trip_group->attributes;
                }
        ?>
                <tr data-key="<?= $tripItem->id ?>" role="row">
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
                            <span class="text-success">(
                                <?= SCHEDULE_LIST_TRIP[$tripItem->round_trip] ?>)
                            </span>
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
                        <?php
                        if (!empty($tripItem->service)) {
                            $serviceIds = is_array($tripItem->service)
                                ? $tripItem->service
                                : (is_string($tripItem->service) && str_starts_with(trim($tripItem->service), '[')
                                    ? json_decode($tripItem->service, true)
                                    : explode(',', $tripItem->service));

                            if (is_array($serviceIds)) {
                                $serviceNames = array_map(fn($id) => SERVICE_LIST[(int) $id] ?? null, $serviceIds);
                                $serviceNames = array_filter($serviceNames);
                                if (!empty($serviceNames)) {
                                    echo '<div class="text-left">Dịch vụ: <span class="text-primary">' . implode(', ', $serviceNames) . '</span></div>';
                                }
                            }
                        }
                        ?>
                        <?php if (!empty($tripItem->description)) { ?>
                            <div class="text-left" style="white-space: normal ;word-break: break-word; text-overflow: ellipsis;">Mô
                                tả: <span>
                                    <?= $tripItem->description ?>
                                </span></div>
                        <?php } ?>

                        <?php if (!empty($tripItem)) { ?>
                            <div>
                                Loại xe:
                                <span class="text-primary">
                                    <?= isset(TYPE_OF_CAR_LIST[$tripItem->type_of_car]) ? TYPE_OF_CAR_LIST[$tripItem->type_of_car] : 'Không xác định' ?>
                                </span>
                            </div>
                        <?php } ?>
                        <hr style="margin: 5px 0;">
                        <?php if (!empty($tripItem->note_private)) { ?>
                            <div class="text-left">Ghi chú chuyến (nội bộ): <span>
                                    <?= $tripItem->note_private ?>
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
                        <?php if (!empty($tripItem->customer_property) && isset(CUSTOMER_PROPERTY_LIST[$tripItem->customer_property])) { ?>
                            <div>Thuộc tính khách: <span class="text-primary">
                                    <?= CUSTOMER_PROPERTY_LIST[$tripItem->customer_property] ?>
                                </span></div>
                        <?php } ?>

                        <?php
                        $html = '';
                        $tripReturn = $tripItem['tripReturn'];
                        if (!empty($tripReturn)) {
                            $driverTripReturn = $tripReturn['driver'];
                            $html .= "<div class='text-danger'>Trả lịch (" . ($tripReturn->refund == 0 ? 'Không hoàn tiền' : 'Hoàn tiền') . '): <span>' . $tripReturn->note . '</span> </div>';
                            if (isset($driverTripReturn)) {
                                $html .= '<div>Tài xế: ' . $driverTripReturn->display_name . ' - ' . $driverTripReturn->username . ' </div>';
                            }
                        }
                        echo $html;
                        ?>
                    </td>
                    <td class="text-center text-bold">
                        <?= MyStringHelper::convertIntegerToPrice((isset($tripItem->price_customer) ? $tripItem->price_customer : 0)) ?>₫
                    </td>
                    <?php
                    if ($searchModel->status == STATUS_TRIP_DONE || $searchModel->status == STATUS_TRIP_COMPLETE || $searchModel->status == STATUS_TRIP_ALL) {
                        $bid = $tripItem['bid'];
                    ?>
                        <td>
                            <?php
                            if (isset($tripItem['bid']['driver'])) {
                                $driver = $tripItem['bid']['driver'];
                            ?>
                                <div class="text-left">Tên: <span class="text-primary">
                                        <?= $tripItem['bid']['driver']->display_name ?>
                                    </span>
                                    <?= ($driver->driver_ban == 1 ? '<span class="text-danger">(Nhiều xe)</span>' : '') ?>
                                </div>
                                <div class="text-left">SĐT: <span class="text-primary">
                                        <?= $driver->username ?> <span
                                            class="text-danger">(<?= isset($driver->driver_rank) && !empty($driver->driver_rank) ? $driver->driver_rank : 'NORMAL' ?>)</span>
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
                                } else {
                                    ?>
                                    <?php
                                    if (isset($tripItem['bid']['driver_sub_id']) && $tripItem['bid']['driver_sub_id'] != '') {
                                        $driverSub = Driver::find()
                                            ->where(['driver.id' => $tripItem['bid']['driver_sub_id']])
                                            ->joinWith(['car'])
                                            ->asArray()
                                            ->one();
                                        echo $this->render('common/data_driver_sub', [
                                            'display_name' => (isset($driverSub['display_name']) ? $driverSub['display_name'] : ''),
                                            'username' => (isset($driverSub['username']) ? $driverSub['username'] : ''),
                                            'bks' => (isset($driverSub['bks']) ? $driverSub['bks'] : (isset($driverSub['car']['bks']) ? $driverSub['car']['bks'] : '')),
                                            'type' => (isset($driverSub['type']) ? $driverSub['type'] : (isset($driverSub['car']['type']) ? $driverSub['car']['type'] : '')),
                                        ]);
                                    } elseif ($tripItem['driver_sub_phone'] !== '' && $tripItem['driver_sub_phone'] !== null && strlen($tripItem['driver_sub_phone']) > 0) {
                                        echo $this->render('common/data_driver_sub', [
                                            'display_name' => $tripItem['driver_sub_name'],
                                            'username' => $tripItem['driver_sub_phone'],
                                            'bks' => $tripItem['driver_sub_bks'],
                                            'type' => $tripItem['driver_sub_type'],
                                        ]);
                                    } else {
                                    ?>
                                        <div class="text-left text-danger">Cần nhập thông tin tài xế phụ</div>
                                    <?php } ?>
                                <?php } ?>
                            <?php
                            } else {
                            ?>
                                <div class="text-left">Tên: <span class="text-primary">
                                        <?= isset($trip_group->driver_name) ? $trip_group->driver_name : '' ?>
                                    </span></div>
                                <div class="text-left">SĐT: <span class="text-primary">
                                        <?= isset($trip_group->driver_phone) ? $trip_group->driver_phone : '' ?>
                                    </span></div>
                                <div class="text-left">Loại xe: <span class="text-primary">
                                        <?= isset($trip_group->vehicle_type) ? $trip_group->vehicle_type : '' ?>
                                    </span></div>
                                <div class="text-left">BKS: <span class="text-primary">
                                        <?= isset($trip_group->license_plates) ? $trip_group->license_plates : '' ?>
                                    </span></div>
                            <?php } ?>
                        </td>
                    <?php
                    }
                    ?>
                    <td class="text-center text-bold">
                        <?= MyStringHelper::convertIntegerToPrice((isset($tripItem->price_bid) ? $tripItem->price_bid : 0)) ?>₫
                        <div class="text-danger">
                            <?= $tripItem->is_auto_price ? 'Giá tự tăng: ' . MyStringHelper::convertIntegerToPrice($tripAutoService->autoIncreasePriceBid($tripItem)) . 'đ' : '' ?>
                        </div>
                        <div class="text-primary"><?= $tripItem->is_auto_price ? '(Tự động tăng)' : '(Không tự động tăng)' ?>
                        </div>
                        <?php
                        if ($tripItem->money_debt_agency > 0) {
                            echo '<div>Thu hộ: <span class="text-danger">' . MyStringHelper::convertIntegerToPrice($tripItem->money_debt_agency) . 'đ</span></div>';
                        }
                        if ($tripItem->money_customer_deposit > 0) {
                            echo '<div>Khách cọc: <span class="text-danger">' . MyStringHelper::convertIntegerToPrice($tripItem->money_customer_deposit) . 'đ</span></div>';
                        }
                        ?>
                    </td>
                    <?php if ($searchModel->status == STATUS_TRIP_DONE || $searchModel->status == STATUS_TRIP_COMPLETE) { ?>
                        <td class="text-center text-bold">
                            <?= MyStringHelper::convertIntegerToPrice((isset($bid->price) ? $bid->price : 0)) ?>₫
                        </td>
                    <?php } ?>
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
                        echo $html;
                        ?>
                        Giờ mở bán: <?= date('d/m/Y H:i', strtotime($tripItem->sell_start_time)) ?>
                    </td>
                    <td class="text-center">
                        <div>
                            <div class="text-info">
                                <?php echo isset(SCHEDULE_LIST_TRIP[$tripItem->round_trip]) ? SCHEDULE_LIST_TRIP[$tripItem->round_trip] : '' ?>
                            </div>
                            <span class='text-primary'>
                                <?= isset(SOURCE_TRIP_TYPE_LIST[$tripItem['source_trip']]) ? SOURCE_TRIP_TYPE_LIST[$tripItem['source_trip']] : '' ?>
                            </span>
                            <?php
                            if ($tripItem['source_trip'] == SOURCE_TRIP_TYPE_AGENCY) {
                                echo "<span class='text-primary'>: " . (isset($tripItem->agency->name) ? $tripItem->agency->name : '') . ' </span>';
                            }
                            ?>
                            <?php
                            if ($tripItem['source_trip'] == SOURCE_TRIP_TYPE_DRIVER) {
                                $driver = Driver::findOne(['id' => $tripItem->driver_id_created]);
                                echo "<div class='text-success'>" . (isset($driver->display_name) ? $driver->display_name : '') . ' </div>';
                                echo "<div class='text-success'>" . (isset($driver->username) ? $driver->username : '') . ' </div>';
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
                                        <span class="text-primary">
                                            <?= $group_zalo->name ?>
                                        </span>
                                    </div>

                                    <?php
                                    if ($trip_group->type == 1 || $trip_group->type == 2):
                                    ?>
                                        <div class="">
                                            Lợi nhuận:
                                            <span class="text-primary">
                                                <?= MyStringHelper::convertIntegerToPrice($tripItem->price_customer - $trip_group->price) ?>₫
                                            </span>
                                        </div>
                                    <?php
                                    endif;
                                }

                                if (isset($group_zalo_seller->name) && $group_zalo_seller->name != ''):
                                    ?>
                                    <div>
                                        Người bán:
                                        <span class="text-primary">
                                            <?= $group_zalo_seller->name ?>
                                        </span>
                                    </div>
                            <?php
                                endif;
                            }
                            ?>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="enabled-onoffswitch">
                            <div class="switch">
                                <div class="onoffswitch" style="margin: auto">
                                    <input type="checkbox" class="onoffswitch-checkbox status js-onoff"
                                        data-id="<?= $tripItem->id ?>" id="status-<?= $tripItem->id ?>"
                                        <?= ($tripItem['display'] == 1 ? 'checked' : '') ?>>
                                    <label class="onoffswitch-label" for="status-<?= $tripItem->id ?>">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center"><?= isset($tripItem->admin->username) ? $tripItem->admin->username : '-' ?></td>
                    <?php
                    if ($searchModel->status == STATUS_TRIP_CANCEL) {
                        $html = '';
                        $html .= '<td>' . $tripItem->note . '</td>';
                        echo $html;
                    }
                    ?>
                    <td style="min-width: 180px; width: 180px">
                        <div class="d-flex" style="justify-content: center; flex-wrap: wrap;">
                            <?php
                            $tripCopy = $tripItem->toArray();
                            unset($tripCopy['id']);
                            $commonButtons = [
                                'update' => Html::a('<span class="fa fa-pencil" style="width:14px" aria-hidden="true"></span> ', ['trip/update', 'id' => $tripItem->id, 'SearchTrip' => $searchModel, 'method' => ($tripItem['status'] == STATUS_TRIP_DONE ? 'driver' : '')], [
                                    'title' => 'Chỉnh sửa',
                                    'class' => 'btn-action-list btn-primary btn mb2',
                                ]),
                                'copy' => Html::a('<span class="fa fa-copy" aria-hidden="true"></span> ', ['trip/copy', 'id' => $tripItem->id], [
                                    'title' => 'Copy chuyến xe',
                                    'class' => 'btn-action-list btn-info btn mb2',
                                    'data' => [
                                        'confirm' => 'Bạn có chắc chắn muốn copy chuyến xe này?',
                                        'method' => 'post',
                                    ],
                                ]),
                                'addManual' => Html::a('<span class="fa fa-car" aria-hidden="true"></span> ', ['trip/add-manual', 'id' => $tripItem->id], [
                                    'title' => 'Điều xe',
                                    'class' => 'btn-action-list btn-success btn mb2',
                                ]),
                                'cancel' => Html::button('<i class="fa fa-times" style="width:14px" aria-hidden="true"></i>', [
                                    'title' => 'Hủy lịch',
                                    'class' => 'btn-action-list js-modal-cancel-trip btn-danger btn mb2',
                                    'data-target' => '#modal-cancel-trip',
                                    'data-toggle' => 'modal',
                                    'data-id' => $tripItem->id,
                                ]),
                                'delete' => Html::button(
                                    '<span><span class="fa fa-trash" aria-hidden="true"></span></span>',
                                    [
                                        'title' => 'Xóa chuyến xe',
                                        'class' => 'js-modal-delete-trip btn-danger btn mb2',
                                        'data-target' => '#modal-delete-trip',
                                        'data-toggle' => 'modal',
                                        'data-id' => $tripItem->id,
                                    ]
                                ),
                                'view' => Html::a('<span class="fa fa-eye" aria-hidden="true"></span> ', ['trip/only-view', 'id' => $tripItem->id], [
                                    'title' => 'Xem thông tin chuyến xe',
                                    'class' => 'btn-action-list btn-info btn mb2',
                                ]),
                                'transfer' => Html::button('<span class="fa fa-exchange" aria-hidden="true"></span>', [
                                    'title' => 'Bán chuyến',
                                    'class' => 'btn-action-list btn-warning btn mb2 transfer-group-zalo modal-toggle',
                                    'data-id' => $tripItem->id,
                                    'data-json' => base64_encode(json_encode($group_zalo)),
                                ]),
                                'updatePrice' => Html::button('<span class="fa fa-tachometer" aria-hidden="true"></span>', [
                                    'title' => 'Cập nhật giờ đi / giá',
                                    'class' => 'btn-action-list btn mb2 js-open-update-trip',
                                    'style' => 'background-color: #6f42c1; color: #fff; border-color: #6f42c1; padding: 10px; margin-left: 5px; margin-right: 5px;',
                                    'data-target' => '#modal-update-trip',
                                    'data-toggle' => 'modal',
                                    'data-trip-id' => $tripItem->id,
                                    'data-pickup-time' => date('Y-m-d\TH:i', strtotime($tripItem->pickup_time)),
                                    'data-price-customer' => number_format((int)$tripItem->price_customer, 0, ',', '.'),
                                    'data-price-bid' => number_format((int)$tripItem->price_bid, 0, ',', '.'),
                                ]),
                                'messageZns' => Html::button('<span class="fa fa-envelope-o" aria-hidden="true"></span>', [
                                    'title' => 'Tin Zalo',
                                    'class' => 'btn-action-list btn-warning btn mb2 js-modal-mes-zns',
                                    'data-target' => '#modalMesZns',
                                    'data-toggle' => 'modal',
                                    'data-id' => $tripItem->id,
                                ]),
                                'return' => Html::a('<span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>', ['trip/return', 'id' => $tripItem->id], [
                                    'title' => 'Trả lịch',
                                    'class' => 'btn-action-list btn-danger btn mb2',
                                ]),
                                'money' => Html::a('<span class="fa fa-fas fa-money" aria-hidden="true"></span>', ['trip/money', 'id' => $tripItem->id], [
                                    'title' => 'Công nợ',
                                    'class' => 'btn-action-list btn-success btn mb2',
                                ]),
                                'copy' => Html::a('<span class="fa fa-fas fa-copy" aria-hidden="true"></span>', ['/trip/create?' . http_build_query($tripCopy)], [
                                    'title' => 'Copy chuyến',
                                    'class' => 'btn-action-list btn-success btn mb2',
                                ]),
                            ];
                            $template = $commonButtons['update'] . $commonButtons['copy'] . $commonButtons['view'];
                            if (isset($searchModel->status) && $searchModel->status == STATUS_TRIP_DONE) {
                                $template .= $commonButtons['return'] . $commonButtons['money'] . $commonButtons['messageZns'];
                                if (isset($tripItem['bid']['id'])) {
                                    $template .= $commonButtons['updatePrice'];
                                }
                            } elseif (isset($searchModel->status) && ($searchModel->status == STATUS_TRIP_COMPLETE || $searchModel->status == STATUS_TRIP_CANCEL)) {
                                $template .= $commonButtons['messageZns'];
                                if (Yii::$app->user->can('ADMIN_ROLE')) {
                                    $template .= $commonButtons['cancel'] . (($searchModel->status == STATUS_TRIP_COMPLETE) ? $commonButtons['money'] : '') . $commonButtons['return'];
                                }

                                if (Yii::$app->user->can('QUAN_LY_NHUNG_ROLE')) {
                                    $template .= $commonButtons['return'];
                                }

                                if (isset($tripItem['bid']['id'])) {
                                    $template .= $commonButtons['updatePrice'];
                                }
                            } else {
                                $template .= $commonButtons['addManual'] . $commonButtons['cancel'] . $commonButtons['transfer'] . $commonButtons['messageZns'];
                            }
                            if (Yii::$app->user->can('ADMIN_ROLE')) {
                                $template .= $commonButtons['delete'];
                            }
                            ?>
                            <?= $template ?>
                        </div>
                    </td>
                </tr>
            <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="100%"><span class="text-danger">Không có dữ liệu phù hợp...</span></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr class="text-bold" style="background-color: #f1f1f1;">
            <td colspan="2" class="text-center" style="vertical-align: middle; font-size: 16px;">Tổng cộng:</td>
            <td class="text-center text-bold" style="vertical-align: middle; font-size: 16px;">
                <?= MyStringHelper::convertIntegerToPrice($totalCustomer) ?>₫
            </td>
            <?php if ($searchModel->status == STATUS_TRIP_DONE || $searchModel->status == STATUS_TRIP_COMPLETE || $searchModel->status == STATUS_TRIP_ALL) { ?>
                <td></td>
            <?php } ?>
            <td class="text-center text-bold" style="vertical-align: middle; font-size: 16px;">
                <?= MyStringHelper::convertIntegerToPrice($totalBid) ?>₫
            </td>
            <?php if ($searchModel->status == STATUS_TRIP_DONE || $searchModel->status == STATUS_TRIP_COMPLETE) { ?>
                <td></td>
            <?php } ?>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <?php if ($searchModel->status == STATUS_TRIP_CANCEL) { ?>
                <td></td>
            <?php } ?>
            <td class="text-center" style="vertical-align: middle;">
                <div class="text-danger" style="font-size: 16px;">
                    Lợi nhuận: <?= MyStringHelper::convertIntegerToPrice($totalProfit) ?>₫
                </div>
            </td>
        </tr>
    </tfoot>
</table>
<div class="d-flex" style="justify-content: space-between;">
    <div>
        <?php
        echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries';
        ?>
    </div>
    <?=
    LinkPager::widget([
        'pagination' => $dataProvider->getPagination(),
        'prevPageLabel' => 'Previous',
        'nextPageLabel' => 'Next',
        'options' => ['class' => 'pagination', 'style' => 'margin:0'],
        'linkOptions' => [
            'class' => 'page-link trip-pagination-item',
            'data-page' => function ($page, $label, $disabled, $active) {
                $options = [];
                $options['tag'] = 'a';
                $options['data-page'] = $page;  // Thêm thuộc tính data-page

                return $options;
            },
        ],
        'maxButtonCount' => 5,
    ]);
    ?>
</div>

<?= $this->render('_update_bid_modal'); ?>
