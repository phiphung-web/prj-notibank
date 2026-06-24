<?php

use app\helpers\MyStringHelper;
use app\models\GroupZalo;
use app\models\GroupZaloCatalogue;
use app\models\GroupZaloSeller;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;

?>
<div class="modal" id="modalTransferZalo">
    <div class="modal-overlay modal-toggle"></div>
    <div class="modal-wrapper modal-transition">
        <?php $form = ActiveForm::begin(['action' => '/trip/transfer', 'method' => 'post', 'id' => 'form-transfer-zalo']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-toggle" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Chuyển nhóm bán cho bên thứ 3</h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-6">
                        <?= $form->field($model, 'zalo_seller_id')->widget(Select2::classname(), [
                            'data' => \yii\helpers\ArrayHelper::map(GroupZaloSeller::find()->orderBy('name', 'asc')->all(), 'id', 'name'),
                            'options' => ['class' => 'action-change-seller-zalo form-control'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'placeholder' => 'Chọn người bán qua bên thứ 3',
                            ],
                            'value' => isset($model->zalo_seller_id) ? $model->zalo_seller_id : '',
                        ]); ?>
                    </div>
                    <div class="col-lg-6 wrap-seller-zalo hidden">
                        <?php
                        $categories = GroupZaloCatalogue::find()->where(['status' => '1'])->orderBy('name', 'asc')->all();
                        $items = GroupZalo::find()->where(['status' => '1'])->all();
                        $options = [];
                        foreach ($categories as $category) {
                            $groupOptions = \yii\helpers\ArrayHelper::map(
                                array_filter($items, function ($item) use ($category) {
                                    return $item->group_zalo_catalogue == $category->id;
                                }),
                                'id',
                                function ($item) {
                                    return $item->name;
                                }
                            );

                            if (! empty($groupOptions)) {
                                $options[$category->name] = $groupOptions;
                            }
                        }
                        ?>
                        <?= $form->field($model, 'group_zalo_id')->widget(Select2::class, [
                            'data' => $options,
                            'options' => ['class' => 'action-change-group-zalo form-control'],
                        ]); ?>
                    </div>
                </div>
                <?php echo $this->render('_form_trip_group', [
                    'modelTripGroup' => $model,
                    'form' => $form,
                ]) ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default modal-toggle" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$script = <<<JS
    $(document).on('click', '.modal-toggle', function(e) {
        e.preventDefault();
        let modalTransferZalo =  $('#modalTransferZalo');
        modalTransferZalo.toggleClass('is-visible');
        modalTransferZalo.find('#form-transfer-zalo')[0].reset();
    });

    $(document).on('submit', '#form-transfer-zalo', function(e) {
        let form = $(this);
        let formIsEmpty = true;
        form.find(':input:not(:hidden,:button)').each(function () {
            let element = $(this);
            let value = element.val();
            if (value === '' || value === null) {
                let label = form.find('label[for="' + element.attr('id') + '"]').first().text();
                toastr.error('Xin vui lòng điền vào trường: ' + label,'Thất bại!');
                formIsEmpty = false;
                return false;
            }
        });

        if (!formIsEmpty) {
            return false;
        }
    });

    $(document).on('click', '.transfer-group-zalo', function(){
        let id = $(this).attr('data-id');
        $('#modalTransferZalo').find('form').attr('action', '/trip/transfer/' + id);

        // Reset the form: clear form fields, unselect select2, remove errors, reset toggles/tabs
        let form = $('#form-transfer-zalo');
        form[0].reset();

        // If using select2 or other UI, reset them as well
        form.find('select').val(null).trigger('change');
        form.find('.has-error').removeClass('has-error');
        $('.tab-chage-type, .tab-content').removeClass('current');
        $('.wrap-info-zalo-transfer').addClass('hidden');

        // Optionally, reset checkbox as well
        $('#checkboxType').prop('checked', false);

        // If you still want to fill data from data-json, do it after reset
        let base64 = $(this).attr('data-json');
        let json = JSON.parse(atob(base64));
        if(json){
            $('input[name="TripGroup[id]"]').val(json.id);
            $('select[name="TripGroup[group_zalo_id]"]').val(json.group_zalo_id).trigger('change');
            $('input#tripgroup-driver_name').val(json.driver_name);
            $('input#tripgroup-driver_phone').val(json.driver_phone);
            $('input#tripgroup-price').val(json.price);
            if(json.type == 0){
                $('#checkboxType').prop('checked', false);
            }else if(json.type == 1){
                $('#checkboxType').prop('checked', true);
            }
            if (json.group_zalo_id > 0) {
                $('.wrap-info-zalo-transfer').removeClass('hidden');
                $('.type-' + json.type).trigger('click');
            } else {
                $('.wrap-info-zalo-transfer').addClass('hidden');
            }
        }
    });

    $(document).on('change', '.action-change-group-zalo', function(){
        let val = $(this).val()
        if(val != 0 && val != null && val != undefined && val != '') {
            if(!$('.tab-chage-type.current').hasClass('current')) $('.type-0').trigger('click');
            $('.wrap-info-zalo-transfer').removeClass('hidden')
        }else{
            $('.wrap-info-zalo-transfer').addClass('hidden')
        }
    })

    $(document).on('change', '.action-change-seller-zalo', function(){
        let val = $(this).val();
        let element = $('.action-change-group-zalo');
        if(val != 0 && val != null && val != undefined && val != ''){
            $('.wrap-seller-zalo').removeClass('hidden')
        }else{
            $('.wrap-seller-zalo').addClass('hidden')
            $.ajax({
                type: "POST",
                url: "get-zalo",
                data: { id: val },
                success: function (response) {
                    let json = JSON.parse(response)
                    element.empty();
                    $.each(json, function(key, value) {
                        if ($.isPlainObject(value)) {
                            var optgroup = $('<optgroup label="' + key + '"></optgroup>');
                            addOptions(optgroup, value);
                            element.append(optgroup);
                        } else {
                            var option = $('<option></option>').attr('value', key).text(value);
                            element.append(option);
                        }
                    });
                    element.select2();
                }
            });
        }
    });

    function addOptions(element, data) {
        $.each(data, function(key, value) {
            if ($.isPlainObject(value)) {
                var optgroup = $('<optgroup label="' + key + '"></optgroup>');
                addOptions(optgroup, value);
                element.append(optgroup);
            } else {
                var option = $('<option></option>').attr('value', key).text(value);
                element.append(option);
            }
        });
    }
JS;
$this->registerJs($script);
?>
