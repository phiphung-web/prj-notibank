<?php

use app\helpers\MyStringHelper;
use app\models\Car;
use app\models\Driver;
use kartik\daterange\DateRangePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = 'Danh sách lái xe';
$this->params['breadcrumbs'][] = $this->title;

$modelDriver = new Driver();
$this->registerJsFile('/js/pages/driver.js', ['depends' => [\yii\web\YiiAsset::class]]);
$driverList = $dataProvider->getModels();
?>
<div class="driver-index">
    <div class="box box-green" style="margin-bottom: 20px; border-top: 0;">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
        </div>
        <div class="box-body">
            <div class="trip-search">

                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                ]); ?>

                <div class="row mb20">
                    <div class="col-lg-3 col-md-12">
                        <?= DateRangePicker::widget([
                            'name' => 'createTimeRange',
                            'presetDropdown' => true,
                            'hideInput' => true,
                            'startAttribute' => 'createTimeStart',
                            'endAttribute' => 'createTimeEnd',
                            'value' => (isset($_GET['createTimeStart']) && isset($_GET['createTimeEnd'])) ? $_GET['createTimeStart'] . ' - ' . $_GET['createTimeEnd'] : date('Y-m-01') . ' - ' . date('Y-m-d'),
                            'startInputOptions' => [
                                'value' => isset($_GET['createTimeStart']) && ! empty($_GET['createTimeStart']) ? $_GET['createTimeStart'] : date('Y-m-01'),
                            ],
                            'endInputOptions' => [
                                'value' => isset($_GET['createTimeEnd']) && ! empty($_GET['createTimeEnd']) ? $_GET['createTimeEnd'] : date('Y-m-d'),
                            ],
                            'pluginOptions' => [
                                'locale' => ['format' => 'YYYY-MM-DD'],
                            ],
                        ]); ?>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex justify-content-between">
                        <div>
                            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
                            <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <?= $this->render('common/url.php') ?>
    <table id="datatables_w0" class="table table-striped table-bordered" style="background: #fff;">
        <thead>
            <tr>

                <th>Tên lái xe</th>
                <th>Biển kiếm soát</th>
                <th class="text-center">Hạng</th>
                <th class="text-center">Loại tài khoản</th>
                <th class="text-center text-nowrap">Đời xe</th>
                <th class="text-center">Tổng số lượt đi</th>
                <th class="text-center">Số tiền kiếm được</th>
                <th class="text-center">Số dư TK</th>
                <th class="text-center">Trạng thái</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($driverList) && is_array($driverList) && count($driverList)) {
                foreach ($driverList as $driverItem) {
                    $car = Car::findOne($driverItem['car_id']); ?>
                    <tr data-key="<?= $driverItem['id'] ?>" role="row" data-toggle="tooltip" data-html="true" title="<?= $car['note'] ?>">
                        <td>
                            <div class="wrap-tooltip position-relative">
                                <?= $driverItem['display_name'] ?> -
                                <?= $driverItem['username'] ?>
                            </div>
                        </td>
                        <td>
                            <?= $car['bks'] ?>
                        </td>
                        <td class="text-center text-bold text-primary">
                            <?= isset($driverItem['driver_rank']) && ! empty($driverItem['driver_rank']) ? RANK_DRIVER_LIST[$driverItem['driver_rank']] : 'Bình thường' ?>
                        </td>
                        <td class="text-center text-bold">
                            <?= isset($driverItem['driver_ban']) && $driverItem['driver_ban'] == STATUS_DRIVER_BAN ? '<span class="text-danger">' . STATUS_DRIVER_BAN_LIST[STATUS_DRIVER_BAN] . '</span>' : ($driverItem['parent_id'] > 0 ? '<span class="text-yellow">Tài xế phụ</span>' : '<span class="text-info">' . STATUS_DRIVER_BAN_LIST[$driverItem['driver_ban']] . '</span>') ?>
                        </td>
                        <td class="text-center text-bold">
                            <?= $car['car_year'] ?>
                        </td>
                        <td class="text-center">
                            <?= isset($driverItem['trip_count']) ? MyStringHelper::convertIntegerToPrice($driverItem['trip_count']) : 0 ?>
                        </td>
                        <td class="text-center">
                            <?= isset($driverItem['trip_price']) ? MyStringHelper::convertIntegerToPrice($driverItem['trip_price']) : 0 ?>₫
                        </td>
                        <td class="text-center">
                            <?= isset($driverItem['money']) ? MyStringHelper::convertIntegerToPrice($driverItem['money']) : 0 ?>₫
                        </td>
                        <td class="text-center">
                            <?php
                            switch ($driverItem['status']) {
                                case 0:
                                    echo '<span class="text-primary">Người mới</span>';

                                    break;
                                case 1:
                                    echo '<span class="text-success">Hoạt động</span>';

                                    break;
                                default:
                                    echo '<span class="text-danger">Khóa</span>';

                                    break;
                            } ?>
                        </td>
                        <td class="d-flex" style="justify-content: space-evenly;">
                            <a href="/driver/update?id=<?= $driverItem['id'] ?>" class="btn-success btn" title="Update" aria-label="Update" data-pjax="0"><span class="fa fa-pencil" aria-hidden="true"></span></a>
                            <a href="/driver/view?id=<?= $driverItem['id'] ?>" class="btn-primary btn" title="View" aria-label="View" data-pjax="0"><span class="fa fa-eye" aria-hidden="true"></span></a>
                            <?= Html::button('<span class="fa fa-ban" aria-hidden="true"></span>', [
                                'title' => 'Khóa tài xế',
                                'class' => 'btn-danger btn mb2 update-status-btn-reject',
                                'data-target' => '#modalReject',
                                'data-toggle' => 'modal',
                                'data-id' => $driverItem['id'],
                            ]); ?>
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

<?php echo $this->render('common/modal_reject', [
    'modelDriver' => $modelDriver,
    'reason_reject_array' => $reason_reject_array,
]) ?>
