<div class="hidden value-trip-group-hidden">
    <?= $form->field($modelTripGroup, 'type')->textInput([
        'id' => 'checkboxType',
        'value' => !empty($modelTripGroup->type) ? $modelTripGroup->type : 0,
    ]) ?>
    <?= $form->field($modelTripGroup, 'driver_name')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
    <?= $form->field($modelTripGroup, 'driver_phone')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
    <?= $form->field($modelTripGroup, 'license_plates')->textInput(['maxlength' => 255, 'class' => 'form-control']) ?>
    <?= $form->field($modelTripGroup, 'type_of_car')->dropDownList(TYPE_OF_CAR_LIST, ['prompt' => 'Chọn loại xe', 'class' => 'form-control']) ?>
    <?= $form->field($modelTripGroup, 'price')->textInput(['maxlength' => 10, 'class' => 'form-control int-price-point']) ?>
</div>
<div
    class=" wrap-info-zalo-transfer <?php echo (isset($modelTripGroup->zalo_seller_id) && $modelTripGroup->zalo_seller_id > 0 && $modelTripGroup->group_zalo_id ? '' : 'hidden') ?>">
    <ul class="tabs">
        <li class="tab type-0 tab-chage-type <?php echo ($modelTripGroup->type == 1 ? 'current' : '') ?>" data-value="1"
            data-tab="tab-2">Tính trực tiếp lái xe</li>
    </ul>
    <div id="tab-2" class="tab-content <?php echo ($modelTripGroup->type == ZALO_TYPE_DRIVER ? 'current' : '') ?>">
        <?= $form->field($modelTripGroup, 'driver_name')->textInput(['maxlength' => true, 'class' => 'form-control value-trip-group name-trip-group']) ?>
        <?= $form->field($modelTripGroup, 'driver_phone')->textInput(['maxlength' => true, 'class' => 'form-control value-trip-group phone-trip-group']) ?>
        <?= $form->field($modelTripGroup, 'price')->textInput(['maxlength' => true, 'class' => 'form-control value-trip-group int price-trip-group', 'maxlength' => '10']) ?>
        <?= $form->field($modelTripGroup, 'license_plates')->textInput(['maxlength' => 255, 'class' => 'form-control value-trip-group license_plates-trip-group']) ?>
        <?= $form->field($modelTripGroup, 'type_of_car')->dropDownList(TYPE_OF_CAR_LIST, ['prompt' => 'Chọn loại xe', 'class' => 'form-control value-trip-group type_of_car-trip-group']) ?>
    </div>
</div>

<?php
$script = <<<JS
    $('.value-trip-group').attr('name', '');
    $(document).on('keyup keydown change blur', '.name-trip-group', function() {
        let val = $(this).val();
        $('.value-trip-group-hidden').find('#tripgroup-driver_name').val(val)
    });
    $(document).on('keyup keydown change blur', '.phone-trip-group', function() {
        let val = $(this).val();
        $('.value-trip-group-hidden').find('#tripgroup-driver_phone').val(val)
    });
    $(document).on('keyup keydown change blur', '.price-trip-group', function() {
        let val = $(this).val();
        $('.value-trip-group-hidden').find('#tripgroup-price').val(val)
    });
    $(document).on('keyup keydown change blur', '.license_plates-trip-group', function() {
        let val = $(this).val();
        $('.value-trip-group-hidden').find('#tripgroup-license_plates').val(val)
    });
    $(document).on('change', '.type_of_car-trip-group', function() {
        let val = $(this).val();
        $('.value-trip-group-hidden').find('#tripgroup-type_of_car').val(val)
    });

    var countZalo = 0;

    $(document).on('submit', '#form-create-trip', function() {
        let form = $(this);
        let zaloSellerId = $('#tripgroup-zalo_seller_id').val();

        if (zaloSellerId) {
            if (!$('#tripgroup-group_zalo_id').val()) {
                let label = form.find('label[for="tripgroup-group_zalo_id"]').first().text();
                alert('Xin vui lòng điền vào trường: ' + label);
                return false;
            }

            let formIsEmpty = true;
            form.find('.wrap-info-zalo-transfer :input:not(:hidden,:button)').each(function() {
                let element = $(this);
                let value = element.val();
                if (!value) {
                    let label = form.find('label[for="' + element.attr('id') + '"]').first().text();
                    alert('Xin vui lòng điền vào trường: ' + label);
                    formIsEmpty = false;
                    return false;
                }
            });

            if (!formIsEmpty) {
                return false;
            }
        }
    });

    $(document).on('click', 'ul.tabs li', function(){
        var tab_id = $(this).attr('data-tab');
        $('ul.tabs li').removeClass('current');
        $('.tab-content').removeClass('current');
        $('.wrap-info-zalo-transfer').find('.input-zalo-disable').attr('disabled', 'true')
        $(this).addClass('current');
        $("#"+tab_id).addClass('current');
        if(countZalo < 1) $("#"+tab_id+ ' input').val('')
        $("#"+tab_id).find('.input-zalo-disable').removeAttr('disabled')
        countZalo++;
    })

    $(document).on('click', '.tab-chage-type', function(){
        let type = $(this).attr('data-value')

        $('#checkboxType').val(type)
    })
JS;
$this->registerJs($script);
?>
