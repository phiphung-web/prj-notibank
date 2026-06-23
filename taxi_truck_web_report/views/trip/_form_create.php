<?php

use app\models\Agency;
use app\models\SystemConfiguration;
use app\models\Trip;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\YiiAsset;

$this->registerJsFile('/datetimepicker/jquery.datetimepicker.full.min.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerCssFile('/datetimepicker/jquery.datetimepicker.css');
?>

<?php if (isset($_GET['phone'])) {
    $data_customer = Trip::find()
        ->select(['type_of_car', 'customer_name', 'customer_phone', 'pickup_address', 'area', 'destination_address'])
        ->where(['customer_phone' => $_GET['phone']])
        ->orderBy(['pickup_time' => SORT_DESC])
        ->one();
} ?>
<?php $form = ActiveForm::begin([
    'id' => 'form-create-trip',
]); ?>
<?php if ($model->hasErrors()) { ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
        <?= $form->errorSummary($model); ?>
    </div>
<?php } ?>
<style>
    .pointer-events-none {
        pointer-events: none;
    }

    .btn-copy-pickup-address {
        right: 0px;
        top: 25px;
        position: absolute;
    }

    .btn-reverse {
        left: 50px;
        top: -5px;
        position: absolute;
        z-index: 1000;
    }
</style>
<div class="col-md-6">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Thông tin khách</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-6">
                    <?php
                    $customerNameConfig = array_merge(
                        isset($method) && $method == 'booking' ? ['value' => $modelBooking['customer_name']] : [],
                        isset($data_customer['customer_name']) ? ['value' => $data_customer['customer_name']] : [],
                        isset($_GET['customer_name']) ? ['value' => $_GET['customer_name']] : [],
                        ['maxlength' => true],
                        ['class' => 'form-control input-count-character', 'data-max' => '30']
                    );
                    echo $form->field($model, 'customer_name')->textInput($customerNameConfig);
                    ?>
                </div>
                <div class="col-lg-6">
                    <?php
                    $customerPhoneConfig = array_merge(
                        isset($method) && $method == 'booking' ? ['value' => $modelBooking['customer_phone']] : [],
                        isset($data_customer['customer_phone']) ? ['value' => $data_customer['customer_phone']] : [],
                        isset($_GET['phone']) ? ['value' => $_GET['phone']] : [],
                        isset($_GET['customer_phone']) ? ['value' => $_GET['customer_phone']] : [],
                        ['maxlength' => true],
                        ['class' => 'form-control input-count-character', 'data-max' => '15']
                    );
                    echo $form->field($model, 'customer_phone')->textInput($customerPhoneConfig);
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 type-of-car-general">
                    <?php
                    $defaultTypeOfCar =
                        isset($method) && $method == 'booking'
                        ? ['value' => $modelBooking['type_of_car']]
                        : (isset($_GET['type_of_car'])
                            ? ['value' => $_GET['type_of_car']]
                            : (isset($data_customer->type_of_car)
                                ? ['value' => $data_customer->type_of_car]
                                : []));
                    echo $form->field($model, 'type_of_car')->dropDownList(TYPE_OF_CAR_LIST, $defaultTypeOfCar);
                    ?>
                </div>
                <div class="col-lg-6 schedule-general">
                    <?php
                    $selectedRoundTrip = isset($method) && $method === 'booking'
                        ? $modelBooking['round_trip']
                        : ($_GET['round_trip'] ?? $model->round_trip ?? '');
                    echo $form
                        ->field($model, 'round_trip')
                        ->dropDownList(
                            SCHEDULE_LIST_TRIP,
                            [
                                'prompt' => 'Chọn loại lịch trình',
                                'value' => $selectedRoundTrip
                            ]
                        );
                    ?>

                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="position-relative">
                        <button type="button" class="btn btn-box-tool btn-reverse"><i
                                class="fa fa-retweet"></i></button>
                        <?php
                        $pickupAddressConfig = array_merge(
                            isset($method) && $method == 'booking' ? ['value' => $modelBooking['pickup_address']] : [],
                            isset($_GET['pickup_address']) ? ['value' => $_GET['pickup_address']] : (isset($data_customer['pickup_address']) ? ['value' => $data_customer['pickup_address']] : []),
                            ['maxlength' => true],
                            ['class' => 'form-control input-search-address input-count-character', 'data-max' => '80'],
                            ['autocomplete' => 'off']
                        );
                        echo $form->field($model, 'pickup_address')->textInput($pickupAddressConfig);
                        ?>
                        <ul class="wrap-address-search" style="display: none;">
                        </ul>
                        <button class="btn btn-primary btn-copy-pickup-address" type="button"><i class="fa fa-copy"
                                aria-hidden="true"></i></button>
                    </div>
                </div>
                <div class="col-lg-12">
                    <?php
                    $areaConfig = array_merge(
                        isset($method) && $method == 'booking' ? ['value' => $modelBooking['area']] : [],
                        isset($_GET['area']) ? ['value' => $_GET['area']] : (isset($data_customer['area']) ? ['value' => $data_customer['area']] : []),
                        ['maxlength' => true]
                    );
                    echo $form->field($model, 'area')->textInput($areaConfig);
                    ?>
                </div>
                <div class="col-lg-12">
                    <div class="position-relative">
                        <?php
                        $destinationAddressConfig = array_merge(
                            isset($method) && $method == 'booking'
                                ? ['value' => $modelBooking['destination_address']]
                                : (isset($_GET['destination_address'])
                                    ? ['value' => $_GET['destination_address']]
                                    : (isset($data_customer['destination_address'])
                                        ? ['value' => $data_customer['destination_address']]
                                        : [])),
                            ['maxlength' => true],
                            ['class' => 'form-control input-search-address input-count-character', 'data-max' => '80'],
                            ['autocomplete' => 'off']
                        );
                        echo $form->field($model, 'destination_address')->textInput($destinationAddressConfig);
                        ?>
                        <ul class="wrap-address-search" style="display: none;">
                        </ul>
                    </div>
                </div>
                <div class="hidden">
                    <input type="text" class="distance-general">
                </div>
                <?php
                if (isset($method) && $method === 'booking' && !empty($modelBooking)) {
                    if (!empty($modelBooking['service'])) {
                        $decoded = json_decode($modelBooking['service'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $model->service = $decoded[0] ?? null;
                        } else {
                            $model->service = $modelBooking['service'];
                        }
                    }
                } else {
                    if (is_string($model->service)) {
                        $decoded = json_decode($model->service, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $model->service = $decoded[0] ?? null;
                        }
                    }
                }
                ?>
                <div class="col-lg-12">
                    <label class="control-label">Dịch vụ</label>
                    <div class="custom-radio-group" style="margin-top: 8px;">
                        <?php foreach (SERVICE_LIST as $value => $label): ?>
                            <label class="custom-radio-item"
                                style="display: inline-flex; align-items: center; margin-right: 30px; font-weight: normal;">
                                <input type="radio" name="Trip[service]" value="<?= Html::encode($value) ?>"
                                    <?= ($model->service == $value) ? 'checked' : '' ?> style="margin-right: 6px;">
                                <?= Html::encode($label) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-lg-12">
                    <?php
                    $descriptionConfig = array_merge(
                        isset($method) && $method == 'booking' ? ['value' => $modelBooking['note']] : [],
                        isset($_GET['description']) ? ['value' => $_GET['description']] : [],
                        ['maxlength' => true],
                        ['class' => 'form-control input-count-character', 'data-max' => '1000']
                    );
                    echo $form->field($model, 'description')->textArea($descriptionConfig);
                    ?>
                </div>
                <div class="col-lg-12">
                    <?php
                    $notePrivateConfig = ['class' => 'form-control input-count-character', 'data-max' => '1000'];
                    echo $form->field($model, 'note_private')->textArea($notePrivateConfig)->label('Ghi chú chuyến (nội bộ)');
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?= $this->render('_form_drive', ['model' => $model, 'form' => $form]) ?>

    <?php if (Url::toRoute('') == '/trip/return') { ?>
        <div class="box box-danger ">
            <div class="box-body">
                <?= $form->field($tripReturn, 'note')->textArea() ?>
                <div class="<?= isset($model->is_collect_money) && !$model->is_collect_money ? 'hidden' : '' ?>">
                    <?= $form->field($tripReturn, 'refund')->checkbox(['class' => 'check-box-return ']) ?>
                    <div class="wrap-input-money-return <?= $tripReturn->refund == 0 ? 'hidden' : '' ?>">
                        <?= $form->field($tripReturn, 'money')->textInput(['maxlength' => true, 'class' => 'form-control input-money-return']) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>


<div class="col-md-6">
    <div class="box box-danger ">
        <div class="box-header with-border">
            <h3 class="box-title">Thông tin chuyến đi</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-3">
                    <?= $form
                        ->field($model, 'is_have_bill')
                        ->checkbox(isset($method) && $method == 'booking' ? ['value' => 1, 'checked' => $modelBooking['is_have_bill'] == 1] : (isset($_GET['is_have_bill']) && $_GET['is_have_bill'] ? ['checked' => true] : [])) ?>
                </div>
                <div class="col-lg-3">
                    <?php
                    $isServiceLoading = ($model->service == SERVICE_LOADING);
                    $initialIsChecked = isset($modelBooking['is_collect_money']) ? (bool) $modelBooking['is_collect_money'] : (isset($model->is_collect_money) ? (bool) $model->is_collect_money : true);
                    $checkboxCheckedState = !$isServiceLoading && $initialIsChecked;
                    ?>
                    <?= $form
                        ->field($model, 'is_collect_money')
                        ->checkbox([
                            'checked' => $checkboxCheckedState,
                            'disabled' => $isServiceLoading,
                            'data-initial-checked' => $initialIsChecked ? 'true' : 'false',
                        ]) ?>
                </div>
                <div class="col-lg-3">
                    <?= $form->field($model, 'display')->checkbox(['checked' => isset($model->display) && $model->display == 0 ? false : true]) ?>
                </div>
                <div class="col-lg-3">
                    <?= $form
                        ->field($model, 'is_toll_fee')
                        ->checkbox(['checked' => isset($modelBooking['is_toll_fee']) ? (bool) $modelBooking['is_toll_fee'] : (isset($model->is_toll_fee) ? (bool) $model->is_toll_fee : false)]) ?>
                </div>
                <?php
                if (isset($_POST['Trip']['pickup_time'])) {
                    // POST
                    $pickuptime = $_POST['Trip']['pickup_time'];
                } elseif (isset($method) && $method == 'booking' && !empty($modelBooking['pickup_time'])) {
                    // booking
                    $pickuptime = date_format(date_create($modelBooking['pickup_time']), 'Y-m-d H:i');
                } elseif (isset($_GET['pickup_time']) && !empty($_GET['pickup_time'])) {
                    // Call in URL
                    $pickuptime = $_GET['pickup_time'];
                } elseif (!empty($model->pickup_time)) {
                    // model/update
                    $pickuptime = date_format(date_create($model->pickup_time, new DateTimeZone('Asia/Ho_Chi_Minh')), 'Y-m-d H:i');
                } else {
                    // defaule
                    $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
                    $now->modify('+30 minutes');
                    $pickuptime = Yii::$app->session->get('pickup_time') ?? date_format($now, 'Y-m-d H:i');
                }
                $model->pickup_time = $pickuptime;
                ?>
                <div class="col-lg-6">
                    <?= $form->field($model, 'pickup_time')->textInput([
                        'maxlength' => true,
                        'class' => 'form-control date-time-picker time-general',
                        'value' => $pickuptime,
                    ]) ?>
                </div>
                <div class="col-lg-6">
                    <?php
                    $priceCustomerConfig = array_merge(isset($method) && $method == 'booking' ? ['value' => $modelBooking['price_customer']] : (isset($_GET['price']) ? ['value' => $_GET['price']] : []), [
                        'class' => 'form-control input-count-character int price-customer-general',
                        'data-max' => '10',
                        'maxlength' => '10',
                    ]);
                    echo $form->field($model, 'price_customer')->textInput($priceCustomerConfig);
                    ?>
                </div>
                <div class="col-lg-6 hidden">
                    <?php
                    echo $form->field($model, 'money_debt_agency')->textInput([
                        'class' => 'form-control input-count-character int',
                        'data-max' => '10',
                        'maxlength' => '10',
                    ]);
                    ?>
                </div>
                <div class="col-lg-6">
                    <?php
                    echo $form->field($model, 'money_customer_deposit')->textInput([
                        'class' => 'form-control input-count-character int',
                        'data-max' => '10',
                        'maxlength' => '10',
                    ]);
                    ?>
                </div>
                <div class="col-lg-6">
                    <?= $form->field($modelTripGroup, 'zalo_seller_id')->widget(Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map(
                            \app\models\GroupZaloSeller::find()
                                ->orderBy('name', 'asc')
                                ->all(),
                            'id',
                            'name'
                        ),
                        'options' => ['class' => 'action-change-seller-zalo form-control'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'placeholder' => 'Chọn người bán qua Zalo',
                        ],
                        'value' => isset($modelTripGroup->zalo_seller_id) ? $modelTripGroup->zalo_seller_id : '',
                    ]) ?>
                </div>
                <div class="col-lg-6">
                    <?php
                    $sourceTripConfig = ['prompt' => 'Chọn nguồn nhận lịch'];
                    if (isset($_GET['source_trip'])) {
                        $sourceTripConfig['options'] = [$_GET['source_trip'] => ['selected' => true]];
                    } elseif (isset($_GET['idCallBack'])) {
                        $sourceTripConfig['options'] = [isset($modelRequestCallBack->source_trip) ? $modelRequestCallBack->source_trip : SOURCE_TRIP_TYPE_MAIL_1 => ['selected' => true]];
                    } elseif (isset($method) && $method == 'booking') {
                        $sourceTripConfig['options'] = [$modelBooking['type'] => ['selected' => true]];
                    }

                    $sourceTripList = SOURCE_TRIP_TYPE_LIST;

                    if (isset($modelBooking['driver_created']) && count($modelBooking['driver_created'])) {
                        $sourceTripList[SOURCE_TRIP_TYPE_DRIVER] = 'Lái xe: ' . $modelBooking['driver_created']['display_name'] . '(' . $modelBooking['driver_created']['username'] . ')';
                    }
                    echo $form->field($model, 'source_trip')->dropDownList($sourceTripList, $sourceTripConfig);
                    ?>
                </div>
                <div class="col-lg-6 hidden" id="agency">
                    <?php
                    $agencyConfig = [
                        'data' => ArrayHelper::map(
                            Agency::find()
                                ->where(['status' => 1])
                                ->all(),
                            'id',
                            'name'
                        ),
                        'options' => ['prompt' => 'Chọn nguồn đại lý'],
                    ];
                    if (isset($_GET['agency_id'])) {
                        $agencyConfig['value'] = $_GET['agency_id'];
                    } elseif (isset($method) && $method == 'booking') {
                        $agencyConfig['value'] = $modelBooking['agency_id'];
                    }
                    echo $form->field($model, 'agency_id')->widget(Select2::class, $agencyConfig);
                    ?>
                </div>
                <div class="col-lg-6 hidden">
                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form
                                ->field($model, 'voucher')
                                ->textInput([
                                    'value' => isset($_POST['Trip']['voucher'])
                                        ? $_POST['Trip']['voucher']
                                        : (isset($model['voucher'])
                                            ? $model['voucher']
                                            : (isset($modelBooking['voucher'])
                                                ? $modelBooking['voucher']
                                                : (isset($_GET['voucher'])
                                                    ? strtoupper($_GET['voucher'])
                                                    : ''))),
                                    'class' => 'check-voucher form-control',
                                ]) ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group field-trip-price-voucher">
                                <label class="control-label" for="trip-price-voucher">Giá đã giảm</label>
                                <input type="text" id="trip-price-voucher" class="form-control int" readonly
                                    name="Trip[price-voucher]" value="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <?= $form
                        ->field($model, 'customer_property')
                        ->inline()
                        ->radioList(CUSTOMER_PROPERTY_LIST, [
                            'value' =>
                            $_POST['Trip']['customer_property']
                                ?? $modelBooking['customer_property']
                                ?? (isset($_GET['customer_property']) ? (int) $_GET['customer_property'] : null)
                                ?? ($model->customer_property !== '' ? (int) $model->customer_property : null),
                        ])
                    ?>
                </div>
                <div
                    class="col-lg-6  wrap-sell-zalo <?php echo isset($modelTripGroup->zalo_seller_id) && !empty($modelTripGroup->zalo_seller_id) ? '' : 'hidden'; ?>">
                    <?php
                    $categories = \app\models\GroupZaloCatalogue::find()
                        ->where(['status' => '1'])
                        ->orderBy('name', 'asc')
                        ->all();
                    $items = \app\models\GroupZalo::find()
                        ->where(['status' => '1'])
                        ->all();
                    $options = [];
                    foreach ($categories as $category) {
                        $groupOptions = \yii\helpers\ArrayHelper::map(
                            array_filter($items, function ($item) use ($category) {
                                return $item->group_zalo_catalogue == $category->id;
                            }),
                            'id',
                            'name'
                        );
                        if (!empty($groupOptions)) {
                            $options[$category->name] = $groupOptions;
                        }
                    }
                    ?>
                    <?= $form->field($modelTripGroup, 'group_zalo_id')->widget(Select2::classname(), [
                        'data' => $options,
                        'options' => ['class' => 'action-change-group-zalo form-control '],
                    ]) ?>
                </div>
            </div>
            <?php echo $this->render('_form_trip_group', ['modelTripGroup' => $modelTripGroup, 'form' => $form]); ?>
        </div>
    </div>

    <div class="box box-danger wrap-price-bid">
        <div class="box-header with-border">
            <h3 class="box-title">Giá bán</h3>
        </div>
        <?php $optionBid = ['maxlength' => true, 'class' => 'form-control date-time-picker']; ?>
        <div class="box-body">
            <div class="d-block">
                <div class="bid-wrap">
                    <div class="row">
                        <div class="col-lg-12">
                            <?= $form->field($model, 'no_auto_price')->checkbox(['checked' => isset($model->is_auto_price) ? (bool) !$model->is_auto_price : false]) ?>
                        </div>
                        <div class="col-lg-6">
                            <div class="bid-time-area bid-area">
                                <?php $model->sell_start_time = date_format(date_create($model->sell_start_time, new DateTimeZone('Asia/Ho_Chi_Minh')), 'Y-m-d H:i'); ?>
                                <div class="bid-end-time-picker">
                                    <?= $form->field($model, 'sell_start_time')->textInput($optionBid) ?>
                                    <label class="hidden" id="error-start-time"
                                        style="color: red;font-weight: normal ">Vui lòng chọn giờ trong tương
                                        lai</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="bid-buynow-area">
                                <?php
                                $defaultValue = 0;
                                if (isset($modelBooking['price_bid'])) {
                                    $defaultValue = $modelBooking['price_bid'];
                                } elseif (isset($model->price_bid)) {
                                    $defaultValue = $model->price_bid;
                                }
                                echo $form
                                    ->field($model, 'price_bid')
                                    ->textInput(['class' => 'form-control int ' . (isset($modelBooking['price_bid']) && $modelBooking['price_bid'] > 0 ? 'booking-price-bid' : ''), 'value' => $defaultValue]);
                                ?>
                            </div>
                            <div class="hidden">
                                <?= $form->field($model, 'price_vat')->textInput(['class' => 'form-control', 'maxlength' => '10']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Xác nhận', ['class' => 'btn btn-success btn-save-trip']) ?>

        <?php if (isset($method) && $method == 'booking') { ?>
            <?= Html::button('Chờ', [
                'class' => 'btn btn-warning mb2 update-status-booking-btn-waiting',
                'data-toggle' => 'modal',
                'data-target' => '#modalWaiting',
                'data-status' => 'WAITING',
                'data-id' => isset($modelBooking['id']) ? $modelBooking['id'] : 0,
                'data-note' => isset($modelBooking['note']) ? $modelBooking['note'] : '',
                'data-pickup-time' => isset($modelBooking['pickup_time']) ? $modelBooking['pickup_time'] : '',
                'data-price-customer' => isset($modelBooking['price_customer']) ? $modelBooking['price_customer'] : 0,
                'data-price-bid' => isset($modelBooking['price_bid']) ? $modelBooking['price_bid'] : 0,
                'data-callback-id' => isset($_GET['idCallBack']) ? (int) $_GET['idCallBack'] : 0,
            ]) ?>

            <?= Html::button('Hủy lịch', [
                'class' => 'btn btn-danger mb2 update-status-booking-btn-reject',
                'data-toggle' => 'modal',
                'data-target' => '#modalReject',
                'data-status' => 'REJECT',
                'data-id' => isset($modelBooking['id']) ? $modelBooking['id'] : 0,
                'data-note' => isset($modelBooking['note']) ? $modelBooking['note'] : '',
                'data-callback-id' => isset($_GET['idCallBack']) ? (int) $_GET['idCallBack'] : 0,
            ]) ?>
        <?php } ?>

        <?php if (Url::toRoute('') == '/trip/return') { ?>
            <input type="checkbox" name="check_cancel" id="check_cancel">
            <?= Html::submitButton('Xác nhận và huỷ lịch', ['class' => 'btn btn-danger btn-save-trip', 'id' => 'cancel-button']) ?>
        <?php } ?>
    </div>

    <?= Html::hiddenInput('returnUrl', Yii::$app->request->referrer) ?>

</div>

<div class="clear" style="clear:both"></div>

<?php ActiveForm::end(); ?>
<?php if (isset($method) && $method == 'booking') {
    $reason_reject = SystemConfiguration::find()
        ->select('content')
        ->where(['keyword' => 'reason_reject'])
        ->scalar();  // Construct a new list of reasons for rejection by combining predefined constants and retrieved data
    $reason_reject = CHOOSE_REASON . '|' . $reason_reject;
    $reason_reject_array = explode('|', $reason_reject);
    $reason_reject_array[999] = ADD_TYPE_REJECT;
    echo $this->render('/statistic/modal_status', compact(['reason_reject_array']));
} ?>

<?php
$serviceLoadingValue = SERVICE_LOADING;
$script = <<<JS
        \$('#cancel-button').on('click', function(event) {
            event.preventDefault();
            \$('#check_cancel').prop('checked', true);
            \$('#form-create-trip').submit();
        });

        \$(document).on('click', function(event) {
            if (!\$(event.target).closest('#cancel-button').length) {
                \$('#check_cancel').prop('checked', false);
            }
        });

        function handleServiceChange() {
            var isServiceLoading = \$('input[name="Trip[service]"]:checked').val() == '{$serviceLoadingValue}';
            var collectMoneyCheckbox = \$('#trip-is_collect_money');
            var initialCheckedState = collectMoneyCheckbox.data('initial-checked') === true || collectMoneyCheckbox.data('initial-checked') === 'true';

            if (isServiceLoading) {
                collectMoneyCheckbox.prop('checked', false).prop('disabled', true);
            } else {
                collectMoneyCheckbox.prop('disabled', false);
                collectMoneyCheckbox.prop('checked', initialCheckedState);
            }

            if (isServiceLoading) {
                \$('#bonus-container').slideDown();
            } else {
                \$('#bonus-container').slideUp();
                \$('#bonus-container input').val('0');
            }
        }

        \$(document).ready(function () {
            var value = \$("#trip-source_trip").trigger("change");
            getPrice();
            const pickupInitVal = \$("#trip-pickup_time").val();
            if (pickupInitVal) {
                const newSellTime = calculateSellStartTime(pickupInitVal);
                \$("#trip-sell_start_time").val(newSellTime);
            }
            handleServiceChange();
            \$(document).on('change', 'input[name="Trip[service]"]', handleServiceChange);
        });

        \$("#trip-source_trip").on("change", function () {
            const selectedValue = \$(this).val();

            if (selectedValue === "5") {
                \$('#trip-customer_property input[value="4"]').prop("checked", true);
                \$("#trip-customer_property").addClass("pointer-events-none");
            } else if (["1"].includes(selectedValue)) {
                \$('#trip-customer_property input[value="2"]').prop("checked", true);
                \$("#trip-customer_property").addClass("pointer-events-none");
            } else {
                \$("#trip-customer_property").removeClass("pointer-events-none");
            }
        });

        function formatDateToString(date) {
            const datetime = new Date(date);
            const year = datetime.getFullYear();
            const month = String(datetime.getMonth() + 1).padStart(2, "0");
            const day = String(datetime.getDate()).padStart(2, "0");
            const hours = String(datetime.getHours()).padStart(2, "0");
            const minutes = String(datetime.getMinutes()).padStart(2, "0");
            const now = year + "-" + month + "-" + day + " " + hours + ":" + minutes;
            return now;
        }
            \$("#trip-pickup_time").on("change", function() {
                const pickupVal = \$(this).val();
                if (!pickupVal) return;
                const newSellTime = calculateSellStartTime(pickupVal);
                \$("#trip-sell_start_time").val(newSellTime);
            });


        function calculateSellStartTime(pickupTimeStr) {
            if (!pickupTimeStr) return "";
            const pickupTime = new Date(pickupTimeStr.replace(/-/g, '/'));
            if (isNaN(pickupTime.getTime())) return "";

            const sellStart = new Date(pickupTime);
            const hour = pickupTime.getHours();

            if (hour >= 0 && hour < 9) {
                sellStart.setDate(sellStart.getDate() - 1);
                sellStart.setHours(15, 0, 0, 0);
            } else if (hour >= 9 && hour < 22) {
                sellStart.setHours(sellStart.getHours() - 4);
            } else {
                sellStart.setHours(15, 0, 0, 0);
            }
            const year = sellStart.getFullYear();
            const month = String(sellStart.getMonth() + 1).padStart(2, "0");
            const day = String(sellStart.getDate()).padStart(2, "0");
            const hours = String(sellStart.getHours()).padStart(2, "0");
            const minutes = String(sellStart.getMinutes()).padStart(2, "0");
            return `\${year}-\${month}-\${day} \${hours}:\${minutes}`;
        }

        function getZaloAjax(id, element, selection = false) {
            let selectedValue = selection == true ? element.val() : null;

            \$.ajax({
                type: "POST",
                url: "get-zalo",
                data: { id: id },
                success: function (response) {
                    let json = JSON.parse(response);
                    element.empty();
                    \$.each(json, function (key, value) {
                        if (\$.isPlainObject(value)) {
                            var optgroup = \$('<optgroup label="' + key + '"></optgroup>');
                            addOptions(optgroup, value);
                            element.append(optgroup);
                        } else {
                            var option = \$("<option></option>").attr("value", key).text(value);
                            element.append(option);
                        }
                    });

                    element.select2();

                    // Set the selected value back
                    if (selectedValue !== null) {
                        element.val(selectedValue).trigger("change");
                    }
                },
            });
        }

        if (\$(".action-change-seller-zalo").val() !== null && \$(".action-change-seller-zalo").val() !== undefined && \$(".action-change-seller-zalo").val() !== "" && \$(".action-change-seller-zalo").val().length !== 0) {
            let val = \$(".action-change-seller-zalo").val();
            let element = \$(".action-change-group-zalo");
            getZaloAjax(val, element, true);
        }

        \$(".action-change-seller-zalo").change(function () {
            let val = \$(this).val();
            let element = \$(".action-change-group-zalo");
            if (val == "" || val == null) {
                \$(".wrap-sell-zalo, .wrap-info-zalo-transfer").addClass("hidden");
                \$(".wrap-price-bid").removeClass("hidden");
                \$(".wrap-info-zalo-transfer").find("input").val("");
            } else {
                \$(".wrap-sell-zalo").removeClass("hidden");
                \$(".wrap-price-bid").find("input#trip-price_bid").val(0);
                \$(".action-change-group-zalo").trigger("change");
            }
            getZaloAjax(val, element);
        });

        function addOptions(element, data) {
            \$.each(data, function (key, value) {
                if (\$.isPlainObject(value)) {
                    var optgroup = \$('<optgroup label="' + key + '"></optgroup>');
                    addOptions(optgroup, value);
                    element.append(optgroup);
                } else {
                    var option = \$("<option></option>").attr("value", key).text(value);
                    element.append(option);
                }
            });
        }

        \$("#trip-source_trip").on("change", function () {
            var value = \$(this).val();
            document.getElementById("agency").classList.add("hidden");
            if (value == 5) {
                document.getElementById("agency").classList.remove("hidden");
            }
        });

        \$(document).on("change", ".action-change-group-zalo", function () {
            let val = \$(this).val();
            if (val > 0) {
                if (!\$(".tab-chage-type.current").hasClass("current")) \$(".type-0").trigger("click");
                \$(".wrap-info-zalo-transfer").removeClass("hidden");
                \$(".wrap-price-bid").addClass("hidden");
            } else {
                \$(".wrap-info-zalo-transfer").addClass("hidden");
                \$(".wrap-price-bid").removeClass("hidden");
            }
        });

        \$('#trip-sell_start_time').on('change', function() {
            let datetime = new Date();
            let year = datetime.getFullYear();
            let month = String(datetime.getMonth() + 1).padStart(2, '0');
            let day = String(datetime.getDate()).padStart(2, '0');
            let hours = String(datetime.getHours()).padStart(2, '0');
            let minutes = String(datetime.getMinutes()).padStart(2, '0');
            let now = year+'-'+month+'-'+day +' '+hours+':'+minutes;
            if(\$('#trip-sell_start_time').val() >= now) {
                \$('#trip-sell_start_time').parent().removeClass('has-error');
                \$('#trip-sell_start_time').parent().find('p').remove();
            }
        });

        \$("form").submit(function () {
            \$(this).find(":submit").prop("disabled", true);
        });

        \$(document).on("change", "#form-create-trip input, #form-create-trip select, #form-create-trip textarea", function () {
            \$(".btn-save-trip").prop("disabled", false);
        });

        \$(".check-box-return").change(function () {
            \$(".wrap-input-money-return").toggleClass("hidden");
        });
        \$(".btn-copy-pickup-address").click(function () {
            var pickupAddress = \$("#trip-pickup_address").val();
            \$("#trip-area").val(pickupAddress);
        });
        \$(document).on("click", ".btn-reverse", function () {
            let pickupAddress = \$("#trip-pickup_address").val();
            let destinationAddress = \$("#trip-destination_address").val();
            let temp = pickupAddress;
            pickupAddress = destinationAddress;
            destinationAddress = temp;
            \$("#trip-pickup_address").val(pickupAddress);
            \$("#trip-area").val(pickupAddress);
            \$("#trip-destination_address").val(destinationAddress);
        });

        \$(document).on("change", "#trip-sell_start_time, #trip-type_of_car, #trip-round_trip, #trip-price_customer, #trip-pickup_time, #trip-money_debt_agency", function () {
            getPrice();
        });

        function getPrice() {
            if ( window.location.pathname != "/trip/return" && \$(".booking-price-bid").length == 0) {
                let radios = document.querySelectorAll('input[name="Trip[round_trip]');
                let schedule = 0;
                for (const radio of radios) {
                    if (radio.checked) {
                        schedule = radio.value;
                        break;
                    }
                }
                let typeOfCar = \$("#trip-type_of_car").val();
                let pickUpTime = \$("#trip-pickup_time").val();
                let priceCustomer = \$("#trip-price_customer").val();
                let priceDeptAgency = \$("#trip-money_debt_agency").val();
                let priceCustomerNumber = priceCustomer.replace(/\./g, "");
                let priceDeptAgencyNumber = priceDeptAgency.replace(/\./g, "");
                \$.ajax({
                    url: "/trip/get-price-bid",
                    type: "post",
                    data: {
                        type_of_car: typeOfCar,
                        pickup_time: pickUpTime,
                        schedule: schedule,
                    },
                    success: function (response) {
                        let data = JSON.parse(response);
                        if (data > 0) {
                            let priceBid = priceCustomerNumber - priceDeptAgencyNumber - data;
                            if (typeof priceBid === "number" && priceBid > 0 && schedule != 3 && schedule != 4) {
                                \$("#trip-price_bid").val(priceBid);
                                \$("#trip-price_bid").trigger("change");
                            }
                        }
                    },
                });
            }
        }
    JS;
$this->registerJs($script);
?>
