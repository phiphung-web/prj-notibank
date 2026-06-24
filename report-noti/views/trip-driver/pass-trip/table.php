<?php

use app\helpers\MyStringHelper;
use yii\helpers\Html;
use yii\widgets\LinkPager;

?>

<table id="datatables_w0" class="table table-striped table-bordered js-ajax-table" width="100%" cellspacing="0" style="background: #fff;">
    <thead>
        <tr>
            <th style="width: 300px">Chuyến xe</th>
            <th>Khách hàng</th>
            <th class="text-center">Thu khách</th>
            <th class="text-center">Giá bid</th>
            <th class="text-center">Trạng thái</th>
            <th class="text-center">Lái xe bán lịch</th>
            <th class="text-center">Lợi nhuận</th>
            <th class="text-center">Phương thức</th>
            <th class="text-center">Trạng thái</th>
            <?php if (empty($searchModel->payment_status)) { ?>
                <th></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($bookingList) && is_array($bookingList) && count($bookingList)) {
            foreach ($bookingList as $bookingItem) {
                ?>
                <tr data-key="<?= $bookingItem['id'] ?>" role="row">
                    <td style="max-width: 300px">
                        <div><span class="text-danger">
                                <?= date('d/m/Y H:i', strtotime($bookingItem['pickup_time'])) ?>
                            </span></div>
                        <div>
                            <span class="text-primary">
                                <?= $bookingItem['pickup_address'] ?>
                            </span>
                            <span style="font-size: 15px;">➜</span>
                            <span class="text-danger">
                                <?= $bookingItem['destination_address'] ?>
                            </span>
                        </div>
                        <div class="text-bold">
                            <span class="text-success">(<?= SCHEDULE_LIST_TRIP[$bookingItem['round_trip']] ?>)</span>
                            <?= ($bookingItem['is_have_bill'] ? ' - <span class="text-primary">(Hóa đơn)</span>' : '') ?>
                            <?= ($bookingItem['is_collect_money'] ? ' - <span class="text-danger">(Thu tiền)</span>' : ' - <span class="text-danger">(Không thu tiền)</span>') ?>
                        </div>
                        <?php if (! empty($bookingItem['description'])) { ?>
                            <div class="text-left">Mô tả: <span>
                                    <?= $bookingItem['description'] ?>
                                </span></div>
                        <?php } ?>
                    </td>
                    <td>
                        <div>Tên: <span class="text-primary">
                                <?= $bookingItem['customer_name'] ?>
                            </span></div>
                        <div>SĐT: <span class="text-primary">
                                <?= $bookingItem['customer_phone'] ?>
                            </span></div>
                        <?php if (! empty($bookingItem)) { ?>
                            <div>Loại xe: <span class="text-primary">
                                    <?= isset(TYPE_OF_CAR_LIST[$bookingItem['type_of_car']]) ? TYPE_OF_CAR_LIST[$bookingItem['type_of_car']] : 'Không xác định' ?>
                                </span></div>
                        <?php } ?>
                    </td>
                    <td class="text-center text-bold">
                        <?= MyStringHelper::convertIntegerToPrice((isset($bookingItem['price_customer']) ? $bookingItem['price_customer'] : 0)) ?>₫
                    </td>
                    <td class="text-center text-bold">
                        <?= MyStringHelper::convertIntegerToPrice((isset($bookingItem['b_price_bid']) ? $bookingItem['b_price_bid'] : 0)) ?>₫
                    </td>
                    <td class="text-center">
                        <?php
                        $html = '';
                if ($bookingItem['status'] == STATUS_TRIP_OPEN && $bookingItem['sell_start_time'] > gmdate('Y-m-d H:i:s', time() + 7 * 3600)) {
                    $html .= '<div><span class="text-primary">' . STATUS_TRIP[STATUS_TRIP_CREATE] . '</span></div>';
                } elseif ($bookingItem['status'] == STATUS_TRIP_EXPIRE) {
                    $html .= '<div><span class="text-danger text-bold">' . STATUS_TRIP[$bookingItem['status']] . '</span></div>';
                } else {
                    $html .= '<div><span class="text-primary">' . STATUS_TRIP[$bookingItem['status']] . '</span></div>';
                }
                $html .= '<div><span class="text-success">' . ($bookingItem['is_called_for_cus'] == 1 && ($bookingItem['status'] == 'DONE' || $bookingItem['status'] == 'COMPLETE') ? 'Đã liên hệ với khách hàng' : '') . '</span></div>';
                echo $html; ?>
                    </td>
                    <td>
                        <div class="text-left">Tên: <span class="text-primary">
                                <?= $bookingItem['display_name'] ?>
                            </span>
                        </div>
                        <div class="text-left">SĐT: <span class="text-primary">
                                <?= $bookingItem['username'] ?>
                            </span></div>
                    </td>
                    <td style="white-space: nowrap">
                        <?php
                        $priceMinus = $bookingItem['price_customer'] - $bookingItem['b_price_bid'];
                $adminReceive = round($priceMinus / 100 * 40, -3); ?>
                        Tổng đài: <span class="text-danger text-bold"><?= MyStringHelper::convertIntegerToPrice($adminReceive) ?>₫</span><br>
                        Lái xe: <span class="text-primary text-bold"><?= MyStringHelper::convertIntegerToPrice($priceMinus - $adminReceive) ?>₫</span>
                    </td>
                    <td class="text-center">
                        <span class='text-primary'>
                            <?= isset(BOOKING_PAYMENT_METHOD_LIST[$bookingItem['payment_method']]) ? BOOKING_PAYMENT_METHOD_LIST[$bookingItem['payment_method']] : '' ?>
                        </span>
                    </td>
                    <?=
                    '<td class="text-center">
                        <span class="js-driver_debt_collection text-bold ' . (empty($bookingItem['paid_driver_on']) ? 'text-danger' : 'text-info collected') . '" data-id="' . $bookingItem['id'] . '" id="collected-money-' . $bookingItem['id'] . '" style= "cursor: pointer;">
                                ' . (empty($bookingItem['paid_driver_on']) ? 'Nợ' : 'Đã thu: ' . date('d/m/Y H:i', strtotime($bookingItem['paid_driver_on']))) .
                        '</span>
                    </td>'; ?>
                    <?php if (empty($searchModel->payment_status)) { ?>
                        <td style="width: 180px">
                            <div class="d-flex" style="justify-content: center; flex-wrap: wrap;">
                                <?php
                                $commonButtons = [
                                    'collection' => Html::button(
                                        '<span>Xác nhận</span>',
                                        [
                                            'title' => 'Xác nhận',
                                            'class' => 'js-btn-pass-booking btn-primary btn mb2',
                                            'data-id' => $bookingItem['booking_id'],
                                            'data-payment-method' => $bookingItem['payment_method'],
                                            'data-price' => ($priceMinus - $adminReceive),
                                        ]
                                    ),
                                ];
                                $template = $commonButtons['collection'];
                                ?>
                                <?= $template ?>
                            </div>
                        </td>
                    <?php } ?>
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
<div class="d-flex" style="justify-content: space-between;">
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
                $options['data-page'] = $page; // Thêm thuộc tính data-page

                return $options;
            },
        ],
        'maxButtonCount' => 5,
    ]);
    ?>
</div>
