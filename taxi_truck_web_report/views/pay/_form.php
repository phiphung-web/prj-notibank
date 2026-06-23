<?php

use app\helpers\MyStringHelper;
use app\models\Driver;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PayTransaction */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pay-transaction-form">

    <?php $form = ActiveForm::begin(); ?>


    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'money')->textInput(
        [
            'type' => 'text',
            'value' => !$model->isNewRecord
                ? MyStringHelper::convertIntegerToPrice($model->money)
                : '',
            'class' => 'form-control int-allow-negative',
            'maxlength' => '15',
        ]
    ) ?>
    <?php
    $listDriver = Driver::find()->andWhere(['is_sub_driver' => DRIVER_TYPE_NORMAL])->all();

    $data = [];

    foreach ($listDriver as $driver) {
        $data[$driver->id] = $driver->toString();
    }
    ?>

    <?= $form->field($model, 'driver_id')->widget(Select2::classname(), [
        'data' => $data,
        'language' => 'vi',
        'options' => ['placeholder' => 'Select a state ...'],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
