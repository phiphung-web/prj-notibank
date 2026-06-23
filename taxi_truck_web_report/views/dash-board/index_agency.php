<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Thống kê' . ' từ ngày ' . date('01-m-Y') . ' đến ngày ' . date('d-m-Y');
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('/css/pages/rev-agency.css');

/* @var $searchModel app\models\Revenue */
/* @var $dataProvider */

$listBooking = $dataProvider->getModels();
?>

<div class="statistical-booking">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>

        <div class="box-body">
            <div class="rev-booking-search">
                <?php $form = ActiveForm::begin([
                    'action' => ['booking'],
                    'method' => 'get',
                ]); ?>

                <div class="fields">
                    <?= $form->field($searchModel, 'createTimeRange')->widget(DateRangePicker::class, [
                        'presetDropdown' => true,
                        'hideInput' => true,
                        'startAttribute' => 'createTimeStart',
                        'endAttribute' => 'createTimeEnd',
                        'pluginOptions' => [
                            'locale' => ['format' => 'Y-MM-DD'],
                        ],
                    ]); ?>
                </div>

                <div class="action-box">
                    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="table-view-list">
        <table id="table-pagination" class="table table-striped table-bordered" style="background: #fff;">
            <thead>
                <tr>
                    <th class="text-center">STT</th>
                    <th>Thời gian</th>
                    <th class="text-center">Tổng số chuyến</th>
                    <th class="text-center">Chưa xử lý</th>
                    <th class="text-center">Chờ xác nhận</th>
                    <th class="text-center">Đã xác nhận</th>
                    <th class="text-center">Đã hủy</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (! empty($listBooking)) :
                    foreach ($listBooking as $key => $itemBooking) :
                ?>
                        <tr>
                            <td class="text-center"><?= $key + 1 ?></td>
                            <td><?= $itemBooking['dtDate'] ?></td>
                            <td class="text-center"><?= isset($itemBooking['total']) ? $itemBooking['total'] : 0 ?></td>
                            <td class="text-center"><?= isset($itemBooking['totalCreate']) ? $itemBooking['totalCreate'] : 0 ?></td>
                            <td class="text-center"><?= isset($itemBooking['totalConfirm']) ? $itemBooking['totalConfirm'] : 0 ?></td>
                            <td class="text-center"><?= isset($itemBooking['totalWaiting']) ? $itemBooking['totalWaiting'] : 0 ?></td>
                            <td class="text-center"><?= isset($itemBooking['totalReject']) ? $itemBooking['totalReject'] : 0 ?></td>
                        </tr>
                <?php
                    endforeach;
                endif;
                ?>
            </tbody>
        </table>
    </div>
</div>