<?php

use app\helpers\MyStringHelper;
use app\models\Driver;
use yii\widgets\LinkPager;

$this->title = 'Lịch sử lái xe';
$this->params['breadcrumbs'][] = $this->title;

$modelDriver = new Driver();
$this->registerJsFile('/js/pages/driver.js', ['depends' => [\yii\web\YiiAsset::class]]);
?>
<div class="driver-index">
	<div class="box box-green" style="margin-bottom: 20px; border-top: 0;">
		<div class="box-header with-border">
			<h3 class="box-title">Filter</h3>
			<div class="box-tools pull-right">
				<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
				<button type="button" class="btn btn-box-tool" data-widget="remove"><i
						class="fa fa-remove"></i></button>
			</div>
		</div>
		<div class="box-body">
			<?php echo $this->render('_history_search'); ?>
		</div>
	</div>
	<?php $historyList = $dataProvider->getModels(); ?>
	<table id="datatables_w0" class="table table-striped table-bordered" style="background: #fff;">
		<thead>
			<tr>
				<th>Chuyến xe</th>
				<th>Khách hàng</th>
				<th class="text-center">Giá báo khách</th>
				<th>Giá bid</th>
				<th class="text-center">Trạng thái</th>
				<th class="text-center">Nguồn</th>
				<th>Lái xe</th>
				<th>Số tiền giao dịch</th>
				<th class="text-center">Tiền trước CK</th>
				<th class="text-center">Tiền sau CK</th>
				<th>Loại giao dịch</th>
			</tr>
		</thead>
		<tbody>
			<?php
            if (isset($historyList) && is_array($historyList) && count($historyList)) {
                foreach ($historyList as $historyItem) {
                    ?>
					<tr role="row">
						<td style="max-width: 300px">
							<?php if ($historyItem['source'] != 'pay_transaction') { ?>
								<div><span class="text-danger">
										<?= date('d/m/Y H:i', strtotime($historyItem['pickup_time'])) ?>
									</span></div>
								<div>
									<span class="text-primary">
										<?= $historyItem['pickup_address'] ?>
									</span>
									<span style="font-size: 15px;">➜</span>
									<span class="text-danger">
										<?= $historyItem['destination_address'] ?>
									</span>
								</div>
								<div class="text-bold">
									<span class="text-success">(
										<?= isset(SCHEDULE_LIST_TRIP[$historyItem['round_trip']]) ? SCHEDULE_LIST_TRIP[$historyItem['round_trip']] : '' ?>)
									</span>
									<?= ($historyItem['is_have_bill'] ? ' - <span class="text-primary">(Hóa đơn)</span>' : '') ?>
									<?= ($historyItem['is_collect_money'] ? ' - <span class="text-danger">(Thu tiền)</span>' : ' - <span class="text-danger">(Không thu tiền)</span>') ?>
								</div>
								<?php if (! empty($historyItem['description'])) { ?>
									<div class="text-left">Mô tả: <span>
											<?= $historyItem['description'] ?>
										</span></div>
								<?php } ?>
								<div>
									Loại xe:
									<span class="text-primary">
										<?= isset(TYPE_OF_CAR_LIST[$historyItem['type_of_car']]) ? TYPE_OF_CAR_LIST[$historyItem['type_of_car']] : 'Không xác định' ?>
									</span>
								</div>
							<?php } else { ?>
								-
							<?php } ?>
						</td>
						<td>
							<?php if ($historyItem['source'] != 'pay_transaction') { ?>
								<div>
									Tên:
									<span class="text-primary">
										<?= $historyItem['customer_name'] ?>
									</span>
								</div>
								<div>
									SĐT:
									<span class="text-primary">
										<?= $historyItem['customer_phone'] ?>
									</span>
								</div>
							<?php } else { ?>
								-
							<?php } ?>
						</td>
						<td class="text-center text-bold">
							<?php if ($historyItem['source'] != 'pay_transaction') { ?>
								<?= MyStringHelper::convertIntegerToPrice((isset($historyItem['price_customer']) ? $historyItem['price_customer'] : 0)) ?>₫
							<?php } else { ?>
								-
							<?php } ?>
						</td>
						<td class="text-center text-bold">
							<?php if ($historyItem['source'] != 'pay_transaction') { ?>
								<?= MyStringHelper::convertIntegerToPrice((isset($historyItem['price_bid']) ? $historyItem['price_bid'] : 0)) ?>₫
							<?php } else { ?>
								-
							<?php } ?>
						</td>
						<td class="text-center">
							<?php if ($historyItem['source'] != 'pay_transaction') { ?>
								<?php
                                $html = '';
                                if ($historyItem['status'] == STATUS_TRIP_OPEN && $historyItem['sell_start_time'] > gmdate('Y-m-d H:i:s', time() + 7 * 3600)) {
                                    $html .= '<div><span class="text-primary">' . STATUS_TRIP[STATUS_TRIP_CREATE] . '</span></div>';
                                } elseif ($historyItem['status'] == STATUS_TRIP_EXPIRE) {
                                    $html .= '<div><span class="text-danger text-bold">' . STATUS_TRIP[$historyItem['status']] . '</span></div>';
                                } else {
                                    $html .= '<div><span class="text-primary">' . (isset(STATUS_TRIP[$historyItem['status']]) ? STATUS_TRIP[$historyItem['status']] : '') . '</span></div>';
                                }
                                echo $html;
                                ?>
							<?php } else { ?>
								-
							<?php } ?>
						</td>
						<td class="text-center">
							<?php if ($historyItem['source'] != 'pay_transaction') { ?>
								<div>
									<div class="text-info"><?php echo isset(SCHEDULE_LIST_TRIP[$historyItem['round_trip']]) ? SCHEDULE_LIST_TRIP[$historyItem['round_trip']] : '' ?></div>
									<span class='text-primary'>
										<?= isset(SOURCE_TRIP_TYPE_LIST[$historyItem['source_trip']]) ? SOURCE_TRIP_TYPE_LIST[$historyItem['source_trip']] : '' ?>
									</span>
									<?php
                                    if ($historyItem['source_trip'] == SOURCE_TRIP_TYPE_DRIVER) {
                                        $driver = Driver::findOne(['id' => $historyItem['driver_id_created']]);
                                        echo "<div class='text-success'>" . (isset($driver->display_name) ? $driver->display_name : '') . ' </div>';
                                        echo "<div class='text-success'>" . (isset($driver->username) ? $driver->username : '') . ' </div>';
                                    }
                                    ?>
								</div>
							<?php } else { ?>
								-
							<?php } ?>
						</td>
						<td>
							<div>
								Tên:
								<span class="text-success">
									<?= $historyItem['display_name'] ?>
								</span>
							</div>
							<div>
								SĐT:
								<span class="text-success">
									<?= $historyItem['username'] ?>
								</span>
							</div>
						</td>
						<td>
							Số tiền giao dịch: <span class="text-success"><?= MyStringHelper::convertIntegerToPrice(abs($historyItem['money_after'] - $historyItem['money_before'])) ?></span>
						</td>
						<td class="text-center">
							<span class="text-danger"><?= MyStringHelper::convertIntegerToPrice($historyItem['money_before']) ?></span>
						</td>
						<td class="text-center">
							<span class="text-danger"><?= MyStringHelper::convertIntegerToPrice($historyItem['money_after']) ?></span>
						</td>
						<td>
							<div class="text-bold">
								<?php if ($historyItem['source'] == 'pay_transaction') { ?>
									Nạp tiền hệ thống
								<?php } elseif ($historyItem['source'] == 'bid') { ?>
									Mua chuyến
								<?php } elseif ($historyItem['source'] == 'trip_return') { ?>
									Trả lịch
								<?php } else { ?>
									-
								<?php } ?>
							</div>
							<?= $historyItem['created_on'] ?>
						</td>
					</tr>
				<?php
                }
            } else { ?>
				<tr>
					<td colspan="100%"><span class="text-danger">Không có dữ liệu phù hợp...</span></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="d-flex" style="justify-content: space-between;">
		<div>
			<?php
            $startIndex = $dataProvider->getPagination()->getPage() * $dataProvider->getPagination()->getPageSize() + 1;
            $endIndex = $startIndex + count($dataProvider->getModels()) - 1;
            $totalCount = $dataProvider->getTotalCount();
            echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries';
            ?>
		</div>
		<?= LinkPager::widget([
            'pagination' => $dataProvider->getPagination(),
            'prevPageLabel' => 'Previous',
            'nextPageLabel' => 'Next',
            'options' => ['class' => 'pagination', 'style' => 'margin:0'],
            'linkOptions' => ['class' => 'page-link'],
            'maxButtonCount' => 5,
        ]) ?>
	</div>
</div>
