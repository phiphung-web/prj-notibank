<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
?>

<div class="trip-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'createTimeRange')->widget(DateRangePicker::class, [
                'presetDropdown' => true,
                'hideInput' => true,
                'startAttribute' => 'createTimeStart',
                'endAttribute' => 'createTimeEnd',
                'pluginOptions' => [
                    'locale' => ['format' => 'Y-MM-DD'],
                ],
            ]); ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'keyword')->textInput() ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>