<?php

use kartik\select2\Select2;
use yii\widgets\ActiveForm;

?>
<div class="modal fade" id="modalReject" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php $form = ActiveForm::begin(['method' => 'post', 'id' => 'form-update-status-driver']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Cập nhật trạng thái</h4>
            </div>
            <div class="modal-body">
                <?= $form->field($modelDriver, 'reason')->widget(Select2::classname(), [
                    'data' => $reason_reject_array,
                    'options' => ['placeholder' => 'Chọn lý do'],
                    'pluginOptions' => [
                        'class' => 'type-reject-driver-modal',
                    ],
                ]); ?>
                <div class="note-driver-modal hidden">
                    <?= $form->field($modelDriver, 'reason')->textarea(['maxlength' => true, 'class' => 'form-control input-reason-lock', 'disabled' => true]) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary btn-submit-status">Save changes</button>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>