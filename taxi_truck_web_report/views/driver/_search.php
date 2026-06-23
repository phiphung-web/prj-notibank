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
            $listDriver = $query
                ->select(['display_name', 'username', 'car.bks'])
                ->from('driver')
                ->leftJoin('car', 'driver.car_id = car.id')
                ->andWhere(['driver.is_sub_driver' => $model->is_sub_driver ?? DRIVER_TYPE_NORMAL])
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
            <?php
            echo $form->field($model, 'status')->label('Trạng thái')->dropDownList(
                [
                    '0' => 'Người mới',
                    '1' => 'Đang hoạt động',
                    '2' => 'Khóa',
                ],
                [
                    'prompt' => 'Tất cả',
                ]
            )
            ?>
        </div>
        <div class="col-lg-3 col-md-12">
            <?php
            echo $form->field($model, 'driver_rank')->label('Hạng')->dropDownList(
                RANK_DRIVER_LIST,
                [
                    'prompt' => 'Tất cả',
                ]
            )
            ?>
        </div>
        <div class="col-lg-3 col-md-12">
            <?php
            echo $form->field($model, 'sort')->dropDownList(
                [
                    '' => 'Mặc định',
                    'car_year asc' => 'Đời xe từ thấp đến cao',
                    'car_year desc' => 'Đời xe từ cao đến thấp',
                ]
            )
            ?>
        </div>
        <div class="col-lg-3 col-md-12">
            <?= $form->field($model, 'is_sub_driver')->dropDownList(
                [
                    DRIVER_TYPE_NORMAL => 'Tài khoản thật',
                    DRIVER_TYPE_SUB => 'Tài khoản ảo',
                ]
            )->label('Loại tài khoản') ?>
        </div>
        <div class="col-lg-3 col-md-12">
            <?= $form->field($model, 'driver_type')->dropDownList(
                [
                    'main' => 'Tài xế chính',
                    'sub' => 'Tài xế phụ',
                ],
                ['prompt' => 'Tất cả']
            )->label('Loại tài xế') ?>
        </div>
        <div class="col-lg-3 col-md-12">
            <?= $form->field($model, 'zero_balance')->checkbox() ?>
        </div>
    </div>

    <?= $form->field($model, 'driver_ban', ['options' => ['class' => 'drp-container trip-search-checkbox ', 'style' => 'margin-bottom: 10px']])->checkbox(); ?>

    <div class="form-group">
        <div class="d-flex justify-content-between">
            <div>
                <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
                <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
                <?= Html::a('Thêm mới', ['create'], ['class' => 'btn btn-success']) ?>
            </div>
            <div>
                <!-- Html::a('Cập nhật danh sách tài xế ngưng hoạt động', ['update-driver-stop-working'], ['class' => 'btn btn-danger', 'id' => 'update-driver-btn'])
                Html::a('Cập nhật tài xế VIP', ['get-driver-vip'], ['class' => 'btn btn-info', 'id' => 'update-driver-btn']) -->
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<<JS
        \$(document).ready(function() {
          \$('#update-driver-btn').click(function(event) {
            event.preventDefault();

            \$.ajax({
              url: \$(this).attr('href'),
              method: 'POST',
              success: function(response) {
                  let json = JSON.parse(response);
                if (json.message) {
                  alert(json.message);
                  window.location.reload();
                } else {
                  alert('Có lỗi xảy ra! Vui lòng thử lại.');
                }
              },
              error: function(error) {
                alert('Có lỗi xảy ra! Vui lòng thử lại.');
              }
            });
      });
    });
    JS;
$this->registerJs($script);
?>
