<?php

use app\models\Booking;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;

$this->registerJsFile('/datetimepicker/jquery.datetimepicker.full.min.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerCssFile('/datetimepicker/jquery.datetimepicker.css');

$modelBooking = new Booking();
?>

<div class="modal fade" id="modalWaiting" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php $form = ActiveForm::begin(['method' => 'post', 'id' => 'form-update-status-booking']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Cập nhật trạng thái: <span class="text-primary title-booking-modal">Waiting</span></h4>
            </div>
            <div class="modal-body">
                <?php echo $form->field($modelBooking, 'status', [
                    'options' => [
                        'class' => 'status-booking-modal readonly',
                    ],
                ])->dropDownList([
                    'WAITING' => 'Chờ khách hàng phản hồi',
                ]);

                ?>
                <?= $form->field($modelBooking, 'price_customer')->textInput(['maxlength' => true, 'class' => 'form-control int', 'autocomplete' => 'off']) ?>
                <?= $form->field($modelBooking, 'price_bid')->textInput(['maxlength' => true, 'class' => 'form-control int', 'autocomplete' => 'off']) ?>
                <?= $form->field($modelBooking, 'pickup_time')->textInput(['maxlength' => true, 'class' => 'form-control date-time-picker time-general', 'autocomplete' => 'off']) ?>
                <?= $form->field($modelBooking, 'note')->textarea(['maxlength' => true, 'class' => 'form-control note-booking-modal']) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="modal fade" id="modalReject" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php $form = ActiveForm::begin(['method' => 'post', 'id' => 'form-update-status-booking']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Cập nhật trạng thái: <span class="text-primary title-booking-modal">Reject</span></h4>
            </div>
            <div class="modal-body">
                <?php echo $form->field($modelBooking, 'status', [
                    'options' => [
                        'class' => 'status-booking-modal readonly',
                    ],
                ])->dropDownList([
                    'REJECT' => 'Hủy lịch',
                ])
                ?>
                <?= $form->field($modelBooking, 'type_reject')->widget(Select2::classname(), [
                    'data' => $reason_reject_array,
                    'options' => ['placeholder' => 'Chọn lý do'],
                    'pluginOptions' => [
                        'class' => 'type-reject-booking-modal',
                    ],
                ]); ?>
                <?= $form->field($modelBooking, 'note')->textarea(['maxlength' => true, 'class' => 'form-control note-booking-modal']) ?>
                <input type="hidden" name="idCallBack" id="idCallBack-modal">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$script = <<<JS
    $(document).on('click', '.update-status-booking-btn-waiting', function() {
        updateBookingModal('#modalWaiting', $(this));
    });
    $(document).on('click', '.update-status-booking-btn-reject', function() {
        updateBookingModal('#modalReject', $(this));
    });

    function updateBookingModal(modalId, element) {
        var id = element.attr('data-id');
        var note = element.attr('data-note');
        var type = element.attr('data-type');
        var pickup_time = element.attr('data-pickup-time');
        var price_customer = element.attr('data-price-customer');
        var price_bid = element.attr('data-price-bid');
        var idCallBack = element.attr('data-callback-id');
        
        $(modalId).find('form')[0].reset();
        $('.note-booking-modal').val(note);

        if (type !== undefined) {
            $('.type-reject-booking-modal').val(type);
        }

        if (price_customer !== undefined) {
            var formattedPriceCustomer = parseInt(price_customer).toLocaleString('vi-VN');
            $('#booking-price_customer').val(formattedPriceCustomer);
        }

        if (price_bid !== undefined) {
            var formattedPriceBid = parseInt(price_bid).toLocaleString('vi-VN');
            $('#booking-price_bid').val(formattedPriceBid);
        }

        if (pickup_time !== undefined) {
            $('#booking-pickup_time').val(pickup_time);
        }

        if(idCallBack > 0) $('#idCallBack-modal').val(idCallBack);
        $(modalId).find('form').attr('action', '/statistic/update-status/' + id);
    }
JS;
$this->registerJs($script);
?>