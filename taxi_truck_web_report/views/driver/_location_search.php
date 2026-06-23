<?php

use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\db\Query;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model */
?>

<div class="trip-search">

  <?php $form = ActiveForm::begin([
    'method' => 'get',
  ]); ?>

  <div class="row">
    <div class="col-lg-3 col-md-12">
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
    <div class="col-lg-3 col-md-12">
      <?php
      $query = new Query();
      $listDriver = $query->select('display_name, username, car.bks')->from('driver')
          ->leftJoin('car', 'driver.car_id = car.id')
          ->all();
      $data = ['0' => 'Tất cả'];
      foreach ($listDriver as $driver) {
          $data[$driver['username']] = $driver['display_name'] . ' - ' . $driver['bks'] . '(' . $driver['username'] . ')';
      }
      ?>

      <?= $form->field($model, 'username')->widget(Select2::classname(), [
        'data' => $data,
        'language' => 'vi',
        'options' => ['placeholder' => 'Select a state ...'],
        'pluginOptions' => [],
      ]); ?>
    </div>
    <div class="col-lg-3 col-md-12">
      <?php echo $form->field($model, 'driver_rank')->label('Hạng')->dropDownList(
          RANK_DRIVER_LIST,
          [
          'prompt' => 'Tất cả',
        ]
      )
      ?>
    </div>
  </div>

  <?= $form->field($model, 'driver_ban', ['options' => ['class' => 'drp-container trip-search-checkbox ', 'style' => 'margin-bottom: 10px']])->checkbox(); ?>

  <div class="form-group">
    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
    <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    <?= Html::a('Thêm mới', ['create'], ['class' => 'btn btn-success']) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>