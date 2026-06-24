<?php

use app\models\Agency;
use kartik\datetime\DateTimePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-lg-12">
        <?= $form->field($model, 'agency_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(Agency::find()->where(['status' => 1])->all(), 'id', 'name'),
            'options' => [
                'placeholder' => 'Chọn đại lý...',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]) ?>
    </div>
    <div class="col-lg-6">
        <?= $form->field($model, 'start_date')->widget(DateTimePicker::classname(), [
            'options' => ['placeholder' => 'Chọn thời gian bắt đầu...'],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd hh:ii:ss',
                'todayHighlight' => true,
            ],
        ]) ?>
    </div>

    <div class="col-lg-6">
        <?= $form->field($model, 'end_date')->widget(DateTimePicker::classname(), [
            'options' => ['placeholder' => 'Chọn thời gian kết thúc...'],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd hh:ii:ss',
                'todayHighlight' => true,
            ],
        ]) ?>
    </div>

    <div class="col-lg-6">
        <div class="form-row relative">
            <label class="control-label text-left">
                <span>Hoa hồng (VNĐ)</span>
            </label>
            <div class="relative mb15">
                <input type="text" name="PriceSetting[price]" value="<?= isset($_POST['PriceSetting']['price']) ? htmlspecialchars($_POST['PriceSetting']['price']) : (isset($model->price) ? htmlspecialchars($model->price) : '0'); ?>" class="form-control int" placeholder="" autocomplete="off">
                <div style="" class="btn btn-white extend text-promotion-agency">VNĐ</div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-row relative">
            <label class="control-label text-left">
                <span>Hoa hồng (%)</span>
            </label>
            <div class="relative mb15">
                <input type="text" name="PriceSetting[percent]" value="<?= isset($_POST['PriceSetting']['percent']) ? htmlspecialchars($_POST['PriceSetting']['percent']) : (isset($model->percent) ? $model->percent * 100 : '100'); ?>" class="form-control" placeholder="" autocomplete="off">
                <div style="" class="btn btn-white extend text-promotion-agency">%</div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <!-- Trạng thái -->
        <?= $form->field($model, 'active')->dropDownList([
            1 => 'Kích hoạt',
            0 => 'Không kích hoạt',
        ]) ?>
    </div>
    <div class="col-lg-12">
        <div class="form-group">
            <?= Html::submitButton('Lưu', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<style>
    .relative {
        position: relative;
    }

    .mb15 {
        margin-bottom: 15px;
    }

    .text-promotion-agency {
        font-size: 13px;
        width: 60px;
        border: 1px solid #c4cdd5;
        position: absolute;
        bottom: 0;
        right: 0;
        background: #eee;
        height: 100%;
    }
</style>