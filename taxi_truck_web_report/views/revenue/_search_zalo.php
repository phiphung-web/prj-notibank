<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="group_zalo-search">

    <?php $form = ActiveForm::begin([
        'action' => ['zalo'],
        'method' => 'get',
    ]); ?>
    <div class="d-flex flex-column-mobile">
        <?= $form->field($model, 'keyword', [
            'options' => [
                'class' => 'form-group',
                'style' => 'width: calc((100% - 60px) / 4); margin-right:20px',
            ],
        ])->textInput() ?>
        <?=
        $form->field($model, 'pickupTimeRange', [
            'options' => [
                'class' => 'form-group',
                'style' => 'width: calc((100% - 60px) / 4); margin-right:20px',
            ],
        ])->widget(
            DateRangePicker::class,
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
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>