<?php

use app\models\AreaConfiguration;
use kartik\select2\Select2;
use yii\helpers\Url;

app\assets\CallAsset::register($this);
/* @var $this yii\web\View */
// https://codepen.io/mranenko/pen/abaqJJa
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

$scheduleList['All'] = 'Hai chiều';
?>

<style>
    table tr td {
        color: black;
    }
</style>
<div class="row">
    <div class="col-md-4 col-xs-12">
        <div class="box box-success" data-select2-id="select2-data-11-15ak">
            <div class="box-header with-border">
                <h3 class="box-title">Tìm kiếm số điện thoại</h3>
            </div>
            <div class="box-body" data-select2-id="select2-data-10-5ijr">
                <form class="d-flex form-search-phone">
                    <input type="text" class="form-control input-search-phone" placeholder="Nhập số điện thoại cần tìm kiếm...">
                    <button class="btn btn-success mt-mb-2" type="submit" style="border-radius:0">Tìm kiếm</button>
                </form>
            </div>
        </div>
        <div id="list-call">
        </div>
    </div>
    <div class="col-md-8 col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Tư vấn khách:
                    <span class="js-phone-call"><?php echo(! empty($_GET['phone']) ? $_GET['phone'] : ''); ?></span>
                </h3>
            </div>
            <div class="box-body">
                <div id="advise-call">
                    <form id="form-get-detail-area">
                        <div class="col-lg-6">
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
                                                'url' => Url::to(['/call/search-area-relationship']),
                                                'dataType' => 'json',
                                                'data' => new \yii\web\JsExpression('function(params) { return { keyword: params.term }; }'),
                                                'processResults' => new \yii\web\JsExpression('function(data) { return { results: data.results.map(item => { return {id: item.id, text: item.street}; }) }; }'),
                                            ],
                                        ],
                                    ]);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-mb-2">
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
                                <div class="app-check">
                                    <?php foreach (SCHEDULE_LIST as $key => $value) { ?>
                                        <div class="app-border mr-10">
                                            <input type="checkbox" class="option-input checkbox js-checkbox-schedule js-checkbox-1-chieu" name="schedule" value="<?php echo $key ?>" />
                                            <label class="app-label">
                                                <?= $value ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                    <div class="app-border mr-10">
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
    var idCallBack = <?php echo isset($_GET['idCallBack']) ? $_GET['idCallBack'] : 0 ?>;
</script>

<?php
$script = <<< JS
    $(document).on('change', '.js-checkbox-round-trip', function(){
        var checkbox = $(this);
        var isChecked = checkbox.prop('checked');
        if (isChecked) {
            $('.js-checkbox-1-chieu').prop('checked', false)
        }
    })

    $(document).on('change', '.js-checkbox-1-chieu', function(){
        var checkbox = $(this);
        var isChecked = checkbox.prop('checked');
        if (isChecked) {
            $('.js-checkbox-round-trip').prop('checked', false)
        }
    })

    $(document).on('change', '.js-checkbox-schedule, .js-select-pickup-address, .js-select-address', function(){
        getDetailArea();
    })

    if($(window).width() >= 768) $('.sidebar-toggle').trigger('click')

    $(document).on('click', '.btn-click-advise' , function(){
        getDetailArea()
    })

    function getDetailArea() {
        var pickupAddress = $('.js-select-pickup-address').val();
        if (pickupAddress === '') {
            return;
        }

        var formData = $('#form-get-detail-area').serializeArray();
        var resultObject = {};
        var phone = $('.js-phone-call').text();
        formData.forEach(function(item) {
            if (resultObject.hasOwnProperty(item.name)) {
                if (!Array.isArray(resultObject[item.name])) {
                    resultObject[item.name] = [resultObject[item.name]];
                }
                resultObject[item.name].push(item.value);
            } else {
                resultObject[item.name] = item.value;
            }
        });
        var checkboxes = $('.js-checkbox-schedule');
        var scheduleData = {};
        var counter = 0;

        checkboxes.each(function() {
            var checkbox = $(this);
            var isChecked = checkbox.prop('checked');
            if (isChecked) {
                var value = checkbox.val();
                var label = checkbox.next('label').text();
                scheduleData[counter++] = value;
            }
        });
        if(Object.keys(scheduleData).length > 0){
            $.ajax({
                url: '/call/get-detail-area',
                type: 'GET',
                data: {
                    schedule: scheduleData,
                    area_id: resultObject.area_id,
                    address: resultObject.address,
                    phone: phone,
                    idCallBack: idCallBack,
                    scheduleList: scheduleList
                },
                dataType: 'json',
                success: function(json) {
                    var html = json.dataArea;
                    var tableList = $(".table-advise");
                    tableList.html('');
                    tableList.append(html);
                }
            });
        }
    }
JS;
$this->registerJs($script);
?>
