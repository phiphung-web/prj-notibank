<?php

use app\helpers\MyStringHelper;
use app\models\Driver;
use kartik\select2\Select2;
use yii\widgets\LinkPager;

/* @var $dataProvider */

$driverModel = Driver::find()->joinWith(['car'])->all();
$listDrivers = ['' => 'Chọn tài xế cần nạp tiền'];
foreach ($driverModel as $driver) {
    $listDrivers[$driver->username] = $driver->display_name . ' - ' . (isset($driver->car->bks) ? $driver->car->bks : '') . '(' . $driver->username . ')';
}
$admins = array_column($adminList, 'username', 'id');
?>

<table id="datatables_w0" class="table table-striped table-bordered " width="100%" cellspacing="0" style="background: #fff;">
    <thead>
        <tr>
            <th>Tài xế</th>
            <th class="text-center">Số điện thoại</th>
            <th class="text-center">Loại nạp tiền</th>
            <th class="text-center">Số tiền</th>
            <th class="text-center">Ngân hàng</th>
            <th class="text-center">Tiền HT gốc</th>
            <th class="text-center">Tiền HT nạp</th>
            <th class="text-center">Tiền LX gốc</th>
            <th class="text-center">Tiền LX nạp</th>
            <th class="text-center">Tài khoản chấp nhận</th>
            <th style="min-width: 210px">Trạng thái</th>
            <th class="text-center">Thời gian tạo</th>
        </tr>
    </thead>

    <tbody>
        <?php
        if (isset($smsPayList) && is_array($smsPayList) && count($smsPayList)) {
            foreach ($smsPayList as $smsPayItem) {
                $isOtpTransaction = (int)$smsPayItem->type_bank === MB_ONLINE_OTP_BANK;
                $acceptedAt = ! empty($smsPayItem->accepted_at) ? $smsPayItem->accepted_at : $smsPayItem->created_on;
                ?>
                <tr data-key="<?= $smsPayItem->id ?>" role="row" style="background-color: <?php echo $smsPayItem->flag == TRANSACTION_FLAG_WARNING ? '#fff0d9' : ($smsPayItem->flag == TRANSACTION_FLAG_DANGER ? '#ffb8b8' : '') ?>">
                    <td>
                        <?php
                        if ($isOtpTransaction) {
                            echo 'OTP';
                        } elseif (! empty($smsPayItem->driver)) {
                            echo $smsPayItem->driver->display_name . ($smsPayItem->flag != 0 ? ' (' . $smsPayItem->message . ')' : '');
                        } elseif (empty($smsPayItem->driver) && $smsPayItem->money > 0) {
                            ?>
                            <div class="d-flex flex-row-mb">
                                <?php
                                echo Select2::widget([
                                    'name' => 'phone',
                                    'data' => $listDrivers,
                                    'language' => 'vi',
                                    'options' => ['placeholder' => 'Chọn tài xế cần nạp tiền', 'class' => 'select-phone-to-accept'],
                                    'pluginOptions' => [],
                                ]); ?>

                                <button class="btn btn-success btn-accept-sms">
                                    <i class="fa fa-check" aria-hidden="true"></i>
                                </button>

                                <button class="btn btn-danger btn-delete-accept-sms">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        <?php
                        } else { ?>
                            Hệ thống trừ tiền
                        <?php } ?>
                    </td>
                    <td class="text-center"><?= $smsPayItem->phone; ?></td>
                    <td class="text-center"><?= PAY_TYPE[$smsPayItem->type] ?></td>
                    <td class="text-center"><?= MyStringHelper::convertIntegerToPrice($smsPayItem->money) . ' VND'; ?></td>
                    <td class="text-center"><?= (isset(BANK_LIST[$smsPayItem->type_bank]) ? BANK_LIST[$smsPayItem->type_bank] . (isset($admins[$smsPayItem->user_id]) ? ' - ' . $admins[$smsPayItem->user_id] : '') : '-'); ?></td>
                    <td class="text-center">
                        <span class="text-danger"><?= $isOtpTransaction ? '-' : MyStringHelper::convertIntegerToPrice($smsPayItem->account_balance_before) . ' VND'; ?></span>
                    </td>
                    <td class="text-center">
                        <span class="text-success"><?= $isOtpTransaction ? '-' : MyStringHelper::convertIntegerToPrice($smsPayItem->account_balance_after) . ' VND'; ?></span>
                    </td>
                    <td class="text-center">
                        <span class="text-danger"><?= ! empty($smsPayItem->money_before) ? round($smsPayItem->money_before / 10000, 2) : 0; ?> HD</span>
                    </td>
                    <td class="text-center">
                        <span class="text-success"><?= ! empty($smsPayItem->money_after) ? round($smsPayItem->money_after / 10000, 2) : 0; ?> HD</span>
                    </td>
                    <td class="text-center"><?= ! empty($smsPayItem->admin->username) ? $smsPayItem->admin->username : 'Hệ thống'; ?></td>
                    <td>
                        <?php
                        if (empty($smsPayItem->admin_id_accepted) && $smsPayItem->status == STATUS_PAY_TRANSACTION_SMS_SUCCESS) {
                            echo "<span class='text-success'>Hệ thống tự động <br> Thời gian: " . date('d/m/y H:i', strtotime($acceptedAt)) . '</span>';
                        } elseif ($smsPayItem->status == STATUS_PAY_TRANSACTION_SMS_SUCCESS && ! empty($smsPayItem->admin_id_accepted)) {
                            echo "<span class='text-primary'>Tài khoản " . (isset($smsPayItem->admin->username) ? $smsPayItem->admin->username : '-') . ' <br> Thời gian: ' . date('d/m/y H:i', strtotime($acceptedAt)) . '</span>';
                        } else {
                            if ($smsPayItem->status == STATUS_PAY_TRANSACTION_SMS_SUCCESS) {
                                echo "<span class='text-success'>" . $smsPayItem->message . '</span>';
                            } else {
                                echo "<span class='text-danger'>" . $smsPayItem->message . '</span>';
                            }
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?= date('d/m/Y H:i', strtotime($smsPayItem->created_on)) ?>
                    </td>
                </tr>
            <?php }
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
    <?=
    LinkPager::widget([
        'pagination' => $dataProvider->getPagination(),
        'prevPageLabel' => 'Previous',
        'nextPageLabel' => 'Next',
        'options' => ['class' => 'pagination', 'style' => 'margin:0'],
        'linkOptions' => [
            'class' => 'page-link trip-pagination-item',
            'data-page' => function ($page, $label, $disabled, $active) {
                $options = [];
                $options['tag'] = 'a';
                $options['data-page'] = $page; // Thêm thuộc tính data-page

                return $options;
            },
        ],
        'maxButtonCount' => 5,
    ]);
    ?>
</div>
