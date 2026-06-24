<?php

use app\models\AreaConfiguration;
use app\models\VnProvince;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->registerJsFile('js/taginput.js', ['depends' => [\yii\web\YiiAsset::class]]);
$scheduleList = [];
$timeList = [0 => 'Chọn thời gian'];

$areaConfigurations = AreaConfiguration::find()->all();
foreach ($areaConfigurations as $areaConfiguration) {
    switch ($areaConfiguration->type) {
        case TIME_AREA_CONFIGURATION:
            $timeList[$areaConfiguration['id']] = $areaConfiguration['value'];

            break;
        case SCHEDULE_AREA_CONFIGURATION:
            $scheduleList[$areaConfiguration['id']] = $areaConfiguration['value'];

            break;
        default:
            break;
    }
}
?>

<script>
    var cityid = '<?php echo isset($_GET['SearchArea']['provinceid']) ? $_GET['SearchArea']['provinceid'] : (isset($model->provinceid) ? $model->provinceid : '0') ?>';
    var districtid = '<?php echo isset($_GET['SearchArea']['districtid']) ? $_GET['SearchArea']['districtid'] : (isset($model->districtid) ? $model->districtid : '0') ?>'
    var typeOfCarList = JSON.parse('<?php echo json_encode(TYPE_OF_CAR_LIST) ?>');
    var addressList = JSON.parse('<?php echo json_encode($scheduleList) ?>');
    var scheduleList = JSON.parse('<?php echo json_encode(SCHEDULE_LIST) ?>');
    var timeList = JSON.parse('<?php echo json_encode($timeList) ?>');
