<?php

use app\helpers\MyStringHelper;
use fedemotta\datatables\DataTables;
use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\ActiveForm;

$this->title = 'Thống kê booking ' . ' từ ngày ' . date('01-m-Y') . ' đến ngày ' . date('d-m-Y');
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('/css/pages/rev-agency.css');
$this->registerJsFile('/js/pages/revenue-booking.js', ['depends' => [YiiAsset::class]]);

/* @var $searchModel app\models\Revenue */
/* @var $dataProvider */
/* @var $reasonReject */
?>

<div class="statistical-booking">
	<div class="box box-green">
		<div class="box-header with-border">
			<h3 class="box-title">Filter</h3>
			<div class="box-tools pull-right">
				<button type="button" class="btn btn-box-tool" data-widget="collapse">
					<i class="fa fa-minus"></i>
				</button>
			</div>
		</div>

		<div class="box-body">
			<div class="rev-booking-search">
				<?php $form = ActiveForm::begin([
					'action' => ['booking'],
					'method' => 'get',
					'options' => [
						'class' => 'booking-search',
					],
				]); ?>

				<div class="fields">
					<?= $form->field($searchModel, 'createTimeRange')->widget(DateRangePicker::class, [
						'presetDropdown' => true,
						'hideInput' => true,
						'startAttribute' => 'createTimeStart',
						'endAttribute' => 'createTimeEnd',
						'pluginOptions' => [
							'locale' => ['format' => 'Y-MM-DD'],
						],
					]) ?>
				</div>

				<div class="action-box">
					<?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
				</div>

				<?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
	<div class="dashboard">
		<div class="row">
			<?php foreach (SCHEDULE_LIST_TRIP as $key => $value) { ?>
				<div class="col-lg-3 col-sm-6 col-xs-12">
					<div class="small-box bg-blue">
						<div class="inner">
							<h3 class="customer-month fsc-sm-24">
								<?= (isset($statisticSchedule['total_' . $key]) ? $statisticSchedule['total_' . $key] : 0) ?>
							</h3>
							<p>Tổng số <b><?= SCHEDULE_LIST_TRIP[$key] ?></b> được tạo.</p>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="col-lg-3 col-sm-6 col-xs-12">
				<div class="small-box bg-yellow">
					<div class="inner">
						<h3 class="customer-month fsc-sm-24">
							<?= (isset($statisticRoom) ? $statisticRoom : 0) ?>
						</h3>
						<p>Tổng số lịch được pass qua room.</p>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-sm-6 col-xs-12">
				<div class="small-box bg-green">
					<div class="inner">
						<h3 class="customer-month fsc-sm-24">
							<?= (isset($statisticCustomerRollback) ? $statisticCustomerRollback : 0) ?>
						</h3>
						<p>Tổng số lịch khách quay đầu.</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="mt-10 mb15">
		<?php $form = ActiveForm::begin([
			'action' => ['booking-export'],
			'method' => 'post',
			'options' => [
				'class' => 'booking-export',
			],
		]); ?>

		<?= Html::hiddenInput('createTimeRange', '') ?>
		<?= Html::hiddenInput('createTimeStart', '') ?>
		<?= Html::hiddenInput('createTimeEnd', '') ?>

		<div class="action-box text-right hidden">
			<?= Html::submitButton('Xuất file <i class="fa fa-download ml-10"></i>', ['class' => 'btn btn-primary']) ?>
		</div>

		<?php ActiveForm::end(); ?>
	</div>
	<div class="revenue-index" style="overflow-x: scroll;">
		<div class="wrap-table" style="width: 3000px">
			<table id="datatables_w0" class="table table-striped table-bordered" cellspacing="0" style="background: #fff;">
				<thead class="bg-success">
					<tr>
						<th class="text-center w155" style="width:175px">Thời gian</th>
						<th class="text-center w155">Tổng số chuyến</th>
						<th class="text-center w155">Tổng chốt</th>
						<th class="text-center w155">Tổng điều</th>
						<th class="text-center w155">Chờ</th>
						<th class="text-center w155">Chưa xử lý</th>
						<th class="text-center w155">Hủy</th>
						<th class="text-center w155">Thu khách</th>
						<th class="text-center w155">Trả lái xe</th>
						<th class="text-center w155">Lợi nhuận</th>
						<?php foreach (SOURCE_TRIP_TYPE_LIST as $key => $value) { ?>
							<th class="text-center w155" style="<?php echo $key == SOURCE_TRIP_TYPE_MAIL_1 ? 'width: 350px' : '' ?>"><?php echo $value ?></th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($revenueList) && is_array($revenueList) && count($revenueList)) {
						foreach ($revenueList as $revenue) {
							$total_revenue = $revenue['total_trip'] + $revenue['total_booking_waiting'] + $revenue['total_booking_create'] + $revenue['total_booking_cancel'];
							$total_close = (int) ($revenue['total_trip_done'] + $revenue['total_trip_complete'] + $revenue['total_trip_create']);
							$source = json_decode($revenue['source_trip'], true);
							foreach (array_keys($total) as $key) {
								$total[$key] += $revenue[strtolower(str_replace('total', '', $key))] ?? 0;
							}
							$total = [
								'total' => $total['total'] + $total_revenue,
								'total_close' => $total['total_close'] + $total_close,
								'totalBookingWaiting' => $total['totalBookingWaiting'] + $revenue['total_booking_waiting'],
								'totalBookingCreate' => $total['totalBookingCreate'] + $revenue['total_booking_create'],
								'totalTripCreate' => $total['totalTripCreate'] + $revenue['total_trip_create'],
								'totalBookingCancel' => $total['totalBookingCancel'] + $revenue['total_booking_cancel'],
								'totalTripCancel' => $total['totalTripCancel'] + $revenue['total_trip_cancel'],
								'totalTripBookingComplete' => $total['totalTripBookingComplete'] + $revenue['total_trip_done'] + $revenue['total_trip_complete'],
								'totalCustomerPrice' => $total['totalCustomerPrice'] + $revenue['customer_price'],
								'totalDriverPrice' => $total['totalDriverPrice'] + $revenue['driver_price'],
								'totalReceive' => $total['totalReceive'] + $revenue['revenue'],
							];
							?>
							<tr>
								<td class="text-center">
									<?= $revenue['dt_date'] ?>
								</td>
								<td class="text-center text-primary text-bold">
									<?= $total_revenue ?>
								</td>
								<td class="text-center text-bold" style="color: #ffa100">
									<?= $total_close ?>
									<span style="color: #ffa100">(<?= !empty($total_revenue) ? floor(($total_close / $total_revenue) * 100) : 0 ?>%)</span>
								</td>
								<td class="text-center text-success text-bold">
									<?= (int) ($revenue['total_trip_done'] + $revenue['total_trip_complete']) ?>
									<span class="text-success">(<?= !empty($total_close) ? floor(((int) ($revenue['total_trip_done'] + $revenue['total_trip_complete']) / $total_close) * 100) : 0 ?>%)</span>
								</td>
								<td class="text-center"><?= !empty($revenue['total_booking_waiting']) ? $revenue['total_booking_waiting'] : 0 ?></td>
								<td>
									Lịch: <?= !empty($revenue['total_trip_create']) ? $revenue['total_trip_create'] : 0 ?>
									<br>
									Booking: <?= !empty($revenue['total_booking_create']) ? $revenue['total_booking_create'] : 0 ?>
								</td>
								<td>
									Lịch: <?= !empty($revenue['total_trip_cancel']) ? $revenue['total_trip_cancel'] : 0 ?>
									<br>
									Booking: <?= !empty($revenue['total_booking_cancel']) ? $revenue['total_booking_cancel'] : 0 ?>
								</td>
								<td class="text-center text-bold">
									<?= !empty($revenue['customer_price']) ? MyStringHelper::convertIntegerToPrice($revenue['customer_price']) : 0 ?>đ
								</td>
								<td class="text-center text-bold">
									<?= !empty($revenue['driver_price']) ? MyStringHelper::convertIntegerToPrice($revenue['driver_price']) : 0 ?>đ
								</td>
								<td class="text-center text-bold">
									<?= MyStringHelper::convertIntegerToPrice($revenue['revenue']) ?>đ
								</td>
								<?php
								foreach (SOURCE_TRIP_TYPE_LIST as $key => $value) {
									$totalSource[$key]['total'] += $source[$key]['total'] ?? 0;
									$totalSource[$key]['success'] += $source[$key]['success'] ?? 0;
									$totalTrip = $source[$key]['total'] ?? 0;
									$successTrip = $source[$key]['success'] ?? 0;
									$percentTrip = ($totalTrip != 0) ? floor(($successTrip / $totalTrip) * 100) : 0;
									?>

									<td style="<?= $key != SOURCE_TRIP_TYPE_MAIL_1 ? 'width: 200px' : '' ?>">
										<div class="d-flex align-items-center justify-content-between">
											<div class="text-left">
												<span class="text-primary">Tổng: <?= $totalTrip ?></span><br>
												<span class="text-success text-bold">Chốt: <?= $successTrip ?> (<?= $percentTrip ?>%)</span>
											</div>

											<?php if ($key == SOURCE_TRIP_TYPE_MAIL_1 && !empty($source[$key]['data']) && is_array($source[$key]['data'])) { ?>
												<div class="text-left" style="width: 150px">
													<?php
													foreach (SOURCE_MAIL_LIST as $keySource => $valueSource) {
														$sourceMail = isset($source[$key]['data']['source_' . $keySource]) ? $source[$key]['data']['source_' . $keySource] : 0;
														$sourceMailSuccess = isset($source[$key]['data']['source_' . $keySource . '_success']) ? $source[$key]['data']['source_' . $keySource . '_success'] : 0;
														$sourceMailEmployee = isset($source[$key]['data']['source_' . $keySource . '_employee']) ? $source[$key]['data']['source_' . $keySource . '_employee'] : 0;
														$totalMailSource["source_{$keySource}"] += $sourceMail;
														$totalMailSource["source_{$keySource}_success"] += $sourceMailSuccess;
														if ($sourceMail > 0) {
															$percentSourceMail = ($sourceMail != 0) ? floor(($sourceMailSuccess / $sourceMail) * 100) : 0;
															?>
															<div class="text-bold">
																<span>
																	<?= $valueSource ?>:
																	<span class="text-primary"><?= $keySource == SOURCE_KEY_EMPLOYEE ? $sourceMailEmployee : $sourceMail ?></span>
																	<?php
																	if ($keySource != SOURCE_KEY_EMPLOYEE):
																		?>
																</span>
																(<span class="text-success"><?= $sourceMailSuccess . ' - ' . $percentSourceMail ?>%</span>)
															<?php endif; ?>
															</div>
													<?php }
													} ?>
												</div>
											<?php } ?>
										</div>
									</td>
								<?php } ?>
							</tr>

						<?php
						}
					} else {
						?>
						<tr>
							<td colspan="100%"><span class="text-danger">Không có dữ liệu phù hợp...</span></td>
						</tr>
					<?php
					}
					?>
					<tr class="bg-primary">
						<td class="text-center text-bold">Tổng</td>
						<td class="text-center text-bold">
							<?= $total['total'] ?>
						</td>
						<td class="text-center text-bold">
							<?= $total['total_close'] ?>
							<span class="text-white">(
								<?= !empty($total['total']) ? floor(($total['total_close'] / $total['total']) * 100) : 0 ?>%)
							</span>
						</td>
						<td class="text-center text-bold">
							<?= $total['totalTripBookingComplete'] ?>
							<span class="text-white">(
								<?= !empty($total['total_close']) ? floor(($total['totalTripBookingComplete'] / $total['total_close']) * 100) : 0 ?>%)
							</span>
						</td>
						<td class="text-center text-bold"><?= $total['totalBookingWaiting'] ?></td>
						<td class="text-bold">
							Lịch: <?= $total['totalTripCreate'] ?>
							<br>
							Booking: <?= $total['totalBookingCreate'] ?>
						</td>
						<td class="text-bold">
							Lịch: <?= $total['totalTripCancel'] ?>
							<br>
							Booking: <?= $total['totalBookingCancel'] ?>
						</td>
						<td class="text-center text-bold">
							<?= MyStringHelper::convertIntegerToPrice($total['totalCustomerPrice']) ?>đ
						</td>
						<td class="text-center text-bold">
							<?= MyStringHelper::convertIntegerToPrice($total['totalDriverPrice']) ?>đ
						</td>
						<td class="text-center text-bold">
							<?= MyStringHelper::convertIntegerToPrice($total['totalReceive']) ?>đ
						</td>
						<?php
						if (isset($totalSource) && is_array($totalSource) && count($totalSource)) {
							foreach ($totalSource as $key => $value) {
								?>
								<td class="text-bold" style="<?= $key != SOURCE_TRIP_TYPE_MAIL_1 ? 'width: 200px' : '' ?>">
									<div class="d-flex align-items-center justify-content-between">
										<div class="text-left">
											Tổng: <?= $value['total'] ?>
											<br>
											Chốt: <?= $value['success'] ?> <span class="text-white">(<?= !empty($value['total']) ? floor($value['success'] / $value['total'] * 100) : 0 ?>%)</span>
										</div>
										<?php if ($key == SOURCE_TRIP_TYPE_MAIL_1 && !empty($source[$key]['data']) && is_array($source[$key]['data'])) { ?>
											<div class="text-left" style="width: 150px">
												<?php
												foreach (SOURCE_MAIL_LIST as $keySource => $valueSource) {
													$totalPercentSourceMail = ($totalMailSource["source_{$keySource}"] != 0) ? floor(($totalMailSource["source_{$keySource}_success"] / $totalMailSource["source_{$keySource}"]) * 100) : 0;
													?>
													<div class="text-bold">
														<span>
															<?= $valueSource ?>: <?= MyStringHelper::convertIntegerToPrice($totalMailSource["source_{$keySource}"]) ?>
															<?php
															if ($keySource != SOURCE_KEY_EMPLOYEE):
																?>
														</span>
														(<?= MyStringHelper::convertIntegerToPrice($totalMailSource["source_{$keySource}_success"]) . ' - ' . $totalPercentSourceMail ?>%)
													<?php endif; ?>
													</div>
												<?php
												}
												?>
											</div>
										<?php } ?>
									</div>
								</td>
						<?php }
						} ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
