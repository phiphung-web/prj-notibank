<?php

use app\helpers\MyStringHelper;
use app\models\Driver;
use yii\helpers\Html;
use yii\widgets\LinkPager;

?>
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
<div class="table-scroll-mobile">
    <table id="datatables_w0" class="table table-striped table-bordered " width="100%" cellspacing="0" style="background: #fff;">
        <thead>
            <tr>
                <th class="info">Thông tin khách hàng</th>
                <th class="trip-info">Thông tin chuyến xe</th>
                <th class="time" style="min-width: 200px">Thời gian</th>
                <th class="text-center">Thu khách</th>
                <th class="text-center">Lái xe nhận</th>
                <?php if (isset($searchModel->status) && $searchModel->status == 'REJECT') { ?>
                    <th>Loại từ chối</th>
                <?php } ?>
                <th class="username text-center">Người tạo</th>
                <th class="agency text-center">Nguồn</th>
                <th class="status text-center">Trạng thái</th>
                <th style="width: 180px; min-width: 180px"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($bookingList) && is_array($bookingList) && count($bookingList)) {
                foreach ($bookingList as $bookingItem) {
                    $stop_point = json_decode($bookingItem->stop_point, true); ?>
                    <tr data-key="<?= $bookingItem->id ?>" role="row">
                        <td>
                            <div>
                                <span class="text-primary">
                                    <?= $bookingItem->pickup_address ?>
                                </span>
                                <span style="font-size: 15px;">➜</span>
                                <span class="text-danger">
                                    <?= $bookingItem->destination_address ?>
                                </span>
                            </div>
                            <div class="text-bold">
                                <span class="text-success">(<?= isset(SCHEDULE_LIST_TRIP[$bookingItem->round_trip]) ? SCHEDULE_LIST_TRIP[$bookingItem->round_trip] : '' ?>)</span>
                                <?= ($bookingItem->is_have_bill ? ' - <span class="text-primary">(Hóa đơn)</span>' : '') ?>
                                <?= ($bookingItem->is_collect_money ? ' - <span class="text-danger">(Thu tiền)</span>' : ' - <span class="text-danger">(Không thu tiền)</span>') ?>
                            </div>
                            <?php
                            if (isset($stop_point) && is_array($stop_point) && count($stop_point)) {
                                foreach ($stop_point as $key => $value) {
                                    ?>
                                    <div>Điểm dừng <?php echo $key + 1 ?>: <span class="text-success"><?php echo $value ?></span></div>
                            <?php
                                }
                            } ?>
                            <?php if (! empty($bookingItem->note)) { ?>
                                <div class="text-left">Mô tả:
                                    <span>
                                        <?= $bookingItem->note ?>
                                    </span>
                                </div>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            $html = '<div>Tên: <span class="text-primary">' . $bookingItem->customer_name . '</span></div>';
                    $html .= '<div>SĐT: <span class="text-primary">' . $bookingItem->customer_phone . '</span></div>';
                    $html .= '<div>Loại xe: <span class="text-primary">' . (isset(TYPE_OF_CAR_LIST[$bookingItem->type_of_car]) ? TYPE_OF_CAR_LIST[$bookingItem->type_of_car] : 'Không xác định') . '</span></div>';
                    echo $html; ?>
                        </td>
                        <td>
                            <?php
                            $html = '<div>Thời gian đi: <span class="text-danger">' . date('d/m/Y H:i:s', strtotime($bookingItem->pickup_time)) . '</span></div>';
                    $html .= '<div>Thời gian tạo: <span class="text-primary">' . date('d/m/Y H:i:s', strtotime($bookingItem->created_on)) . '</span></div>';
                    echo $html; ?>
                        </td>
                        <td class="text-center text-bold">
                            <?= MyStringHelper::convertIntegerToPrice((isset($bookingItem->price_customer) ? $bookingItem->price_customer : 0)) ?>₫
                        </td>
                        <td class="text-center text-bold">
                            <?= MyStringHelper::convertIntegerToPrice((isset($bookingItem->price_bid) ? $bookingItem->price_bid : 0)) ?>₫
                            <br>
                            <div class="text-primary">
                            <?= (! empty($bookingItem->type) && $bookingItem->type == SOURCE_TRIP_TYPE_DRIVER) ? '(' . BOOKING_PAYMENT_METHOD_LIST[$bookingItem->payment_method] . ')' : '' ?>
                            </div>
                        </td>
                        <?php if (isset($searchModel->status) && $searchModel->status == 'REJECT') { ?>
                            <td>
                                <?= isset($reason_reject_array[$bookingItem->type_reject]) ? $reason_reject_array[$bookingItem->type_reject] : 'Không xác định' ?>
                            </td>
                        <?php } ?>
                        <td class="text-center">
                            <?= ! empty($bookingItem->admin->username) ? $bookingItem->admin->username : '' ?>
                            <?= (! empty($bookingItem->type) && $bookingItem->type == SOURCE_TRIP_TYPE_DRIVER && ($driver = Driver::findOne(['id' => $bookingItem->driver_id_created]))) ? $driver->display_name : '' ?>
                        </td>
                        <td class="text-center">
                            <?= (SOURCE_TRIP_TYPE_LIST[$bookingItem->type] ?? '') . (($bookingItem->type == SOURCE_TRIP_TYPE_AGENCY && ! empty($bookingItem->agency->name)) ? ': ' . $bookingItem->agency->name : '') ?>
                        </td>
                        <td class="text-center">
                            <div>
                                <?php
                                switch ($bookingItem->status) {
                                    case 'CONFIRM':
                                        echo "<span class='text-success'>Đã xác nhận</span>";

                                        break;
                                    case 'WAITING':
                                        echo "<span class='text-warning'>Chờ xác nhận</span>";

                                        break;
                                    case 'CREATE':
                                        echo "<span class='text-danger'>Chưa xử lý</span>";

                                        break;
                                    case 'REJECT':
                                        echo "<span class='text-danger'>Đã hủy</span>";

                                        break;
                                    default:
                                        break;
                                } ?>
                            </div>
                        </td>
                        <td class="p-4" style="flex-wrap: wrap;">
                        <div class="flex flex-wrap gap-2 justify-center">
                            <?php if (! Yii::$app->user->can('DAI_LY_ROLE')) : ?>
                                <?= Html::a('<span class="glyphicon glyphicon-check" aria-hidden="true"></span> ', '/trip/create?id=' . $bookingItem->id, [
                                    'title' => 'Thêm chuyến đi',
                                    'class' => 'btn-success btn mb2',
                                    'id' => $bookingItem->id,
                                ]); ?>

                                <?= Html::button('<span class="glyphicon glyphicon-hourglass" aria-hidden="true"></span>', [
                                    'title' => 'Trạng thái chờ',
                                    'class' => 'btn-info btn mb2 update-status-booking-btn-waiting',
                                    'data-target' => '#modalWaiting',
                                    'data-toggle' => 'modal',
                                    'data-status' => 'WAITING',
                                    'data-price-customer' => $bookingItem->price_customer,
                                    'data-price-bid' => $bookingItem->price_bid,
                                    'data-pickup-time' => $bookingItem->pickup_time,
                                    'data-id' => $bookingItem->id,
                                    'data-note' => $bookingItem->note,
                                ]); ?>
                                    <?= Html::a('<span class="glyphicon glyphicon-pencil"></span>', '/statistic/show?id=' . $bookingItem->id, [
                                        'title' => 'Sửa lịch đặt xe heheeh',
                                        'class' => 'btn-success btn mb2',
                                        'id' => $bookingItem->id,
                                    ]); ?>
                            <?php endif; ?>

                            <?php if (Yii::$app->user->can('DAI_LY_ROLE')) : ?>
                                <?= Html::a('<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> ', '/statistic/show?id=' . $bookingItem->id, [
                                    'title' => 'Sửa lịch đặt xe',
                                    'class' => 'btn-success btn mb2',
                                    'id' => $bookingItem->id,
                                ]); ?>
                            <?php endif; ?>

                            <?= Html::button('<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>', [
                                'title' => 'Hủy lịch',
                                'class' => 'btn-warning btn mb2 update-status-booking-btn-reject',
                                'data-target' => '#modalReject',
                                'data-toggle' => 'modal',
                                'data-status' => 'REJECT',
                                'data-pickup-time' => $bookingItem->pickup_time,
                                'data-id' => $bookingItem->id,
                                'data-note' => $bookingItem->note,
                                'data-type' => $bookingItem->type_reject,
                            ]); ?>
                            <?php
                            // if (Yii::$app->user->can('ADMIN_ROLE')) {
                            //     $url = yii\helpers\Url::to(['statistic/delete', 'id' => $bookingItem->id]);
                            //     echo Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                            //         'title' => 'Xóa lịch',
                            //         'data-confirm' => Yii::t('yii', 'Xóa lịch này?'),
                            //         'data-method' => 'post',
                            //         'class' => 'btn-danger btn mb2'
                            //     ]);
                            // }
                            ?>
                        </div>
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
</div>
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
                $options['data-page'] = $page; // Thêm thuộc tính data-page

                return $options;
            },
        ],
        'maxButtonCount' => 5,
    ]);
    ?>
</div>
