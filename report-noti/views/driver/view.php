<?php

use app\helpers\MyStringHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $model app\models\Driver */

$this->title = 'Tài xế: ' . $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Danh sách tài xế', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="driver-view">

	<p>
		<?= Html::a('Sửa', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
		<?= Html::a('Xóa', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
	</p>

	<?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'created_on',
            'modified_on',
            'display_name',
            'password',
            'username',
            [
                'attribute' => 'driver_ban',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->driver_ban == 1 ? 'yes' : 'no';
                },
            ],
            'car_id',
            'money:integer',
        ],
    ]) ?>
	<?php $payHistoryList = $payHistory->getModels(); ?>
	<label>Lịch sử nạp tiền</label>
	<table class="table table-striped table-bordered" style="background: #fff;">
		<thead>
			<tr>
				<th class="text-center">Thời gian tạo</th>
				<th class="text-center text-nowrap">Mô tả</th>
				<th class="text-center">Số tiền (VNĐ)</th>
				<th class="text-center">Số điện thoại</th>
				<th class="text-center">Nội dung chuyển khoản</th>
				<th class="text-center">Tiền trước CK</th>
				<th class="text-center">Tiền sau CK</th>
				<th class="text-center">Loại</th>
				<th class="text-center">Trạng thái</th>
				<th class="text-center">Ngân hàng</th>
			</tr>
		</thead>
		<tbody>
			<?php
            if (isset($payHistoryList) && is_array($payHistoryList) && count($payHistoryList)) {
                foreach ($payHistoryList as $payItem) {
                    ?>
					<tr data-key="<?= $payItem['id'] ?>" role="row">
						<td><?= $payItem['created_on'] ?></td>
						<td><?= $payItem['description'] ?></td>
						<td><?= MyStringHelper::convertIntegerToPrice($payItem['money']) ?></td>
						<td><?= $payItem['phone'] ?></td>
						<td><?= $payItem['content_bank'] ?></td>
						<td><?= MyStringHelper::convertIntegerToPrice($payItem['money_before']) ?></td>
						<td><?= MyStringHelper::convertIntegerToPrice($payItem['money_after']) ?></td>
						<td><?= (isset($payItem['type']) ? PAY_TYPE[$payItem['type']] : '-'); ?></td>
						<td><?php
                            if (empty($payItem->admin_id_accepted) && $payItem->status == STATUS_PAY_TRANSACTION_SMS_SUCCESS) {
                                echo "<span class='text-success'>Hệ thống tự động <br> Thời gian: " . date('d/m/y H:i', strtotime($payItem->accepted_at)) . '</span>';
                            } elseif ($payItem->status == STATUS_PAY_TRANSACTION_SMS_SUCCESS && ! empty($payItem->admin_id_accepted)) {
                                echo "<span class='text-primary'>Tài khoản " . (isset($payItem->admin->username) ? $payItem->admin->username : '-') . ' <br> Thời gian: ' . date('d/m/y H:i', strtotime($payItem->accepted_at)) . '</span>';
                            } else {
                                if ($payItem->status == STATUS_PAY_TRANSACTION_SMS_SUCCESS) {
                                    echo "<span class='text-success'>" . $payItem->message . '</span>';
                                } else {
                                    echo "<span class='text-danger'>" . $payItem->message . '</span>';
                                }
                            } ?></td>
						<td><?= (isset($payItem['type_bank']) ? BANK_LIST[$payItem['type_bank']] : '-'); ?></td>
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
            $startIndex = $payHistory->getPagination()->getPage() * $payHistory->getPagination()->getPageSize() + 1;
            $endIndex = $startIndex + count($payHistory->getModels()) - 1;
            $totalCount = $payHistory->getTotalCount();

            echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries';

            ?>
		</div>
		<?=
        LinkPager::widget([
            'pagination' => $payHistory->getPagination(),
            'prevPageLabel' => 'Previous',
            'nextPageLabel' => 'Next',
            'options' => ['class' => 'pagination', 'style' => 'margin:0'],
            'linkOptions' => ['class' => 'page-link'],
            'maxButtonCount' => 5,
        ]);
        ?>
	</div>
</div>
