<?php

use app\models\Customer;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Customer */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile('/datetimepicker/jquery.datetimepicker.full.min.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerCssFile('/datetimepicker/jquery.datetimepicker.css');
?>

<div class="customer-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Thông tin khách hàng</h3>
        </div>
        <div class="box-body">
            <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

            <?php
            $rankOption = [
                Customer::RANK_SILVER => 'Hạng Bạc',
                Customer::RANK_GOLD => 'Hạng Vàng',
                Customer::RANK_PLATINUM => 'Hạng Bạch Kim',

            ];

            $rankConfig = [
                'prompt' => 'Chọn Hạng',
            ];
            ?>
            <?= $form->field($model, 'rank')->dropDownList(
                $rankOption,
                $rankConfig
            ) ?>

            <?= $form->field($model, 'birthday')->textInput(['maxlength' => true, 'class' => 'form-control date-time-picker']) ?>

            <?php
            $genderOption = [
                Customer::MALE => 'Nam',
                Customer::FEMALE => 'Nữ',
            ];

            $genderConfig = [
                'prompt' => 'Giới tính',
            ];
            ?>
            <?= $form->field($model, 'gender')->dropDownList(
                $genderOption,
                $genderConfig
            ) ?>

            <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>