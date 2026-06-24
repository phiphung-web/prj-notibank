<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\ActiveForm;

$this->title = 'Thống kê booking ' . ' từ ngày ' . date('01-m-Y') . ' đến ngày ' . date('d-m-Y');
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('/css/pages/rev-agency.css');
$this->registerJsFile('/js/pages/revenue-booking.js', ['depends' => [YiiAsset::class]]);

/* @var $searchModel app\models\Revenue */
/* @var $dataProvider */
/* @var $reasonReject */
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
                    'options' => [
                        'class' => 'booking-search',
                    ],
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

    <div class="mt-10 mb15">
        <?php
        $form = ActiveForm::begin([
            'action' => ['booking-export'],
            'method' => 'post',
            'options' => [
                'class' => 'booking-export',
            ],
        ]);
        ?>

        <?= Html::hiddenInput('createTimeRange', '') ?>
        <?= Html::hiddenInput('createTimeStart', '') ?>
        <?= Html::hiddenInput('createTimeEnd', '') ?>

        <div class="action-box text-right">
            <?= Html::submitButton('Xuất file <i class="fa fa-download ml-10"></i>', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="table-view-list table-revenue-booking">
        <div class="table-box">
            <div class="table-box__heading">
                <div class="stt">STT</div>

                <div class="time">Thời gian</div>

                <div class="source">
                    <div class="source__heading">Nguồn</div>

                    <div class="status">
                        <div class="status__heading">Trạng thái</div>

                        <div class="status__list">
                            <div class="item">Xác nhận</div>
                            <div class="item">Chưa xử lí</div>
                            <div class="item">Lịch chờ</div>
                            <div class="item">Hủy lịch đặt</div>
                            <div class="item">Tổng</div>
                        </div>
                    </div>
                </div>

                <div class="total-trip">Tổng lịch đặt</div>
            </div>

            <div class="table-box__body">
                <?php
                $stt = 1;

                foreach ($dataProvider as $key => $itemProvider) :
                ?>
                    <div class="booking-list">
                        <div class="booking-list__stt">
                            <?= $stt ?>
                        </div>

                        <div class="booking-list__time">
                            <?= $key ?>
                        </div>

                        <div class="booking-list__source">
                            <?php foreach (SOURCE_TRIP_TYPE_LIST as $keyTripType => $itemTripType) : ?>
                                <div class="group-source-trip-type">
                                    <div class="heading">
                                        <div class="heading__item">
                                            <?= $itemTripType ?>
                                        </div>
                                    </div>

                                    <div class="status">
                                        <?php foreach (TRIP_STATUS_LIST_FULL as $keyTripStatus => $itemTripStatus) : ?>
                                            <div class="item item-<?= strtolower($keyTripStatus) ?>">
                                                <?php if ($keyTripStatus == TRIP_STATUS_REJECT && ! empty($itemProvider[$keyTripType][$keyTripStatus]['type_rejects'])) : ?>
                                                    <ul class="reason-reject-list">
                                                        <?php
                                                        foreach ($reasonReject as $keyReject => $itemReject) :
                                                            $totalReject = array_search($keyReject, $itemProvider[$keyTripType][$keyTripStatus]['type_rejects']);

                                                            if ($totalReject) :
                                                        ?>
                                                                <li>
                                                                    <span><?= $itemReject ?>: </span>
                                                                    <span><?= array_search($keyReject, $itemProvider[$keyTripType][$keyTripStatus]['type_rejects']) ?></span>
                                                                </li>
                                                        <?php
                                                            endif;
                                                        endforeach;
                                                        ?>
                                                    </ul>
                                                <?php endif; ?>

                                                <span class="total-trip-status"><?= $itemProvider[$keyTripType][$keyTripStatus]['total'] ?></span>
                                            </div>
                                        <?php endforeach; ?>

                                        <span class="item fw-bold">
                                            <?= $itemProvider[$keyTripType]['totalStatus'] ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="booking-list__total-trip">
                            <?= $itemProvider['totalDate'] ?>
                        </div>
                    </div>
                <?php
                    $stt++;
                endforeach;
                ?>
            </div>
        </div>
    </div>
</div>