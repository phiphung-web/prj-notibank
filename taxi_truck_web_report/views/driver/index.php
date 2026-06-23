<?php

use app\helpers\MyStringHelper;
use app\models\Driver;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = 'Danh sách lái xe';
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
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $this->render('_search', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
    <div class="dashboard">
        <div class="row">
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="small-box bg-blue">
                    <div class="inner">
                        <h3 class="customer-month fsc-sm-24">
                            <?= (isset($statistic['total']) ? $statistic['total'] : 0) ?>
                        </h3>
                        <p>Tổng số lái xe đã điều.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 class="customer-booked fsc-sm-24">
                            <?= (isset($statistic['complete']) ? $statistic['complete'] : 0) ?>
                        </h3>
                        <p>Tổng số chuyến lái xe đi thành công.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 class="customer-canceled fsc-sm-24">
                            <?= (isset($statistic['cancel']) ? $statistic['cancel'] : 0) ?>
                        </h3>
                        <p>Tổng số chuyến lái xe đã hủy.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 class="customer-successfully fsc-sm-24">
                            <?= (isset($statistic['recharge']) ? $statistic['recharge'] : 0) ?>đ
                        </h3>
                        <p>Tổng số tiền lái xe đã nạp.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= $this->render('common/url.php') ?>

    <?php $driverList = $dataProvider->getModels(); ?>
    <table id="datatables_w0" class="table table-striped table-bordered" style="background: #fff;">
        <thead>
            <tr>

                <th>
                    <?= $model->attributeLabels()['display_name'] ?>
                </th>
                <th>
                    <?= $model->attributeLabels()['bks'] ?>
                </th>
                <th class="text-center">Hạng</th>
                <th class="text-center">Loại tài khoản</th>
                <th class="text-center text-nowrap">Đời xe</th>
                <th class="text-center">Tổng số chuyến</th>
                <th class="text-center">Tổng số hoàn thành</th>
                <th class="text-center">Tổng số trả lịch</th>
                <th class="text-center">Điểm</th>
                <th class="text-center">Tổng tiền nạp</th>
                <th class="text-center">
                    Số dư TK
                </th>
                <th class="text-center">
                    Trạng thái
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($driverList) && is_array($driverList) && count($driverList)) {
                foreach ($driverList as $driverItem) {
                    ?>
                    <tr data-key="<?= $driverItem['id'] ?>" role="row" data-toggle="tooltip" data-html="true" title="<?= nl2br($driverItem['note']) ?>">
                        <td>
                            <div class="wrap-tooltip position-relative">
                                <?= $driverItem['display_name'] ?> -
                                <?= $driverItem['username'] ?>
                            </div>
                        </td>
                        <td>
                            <?= $driverItem['bks'] ?>
                        </td>
                        <td class="text-center text-bold text-primary">
                            <?= isset($driverItem['driver_rank']) && !empty($driverItem['driver_rank']) ? RANK_DRIVER_LIST[$driverItem['driver_rank']] : 'Bình thường' ?>
                        </td>
                        <td class="text-center text-bold">
                            <?= isset($driverItem['driver_ban']) && $driverItem['driver_ban'] == STATUS_DRIVER_BAN ? '<span class="text-danger">' . STATUS_DRIVER_BAN_LIST[STATUS_DRIVER_BAN] . '</span>' : ($driverItem['parent_id'] > 0 ? '<span class="text-yellow">Tài xế phụ</span>' : '<span class="text-info">' . STATUS_DRIVER_BAN_LIST[$driverItem['driver_ban']] . '</span>') ?>
                        </td>
                        <td class="text-center text-bold">
                            <?= $driverItem['car_year'] ?>
                        </td>
                        <td class="text-center">
                            <?= isset($driverItem['total_trip_bid']) ? MyStringHelper::convertIntegerToPrice($driverItem['total_trip_bid']) : 0 ?>
                        </td>
                        <td class="text-center">
                            <?= isset($driverItem['total_complete']) ? MyStringHelper::convertIntegerToPrice($driverItem['total_complete']) : 0 ?>
                        </td>
                        <td class="text-center">
                            <?= isset($driverItem['total_cancel']) ? MyStringHelper::convertIntegerToPrice($driverItem['total_cancel']) : 0 ?>
                        </td>
                        <td class="text-center text-primary text-bold">
                            <?= isset($driverItem['point']) ? MyStringHelper::convertIntegerToPrice($driverItem['point']) : 0 ?>
                        </td>
                        <td class="text-center">
                            <?= isset($driverItem['total_recharge']) ? MyStringHelper::convertIntegerToPrice($driverItem['total_recharge']) : 0 ?>₫
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
                            }
                            ?>
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
                    if (isset($driverItem['driver_ban']) && !empty($driverItem['driver_ban'])) {
                        $driverSubList = Driver::find()->andWhere(['parent_id' => $driverItem['id']])->innerJoinWith('car')->all();
                        if (isset($driverSubList)) {
                            foreach ($driverSubList as $driverSubItem) {
                                ?>
                                <tr data-key="<?= $driverSubItem['id'] ?>" role="row">
                                    <td>
                                        |----
                                        <?= $driverSubItem['display_name'] ?> -
                                        <?= $driverSubItem['username'] ?>
                                    </td>
                                    <td>
                                        <?= $driverSubItem->car->bks ?>
                                    </td>
                                    <td class="text-center text-bold text-primary">
                                        <?= isset($driverSubItem['driver_rank']) && !empty($driverSubItem['driver_rank']) ? RANK_DRIVER_LIST[$driverSubItem['driver_rank']] : 'Bình thường' ?>
                                    </td>
                                    <td class="text-center text-bold">
                                        <span class="text-yellow">Tài xế phụ</span>
                                    </td>
                                    <td class="text-center text-bold">
                                        <?= $driverSubItem->car->car_year ?>
                                    </td>
                                    <td class="text-center">
                                        <?= isset($driverSubItem['total_trip_bid']) ? MyStringHelper::convertIntegerToPrice($driverSubItem['total_trip_bid']) : 0 ?>
                                    </td>
                                    <td class="text-center">
                                        <?= isset($driverSubItem['total_complete']) ? MyStringHelper::convertIntegerToPrice($driverSubItem['total_complete']) : 0 ?>
                                    </td>
                                    <td class="text-center">
                                        <?= isset($driverSubItem['total_cancel']) ? MyStringHelper::convertIntegerToPrice($driverSubItem['total_cancel']) : 0 ?>
                                    </td>
                                    <td class="text-center">
                                        <?= isset($driverItem['point']) ? MyStringHelper::convertIntegerToPrice($driverItem['point'], 2) : 0 ?>
                                    </td>
                                    <td class="text-center">
                                        <?= isset($driverSubItem['total_recharge']) ? MyStringHelper::convertIntegerToPrice($driverSubItem['total_recharge']) : 0 ?>₫
                                    </td>
                                    <td class="text-center">
                                        <?= isset($driverSubItem['money']) ? MyStringHelper::convertIntegerToPrice($driverSubItem['money']) : 0 ?>₫
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        switch ($driverSubItem['status']) {
                                            case 0:
                                                echo '<span class="text-primary">Người mới</span>';

                                                break;
                                            case 1:
                                                echo '<span class="text-success">Hoạt động</span>';

                                                break;
                                            default:
                                                echo '<span class="text-danger">Khóa</span>';

                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td class="d-flex" style="justify-content: space-evenly;">
                                        <a href="/driver/update?id=<?= $driverSubItem['id'] ?>" class="btn-success btn" title="Update" aria-label="Update" data-pjax="0"><span class="fa fa-pencil" aria-hidden="true"></span></a>
                                        <a href="/driver/view?id=<?= $driverSubItem['id'] ?>" class="btn-primary btn" title="View" aria-label="View" data-pjax="0"><span class="fa fa-eye" aria-hidden="true"></span></a>
                                        <?= Html::button('<span class="fa fa-ban" aria-hidden="true"></span>', [
                                            'title' => 'Khóa tài xế',
                                            'class' => 'btn-danger btn mb2 update-status-btn-reject',
                                            'data-target' => '#modalReject',
                                            'data-toggle' => 'modal',
                                            'data-id' => $driverSubItem['id'],
                                        ]); ?>
                                    </td>
                                </tr>
                    <?php
                            }
                        }
                    }
                    ?>
                <?php
                }
            } else {
                ?>
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
