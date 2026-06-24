<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $reason_reject_array array */
?>

<div class="modal fade" id="modalReject" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="form-update-status-booking" action="" method="post" data-select2-id="select2-data-form-update-status-booking">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Cập nhật trạng thái: <span class="text-primary title-booking-modal">Reject</span></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group field-booking-customer_phone required">
                        <label class="control-label" for="booking-customer_phone">Số điện thoại</label>
                        <input type="text" id="booking-customer_phone" class="form-control" readonly name="customer_phone" aria-required="true">
                        <input type="hidden" id="booking-hotline" class="form-control" name="hotline">
                    </div>
                    <div class="form-group field-booking-status">
                        <label class="control-label" for="">Trạng thái</label>
                        <?= Html::dropDownList(
                            'status',
                            null,
                            [
                                'REJECT' => 'Hủy lịch',
                            ],
                            [
                                'class' => 'form-control status-booking-modal readonly',
                                'readonly' => true,
                            ]
                        ) ?>
                    </div>
                    <div class="form-group field-booking-source_trip">
                        <label class="control-label" for="">Nguồn nhận lịch</label>
                        <?= Html::dropDownList(
                            'source_trip',
                            null,
                            SOURCE_TRIP_TYPE_LIST,
                            [
                                'class' => 'form-control source_trip-booking-modal',
                            ]
                        ) ?>
                    </div>
                    <div class="form-group field-booking-type_reject">
                        <label class="control-label" for="">Loại từ chối</label>
                        <?= Html::dropDownList(
                            'type_reject',
                            null,
                            $reason_reject_array,
                            [
                                'class' => 'form-control type_reject-booking-modal readonly',
                            ]
                        ) ?>
                    </div>
                    <div class="form-group field-booking-note">
                        <label class="control-label" for="booking-note">Ghi chú</label>
                        <textarea id="booking-note" class="form-control note-booking-modal" name="note"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-save-reject">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
