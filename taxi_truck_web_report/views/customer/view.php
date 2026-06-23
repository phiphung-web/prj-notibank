<?php

use app\helpers\MyStringHelper;
use app\models\Driver;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Customer */

$this->title = 'Khách hàng: ' . $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Danh Sách Khách Hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-view">
    <p>
        <?= Html::a('Sửa', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Xóa', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'display_name',
            'phone',
            'rank',
            'birthday',
            'gender',
            'address',
            'created_on',
            'modified_on',
        ],
    ]) ?>

</div>

<div>
    <label>Lịch sử chuyến xe</label>
    <table class="table table-striped table-bordered" style="background: #fff;">
        <thead>
            <tr>
                <td class="text-center" style="width: 40px"></td>
                <th style="width: 350px; min-width: 160px;">Chuyến xe</th>
                <th style="min-width: 160px">Khách hàng</th>
                <th class="text-center" style="min-width: 100px">Thu khách</th>
                <th class="text-center" style="min-width: 100px">Giá bid</th>
                <th class="text-center" style="min-width: 100px">Trạng thái</th>
                <th class="text-center" style="min-width: 100px">Nguồn</th>
                <th class="text-center" style="min-width: 100px">Người tạo</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($tripList) && is_array($tripList) && count($tripList)) {
                foreach ($tripList as $key => $tripItem) {
                    $trip_group = $tripItem->tripGroup;
                    $group_zalo = [];
                    if ($trip_group !== null) {
                        $group_zalo = $trip_group->attributes;
                    } ?>
                    <tr data-key="<?= $tripItem->id ?>" role="row">
                        <td class="text-center" style="width: 40px; vertical-align: middle !important;"><?= ++$key ?></td>
                        <td style="max-width: 300px">
                            <div>
                                <span class="text-danger">
                                    <?= date('d/m/Y H:i', strtotime($tripItem->pickup_time)) ?>
                                </span>
                            </div>
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
                                } ?>
                            </div>
                            <?php if (! empty($tripItem->description)) { ?>
                                <div class="text-left">Mô tả: <span>
                                        <?= $tripItem->description ?>
                                    </span></div>
                            <?php } ?>
                            <?php if (! empty($tripItem)) { ?>
                                <div>
                                    Loại xe:
                                    <span class="text-primary">
                                        <?= isset(TYPE_OF_CAR_LIST[$tripItem->type_of_car]) ? TYPE_OF_CAR_LIST[$tripItem->type_of_car] : 'Không xác định' ?>
                                    </span>
                                </div>
                            <?php } ?>

                        </td>
                        <td>
                            <div>
                                Tên:
                                <span class="text-primary">
                                    <?= $tripItem->customer_name ?>
                                </span>
                            </div>
                            <div>
                                SĐT:
                                <span class="text-primary">
                                    <?= $tripItem->customer_phone ?>
                                </span>
                            </div>
                        </td>
                        <td class="text-center text-bold">
                            <?= MyStringHelper::convertIntegerToPrice((isset($tripItem->price_customer) ? $tripItem->price_customer : 0)) ?>₫
                        </td>
                        <td class="text-center text-bold">
                            <?= MyStringHelper::convertIntegerToPrice((isset($tripItem->price_bid) ? $tripItem->price_bid : 0)) ?>₫
                        </td>
                        <td class="text-center">
                            <?php
                            $html = '';
                            if ($tripItem->status == STATUS_TRIP_OPEN && $tripItem->sell_start_time > gmdate('Y-m-d H:i:s', time() + 7 * 3600)) {
                                $html .= '<div><span class="text-primary">' . STATUS_TRIP[STATUS_TRIP_CREATE] . '</span></div>';
                            } elseif ($tripItem->status == STATUS_TRIP_EXPIRE) {
                                $html .= '<div><span class="text-danger text-bold">' . STATUS_TRIP[$tripItem->status] . '</span></div>';
                            } else {
                                $html .= '<div><span class="text-primary">' . STATUS_TRIP[$tripItem->status] . '</span></div>';
                            }
                            $html .= '<div><span class="text-success">' . ($tripItem->is_called_for_cus == 1 && ($tripItem->status == 'DONE' || $tripItem->status == 'COMPLETE') ? 'Đã liên hệ với khách hàng' : '') . '</span></div>';
                            echo $html; ?>
                            Giờ mở bán: <?= date('d/m/Y H:i', strtotime($tripItem->sell_start_time)) ?>
                        </td>
                        <td class="text-center">
                            <div>
                                <span class='text-primary'>
                                    <?= isset(SOURCE_TRIP_TYPE_LIST[$tripItem->source_trip]) ? SOURCE_TRIP_TYPE_LIST[$tripItem->source_trip] : '' ?>
                                </span>
                                <?php
                                if ($tripItem->source_trip == SOURCE_TRIP_TYPE_AGENCY) {
                                    echo "<span class='text-primary'>: " . (isset($tripItem->agency->name) ? $tripItem->agency->name : '') . ' </span>';
                                } ?>
                                <?php
                                if ($tripItem->source_trip == SOURCE_TRIP_TYPE_DRIVER) {
                                    $driver = Driver::findOne(['id' => $tripItem->driver_id_created]);
                                    echo "<div class='text-success'>" . (isset($driver->display_name) ? $driver->display_name : '') . ' </div>';
                                    echo "<div class='text-success'>" . (isset($driver->username) ? $driver->username : '') . ' </div>';
                                } ?>
                                <?php
                                if (isset($trip_group)) {
                                    $group_zalo = $trip_group->groupZalo;
                                    $group_zalo_seller = $trip_group->groupZaloSeller;
                                    if (isset($group_zalo->name) && $group_zalo->name != '') {
                                ?>
                                        <div>
                                            Nhóm bán:
                                            <span class="text-primary">
                                                <?= $group_zalo->name ?>
                                            </span>
                                        </div>

                                        <?php
                                        if ($trip_group->type == 1 || $trip_group->type == 2) :
                                        ?>
                                            <div class="">
                                                Thu:
                                                <span class="text-primary">
                                                    <?= MyStringHelper::convertIntegerToPrice($trip_group->price) ?>₫
                                                </span>
                                            </div>
                                        <?php
                                        endif;
                                    }

                                    if (isset($group_zalo_seller->name) && $group_zalo_seller->name != '') :
                                        ?>
                                        <div>
                                            Người bán:
                                            <span class="text-primary">
                                                <?= $group_zalo_seller->name ?>
                                            </span>
                                        </div>
                                <?php
                                    endif;
                                } ?>
                            </div>
                        </td>
                        <td class="text-center"><?= isset($tripItem->admin->username) ? $tripItem->admin->username : '-' ?></td>
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
