<?php

use yii\widgets\LinkPager;

?>
<div class="d-flex" style="justify-content: space-between; margin-bottom: 20px">
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
            'data-page' => function ($page) {
                $options = [];
                $options['tag'] = 'a';
                $options['data-page'] = $page;

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
                <th>Họ tên</th>
                <th>Số điện thoại</th>
                <th class="trip-info">Thông tin chuyến xe</th>
                <th class="time">Thời gian</th>
                <th>Voucher</th>
                <th>Remote IP</th>
                <th>Utm source</th>
                <th>Utm medium</th>
                <th>Utm campaign</th>
                <th>URL</th>
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
                                <span class="text-success">(<?= SCHEDULE_LIST_TRIP[$bookingItem->round_trip] ?>)</span>
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
                        <td><?php echo $bookingItem->customer_name ?></td>
                        <td><?php echo $bookingItem->customer_phone ?></td>
                        <td>
                            <?= '<div>Loại xe: <span class="text-primary">' . (isset(TYPE_OF_CAR_LIST[$bookingItem->type_of_car]) ? TYPE_OF_CAR_LIST[$bookingItem->type_of_car] : 'Không xác định') . '</span></div>'; ?>
                        </td>
                        <td>
                            <?php
                            $html = '<div>Thời gian đi: <span class="text-danger">' . date('d/m/Y H:i:s', strtotime($bookingItem->pickup_time)) . '</span></div>';
                    $html .= '<div>Thời gian tạo: <span class="text-primary">' . date('d/m/Y H:i:s', strtotime($bookingItem->created_on)) . '</span></div>';
                    echo $html; ?>
                        </td>
                        <td class="text-center">
                            <?= isset($bookingItem->voucher) ? $bookingItem->voucher : '-' ?>
                        </td>
                        <td>
                            <?= isset($bookingItem->remote_ip) ? $bookingItem->remote_ip : '-' ?>
                        </td>
                        <td>
                            <?= isset($bookingItem->utm_source) ? $bookingItem->utm_source : '-' ?>
                        </td>
                        <td>
                            <?= isset($bookingItem->utm_medium) ? $bookingItem->utm_medium : '-' ?>
                        </td>
                        <td>
                            <?= isset($bookingItem->utm_campaign) ? $bookingItem->utm_campaign : '-' ?>
                        </td>
                        <td class="td-url">
                            <?= isset($bookingItem->url) ? $bookingItem->url : '-' ?>
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
            'data-page' => function ($page) {
                $options = [];
                $options['tag'] = 'a';
                $options['data-page'] = $page;

                return $options;
            },
        ],
        'maxButtonCount' => 5,
    ]);
    ?>
</div>