<?php

use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="trip-search">
    <?php $form = ActiveForm::begin([
        'action' => $source == 0 ? ['index'] : ['customer-rollback'],
        'method' => 'get',
    ]); ?>
    <div class="row">
        <div class="col-lg-3 col-md-12">
        <?=
        $form->field($model, 'pickupTimeRange', [
            'options' => [
                'class' => 'drp-container form-group',
            ],
        ])->widget(
            DateRangePicker::className(),
            [
                'presetDropdown' => true,
                'hideInput' => true,
                'startAttribute' => 'pickupTimeStart',
                'endAttribute' => 'pickupTimeEnd',
                'pluginOptions' => [
                    'locale' => ['format' => 'Y-MM-DD'],
                ],
            ]
        );
        ?>
        </div>
        <div class="col-lg-3 col-md-12">
            <?= $form->field($model, 'status')->dropDownList(
            [CUSTOMER_SERVICE_TIMES_ALL => 'Tất cả'] + STATUS_CUSTOMER_SERVICE_LIST
        ) ?>
        </div>
        <div class="col-lg-3 col-md-12">
            <?= $form->field($model, 'userid_created')->widget(Select2::classname(), [
                'data' => $adminList,
                'language' => 'vi',
                'options' => ['placeholder' => 'Chọn người phụ trách...'],
                'pluginOptions' => [],
            ])->label('Người phụ trách'); ?>
        </div>
        <div class="col-lg-3 col-md-12">
            <?= $form->field($model, 'keyword')->textInput()->label('Từ khóa') ?>
        </div>
        <div class="col-lg-3 col-md-12">
            <?= $form->field($model, 'times')->checkboxList(TIMES_LIST)->label('Số lần sử dụng') ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>