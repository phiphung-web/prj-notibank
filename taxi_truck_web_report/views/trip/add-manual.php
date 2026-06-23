<?php

use app\models\Driver;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Bid */
/* @var $form ActiveForm */
$this->title = 'Điều lịch';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách chuyến xe', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trip-add-manual">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-6">
            <?php
                    $listDriver = Driver::find()->andWhere(['is_sub_driver' => DRIVER_TYPE_NORMAL])->all();
                    $data = [];
                    foreach ($listDriver as $driver) {
                        $data[$driver->id] = $driver->toString();
                    }
                ?>
            <?php echo $form->field($model, 'driver_id')->widget(Select2::classname(), [
                    'data' => $data,
                    'language' => 'de',
                    'options' => ['placeholder' => 'Select a state ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]); ?>
            <?= $form->field($model, 'price')->textInput(['class' => 'form-control int']) ?>
            <?= $form->field($model, 'description') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'send_zalo_message')->checkbox([
                    'checked' => true,
                    'label' => 'Gửi tin nhắn Zalo cho khách hàng',
                    'value' => 1,
                    'uncheck' => 0,
                    'id' => 'send_zalo_message_checkbox'
                ]) ?>

            <div id="zalo_disable_reason_container" style="display: none;">
                <?= $form->field($model, 'zalo_disable_reason')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Vui lòng nhập lý do không gửi tin nhắn Zalo cho khách hàng...',
                        'class' => 'form-control'
                    ])->label('Lý do không gửi tin nhắn Zalo') ?>
            </div>

            <?= $form->field($model, 'referrer')->hiddenInput(['value' => $ref])->label(false); ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div><!-- trip-add-manual -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('send_zalo_message_checkbox');
    const reasonContainer = document.getElementById('zalo_disable_reason_container');
    const reasonTextarea = reasonContainer.querySelector('textarea');

    // Function to toggle reason field visibility
    function toggleReasonField() {
        if (!checkbox.checked) {
            reasonContainer.style.display = 'block';
            reasonTextarea.required = true;
        } else {
            reasonContainer.style.display = 'none';
            reasonTextarea.required = false;
            reasonTextarea.value = '';
        }
    }

    // Initial state
    toggleReasonField();

    // Listen for checkbox changes
    checkbox.addEventListener('change', toggleReasonField);

    // Form validation before submit
    const form = checkbox.closest('form');
    form.addEventListener('submit', function(e) {
        if (!checkbox.checked && reasonTextarea.value.trim() === '') {
            e.preventDefault();
            alert('Vui lòng nhập lý do không gửi tin nhắn Zalo cho khách hàng');
            reasonTextarea.focus();
            return false;
        }
    });
});
</script>
