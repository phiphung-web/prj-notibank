<?php

use app\models\Agency;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->registerJsFile('/js/pages/booking.js', [
    'depends' => [\yii\web\YiiAsset::class],
    'position' => \yii\web\View::POS_END,
]);
$this->registerJsFile('/js/pages/booking.js', ['depends' => [\yii\web\YiiAsset::class]]);
$role = Yii::$app->controller->roleCurrentUser;
$user = Yii::$app->user->identity;
?>
<style>
    .btn-copy-pickup-address{
        right: 0px;
        bottom: 0px;
        position: absolute;
    }
    .pointer-events-none {
        pointer-events: none;
    }
    .btn-reverse{
        left: 50px;
        top: -5px;
        position: absolute;
        z-index: 1000;
    }
</style>
<div class="driver-form trip-create">
    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-6 col-sm-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Thông tin đặt lịch</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-6">
                        <?= $form->field($model, 'customer_name')->textInput([
                            'value' => (isset($_POST['Booking']['customer_name']) ? $_POST['Booking']['customer_name'] : (isset($model['customer_name']) ? $model['customer_name'] : '')),
                        ]) ?>
                    </div>
                    <div class="col-lg-6">
                        <?= $form->field($model, 'customer_phone')->textInput([
                            'value' => isset($_POST['Booking']['customer_phone']) ? $_POST['Booking']['customer_phone'] : (isset($model['phone']) ? $model['phone'] : (isset($model['customer_phone']) ? $model['customer_phone'] : '')),
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 type-of-car-general">
                        <?= $form->field($model, 'type_of_car')->dropDownList(
                            TYPE_OF_CAR_LIST,
                            isset($model['type_of_car']) ? ['options' => [$model['type_of_car'] => ['selected' => true]]] : []
                        ) ?>
                    </div>
                    <div class="col-lg-6 schedule-general">
                        <?php echo $form->field($model, 'round_trip')->inline()->radioList(SCHEDULE_LIST_TRIP, (isset($_GET['round_trip']) ? ['value' => $_GET['round_trip']] : (isset($model->round_trip) ? ['value' => $model->round_trip] : ['value' => 0]))); ?>
                    </div>
                </div>
                <div class="position-relative">
                    <button type="button" class="btn btn-box-tool btn-reverse"><i class="fa fa-retweet"></i></button>
                    <?= $form->field($model, 'pickup_address')->textInput([
                        'value' => isset($_POST['Booking']['pickup_address']) ? $_POST['Booking']['pickup_address'] : (isset($model['pickup_address']) ? $model['pickup_address'] : ''),
                        'class' => 'form-control input-search-address',
                        'autocomplete' => 'off',
                    ]) ?>
                    <ul class="wrap-address-search" style="display: none;">
                    </ul>
                    <button class="btn btn-primary btn-copy-pickup-address" type="button"><i class="fa fa-copy" aria-hidden="true"></i></button>
                </div>
                <div>
                <?php
                $areaConfig = array_merge(
                        isset($_GET['area']) ? ['value' => $_GET['area']] : (isset($data_customer['area']) ? ['value' => $data_customer['area']] : []),
                        ['maxlength' => true]
                    );
                echo $form->field($model, 'area')->textInput($areaConfig);
                ?>
                </div>
                <div class="position-relative">
                    <?= $form->field($model, 'destination_address')->textInput([
                        'value' => isset($_POST['Booking']['destination_address']) ? $_POST['Booking']['destination_address'] : (isset($model['destination_address']) ? $model['destination_address'] : ''),
                        'class' => 'form-control input-search-address',
                        'autocomplete' => 'off',
                    ]) ?>
                    <ul class="wrap-address-search" style="display: none;">
                    </ul>
                </div>
                <div class="hidden">
                    <input type="text" class="distance-general">
                </div>
                <?php
                if (is_string($model->service)) {
                    $decoded = json_decode($model->service, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $model->service = $decoded[0] ?? null;
                    }
                }
                ?>
                <div class="position-relative">
                    <label class="control-label">Dịch vụ</label>
                    <div class="custom-radio-group" style="margin-top: 8px;">
                        <?php foreach (SERVICE_LIST as $value => $label): ?>
                            <label class="custom-radio-item" style="display: inline-flex; align-items: center; margin-right: 30px; font-weight: normal;">
                                <input type="radio"
                                    name="Booking[service]"
                                    value="<?= Html::encode($value) ?>"
                                    <?= ($model->service == $value) ? 'checked' : '' ?>
                                    style="margin-right: 6px;">
                                <?= Html::encode($label) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?= $form->field($model, 'note')->textarea([
                    'value' => isset($_POST['Booking']['note']) ? $_POST['Booking']['note'] : (isset($model['note']) ? $model['note'] : ''),
                ]) ?>

            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Thông tin chuyến xe</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-4">
                        <?= $form->field($model, 'is_have_bill')->checkbox([
                            'checked' => isset($_GET['is_have_bill']) ? (bool)$_GET['is_have_bill'] : (Yii::$app->request->isPost ?
                                (isset($_POST['Booking']['is_have_bill']) && $_POST['Booking']['is_have_bill']) ?? false : (isset($model['is_have_bill']) && $model['is_have_bill'] ?? false)),
                        ]) ?>
                    </div>
                    <div class="col-lg-4">
                        <?= $form->field($model, 'is_collect_money')->checkbox([
                            'checked' => isset($_GET['is_collect_money']) ? $_GET['is_collect_money'] : (Yii::$app->request->isPost ?
                                (isset($_POST['Booking']['is_collect_money']) && $_POST['Booking']['is_collect_money']) ?? false : (isset($model->is_collect_money) && $model->is_collect_money == 0 ? false : true)),
                        ]) ?>
                    </div>
                    <div class="col-lg-4">
                    <?= $form
                        ->field($model, 'is_toll_fee')
                        ->checkbox(['checked' => isset($modelBooking['is_toll_fee']) ? (bool) $modelBooking['is_toll_fee'] : (isset($model->is_toll_fee) ? (bool) $model->is_toll_fee : false)]) ?>
                    </div>
                    <div class="col-lg-6">
                        <?php
                        $dateTime = date_create($model->pickup_time, new DateTimeZone('Asia/Ho_Chi_Minh'));
                        if (isset($model['pickup_time'])) {
                            $dateTime = date_create($model['pickup_time']);
                        }
                        if (isset($_GET['pickup_time'])) {
                            $model->pickup_time = $_GET['pickup_time'];
                        } else {
                            $model->pickup_time = date_format($dateTime, 'Y-m-d H:i');
                        }
                        ?>

                        <?= $form->field($model, 'pickup_time')->textInput(['maxlength' => true, 'class' => 'form-control date-time-picker time-general']) ?>
                    </div>
                    <div class="col-lg-6">
                        <?php
                        $priceCustomerConfig = array_merge(isset($_GET['price_customer']) ? ['value' => $_GET['price_customer']] : [], ['class' => 'form-control input-count-character int price-customer-general', 'data-max' => '10', 'maxlength' => '10']);
                        echo $form->field($model, 'price_customer')->textInput($priceCustomerConfig);
                        ?>
                    </div>
                    <div class="col-lg-6">
                        <?php
                        $sourceTripConfig = ['prompt' => 'Chọn nguồn nhận lịch'];
                        $agencyConfig = ['prompt' => 'Chọn nguồn đại lý'];
                        if (isset($_GET['source_trip'])) {
                            $sourceTripConfig['options'] = [$_GET['source_trip'] => ['selected' => true]];
                        }

                        if (isset($role[DAI_LY_ROLE])) {
                            $sourceTripConfig['options'] = [SOURCE_TRIP_TYPE_AGENCY => ['selected' => true]];
                            $sourceTripConfig['disabled'] = true;
                            if (isset($user->agency_id) && $user->agency_id > 0) {
                                $agencyConfig['options'] = [$user->agency_id => ['selected' => true]];
                            }
                            $agencyConfig['disabled'] = true;
                        }

                        if (isset($_GET['idCallBack']) && ! empty($_GET['idCallBack'])) {
                            $sourceTripConfig['options'] = [(isset($modelRequestCallBack->source_trip) ? $modelRequestCallBack->source_trip : SOURCE_TRIP_TYPE_MAIL_1) => ['selected' => true]];
                        }

                        echo $form->field($model, 'type')->dropDownList(SOURCE_TRIP_TYPE_LIST, $sourceTripConfig);
                        ?>
                    </div>
                    <div class="col-lg-6 hidden" id="agency">
                        <?= $form->field($model, 'agency_id')->dropDownList(
                            ArrayHelper::map(
                                Agency::find()
                                    ->where(['status' => 1])
                                    ->all(),
                                'id',
                                'name'
                            ),
                            $agencyConfig
                        ) ?>
                    </div>
                    <div class="col-lg-6">
                        <?= $form->field($model, 'voucher')->textInput([
                            'value' => isset($_POST['Booking']['voucher']) ? $_POST['Booking']['voucher'] : (isset($model['voucher']) ? $model['voucher'] : (isset($model['voucher']) ? $model['voucher'] : '')),
                        ]) ?>
                    </div>
                    <div class="col-lg-6">
                        <?= $form->field($model, 'customer_property')->inline()->radioList(CUSTOMER_PROPERTY_LIST, [
                            'value' => (isset($_POST['Booking']['customer_property']) ? $_POST['Booking']['customer_property'] : (isset($_GET['customer_property']) ? strtoupper($_GET['customer_property']) : CUSTOMER_PROPERTY_NEW)),
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php if (! isset($role[DAI_LY_ROLE])) { ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Trạng thái chuyến xe</h3>
                </div>
                <div class="box-body">
                    <?= $form->field($model, 'status')->radioList(TRIP_STATUS_LIST, [
                        'value' => (isset($_POST['Booking']['status']) ? $_POST['Booking']['status'] : (isset($_GET['status']) ? strtoupper($_GET['status']) : 'CREATE')),
                    ])->label(false) ?>

                    <div class="wrap-reject <?= (isset($_POST['Booking']['status']) && $_POST['Booking']['status'] == 'REJECT' ? '' : (isset($_GET['status']) && $_GET['status'] == 'reject' ? '' : 'hidden')) ?>">
                        <?= $form->field($model, 'type_reject')->widget(\kartik\select2\Select2::classname(), [
                            'data' => isset($reason_reject_array) ? $reason_reject_array : [0 => ADD_TYPE_REJECT],
                            'options' => ['placeholder' => 'Chọn lý do', 'disabled' => false],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="form-group">
            <?= Html::submitButton('Lưu', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    <div class="clear" style="clear:both"></div>
</div>

<?php
$serviceLoadingValue = SERVICE_LOADING;
$script = <<<JS
    $('#booking-type').on('change', function() {
        if ($(this).val() === '5') {
            $('#booking-customer_property input[value="4"]').prop('checked', true);
            $('#booking-customer_property').addClass('pointer-events-none');
        } else {
            $('#booking-customer_property').removeClass('pointer-events-none');
        }
    });

    $('.btn-copy-pickup-address').click(function() {
        var pickupAddress = $('#booking-pickup_address').val();
        $('#booking-area').val(pickupAddress);
    });
     $(document).on('click', '.btn-reverse' , function(){
         let pickupAddress = $('#booking-pickup_address').val();
         let destinationAddress = $('#booking-destination_address').val();
         let temp = pickupAddress;
         pickupAddress = destinationAddress;
         destinationAddress = temp;
         $('#booking-pickup_address').val(pickupAddress);
         $('#booking-area').val(pickupAddress);
         $('#booking-destination_address').val(destinationAddress);
     });
      \$(document).ready(function () {
        });
JS;
$this->registerJs($script);
?>
