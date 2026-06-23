<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="voucher-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'quantity')->textInput() ?>

    <?= $form->field($model, 'value')->textInput() ?>

    <?= $form->field($model, 'type')->dropDownList(['Type 1' => 'Type 1', 'Type 2' => 'Type 2']) ?>

    <?= $form->field($model, 'is_send')->checkbox() ?>

    <?= $form->field($model, 'status')->dropDownList(['Active' => 'Active', 'Inactive' => 'Inactive']) ?>

    <?= $form->field($model, 'expired_at')->textInput(['type' => 'date']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
