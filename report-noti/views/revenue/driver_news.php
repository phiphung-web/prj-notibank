<?php

use app\helpers\MyStringHelper;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\db\Query;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */

$driverList = $dataProvider->getModels();
$this->title = 'Tài xế mới tham gia hệ thống và đã nạp tiền lần đầu';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('/js/pages/revenue.js', ['depends' => [\yii\web\YiiAsset::class]]);
?>

<div class="revenue-index">
    <div class="box box-green">
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
                        <label class="app-label mr-10">Thời gian duyệt</label>
                        <?= DateRangePicker::widget([
                            'name' => 'createTimeRange',
                            'value' => Yii::$app->request->get('createTimeRange', date('Y-m-01') . ' - ' . date('Y-m-t')),
                            'presetDropdown' => true,
                            'hideInput' => true,
                            'startAttribute' => 'createTimeStart',
                            'endAttribute' => 'createTimeEnd',
                            'options' => [
                                'required' => true,
                                'id' => 'dateRangePicker',
                            ],
                            'pluginOptions' => [
                                'locale' => ['format' => 'YYYY-MM-DD'],
                                'showDropdowns' => true,
                                'showCustomRangeLabel' => false,
                                'linkedCalendars' => false,
                                'alwaysShowCalendars' => false,
                                'ranges' => new \yii\web\JsExpression("
                                    {
                                        'Tháng 1': ['" . date('Y-01-01') . "', '" . date('Y-01-31') . "'],
                                        'Tháng 2': ['" . date('Y-02-01') . "', '" . date('Y-02-t', strtotime('February 1')) . "'],
                                        'Tháng 3': ['" . date('Y-03-01') . "', '" . date('Y-03-31') . "'],
                                        'Tháng 4': ['" . date('Y-04-01') . "', '" . date('Y-04-30') . "'],
                                        'Tháng 5': ['" . date('Y-05-01') . "', '" . date('Y-05-31') . "'],
                                        'Tháng 6': ['" . date('Y-06-01') . "', '" . date('Y-06-30') . "'],
                                        'Tháng 7': ['" . date('Y-07-01') . "', '" . date('Y-07-31') . "'],
                                        'Tháng 8': ['" . date('Y-08-01') . "', '" . date('Y-08-31') . "'],
                                        'Tháng 9': ['" . date('Y-09-01') . "', '" . date('Y-09-30') . "'],
                                        'Tháng 10': ['" . date('Y-10-01') . "', '" . date('Y-10-31') . "'],
                                        'Tháng 11': ['" . date('Y-11-01') . "', '" . date('Y-11-30') . "'],
                                        'Tháng 12': ['" . date('Y-12-01') . "', '" . date('Y-12-31') . "']
                                    }
                                "),
                                'minDate' => date('Y-01-01'),
                                'maxDate' => date('Y-12-31'),
                                'startDate' => date('Y-m-01'),
                                'endDate' => date('Y-m-t'),
                            ],
                        ]); ?>
                    </div>
                    <div class="col-lg-3 col-md-12">
                        <label class="app-label mr-10">Tổng đài viên</label>
                        <?php
                        $admins = (new Query())
                            ->select(['id', 'username'])
                            ->from('admin')
                            ->all();

                        $adminOptions = [];
                        $selectedAdminId = Yii::$app->request->get('admin_id_accepted');
                        $selectedUsername = '';

                        foreach ($admins as $admin) {
                            $adminOptions[$admin['id']] = $admin['username'];
                            if ($admin['id'] == $selectedAdminId) {
                                $selectedUsername = $admin['username'];
                            }
                        }
                        ?>

                        <?= Select2::widget([
                            'name' => 'admin_id_accepted',
                            'value' => $selectedAdminId,
                            'data' => $adminOptions,
                            'language' => 'vi',
                            'pluginOptions' => [
                                'allowClear' => true,
                                'placeholder' => $selectedUsername ?: 'Chọn tổng đài viên',
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
    <div class="dashboard">
        <div class="row">
            <div class="col-lg-2 col-sm-3 col-xs-12">
				<div class="small-box bg-green">
					<div class="inner d-flex align-items-center justify-content-center">
                        Tổng số xe duyệt: <span style="font-size: 70px; margin-left: 20px;"><?php echo count($driverList) ?></span>
					</div>
				</div>
			</div>
        </div>
    </div>
    <table id="datatables_w0" class="table table-striped table-bordered" style="background: #fff;">
        <thead>
            <tr>
                <th class="text-center">STT</th>
                <th>Thời gian phê duyệt</th>
                <th>Thông tin tài xế</th>
                <th>Loại xe</th>
                <th class="text-center">Tiền nạp</th>
                <th class="text-center">Trạng thái</th>
                <th>Người phê duyệt</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($driverList) && is_array($driverList) && count($driverList)) {
                ;
                foreach ($driverList as $key => $val) {
                    ?>
                    <tr data-key="<?= $val['id'] ?>" role="row" data-toggle="tooltip" data-html="true" title="">
                        <td class="text-center"> <?= $key + 1 ?></td>
                        <td>
                            <?= $val['accepted_on']; ?>
                        </td>
                        <td>
                            Tài xế: "<?= $val['display_name'] ?>" - SDT: "<?= $val['username'] ?>"
                            <?php
                                if (! empty($val['driver_ban']) && $val['driver_ban'] != 0) {
                                    echo '<br><span>Tài xế có nhiều xe</span>';
                                } ?>
                            <?php
                                if (! empty($val['parent_id']) && $val['parent_id'] != 0) {
                                    echo '<br><span>Tài xế phụ</span>';
                                } ?>
                        </td>
                        <td>
                            Loại xe: <?php
                                if (! empty($val['type_of_car']) && isset(TYPE_OF_CAR_LIST[$val['type_of_car']])) {
                                    echo '<span>' . TYPE_OF_CAR_LIST[$val['type_of_car']] . '</span>';
                                } ?> (<?= ($val['car_type']) == 0 ? 'Xe xăng' : 'Xe điện' ?>)
                            <br>
                            Hãng xe: <?= $val['type'] ?> (<?= $val['car_year'] ?>)
                            <br>
                            BKS: <?= $val['bks'] ?>
                            <br>
                            Màu xe: <?= $val['color'] ?>
                        </td>
                        <td class="text-center">
                            <?= isset($val['money']) ? MyStringHelper::convertIntegerToPrice($val['money']) : 0 ?>₫
                        </td>
                        <td class="text-center text-bold text-primary">
                            <?php
                                switch ($val['status']) {
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
                        <td>
                            <?= $val['admin_name'] ?>
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
