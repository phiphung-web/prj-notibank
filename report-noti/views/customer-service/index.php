<?php

use app\helpers\MyHelper;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = 'Danh sách chăm sóc khách hàng';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('/js/pages/customer-service.js', ['depends' => [\yii\web\YiiAsset::class]]);
?>
<div class="driver-index">
    <div class="box box-green" style="margin-bottom: 20px; border-top: 0;">
        <div class="box-header with-border">
            <h3 class="box-title">Bộ lọc</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $this->render('_search', [
                'model' => $model,
                'adminList' => $adminList,
                'source' => $source,
            ]) ?>
        </div>
    </div>
    <?php $customerServiceList = $dataProvider->getModels(); ?>
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
            'linkOptions' => ['class' => 'page-link'],
            'maxButtonCount' => 5,
        ]);
        ?>
    </div>
    <div class="wrap-select-admin d-flex mb20" style="max-width: 300px;">
        <?php if (MyHelper::check_user_can(['/customer-service/*', '/customer-service/deliver'], $listPermission)) { ?>
            <?= Select2::widget([
                'name' => 'userid_created',
                'data' => $adminList,
                'options' => [
                    'placeholder' => 'Chọn người phụ trách...',
                    'id' => 'select-admin-deliver',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
            <button type=" button" class="btn btn-primary btn-select-admin ml10">Bàn giao</button>
        <?php } ?>
        <?php if (MyHelper::check_user_can(['/customer-service/*', '/customer-service/delete-all'], $listPermission)) { ?>
            <button type=" button" class="btn btn-danger btn-delete-admin ml10">Xóa nhanh</button>
        <?php } ?>
    </div>

    <table id="datatables_w0" class="table table-striped table-bordered" style="background: #fff;">
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center text-nowrap">
                    <input type="checkbox" id="checkbox-all">
                </th>
                <th class="text-nowrap">Lịch trình</th>
                <th class="text-nowrap">Thông tin khách hàng</th>
                <th class="text-nowrap" style="width: 400px;">Phản hồi khách hàng</th>
                <th class="text-nowrap">Điểm</th>
                <th class="text-center text-nowrap">Trạng thái</th>
                <th class="text-center text-nowrap">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($customerServiceList) && is_array($customerServiceList) && count($customerServiceList)) {
                foreach ($customerServiceList as $tripItem) {
                    ?>
                    <tr data-id="<?= $tripItem['id'] ?>" id="post-<?= $tripItem['id'] ?>" role="row">
                        <td class="text-center">
                            <input type="checkbox" name="checkbox[]" value="<?= $tripItem['id'] ?>" class="checkbox-item">
                        </td>
                        <td>
                            <div>
                                <span class="text-danger">
                                    <?= date('d/m/Y H:i', strtotime($tripItem['pickup_time'])) ?>
                                </span>
                            </div>
                            <div>
                                <span class="text-primary">
                                    <?= $tripItem['pickup_address'] ?>
                                </span>
                                <span style="font-size: 15px;">➜</span>
                                <span class="text-danger">
                                    <?= $tripItem['destination_address'] ?>
                                </span>
                            </div>
                            <div class="text-bold">
                                <span class="text-success">(
                                    <?= SCHEDULE_LIST_TRIP[$tripItem['round_trip']] ?>)
                                </span>
                                <?= ($tripItem['is_have_bill'] ? ' - <span class="text-primary">(Hóa đơn)</span>' : '') ?>
                                <?= ($tripItem['is_collect_money'] ? ' - <span class="text-danger">(Thu tiền)</span>' : ' - <span class="text-danger">(Không thu tiền)</span>') ?>
                            </div>
                            <div class="js-collected-money-<?= $tripItem['id'] ?>">
                                <?php
                                if ($tripItem['collected_money'] > 0 && $tripItem['collected_money_at'] != null) {
                                    echo 'Thu tiền : <span class="text-success">' . date('d/m/Y H:i', strtotime($tripItem['collected_money_at'])) . '</span>';
                                } ?>
                            </div>
                            <?php if (! empty($tripItem['description'])) { ?>
                                <div class="text-left">Mô tả: <span>
                                        <?= $tripItem['description'] ?>
                                    </span></div>
                            <?php } ?>
                        </td>
                        <td>
                            <div>Tên: <span class="text-primary">
                                    <?= $tripItem['customer_name'] ?>
                                </span>
                            </div>
                            <div>SĐT: <span class="text-primary">
                                    <?= $tripItem['customer_phone'] ?>
                                </span>
                            </div>
                        </td>

                        <td>
                            <div>
                                <span class="text-primary">Khách hàng phản hồi tổng đài:</span>
                                <div>
                                    <?= isset($tripItem['cus_feedback_trip']) ? $tripItem['cus_feedback_trip'] : '' ?>
                                </div>
                            </div>
                            <div>
                                <span class="text-primary">Khách hàng phản hồi lái xe:</span>
                                <div>
                                    <?php
                                        $feedbackDriver = json_decode($tripItem['cus_feedback_driver'], true);
                    if (isset($feedbackDriver) && is_array($feedbackDriver) && count($feedbackDriver)) {
                        $countFeedback = 0;
                        foreach ($feedbackDriver as $key => $value) {
                            if (isset($feedbacks[$key])) {
                                echo $feedbacks[$key]['text'] . '(' . $feedbacks[$key]['point'] . ' điểm)';
                            }
                            if ($countFeedback < count($feedbackDriver)) {
                                echo ', ';
                            }
                            $countFeedback++;
                        }
                    } ?>
                                </div>
                            </div>
                        </td>
                        <td class="text-center text-bold text-primary text-nowrap"><?= (float)$tripItem['point'] ?></td>
                        <td class="text-center text-nowrap">
                            <div>
                                <?php
                                if (isset($tripItem['customer_service_status'])) {
                                    switch ($tripItem['customer_service_status']) {
                                        case STATUS_CUSTOMER_SERVICE_SUCCESS:
                                            echo '<span class="text-success">' . STATUS_CUSTOMER_SERVICE_LIST[STATUS_CUSTOMER_SERVICE_SUCCESS] . '</span>';

                                            break;
                                        case STATUS_CUSTOMER_SERVICE_ERROR:
                                            echo '<span class="text-error">' . STATUS_CUSTOMER_SERVICE_LIST[STATUS_CUSTOMER_SERVICE_ERROR] . '</span>';

                                            break;
                                        default:
                                            echo '<span class="text-primary">' . STATUS_CUSTOMER_SERVICE_LIST[$tripItem['customer_service_status']] . '</span>';

                                            break;
                                    }
                                } else {
                                    echo '<span class="text-primary">' . STATUS_CUSTOMER_SERVICE_LIST[STATUS_CUSTOMER_SERVICE_NO_PROCESS] . '</span>';
                                } ?>
                            </div>
                            <div class="text-danger text-bold">
                                <?= (int)$tripItem['times'] ?> lần
                            </div>
                            <?php if (isset($tripItem['customer_service_userid_created']) && $tripItem['customer_service_userid_created'] > 0) { ?>
                                <div>Người phụ trách: <span class="text-danger">
                                        <?= $adminList[$tripItem['customer_service_userid_created']] ?>
                                    </span></div>
                            <?php } ?>
                            <?php if (isset($tripItem['customer_service_userid_updated']) && $tripItem['customer_service_userid_updated'] > 0) { ?>
                                <div>Người CSKH: <span class="text-success">
                                        <?= $adminList[$tripItem['customer_service_userid_updated']] ?>
                                    </span></div>
                            <?php } ?>
                        </td>
                        <td class="d-flex" style="justify-content: space-evenly;flex-wrap: wrap">
                            <?php
                                if ($tripItem['times'] < CUSTOMER_SERVICE_TIMES_SUCCESS) {
                                    echo Html::button('<span class="fa fa-check" aria-hidden="true"></span>', [
                                        'class' => 'btn-success btn mb2 update-feedback-customer',
                                        'data-target' => '#modalReject',
                                        'data-toggle' => 'modal',
                                        'data-id' => $tripItem['id'],
                                        'data-phone' => $tripItem['customer_phone'],
                                        'data-driver' => $tripItem['driver_id'],
                                    ]);
                                } ?>
                            <?php
                                unset($tripItem['id']);
                    $url_waiting_booking = '/statistic/create?' . http_build_query($tripItem) . '&status=WAITING';
                    $url_trip = '/trip/create?' . http_build_query($tripItem); ?>
                            <a href="<?= $url_waiting_booking ?>" class="btn btn-primary mb2" title="Lịch chờ"><i class="fa fa-th-list"></i></a>
                            <a href="<?= $url_trip ?>" class="btn btn-info" title="Tạo chuyến"><i class="fa fa-car" aria-hidden="true"></i></a>
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
            echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries';
            ?>
        </div>
        <?=
        LinkPager::widget([
            'pagination' => $dataProvider->getPagination(),
            'prevPageLabel' => 'Previous',
            'nextPageLabel' => 'Next',
            'options' => ['class' => 'pagination', 'style' => 'margin:0'],
            'linkOptions' => ['class' => 'page-link'],
            'maxButtonCount' => 5,
        ]);
        ?>
    </div>
</div>
<?php
$feedbacks = MyHelper::getFeedbackConfigbie();
$feedbackList = [];

foreach ($feedbacks as $key => $value) {
    $feedbackList[] = $value['text'] . ' (' . ($value['point'] > 0 ? '+' . $value['point'] : $value['point']) . ' điểm)';
}
?>
<script>
    var feedbackList = JSON.parse('<?= json_encode($feedbacks) ?>');
</script>
<div class="modal fade" id="modalReject" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php $form = ActiveForm::begin(['method' => 'post', 'id' => 'form-update-feedback', 'action' => '/customer-service/update']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Cập nhật thông tin phản hồi</h4>
            </div>
            <div class="modal-body">
                <div class="hidden">
                    <?= $form->field($modelCustomerService, 'id')->textInput(); ?>
                    <?= $form->field($modelCustomerService, 'trip_id')->textInput(); ?>
                    <?= $form->field($modelCustomerService, 'driver_id')->textInput(); ?>
                    <?= $form->field($modelCustomerService, 'customer_id')->textInput(); ?>
                </div>
                <?= $form->field($modelCustomerService, 'status')->radioList(STATUS_CUSTOMER_SERVICE_LIST); ?>
                <?= $form->field($modelCustomerService, 'type')->widget(Select2::classname(), [
                    'data' => TYPE_CUSTOMER_SERVICE_LIST,
                    'language' => 'vi',
                    'options' => ['placeholder' => 'Vui lòng chọn loại phản hồi'],
                    'pluginOptions' => [],
                ]); ?>
                <?= $form->field($modelCustomerService, 'point')->input('number', ['min' => 0, 'max' => 10, 'readonly' => true]) ?>
                <?= $form->field($modelCustomerService, 'cus_feedback_trip')->textarea(['maxlength' => true, 'class' => 'form-control input-reason-lock']) ?>
                <?= $form->field($modelCustomerService, 'cus_feedback_driver')->widget(\kartik\select2\Select2::classname(), [
                    'data' => $feedbackList,
                    'options' => [
                        'placeholder' => 'Chọn lựa...',
                        'multiple' => true,
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary btn-submit-status">Save changes</button>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$script = <<< JS
    $(document).on('change', '#customerservice-cus_feedback_driver', function(){
        let val = $(this).val();
        let point  = 0;
        if(val.length > 0){
            for (let i = 0; i < val.length; i++) {
                console.log(feedbackList[val[i]]);
                point = point + feedbackList[val[i]].point;
            }
        }

        $('#customerservice-point').val(point)
    })
    JS;
$this->registerJs($script);
?>