<?php

use app\helpers\MyStringHelper;
use app\models\Agency;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

if (isset($_GET['time_start']) && isset($_GET['time_end'])) {
    $timeStartFormatted = (DateTime::createFromFormat('Ym', $_GET['time_start']))->format('01-m-Y');
    $timeEndFormatted = (DateTime::createFromFormat('Ym', $_GET['time_end']))->modify('last day of this month')->format('d-m-Y');
    $this->title = 'Thống kê đại lý từ ngày ' . $timeStartFormatted . ' đến ngày ' . $timeEndFormatted;
} else {
    $this->title = 'Thống kê đại lý từ ngày ' . date('01-01-Y') . ' đến ngày ' . date('d-m-Y');
}

$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('/css/pages/rev-agency.css');
$this->registerJsFile('/js/jquery.min.js', ['depends' => [YiiAsset::class]]);
$this->registerJsFile('/js/month-picker.js', ['depends' => [YiiAsset::class]]);

?>

<div class="statistical-agency">
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
            <div class="rev-agency-search">
                <?php $form = ActiveForm::begin([
                    'action' => ['revenue/agency'],
                    'method' => 'get',
                ]); ?>
                <div class="row mb-10">
                    <div class="col-lg-3">
                        <label for="" class="control-label">Thời gian</label>
                        <div id="sla-data-range" class="mrp-container ">
                            <div class="mrp-icon"><i class="fa fa-calendar"></i></div>
                            <div class="mrp-monthdisplay">
                                <span class="mrp-lowerMonth">Tháng <?php echo isset($_GET['time_start']) ? DateTime::createFromFormat('Ym', $_GET['time_start'])->format('n - Y') : (new DateTime(date('Y') . '-01-01'))->format('n - Y'); ?></span>
                                <span class="mrp-to"> đến </span>
                                <span class="mrp-upperMonth">Tháng <?php echo isset($_GET['time_end']) ? DateTime::createFromFormat('Ym', $_GET['time_end'])->format('n - Y') : (new DateTime())->format('n - Y'); ?></span>

                            </div>
                            <input type="hidden" value="<?php echo isset($_GET['time_start']) ? $_GET['time_start'] : (new DateTime(date('Y') . '-01-01'))->format('Ym'); ?>" name="time_start" id="mrp-lowerDate" class="mpr-lowerDate" />
                            <input type="hidden" value="<?php echo isset($_GET['time_end']) ? $_GET['time_end'] : (new DateTime())->format('Ym'); ?>" name="time_end" id="mrp-upperDate" class="mpr-upperDate" />

                        </div>
                    </div>
                    <?php if (! isset($admin->agency_id) || empty($admin->agency_id)) { ?>
                        <div class="col-lg-4">
                            <?php
                            $listAgency = Agency::find()
                                ->where(['status' => 1])
                                ->orderBy(['name' => SORT_DESC])
                                ->all();
                            $data = ['0' => 'Tất cả'];
                            foreach ($listAgency as $agency) {
                                $data[$agency['id']] = $agency['name'] . ' - ' . $agency['phone'];
                            }
                            ?>

                            <?= $form->field($searchModel, 'id')->widget(Select2::classname(), [
                                'data' => $data,
                                'language' => 'vi',
                                'options' => ['placeholder' => 'Chọn đại lý'],
                                'pluginOptions' => [],
                            ]) ?>
                        </div>
                    <?php } ?>
                </div>

                <div class="action-box">
                    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
                    <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <table id="datatables_w0" class="table table-bordered" width="100%" cellspacing="0" style="background: #fff;">
        <thead>
            <tr>
                <th class="bg-warning">
                    Thông tin đại lý
                </th>
                <th class="text-center">
                    Thời gian
                </th>
                <th class="text-center">
                    Số chuyến
                </th>
                <th class="text-center">
                    Trạng thái
                </th>
                <th class="text-center">
                    Tiền hoa hồng
                </th>
                <th class="text-center">
                    Giá báo khách
                </th>
                <th class="text-center" style="width:150px"></th>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($agencyList) && is_array($agencyList) && count($agencyList)) {
                                foreach ($agencyList as $agency) {
                                    $agencyId = $agency['id']; ?>
                    <?php if (isset($agency['data']) && is_array($agency['data']) && count($agency['data'])) {
                                        foreach ($agency['data'] as $key => $value) {    ?>
                            <tr data-key="<?= $agencyId ?>" role="row">
                                <?php if ($key == 0) { ?>
                                    <td style="min-width: 180px" class="text-left info-agency-statistic bg-warning" rowspan="<?= count($agency['data']) ?>">
                                        <div>Đại lý: <span class="text-primary"><?= $agency['name'] ?></span></div>
                                        <div>Người liên hệ: <span class="text-primary"><?= $agency['contact_person'] ?></span></div>
                                        <div>SĐT: <span class="text-primary"><?= $agency['phone'] ?></span></div>
                                        <div>Email: <span class="text-primary"><?= $agency['email'] ?></span></div>
                                        <div>Địa chỉ: <span class="text-primary"><?= $agency['address'] ?></span></div>
                                        <div>Ghi chú: <span class="text-primary"><?= $agency['note'] ?></span></div>
                                    </td>
                                <?php } ?>
                                <td class="text-center text-bold">
                                    <?= $value['dtMonth'] ?>
                                </td>
                                <td class="text-center">
                                    <?= $value['count_trip'] ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $value['agency_debt'] == 1 ? '<span class="text-danger">Tổng đài nợ tài lý</span>' : '<span class="text-success">Đại lý nợ tổng đài</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?= MyStringHelper::convertIntegerToPrice($value['trip_debt']) ?>đ
                                </td>
                                <td class="text-center">
                                    <?= MyStringHelper::convertIntegerToPrice($value['total_price'])  ?>đ
                                </td>
                                <td class="text-center">
                                    <div class="d-flex" style="justify-content: center; flex-wrap: wrap;">
                                        <?php echo Html::button('Chi tiết', [
                                            'title' => 'Chi tiết',
                                            'class' => 'js-btn-revenue-detail btn-success btn mb2',
                                            'data-target' => '#modalDetail',
                                            'data-toggle' => 'modal',
                                            'data-id' => $agencyId,
                                            'data-time' => (preg_match('/Tháng (\d{1,2})\/(\d{4})/', $value['dtMonth'], $matches) ? sprintf('%04d-%02d', $matches[2], $matches[1]) : ''),
                                        ]); ?>
                                    </div>
                                </td>
                            </tr>
                    <?php }
                                    } ?>
                <?php
                                }
                            } else {
                                ?>
                <tr>
                    <td colspan="100%"><span class="text-danger">Không có dữ liệu phù hợp...</span></td>
                </tr>
            <?php
                            } ?>
        </tbody>
    </table>
    <?php $pagination = $dataProvider->getPagination(); ?>
    <div class="d-flex" style="justify-content: space-between;">
        <div>
            <?php
            $startIndex = $pagination->getPage() * $pagination->getPageSize() + 1;
            $endIndex = $startIndex + count($dataProvider->getModels()) - 1;
            $totalCount = $dataProvider->getTotalCount();
            echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries';
            ?>
        </div>
        <?= LinkPager::widget([
            'pagination' => $pagination,
            'prevPageLabel' => 'Previous',
            'nextPageLabel' => 'Next',
            'options' => ['class' => 'pagination', 'style' => 'margin:0'],
            'linkOptions' => ['class' => 'page-link'],
            'maxButtonCount' => 5,
        ]) ?>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog-large" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Chi tiết các chuyến xe đại lý</h4>
            </div>
            <div class="modal-body table-responsive">
                <table id="tableDetail" class="table-striped table-bordered table" width="100%">
                    <thead>
                        <tr class="bg-primary">
                            <th class="text-center">STT</th>
                            <th style=" width: 350px">Chuyến xe</th>
                            <th>Khách hàng</th>
                            <th>Lái xe</th>
                            <th class="text-center">Thu khách</th>
                            <?php if (! isset($admin->agency_id) || empty($admin->agency_id)) { ?>
                                <th class="text-center">Lái xe nhận</th>
                            <?php } ?>
                            <th class="text-center">Hoa hồng đại lý</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Nguồn</th>
                        </tr>
                    </thead>
                    <tbody class="js-tbody">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var currentDate = new Date();
    var startMonth = <?php echo isset($_GET['time_start']) ? date('n', strtotime(substr($_GET['time_start'], 0, 4) . '-' . substr($_GET['time_start'], 4, 2))) : '1' ?>;
    var startYear = <?php echo isset($_GET['time_start']) ? date('Y', strtotime(substr($_GET['time_start'], 0, 4) . '-' . substr($_GET['time_start'], 4, 2))) : 'currentDate.getFullYear()' ?>;
    var endMonth = <?php echo isset($_GET['time_end']) ? date('n', strtotime(substr($_GET['time_end'], 0, 4) . '-' . substr($_GET['time_end'], 4, 2))) : 'currentDate.getMonth() + 1' ?>;
    var endYear = <?php echo isset($_GET['time_end']) ? date('Y', strtotime(substr($_GET['time_end'], 0, 4) . '-' . substr($_GET['time_end'], 4, 2))) : 'currentDate.getFullYear()' ?>;
    var fiscalMonth = endMonth - startMonth;
    if (startMonth < 10)
        startDate = parseInt("" + startYear + '0' + startMonth + "");
    else
        startDate = parseInt("" + startYear + startMonth + "");
    if (endMonth < 10)
        endDate = parseInt("" + endYear + '0' + endMonth + "");
    else
        endDate = parseInt("" + endYear + endMonth + "");
</script>
<?php
$script = <<<JS
    $(document).on('click', '.js-btn-revenue-detail', function() {
        let id = $(this).data('id');
        let time = $(this).data('time');
        $('#tableDetail .js-tbody').html("");
        $.ajax({
            type: "get",
            url: "/revenue/agency-detail",
            data: {
                id: id,
                time: time,
            },
            success: function(data) {
                $('#tableDetail .js-tbody').html(data);
            },
        });
    });
JS;
$this->registerJs($script);
?>
