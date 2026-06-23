<?php

use app\models\Driver;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\MessageSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="message-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'GET',
    ]); ?>


    <?php

    $listDriver = Driver::find()->andWhere(['is_sub_driver' => DRIVER_TYPE_NORMAL])->all();

    $data = ['0' => 'Tất cả'];

    foreach ($listDriver as $driver) {
        $data[$driver->username] = $driver->toString();
    }

    if (empty($model->phone)) {
        $model->phone = '0';
    }

    ?>
    <?= $form->field($model, 'phone')->widget(Select2::classname(), [
        'data' => $data,
        'language' => 'vi',
        'options' => ['placeholder' => 'Select a state ...'],
        'pluginOptions' => [
            //                'allowClear' => true
        ],
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

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
