<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SearchPayTransaction */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pay-transaction-search">

    <?php $form = ActiveForm::begin([
        //'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'createTimeRange', [
        'options' => ['class' => 'drp-container form-group', 'style' => 'max-width: 300px;'],
    ])->widget(
        DateRangePicker::className(),
        [
            'presetDropdown' => true,
            'hideInput' => true,
            'startAttribute' => 'createTimeStart',
            'endAttribute' => 'createTimeEnd',
            'pluginOptions' => [
                'locale' => ['format' => 'Y-MM-DD'],
            ],

        ]
    ); ?>

    <?php // echo $form->field($model, 'driver_id')
    ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>