</script>
<?php
$vnProvinceList = VnProvince::find()->all();
$data = ['0' => 'Chọn tỉnh/thành phố'];
foreach ($vnProvinceList as $province) {
    $data[$province->provinceid] = $province->name;
}
?>
<div class="driver-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Thông tin khu vực</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <?= $form->field($model, 'area_name')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-4">
                            <?= $form->field($model, 'provinceid')->widget(Select2::classname(), [
                                'data' => $data,
                                'options' => [
                                    'id' => 'city',
                                ],
                            ]); ?>
                        </div>
                        <div class="col-lg-4">
                            <?php echo $form->field($model, 'districtid', [
                                'options' => [
                                    'id' => 'district',
                                ],
                            ])->dropDownList([0 => 'Chọn quận/huyện'])
                            ?>
                        </div>
                        <div class="col-lg-6">
                        </div>
                    </div>


                    <?= $form->field($model, 'street')->textInput(['maxlength' => true, 'id' => 'inputTagStreet']) ?>
                    <?= $form->field($model, 'description')->textarea(['maxlength' => true]) ?>

                </div>
                <div class="form-group uk-clearfix pb-10" style="margin: 0 10px 10px 0;padding-bottom:10px">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success pull-right btn-save-area-relationship']) ?>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border " style="display: flex; align-items:center; justify-content: space-between">
                    <h3 class="box-title" style="width: 100%">Bảng giá </span></h3>
                    <button class="btn btn-add-area-relationship btn-primary">Thêm mới</button>
                </div>
                <div class="box-body">
                    <table id="tablePriceArea" class="table table-striped table-bordered " width="100%" cellspacing="0" style="background: #fff;">
                        <thead>
                            <tr>
                                <th>Loại xe</th>
                                <th>Thời gian đi</th>
                                <th>Địa điểm</th>
                                <th>Lịch trình</th>
                                <th class="text-center">Giá thu khách</th>
                                <th class="text-center">Giá khứ hồi</th>
                                <th class="text-center">Mô tả ngắn</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $priceList = (isset($_GET['id-clone']) ? (isset($areaClone->price_list) ? json_decode($areaClone->price_list, true) : []) : json_decode($model->price_list, true));
                            if (isset($priceList) && is_array($priceList) && count($priceList)) {
                                foreach ($priceList as $key => $value) {
                                    ?>
                                    <tr role="row">
                                        <td><?php echo Html::dropDownList('area_relationship[type_of_car][]', $value['type_of_car'], TYPE_OF_CAR_LIST, ['class' => 'form-control type-of-car-area-relationship']); ?></td>
                                        <td><?php echo Html::dropDownList('area_relationship[time][]', $value['time'], $timeList, ['class' => 'form-control time-area-relationship']); ?></td>
                                        <td><?php echo Html::dropDownList('area_relationship[address][]', (isset($value['address']) ? $value['address'] : ''), $scheduleList, ['class' => 'form-control address-area-relationship']); ?></td>
                                        <td><?php echo Html::dropDownList('area_relationship[schedule][]', $value['schedule'], SCHEDULE_LIST, ['class' => 'form-control schedule-area-relationship']); ?></td>
                                        <td><?php echo Html::textInput('area_relationship[price][]', $value['price'], ['class' => 'form-control int', 'placeholder' => 'Giá']); ?></td>
                                        <td><?php echo Html::textInput('area_relationship[roundtrip_price][]', $value['roundtrip_price'], ['class' => 'form-control int', 'placeholder' => 'Giá khứ hồi']); ?></td>
                                        <td><?php echo Html::textInput('area_relationship[description][]', $value['description'], ['class' => 'form-control', 'placeholder' => 'Mô tả']); ?></td>
                                        <td><button class="btn btn-danger btn-delete-area-relationship"><i class="fa fa-trash-o" aria-hidden="true"></i></button></td>
                                    </tr>
                            <?php
                                }
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS
    $("#inputTagStreet").tagsinput("items");

    $('#area-districtid').select2()

    $(document).on('change', '#city', function (e, data) {
        let _this = $(this);
        console.log(123);
        let id = _this.val();
        let param = {
            'id': id,
            'text': 'Chọn quận/huyện',
            'table': 'vn_district',
            'trigger_district': (typeof (data) != 'undefined') ? true : false,
            'where': { 'provinceid': id },
            'select': 'districtid as id, name',
            'object': '#area-districtid',
        };
        get_location(param);
    });

    if (typeof (cityid) != 'undefined' && cityid != '') {
        $('#city').val(cityid).trigger('change', [{ 'trigger': true }]);
    }

    $(document).on("click", ".btn-delete-area-relationship", function () {
        let tbody = $(this).parents('tbody');
        $(this).parents('tr').remove();
        if (tbody.find('tr').length==0) {
            $('.btn-save-area-relationship').removeAttr('disabled');
        }
        return false;
    })
    $(document).on("click", ".btn-add-area-relationship", function () {
        var city_select = $('#city').val();
        var newRowHtml = '<tr role="row">';
        newRowHtml += "<td>";
        newRowHtml += '<select name="area_relationship[type_of_car][]" class="form-control type-of-car-area-relationship">';
        for (var value in typeOfCarList) {
            if (typeOfCarList.hasOwnProperty(value)) {
                newRowHtml += '<option value="' + value + '">' + typeOfCarList[value] + "</option>";
            }
        }
        newRowHtml += "</select>";
        newRowHtml += "</td>";
        newRowHtml += "<td>";
        newRowHtml += '<select name="area_relationship[time][]" class="form-control time-area-relationship">';
        for (var value in timeList) {
            if (timeList.hasOwnProperty(value)) {
                newRowHtml += '<option value="' + value + '" '+(city_select != '01TTT' && value == '22' ? 'selected' : '')+'>' + timeList[value] + "</option>";
            }
        }
        newRowHtml += "</select>";
        newRowHtml += "</td>";
        newRowHtml += "<td>";
        newRowHtml += '<select name="area_relationship[address][]" class="form-control address-area-relationship">';
        for (var value in addressList) {
            if (addressList.hasOwnProperty(value)) {
                newRowHtml += '<option value="' + value + '">' + addressList[value] + "</option>";
            }
        }
        newRowHtml += "</select>";
        newRowHtml += "</td>";
        newRowHtml += "<td>";
        newRowHtml += '<select name="area_relationship[schedule][]" class="form-control schedule-area-relationship">';
        for (var value in scheduleList) {
            if (scheduleList.hasOwnProperty(value)) {
                newRowHtml += '<option value="' + value + '">' + scheduleList[value] + "</option>";
            }
        }
        newRowHtml += "</select>";
        newRowHtml += "</td>";
        newRowHtml += "<td>";
        newRowHtml += '<input type="text" name="area_relationship[price][]" class="form-control int" placeholder="Giá">';
        newRowHtml += "</td>";
        newRowHtml += "<td>";
        newRowHtml += '<input type="text" name="area_relationship[roundtrip_price][]" class="form-control int" placeholder="Giá khứ hồi">';
        newRowHtml += "</td>";
        newRowHtml += "<td>";
        newRowHtml += '<input type="text" name="area_relationship[description][]" class="form-control" placeholder="Mô tả">';
        newRowHtml += "</td>";
        newRowHtml += "<td>";
        newRowHtml += '<button class="btn btn-danger btn-delete-area-relationship"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';
        newRowHtml += "</td>";
        newRowHtml += "</tr>";
        $("#tablePriceArea tbody").append(newRowHtml);
        checkDuplicateAndAddError();
        return false;
    });

    $(document).on("change", "#tablePriceArea select", function() {
        checkDuplicateAndAddError();
    });

    function checkDuplicateAndAddError() {
        var rows = $("#tablePriceArea tbody tr");
        var values = {};
        rows.each(function() {
            var type_of_car = $(this).find(".type-of-car-area-relationship").val();
            var time = $(this).find(".time-area-relationship").val();
            var schedule = $(this).find(".schedule-area-relationship").val();
            var address = $(this).find(".address-area-relationship").val();
            var key = type_of_car + "-" + time + "-" + schedule + "-" + address;
            if (time == 0) {
                $(this).addClass("has-error");
                $('.btn-save-area-relationship').attr('disabled', 'disabled');
            } else if (key in values) {
                $(this).addClass("has-error");
                $('.btn-save-area-relationship').attr('disabled', 'disabled');
            } else {
                $(this).removeClass("has-error");
                $('.btn-save-area-relationship').removeAttr('disabled');
            }

            values[key] = true;
        });
    }

    function get_location(param) {
        if (districtid == '' || param.trigger_district == false) districtid = 0;

        let formURL = '/area/get-location';
        $.post(formURL, {
            param: param
        },
            function (data) {
                if (param.object == '#area-districtid') {
                    $(param.object).html(data.html).val(districtid).trigger('change');
                    $('#area-districtid').select2()
                }
            });
    }
JS;
$this->registerJs($script);
?>