<?php

use app\helpers\MyStringHelper;
use app\models\Agency;
use app\models\Bid;
use app\models\GroupZalo;
use app\models\GroupZaloSeller;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Driver;
use app\models\Car;

$latestBid = Bid::find()
    ->where(['trip_id' => $model->id, 'status' => 'SUCCESS'])
    ->orderBy(['id' => SORT_DESC])
    ->one();

$driver = null;
if ($latestBid && $latestBid->driver_id) {
    $driver = Driver::find()
        ->alias('d')
        ->select([
            'd.*',
            'c.bks AS car_bks',
            'c.color AS car_color',
            'c.type AS car_type_name',
        ])
        ->leftJoin(['c' => Car::tableName()], 'c.id = d.car_id')
        ->where(['d.id' => $latestBid->driver_id])
        ->asArray()
        ->one();
}

$this->title = 'Chuyến xe tạo bởi ' . (isset($user->username) ? $user->username : '') . ' vào lúc: ' . (isset($model->created_on) ? $model->created_on : '');
if (isset($flag) && $flag == 'accept') {
    $this->title = 'Xác nhận chuyến xe';
}
$this->params['breadcrumbs'][] = ['label' => 'Thêm mới chuyến xe', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
$detailTrip = [
    [
        'label' => 'Lịch trình',
        'value' => SCHEDULE_LIST_TRIP[$model->round_trip],
    ],
    [
        'label' => 'Lấy hóa đơn',
        'value' => $model->is_have_bill ? 'Có' : 'Không',
    ],
    [
        'label' => 'Thu tiền khách',
        'value' => $model->is_collect_money ? 'Có' : 'Không',
    ],
    [
        'attribute' => 'pickup_time',
        'value' => '<b>' . $model->pickup_time . '</b>',
        'format' => 'raw',
    ],
    [
        'label' => 'Giá báo khách',
        'value' => '<b>' . MyStringHelper::convertIntegerToPrice($model->price_customer) . '</b>',
        'format' => 'raw',
    ],
    [
        'label' => 'Nguồn nhận lịch',
        'value' => function ($model) {
            if ($model->source_trip == SOURCE_TRIP_TYPE_AGENCY) {
                $agency = Agency::findOne($model->agency_id);
            }

            return ($model->source_trip == SOURCE_TRIP_TYPE_AGENCY) ? SOURCE_TRIP_TYPE_LIST[$model->source_trip] . ' (' . (isset($agency->name) ? $agency->name : '') . ')' : SOURCE_TRIP_TYPE_LIST[$model->source_trip];
        },
    ],
    [
        'label' => 'Giá bán cho lái xe',
        'value' => '<b>' . MyStringHelper::convertIntegerToPrice($model->price_bid) . '</b>',
        'format' => 'raw',
    ],
    [
        'label' => 'Thời gian lái xe bid',
        'value' => function ($model) {
            $bid = Bid::find()->where(['trip_id' => $model->id, 'status' => 'SUCCESS'])->one();

            return isset($bid->created_on) ? date('d/m/Y H:i', strtotime($bid->created_on)) : '';
        },
    ],

];

if (isset($modelTripGroup) && ! empty($modelTripGroup->group_zalo_id)) {
    $zalo = GroupZalo::findOne($modelTripGroup->group_zalo_id);
    $zaloSeller = GroupZaloSeller::findOne($modelTripGroup->zalo_seller_id);
    $detailTrip = array_merge($detailTrip, [
        [
            'label' => 'Nhóm bán',
            'value' => isset($zalo->name) ? $zalo->name : '',
        ],
        [
            'label' => 'Người bán',
            'value' => isset($zaloSeller->name) ? $zaloSeller->name : '',
        ],
        [
            'label' => 'Tài xế',
            'value' => $modelTripGroup->driver_name,
        ],
        [
            'label' => 'Số điện thoại tài xế',
            'value' => $modelTripGroup->driver_phone,
        ],
        [
            'label' => 'Biển số xe',
            'value' => $modelTripGroup->license_plates,
        ],
        [
            'label' => 'Loại xe',
            'value' => isset(TYPE_OF_CAR_LIST[$modelTripGroup->type_of_car]) ? TYPE_OF_CAR_LIST[$modelTripGroup->type_of_car] : 'Không xác định',
        ],
        [
            'label' => 'Tiền lái xe nhận',
            'value' => '<b>' . MyStringHelper::convertIntegerToPrice($modelTripGroup->price) . '</b>',
            'format' => 'raw',
        ],
    ]);
}
?>

<div class="trip-view row">
    <div class="col-md-6">
        <div class="box box-green">
            <div class="box-header with-border">
                <h3 class="box-title">Thông tin khách hàng</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'customer_name',
                        'customer_phone',
                        [
                            'attribute' => 'type_of_car',
                            'label' => 'Loại xe',
                            'value' => TYPE_OF_CAR_LIST[$model->type_of_car],
                        ],
                        [
                            'label' => 'Lịch trình',
                            'value' => function ($model) {
                                return '<b>' . $model->pickup_address . ' ➜ ' . $model->destination_address . '</b>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'area',
                            'value' => '<b>' . $model->area . '</b>',
                            'format' => 'raw',
                        ],
                        'description',
                        [
                            'label' => 'Hiện',
                            'value' => $model->display ? 'Có' : 'Không',
                        ],
                    ],
                ]) ?>
            </div>
        </div>
        <?php if ($driver): ?>
            <div class="box box-success mt10">
                <div class="box-header with-border">
                    <h3 class="box-title">Thông tin tài xế đã điều chuyến</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $driver,
                        'attributes' => [
                            [
                                'label' => 'Họ tên tài xế',
                                'value' => $driver['display_name'] ?? '(Chưa cập nhật)',
                            ],
                            [
                                'label' => 'Số điện thoại đăng nhập',
                                'value' => $driver['username'] ?? '(Chưa cập nhật)',
                            ],
                            [
                                'label' => 'Biển kiểm soát',
                                'value' => $driver['car_bks'] ?? '(Chưa cập nhật)',
                            ],
                            [
                                'label' => 'Màu xe',
                                'value' => $driver['car_color'] ?? '(Chưa cập nhật)',
                            ],
                            [
                                'label' => 'Hãng xe',
                                'value' => $driver['car_type_name'] ?? '(Chưa cập nhật)',
                            ],
                            [
                                'label' => 'Loại xe',
                                'value' => isset($driver['type_of_car'])
                                    ? (TYPE_OF_CAR_LIST[$driver['type_of_car']] ?? $driver['type_of_car'])
                                    : '(Chưa cập nhật)',
                            ],
                            [
                                'label' => 'Trạng thái tài xế',
                                'value' => isset($driver['status']) && $driver['status'] == 1
                                    ? 'Hoạt động'
                                    : 'Ngưng hoạt động',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Thông tin chuyến xe</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="mb10">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => $detailTrip,
                    ]) ?>
                </div>
                <?php if (isset($flag) && $flag == 'accept') : ?>
                    <div class="d-flex full-width" style="justify-content: space-between;">
                        <div>
                            <?= Html::a(
                                'Xác nhận',
                                ['/trip/confirm', 'id' => $model->id] +
                                    (isset($_GET['idCallBack']) ? ['idCallBack' => $_GET['idCallBack']] : []) +
                                    (isset($_GET['trip_id']) ? ['trip_id' => $_GET['trip_id']] : []) +
                                    (isset($_GET['id_booking']) ? ['id_booking' => $_GET['id_booking']] : []),
                                ['class' => 'btn btn-success']
                            ) ?>
                            <?= Html::a('Chỉnh sửa', ['/trip/update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>

                            <?php
                            $url_waiting_booking = '/statistic/create?' . http_build_query($model->attributes)
                                . (isset($_GET['idCallBack']) ? '&idCallBack=' . $_GET['idCallBack'] : '')
                                . (isset($_GET['trip_id']) ? '&trip_id=' . $_GET['trip_id'] : '')
                                . '&status=WAITING&id=' . $model->id;

                            $booking = $model->attributes;
                            unset($booking['status']);
                            $url_cancel_booking = '/statistic/create?' . http_build_query($booking)
                                . (isset($_GET['idCallBack']) ? '&idCallBack=' . $_GET['idCallBack'] : '')
                                . (isset($_GET['trip_id']) ? '&trip_id=' . $_GET['trip_id'] : '')
                                . '&status=reject&id=' . $model->id;
                            ?>
                            <a href="<?= $url_waiting_booking ?>" class="btn btn-primary">Chờ</a>
                            <a href="<?= $url_cancel_booking ?>" class="btn btn-primary">Hủy</a>
                        </div>
                        <div>
                            <?php
                            $url_confirm_round = '/trip/confirm?' . http_build_query(['Trip' => $model->attributes])
                                . (isset($_GET['id_booking']) ? '&id_booking=' . $_GET['id_booking'] : '')
                                . (isset($_GET['idCallBack']) ? '&idCallBack=' . $_GET['idCallBack'] : '')
                                . (isset($_GET['trip_id']) ? '&trip_id=' . $_GET['trip_id'] : '')
                                . '&id=' . $model->id;

                            $url_waiting = '/trip/confirm?' . http_build_query(['Booking' => $model->attributes])
                                . (isset($_GET['idCallBack']) ? '&idCallBack=' . $_GET['idCallBack'] : '')
                                . (isset($_GET['trip_id']) ? '&trip_id=' . $_GET['trip_id'] : '')
                                . '&status=WAITING&id=' . $model->id;
                            ?>
                            <a href="<?= $url_waiting ?>" class="btn btn-primary">Xác nhận & Lịch chờ</a>
                            <a href="<?= $url_confirm_round ?>" class="btn btn-success">Xác nhận & Khứ hồi</a>
                        </div>
                    </div>
                <?php endif ?>
            </div>
        </div>
        <?php if ($latestBid != null && $latestBid->pickup_images): ?>
            <div class="box box-info mt10">
                <div class="box-header with-border">
                    <h3 class="box-title">Ảnh nhận và hoàn thành chuyến </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <?php
                    $pickupImages = !empty($latestBid->pickup_images) ? json_decode($latestBid->pickup_images, true) : [];
                    $dropoffImages = !empty($latestBid->dropoff_images) ? json_decode($latestBid->dropoff_images, true) : [];
                    ?>
                    <h4><b>Ảnh nhận chuyến</b></h4>
                    <?php if (!empty($pickupImages)): ?>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <?php foreach ($pickupImages as $img): ?>
                                <img src="<?= $img ?>"
                                    style="width:120px; height:120px; object-fit:cover; border:1px solid #ccc; border-radius:4px;"
                                    class="img-lightbox" data-toggle="modal" data-target="#imageModal"
                                    onclick="showImage('<?= $img ?>')">
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><i>Không có ảnh nhận chuyến</i></p>
                    <?php endif; ?>
                    <hr>
                    <h4><b>Ảnh hoàn thành chuyến</b></h4>
                    <?php if (!empty($dropoffImages)): ?>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <?php foreach ($dropoffImages as $img): ?>
                                <img src="<?= $img ?>"
                                    style="width:120px; height:120px; object-fit:cover; border:1px solid #ccc; border-radius:4px;"
                                    class="img-lightbox" data-toggle="modal" data-target="#imageModal"
                                    onclick="showImage('<?= $img ?>')">
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><i>Không có ảnh hoàn thành chuyến</i></p>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>
        <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="background: transparent; border: none;">
                    <div class="modal-body" style="text-align: center; padding: 0;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            style="position: absolute; top: 10px; right: 20px; color: white; font-size: 28px; background: none; border: none; cursor: pointer;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <img id="modalImage" src="" style="max-width: 100%; max-height: 90vh; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if (! isset($flag) || $flag != 'accept') : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Thao tác</h3>
                </div>
                <div class="box-body">
                    <?= Html::a('Copy chuyến xe', ['/trip/copy', 'id' => $model->id], [
                        'class' => 'btn btn-info',
                        'data' => [
                            'confirm' => 'Bạn có chắc chắn muốn copy chuyến xe này?',
                            'method' => 'post',
                        ],
                    ]) ?>
                    <?= Html::a('Chỉnh sửa', ['/trip/update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                    <?= Html::a('Quay lại', ['/trip/index'], ['class' => 'btn btn-default']) ?>
                </div>
            </div>
        </div>
    </div>

    <label>Lịch sử thao tác</label>
    <table class="table table-striped table-bordered" style="background: #fff;">
        <thead>
            <tr>
                <th class="text-center">Thời gian</th>
                <th class="text-center text-nowrap">Hành động</th>
                <th class="text-center">Người thực hiện</th>
                <th class="text-center">Nội dung</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($logs) && ! empty($logs) && count($logs)) {
                foreach ($logs as $log) {
            ?>
                    <tr role="row">
                        <td><?= $log['created_on'] ?></td>
                        <td><?= $log['action'] ?></td>
                        <td><?= $log['user_name'] ?></td>
                        <td><?= $log['message'] ?></td>
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
<?php endif ?>
<style>
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.7) !important;
    }

    #imageModal .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<script>
    function showImage(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
    }
</script>
