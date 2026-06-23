<?php
  use kartik\time\TimePicker;
  use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Cấu hình giá tự động bán';
?>
  <style>
    .type-of-car-config-auto-sale {
      pointer-events: none;
      cursor: default;
    }
  </style>
  <div class="system-configuration-create row">
    <?php $form = ActiveForm::begin(['action' => ['/calculation-formula/update-config-auto-sale']]); ?>
    <div class="col-md-12">
      <div class="box box-success">
        <div class=" wrap-config-auto-sale box-body">
          <ul class="tabs">
            <?php foreach (TYPE_OF_CAR_LIST as $key => $value) { ?>
              <li class="tab type-<?= $key ?> tab-change-type <?= $key == 1 ? 'current' : '' ?>" data-value="<?= $key ?>" data-tab="tab-<?= $key ?>"><?= $value ?></li>
            <?php } ?>
          </ul>
          <?php foreach (TYPE_OF_CAR_LIST as $key => $valueTypeOfCar) { ?>
            <div id="tab-<?= $key ?>" class="tab-content <?= $key == 1 ? 'current' : '' ?>" style="background: #fff">
              <div style="display: flex; align-items:center; justify-content: space-between">
                <h3 class="box-title" style="width: 100%; margin: 0">Bảng giá </span></h3>
                <button class="btn btn-add-config-auto-sale btn-primary">Thêm mới</button>
              </div>
              <table class="table table-striped table-bordered table-config-auto-sale" width="100%"
                     cellspacing="0" style="background: #fff;margin-bottom: 0">
                <thead>
                <tr>
                  <th style="width: 150px;vertical-align:middle;">Loại xe</th>
                  <th style="width: 150px;vertical-align:middle;" class="text-center">Thời gian bắt đầu</th>
                    <th style="width: 150px;vertical-align:middle;" class="text-center">Thời gian kết thúc</th>
                  <th style="width: 150px;vertical-align:middle;" class="text-center">Lịch trình</th>
                  <th style="width: 150px;vertical-align:middle;" class="text-center">Giá chênh lệch</th>
                  <th style="width: 150px;vertical-align:middle;" class="text-center"></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (isset($dataProvider) && is_array($dataProvider) && count($dataProvider)) {
                    foreach ($dataProvider as $value) {
                        if ($value['type_of_car'] != $key) {
                            continue;
                        } ?>
                    <tr role="row">
                      <td>
                        <?php echo Html::dropDownList('ConfigAutoSale[type_of_car][]', $value['type_of_car'], TYPE_OF_CAR_LIST, ['class' => 'form-control type-of-car-config-auto-sale']); ?>
                      </td>
                      <td>
                        <?php echo TimePicker::widget([
                          'name' => 'ConfigAutoSale[from_time][]',
                          'value' => $value['from_time'],
                          'pluginOptions' => [
                            'format' => 'HH:mm',
                            'showSeconds' => false,
                            'showMeridian' => false,
                            'autoclose' => true,
                          ],
                        ]); ?>
                      </td>
                        <td>
                          <?php echo TimePicker::widget([
                            'name' => 'ConfigAutoSale[to_time][]',
                            'value' => $value['to_time'],
                            'pluginOptions' => [
                              'format' => 'HH:mm',
                              'showSeconds' => false,
                              'showMeridian' => false,
                              'autoclose' => true,
                            ],
                          ]); ?>
                        </td>
                        <td>
                          <?php echo Html::dropDownList('ConfigAutoSale[schedule][]', $value['schedule'], SCHEDULE_LIST_TRIP, ['class' => 'form-control schedule-config-auto-sale']); ?>
                        </td>
                      <td>
                        <?php echo Html::textInput('ConfigAutoSale[price][]', $value['price'], ['class' => 'form-control int']); ?>
                      </td>
                      <td class="text-center">
                        <button class="btn btn-danger btn-delete-config-auto-sale" type="button"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                      </td>
                    </tr>
                  <?php
                    }
                } ?>
                </tbody>
              </table>

            </div>
          <?php } ?>
          <div class="pull-right">
            <?= Html::submitButton('Save', ['class' => 'btn btn-primary btn-save-config-auto-sale mt-10']) ?>
          </div>
        </div>
      </div>
    </div>
    <?php ActiveForm::end(); ?>
  </div>
  <script>
      var scheduleList = JSON.parse('<?= json_encode(SCHEDULE_LIST_TRIP) ?>');
      var typeOfCarList = JSON.parse('<?= json_encode(TYPE_OF_CAR_LIST) ?>');
  </script>
<?php
$script = <<<JS

    function getSelectedTabValue() {
        return $('.tabs li.current').data('value');
    }
    $(".btn-add-config-auto-sale").on("click", function () {
        var currentTabValue = getSelectedTabValue();
        var tableBody = $(this).parents('.tab-content').find('.table-config-auto-sale > tbody');
        var newRowHtml = `<tr role="row">
            <td><select class="form-control type-of-car-config-auto-sale" name="ConfigAutoSale[type_of_car][]"></select></td>
            <td><div class="bootstrap-timepicker input-group"><input type="text" class="form-control time-picker" name="ConfigAutoSale[from_time][]" value="00:00:00"><span class="input-group-addon picker"><i class="glyphicon glyphicon-time"></i></span></div></td>
            <td><div class="bootstrap-timepicker input-group"><input type="text" class="form-control time-picker" name="ConfigAutoSale[to_time][]" value="00:00:00"><span class="input-group-addon picker"><i class="glyphicon glyphicon-time"></i></span></div></td>
            <td><select class="form-control schedule-config-auto-sale" name="ConfigAutoSale[schedule][]"></select></td>
            <td><input type="text" name="ConfigAutoSale[price][]" class="form-control int"></td>
            <td class="text-center">
            <button class="btn btn-danger btn-delete-config-auto-sale"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
            </td>
        </tr>`;
        
        tableBody.append(newRowHtml);
        
        var newRow = tableBody.find("tr:last");
        newRow.find('.type-of-car-config-auto-sale').html(populateSelectOptions(typeOfCarList, currentTabValue));
        newRow.find('.schedule-config-auto-sale').html(populateSelectOptions(scheduleList));
        newRow.find('.time-picker').timepicker({
            format: 'HH:mm',
            showSeconds: false,
            showMeridian: false,
            autoclose: true
        });
        return false;
    });
    function populateTimeSelect(select) {
        for (var hours = 0; hours < 24; hours++) {
            for (var minutes = 0; minutes < 60; minutes += 15) {
                var formattedTime = ('0' + hours).slice(-2) + ':' + ('0' + minutes).slice(-2);
                var option = $('<option></option>').attr('value', formattedTime).text(formattedTime);
                select.append(option);
            }
        }
    }

    function populateSelectOptions(data, selectedValue) {
        var optionsHtml = '';
        Object.keys(data).forEach(function (key) {
            var item = data[key];
            var isSelected = key == selectedValue; 
            optionsHtml += '<option value="' + key + '" ' + (isSelected ? 'selected' : '') + '>' + item + '</option>';
        });
        return optionsHtml;
    }

    $(document).on("click", ".btn-delete-config-auto-sale", function () {
        $(this).closest("tr").remove();
    });

    $(document).on('click', 'ul.tabs li', function(){
        var tab_id = $(this).attr('data-tab');
        $('ul.tabs li').removeClass('current');
        $('.tab-content').removeClass('current');
        $(this).addClass('current');
        $("#"+tab_id).addClass('current');
    })
JS;
$this->registerJs($script);
?>