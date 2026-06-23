<?php

use kartik\time\TimePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Cấu hình bảng giá';
?>
<div class="box box-header">
    <div class="text-bold" style="font-size: 18px">Giải thích các item <span class="text-danger text-sm">(* Không giải thích những trường dễ hiểu)</span></div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tên trường</th>
                <th>Giải thích</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Km bắt đầu</td>
                <td>Bắt đầu tính km nếu km bằng 0 thì hiểu là bắt đầu tính từ 0</td>
            </tr>
            <tr>
                <td>Km kết thúc </td>
                <td>Nếu = 0 thì hiểu là từ km bắt đầu tính đến về sau</td>
            </tr>

        </tbody>
    </table>
</div>
<div class="system-configuration-create row">
    <?php $form = ActiveForm::begin(['action' => ['/calculation-formula/update']]); ?>
    <div class="col-md-12">
        <div class="box box-success">
            <div class=" wrap-calculation-formula box-body">
                <ul class="tabs flex-wrap">
                    <?php foreach (TYPE_OF_CAR_LIST as $key => $value) { ?>
                        <li class="tab type-<?= $key ?> tab-chage-type <?= $key == 11 ? 'current' : '' ?>" data-value="<?= $key ?>" data-tab="tab-<?= $key ?>"><?= $value ?></li>
                    <?php } ?>
                </ul>
                <?php foreach (TYPE_OF_CAR_LIST as $key => $valueTypeOfCar) { ?>
                    <div id="tab-<?= $key ?>" class="tab-content <?= $key == 11 ? 'current' : '' ?>" style="background: #fff">
                        <div style="display: flex; align-items:center; justify-content: space-between">
                            <h3 class="box-title" style="width: 100%; margin: 0">Bảng giá </span></h3>
                            <button class="btn btn-add-calculation-formula btn-primary">Thêm mới</button>
                        </div>
                        <table class="table table-striped table-bordered table-calculation-fomular" width="100%"
                            cellspacing="0" style="background: #fff;margin-bottom: 0">
                            <thead>
                                <tr>
                                    <th style="width: 150px;vertical-align:middle;">Loại xe</th>

                                    <th class="text-center" style="vertical-align:middle;">Km bắt đầu</th>
                                    <th class="text-center" style="vertical-align:middle;">Km kết thúc</th>
                                    <th class="text-center" style="vertical-align:middle;">Giá / km</th>
                                    <th class="text-center" style="vertical-align:middle;">Giá niêm yết</th>
                                    <th class="text-center" style="vertical-align:middle;">Phụ phí</th>
                                    <th class="text-center" style="vertical-align:middle;">Thời gian chờ (h)</th>
                                    <th class="text-center" style="vertical-align:middle;">Phí lưu đêm</th>
                                    <th class="text-center" style="vertical-align:middle;">Mô tả</th>
                                    <th style="width:100px;vertical-align:middle;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($dataProvider) && is_array($dataProvider) && count($dataProvider)) {
                                    foreach ($dataProvider as $value) {
                                        if ($value['type_of_car'] != $key) {
                                            continue;
                                        }
                                        ?>
                                        <tr role="row">
                                            <td>
                                                <?php echo Html::dropDownList('CalculationFormula[type_of_car][]', $value['type_of_car'], TYPE_OF_CAR_LIST, ['class' => 'form-control type-of-car-calculation-formula']); ?>
                                            </td>

                                            <td>
                                                <?php echo Html::textInput('CalculationFormula[km_start][]', $value['km_start'], ['class' => 'form-control int', 'placeholder' => 'Số km bắt đầu']); ?>
                                            </td>
                                            <td>
                                                <?php echo Html::textInput('CalculationFormula[km_end][]', $value['km_end'], ['class' => 'form-control int', 'placeholder' => 'Số km kết thúc']); ?>
                                            </td>
                                            <td>
                                                <?php echo Html::textInput('CalculationFormula[price][]', $value['price'], ['class' => 'form-control int', 'placeholder' => 'Giá / km']); ?>
                                            </td>
                                            <td>
                                                <?php echo Html::textInput('CalculationFormula[price_by_km][]', $value['price_by_km'], ['class' => 'form-control int', 'placeholder' => 'Giá niêm yết']); ?>
                                            </td>
                                            <td>
                                                <?php echo Html::textInput('CalculationFormula[surcharge][]', $value['surcharge'], ['class' => 'form-control int', 'placeholder' => 'Phụ phí']); ?>
                                            </td>
                                            <td>
                                                <?php echo Html::textInput('CalculationFormula[price_wait][]', $value['price_wait'], ['class' => 'form-control int price-wait', 'placeholder' => 'Phí chờ']); ?>
                                            </td>
                                            <td>
                                                <?php echo Html::textInput('CalculationFormula[overnight_fee][]', $value['overnight_fee'], ['class' => 'form-control int price-wait', 'placeholder' => 'Phí lưu đêm']); ?>
                                            </td>
                                            <td>
                                                <?php echo Html::textInput('CalculationFormula[description][]', $value['description'], ['class' => 'form-control', 'placeholder' => 'Mô tả']); ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-delete-calculation-formula" type="button"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                                <button class="btn btn-primary btn-copy-calculation-formula" type="button"><i class="fa fa-copy" aria-hidden="true"></i></button>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>

                    </div>
                <?php } ?>
                <div class="pull-right">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-primary btn-save-calculation-formula mt-10']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<script>

    var typeOfCarList = JSON.parse('<?= json_encode(TYPE_OF_CAR_LIST) ?>');
</script>
<?php
$script = <<<JS
        \$(".btn-add-calculation-formula").on("click", function () {
            var tableBody = \$(this).parents('.tab-content').find('.table-calculation-fomular > tbody');
            var typeOfCar  = \$('.tab-chage-type.current').attr('data-value')
            var newRowHtml = `<tr role="row">
                <td><select class="form-control type-of-car-calculation-formula" name="CalculationFormula[type_of_car][]"></select></td>

                <td><input type="text" name="CalculationFormula[km_start][]" class="form-control int" placeholder="Số km bắt đầu"></td>
                <td><input type="text" name="CalculationFormula[km_end][]" class="form-control int" placeholder="Số km kết thúc"></td>
            <td><input type="text" name="CalculationFormula[price][]" class="form-control int" placeholder="Giá / km"></td>
                <td><input type="text" name="CalculationFormula[price_by_km][]" class="form-control int" placeholder="Giá niêm yết"></td>
                <td><input type="text" name="CalculationFormula[surcharge][]" class="form-control int" placeholder="Phụ phí"></td>
                <td><input type="text" name="CalculationFormula[price_wait][]" class="form-control int price-wait" placeholder="Phí chờ"></td>
                <td><input type="text" name="CalculationFormula[overnight_fee][]" class="form-control int price-overnight" placeholder="Lưu đêm"></td>
                <td><input type="text" name="CalculationFormula[description][]" class="form-control" placeholder="Mô tả"></td>
                <td class="text-center">
                <button class="btn btn-danger btn-delete-calculation-formula"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                <button class="btn btn-primary btn-copy-calculation-formula" type="button"><i class="fa fa-copy" aria-hidden="true"></i></button>
                </td>
            </tr>`;

            tableBody.append(newRowHtml);

            var newRow = tableBody.find("tr:last");
            newRow.find('.type-of-car-calculation-formula').html(populateSelectOptions(typeOfCarList));

            newRow.find('.type-of-car-calculation-formula').val(typeOfCar)
            \$('.int').trigger('change')
            return false;
        });

        function populateSelectOptions(data) {
            var optionsHtml = '';
            Object.keys(data).forEach(function (key) {
                optionsHtml += '<option value="' + key + '">' + data[key] + '</option>';
            });
            return optionsHtml;
        }

        \$(document).on("click", ".btn-delete-calculation-formula", function () {
            \$(this).closest("tr").remove();
        });

        \$(document).on("click", ".btn-copy-calculation-formula", function () {
            var originalRow = \$(this).closest('tr');
            var newRow = originalRow.clone();
            originalRow.after(newRow);
        });

        function populateTimeSelect(select) {
            for (var hours = 0; hours < 24; hours++) {
                for (var minutes = 0; minutes < 60; minutes += 15) {
                    var formattedTime = ('0' + hours).slice(-2) + ':' + ('0' + minutes).slice(-2);
                    var option = \$('<option></option>').attr('value', formattedTime).text(formattedTime);
                    select.append(option);
                }
            }
        }

        \$(document).on('click', 'ul.tabs li', function(){
            var tab_id = \$(this).attr('data-tab');
            \$('ul.tabs li').removeClass('current');
            \$('.tab-content').removeClass('current');
            \$(this).addClass('current');
            \$("#"+tab_id).addClass('current');
        })
    JS;
$this->registerJs($script);
?>
