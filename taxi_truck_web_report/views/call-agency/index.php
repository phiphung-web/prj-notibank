<?php

use app\models\AreaConfiguration;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\web\YiiAsset;

$this->title = '';
$scheduleList = [];
$timeList = [];

$areaConfigurations = AreaConfiguration::find()->all();
foreach ($areaConfigurations as $areaConfiguration) {
    switch ($areaConfiguration->type) {
        case TIME_AREA_CONFIGURATION:
            $timeList[$areaConfiguration['id']] = $areaConfiguration['value'];

            break;
        case SCHEDULE_AREA_CONFIGURATION:
            $addressList[$areaConfiguration['id']] = $areaConfiguration['value'];

            break;
        default:
            break;
    }
}
$this->registerJsFile('/js/pages/call-agency.js', ['depends' => [YiiAsset::class]]);
$scheduleList['All'] = 'Hai chiều';
?>

<style>
    table tr td {
        color: black;
    }
</style>
<div class="row">
    <div class="col-md-12 col-lg-8 col-sm-12">
        <div class="box box-warning">
            <div class="box-header with-border " style="display: flex;align-items:center;">
                <h3 class="box-title" style="white-space: nowrap;margin-right: 20px;">Tư vấn khách:</h3>
                <input type="text" placeholder="Số điện thoại khách hàng" class="form-control js-phone-call">
            </div>
            <div class="box-body">
                <div id="advise-call">
                    <form id="form-get-detail-area">
                        <div class="col-lg-6 col-sm-12 mobile-mb-10">
                            <div class="d-flex" style="align-items: center;">
                                <div class="text-bold" style="width: 130px;">Địa điểm</div>
                                <div class="app-check" style="width: calc(100% - 130px);">
                                    <?=
                                    Select2::widget([
                                        'name' =>
                                        'area_id',
                                        'options' => [
                                            'placeholder' => 'Địa điểm',
                                            'class' => 'js-select-pickup-address',
                                        ],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            'ajax' => [
                                                'url' => Url::to(['/call-agency/search-area-relationship']),
                                                'dataType' => 'json',
                                                'data' => new \yii\web\JsExpression('function(params) { return { term: params.term }; }'),
                                                'processResults' => new \yii\web\JsExpression('function(data) { return { results: data.results.map(item => { return {id: item.id, text: item.street}; }) }; }'),
                                            ],
                                        ],
                                    ]);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="d-flex" style="align-items: center;">
                                <div class="text-bold" style="width: 130px;">Điểm đến</div>
                                <div class="app-check" style="width: calc(100% - 130px);">
                                    <?= Select2::widget([
                                        'name' => 'address',
                                        'data' => $addressList,
                                        'value' => '10',
                                        'options' => ['placeholder' => 'Lịch trình', 'class' => 'js-select-address'],
                                        'pluginOptions' => ['allowClear' => true],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12" style="margin-top: 10px">
                            <div class=" d-flex" style="align-items:center">
                                <div class="text-bold" style="width:130px">Lịch trình</div>
                                <div class="app-check" style="width: calc(100% - 130px);">
                                    <?php foreach (SCHEDULE_LIST as $key => $value) { ?>
                                        <div class="app-border mr-10 mobile-mb-10">
                                            <input type="checkbox" class="option-input checkbox js-checkbox-schedule js-checkbox-1-chieu" name="schedule" value="<?php echo $key ?>" />
                                            <label class="app-label">
                                                <?= $value ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                    <div class="app-border mr-10 mobile-mb-10">
                                        <input type="checkbox" class="option-input checkbox js-checkbox-schedule js-checkbox-round-trip" name="schedule" value="All" />
                                        <label class="app-label">
                                            Hai chiều
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="col-lg-12">
                        <div class="wrap-table-detail-area">
                            <div class="table-advise">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var scheduleList = JSON.parse('<?php echo json_encode(SCHEDULE_LIST) ?>');
</script>