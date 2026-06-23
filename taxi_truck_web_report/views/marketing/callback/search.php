<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="booking-search">
    <?php $form = ActiveForm::begin([
        'action' => ['callback'],
        'method' => 'get',
    ]); ?>

    <div class="d-flex">
        <?= $form->field($model, 'createTimeRange', [
            'options' => ['class' => 'drp-container form-group', 'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px'],
        ])->widget(
            DateRangePicker::class,
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
    </div>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>