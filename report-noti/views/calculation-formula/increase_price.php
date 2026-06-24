<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Cấu hình giá tự động tăng';
?>
<style>
    .type-of-car-increase-price {
        pointer-events: none;
        cursor: default;
    }
</style>
  <div class="system-configuration-create row">
    <?php $form = ActiveForm::begin(['action' => ['/calculation-formula/update-increase-price']]); ?>
    <div class="col-md-12">
      <div class="box box-success">
        <div class=" wrap-increase-price box-body">
          <ul class="tabs">
            <?php foreach (TYPE_OF_CAR_LIST as $key => $value) { ?>
              <li class="tab type-<?= $key ?> tab-change-type <?= $key == 1 ? 'current' : '' ?>" data-value="<?= $key ?>" data-tab="tab-<?= $key ?>"><?= $value ?></li>
            <?php } ?>
          </ul>
          <?php foreach (TYPE_OF_CAR_LIST as $key => $valueTypeOfCar) { ?>
            <div id="tab-<?= $key ?>" class="tab-content <?= $key == 1 ? 'current' : '' ?>" style="background: #fff">
              <div style="display: flex; align-items:center; justify-content: space-between">
                <h3 class="box-title" style="width: 100%; margin: 0">Bảng giá </span></h3>
                <button class="btn btn-add-increase-price btn-primary">Thêm mới</button>
              </div>
              <table class="table table-striped table-bordered table-increase-price" width="100%"
                     cellspacing="0" style="background: #fff;margin-bottom: 0">
                <thead>
                <tr>
                  <th style="width: 150px;vertical-align:middle;">Loại xe</th>
                  <th style="width: 150px;vertical-align:middle;" class="text-center">Số phút trước thời gian đi</th>
                  <th style="width: 150px;vertical-align:middle;" class="text-center">Giá tăng</th>
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
                        <?php echo Html::dropDownList('IncreasePrice[type_of_car][]', $value['type_of_car'], TYPE_OF_CAR_LIST, ['class' => 'form-control type-of-car-increase-price']); ?>
                      </td>
                      <td>
                        <?php echo Html::textInput('IncreasePrice[minute_before][]', $value['minute_before'], ['class' => 'form-control int']); ?>
                      </td>
                      <td>
                        <?php echo Html::textInput('IncreasePrice[price_increase][]', $value['price_increase'], ['class' => 'form-control int']); ?>
                      </td>
                      <td class="text-center">
                        <button class="btn btn-danger btn-delete-increase-price" type="button"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
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
            <?= Html::submitButton('Save', ['class' => 'btn btn-primary btn-save-increase-price mt-10']) ?>
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
    $(".btn-add-increase-price").on("click", function () {
        var currentTabValue = getSelectedTabValue();
        var tableBody = $(this).parents('.tab-content').find('.table-increase-price > tbody');
        var newRowHtml = `<tr role="row">
            <td><select class="form-control type-of-car-increase-price" name="IncreasePrice[type_of_car][]"></select></td>
            <td><input type="text" name="IncreasePrice[minute_before][]" class="form-control int"></td>
            <td><input type="text" name="IncreasePrice[price_increase][]" class="form-control int"></td>
            <td class="text-center">
            <button class="btn btn-danger btn-delete-increase-price"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
            </td>
        </tr>`;
        
        tableBody.append(newRowHtml);
        
        var newRow = tableBody.find("tr:last");
        newRow.find('.type-of-car-increase-price').html(populateSelectOptions(typeOfCarList, currentTabValue));
        return false;
    });

    function populateSelectOptions(data, selectedValue) {
        var optionsHtml = '';
        Object.keys(data).forEach(function (key) {
            var item = data[key];
            var isSelected = key == selectedValue; 
            optionsHtml += '<option value="' + key + '" ' + (isSelected ? 'selected' : '') + '>' + item + '</option>';
        });
        return optionsHtml;
    }

    $(document).on("click", ".btn-delete-increase-price", function () {
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