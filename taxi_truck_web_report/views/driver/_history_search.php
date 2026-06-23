<?php

use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\db\Query;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="trip-search">
	<?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['history-driver'],
        'options' => ['class' => 'form-inline'],
    ]); ?>

	<div class="row mb15 d-flex d-flex-end">
		<!-- Bộ lọc khoảng thời gian -->
		<div class="col-lg-3 col-md-12">
			<label class="app-label mr-10">Thời gian giao dịch</label>
			<?= DateRangePicker::widget([
                'name' => 'createTimeRange',
                'value' => Yii::$app->request->get('createTimeRange', date('Y-m-d') . ' - ' . date('Y-m-d')),
                'presetDropdown' => true,
                'hideInput' => true,
                'startAttribute' => 'createTimeStart',
                'endAttribute' => 'createTimeEnd',
                'options' => [
                    'required' => true,
                ],
                'pluginOptions' => [
                    'locale' => ['format' => 'YYYY-MM-DD'],
                ],
            ]); ?>
		</div>

		<div class="col-lg-3 col-md-12">
			<label class="app-label mr-10">Lái xe</label>
			<?php
            $drivers = (new Query())
                ->select(['display_name', 'username', 'bks' => 'car.bks'])
                ->from('driver')
                ->leftJoin('car', 'driver.car_id = car.id')
                ->andWhere(['driver.is_sub_driver' => DRIVER_TYPE_NORMAL])
                ->all();

            $driverOptions = ['0' => 'Chọn lái xe'];
            foreach ($drivers as $driver) {
                $driverOptions[$driver['username']] = sprintf(
                    '%s - %s (%s)',
                    $driver['display_name'] ?? 'N/A',
                    $driver['bks'] ?? 'N/A',
                    $driver['username']
                );
            }
            ?>
			<?= Select2::widget([
                'name' => 'username',
                'value' => Yii::$app->request->get('username', '0'),
                'data' => $driverOptions,
                'language' => 'vi',
                'options' => ['placeholder' => 'Chọn lái xe ...'],
                'pluginOptions' => ['allowClear' => true],
            ]); ?>
		</div>

		<div class="col-lg-3 col-md-12">
			<label class="app-label mr-10">Hiện giá</label>
			<input type="checkbox" class="option-input checkbox" name="driver-price" />
		</div>
	</div>

	<div class="form-group">
		<?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>
