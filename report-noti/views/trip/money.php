<?php

use app\helpers\MyStringHelper;
use app\models\Agency;
use app\models\GroupZalo;
use app\models\GroupZaloSeller;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

$this->title = 'Cập nhật công nợ: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Lịch xe', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Công nợ';
?>
<div class="trip-update-money">
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
                return ($model->source_trip == SOURCE_TRIP_TYPE_AGENCY) ? SOURCE_TRIP_TYPE_LIST[$model->source_trip] . ' (' . (isset($model->agency->name) ? $model->agency->name : '') . ')' : SOURCE_TRIP_TYPE_LIST[$model->source_trip];
            },
        ],
        [
            'label' => 'Giá bán cho lái xe',
            'value' => '<b>' . MyStringHelper::convertIntegerToPrice($model->price_bid) . '</b>',
            'format' => 'raw',
        ],

    ];

    if (isset($model->tripGroup) && ! empty($model->tripGroup->group_zalo_id)) {
        $zalo = GroupZalo::findOne($model->tripGroup->group_zalo_id);
        $zaloSeller = GroupZaloSeller::findOne($model->tripGroup->zalo_seller_id);
        $detailTrip = array_merge($detailTrip, [
            [
                'label' => 'Nhóm Zalo bán',
                'value' => isset($zalo->name) ? $zalo->name : '',
            ],
            [
                'label' => 'Người bán Zalo',
                'value' => isset($zaloSeller->name) ? $zaloSeller->name : '',
            ],
            [
                'label' => 'Tài xế',
                'value' => $model->tripGroup->driver_name,
            ],
            [
                'label' => 'Số điện thoại tài xế',
                'value' => $model->tripGroup->driver_phone,
            ],
            [
                'label' => 'Tiền lái xe nhận',
                'value' => '<b>' . MyStringHelper::convertIntegerToPrice($model->tripGroup->price) . '</b>',
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
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa fa-minus"></i></button>
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
                                'value' => (isset(TYPE_OF_CAR_LIST[$model->type_of_car]) ? TYPE_OF_CAR_LIST[$model->type_of_car] : 'Không xác định'),
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
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Thông tin chuyến xe</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => $detailTrip,
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <?php $form = ActiveForm::begin(); ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Cập nhật công nợ tài xế</h3>
                    <div class="text-danger">
                        Lưu ý: Nếu chọn sẽ cập nhật lại chuyến xe trạng thái tương tự, cân nhắc trước khi chọn chuyến xe
                        để
                        tránh xảy ra nhầm lẫn :D
                    </div>
                </div>
                <div class="box-body">
                    <div class="trip-form">
                        <?php if (isset($model->tripGroup)) { ?>
                            <div class="form-group">
                                <?= Html::checkbox('status_trip[]', ($model->is_collect_money == 1 && $model->driver_debt == 0 && isset($model->tripGroup->price) && $model->tripGroup->price > 0), ['label' => 'Tài xế nợ tổng đài', 'value' => 'driver_debt_admin']) ?>
                            </div>
                        <?php } ?>
                        <div class="form-group" style="margin-bottom: 0">
                            <?= Html::checkbox('status_trip[]', (($model->is_collect_money == 0 && $model->driver_debt == 0) || (isset($model->tripGroup->type) && $model->tripGroup->type == 2 && $model->tripGroup->price > 0 && $model->is_collect_money == 1 && $model->driver_debt == 0)), ['label' => 'Tổng đài nợ tài xế', 'value' => 'admin_debt_driver']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Cập nhật công nợ khách hàng</h3>
                </div>
                <div class="box-body">
                    <div class="trip-form">
                        <div class="form-group" style="margin-bottom: 0">
                            <?= Html::checkbox('status_trip[]', ($model->is_collect_money == 0 && $model->collected_money == 0), ['label' => 'Khách hàng nợ tổng đài', 'value' => 'customer_debt_admin']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($model->agency_id != 0 && $model->source_trip == SOURCE_TRIP_TYPE_AGENCY) {
                $agency = Agency::findOne($model->agency_id); ?>
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Cập nhật công nợ đại lý</h3>
                    </div>
                    <div class="box-body">
                        <div class="trip-form">
                            <?php if ($agency->agency_debt == ADMIN_DEBT_AGENCY) { ?>
                                <div class="form-group">
                                    <?= Html::checkbox('status_trip[]', ($model->agency_debt == 0), ['label' => 'Tổng đài nợ đại lý', 'value' => 'admin_debt_agency']) ?>
                                </div>
                            <?php } else { ?>
                                <div class="form-group">
                                    <?= Html::checkbox('status_trip[]', ($model->agency_debt == 0), ['label' => 'Đại lý nợ tổng đài', 'value' => 'agency_debt_admin']) ?>
                                </div>
                            <?php } ?>
                            <label for="">Số tiền nợ</label>
                            <?php echo Html::input('text', 'money_debt_agency', (! empty($model->money_debt_agency) ? $model->money_debt_agency : ($model->price_customer - $model->bid->price > $model->price_customer / 100 * $agency->percent ? $model->price_customer / 100 * $agency->percent : ($model->price_customer - $model->bid->price > $agency->price ? $agency->price : 0))), ['class' => 'form-control int']); ?>
                        </div>
                    </div>
                </div>
            <?php
            } ?>
            <div class="form-group" style="margin-bottom: 0">
                <?= Html::submitButton('Lưu', ['class' => 'btn btn-success']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
