<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="trip-search">
    <?php $form = ActiveForm::begin([
        'action' => ['pass-trip'],
        'method' => 'get',
    ]); ?>
    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'keyword')->textInput() ?>
        </div>
        <div class="col-lg-4">
            <?= $form->field($model, 'payment_status')->dropDownList([
                false => 'Chưa thanh toán',
                true => 'Đã thanh toán',
            ]) ?>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
                <?= Html::submitButton('Tìm kiếm', ['class' => 'btn btn-primary']) ?>
                <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>