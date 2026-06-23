<?php

use app\models\DriverSub;
use yii\helpers\Html;
use yii\widgets\DetailView;

?>
<?php if (isset($_GET['method']) && $_GET['method'] == 'driver') { ?>
    <?php
    $driverId = \app\models\Bid::find()->where(['trip_id' => $model['id'], 'status' => STATUS_BID_SUCCESS])->one()->driver_id;
    $driverSubModel = \app\models\DriverSub::find()->where(['trip_id' => $model->id, 'driver_id' => $driverId])->one();
    if (! isset($driverSubModel->id) || empty($driverSubModel->id)) {
        $driverSubModel = new DriverSub();
    }
    $driver = \app\models\Driver::findOne($driverId);
    if ($driver->driver_ban) {
        ?>
        <div class="box box-danger box-driver-trip" data-id="<?= $model->id ?>" data-driverid="<?= $driverId ?>"
            data-driversub="<?= $driverSubModel->id ?>">
            <div class="box-header with-border " style="align-items:center; display:flex">
                <h3 class="box-title" style="margin-right: 20px;">Thông tin lái xe bid nhiều xe</h3>
                <?= Html::Button('Gửi thông tin lái xe qua Zalo', ['class' => 'btn btn-primary btn-send-message', 'style' => 'margin-right: 20px']) ?>
            </div>
            <div class="box-body">
                <div class="driver-primary-info" style="margin-bottom: 15px;">
                    <?= DetailView::widget([
                        'model' => $driver,
                        'attributes' => [
                            [
                                'label' => 'Lái xe chính',
                                'value' => '',
                                'captionOptions' => ['class' => 'table-caption', 'style' => 'color:#ff4b4b'],
                            ],
                            'display_name',
                            'username',
                            'car.bks',
                            'car.type',
                        ],
                    ]) ?>
                </div>
                <div class="driver-sub-info">
                    <?php if (isset($driverSubModel->id) && ! empty($driverSubModel->id)) { ?>
                        <?= DetailView::widget([
                            'model' => $driverSubModel,
                            'attributes' => [
                                [
                                    'label' => 'Lái xe phụ',
                                    'value' => '<button class="btn btn-warning btn-edit-driver-sub" style="float:right">Chỉnh sửa</button>',
                                    'captionOptions' => ['class' => 'table-caption', 'style' => 'color: #f39c12'],
                                    'format' => 'raw',
                                ],
                                'name',
                                'phone',
                                'bks',
                                'type',
                            ],
                        ]); ?>
                    <?php } ?>
                </div>
                <?php echo $form->field($model, 'driver_sub')->radioList([
                    0 => 'Lái xe chính',
                    1 => 'Lái xe phụ',
                ], ['class' => 'change-driver-trip'])->label('Chọn trạng thái gửi zalo') ?>
            </div>
            <div class="box-driver-sub hidden">
                <div class="box-header with-border">
                    <div class="box-title">Thông tin lái xe phụ</div>
                </div>
                <div class="box-body">
                    <div class="hidden">
                        <?= $form->field($driverSubModel, 'id')->textInput() ?>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($driverSubModel, 'name')->textInput() ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($driverSubModel, 'phone')->textInput() ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($driverSubModel, 'bks')->textInput() ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($driverSubModel, 'type')->textInput() ?>
                        </div>
                    </div>
                    <?= Html::Button('Lưu lại', ['class' => 'btn btn-primary btn-save-driver-sub', 'style' => 'margin-right: 20px']) ?>
                </div>
            </div>
        </div>
    <?php
    } ?>
<?php } ?>

<?php
$script = <<<JS
    $(".btn-save-driver-sub").click(function(){
        let driver_sub = {
            name: $('#driversub-name').val(),
            phone: $('#driversub-phone').val(),
            bks: $('#driversub-bks').val(),
            type: $('#driversub-type').val(),
        };
        let id =  $('#driversub-id').val();
        let trip_id = $(this).parents('.box-driver-trip').attr("data-id");
        let driver_id = $(this).parents('.box-driver-trip').attr("data-driverid");
        let check = true;
        // Object.entries(driver_sub).forEach(([key, value]) => {
        //     if (value.trim() === '') {
        //         toastr.error(key + " không được để trống.");
        //         check = false;
        //     }
        // });
        if (check) {
            $.ajax({
                url: '/driver/change-driver-sub',
                type: 'post',
                data: {
                    id: id,
                    name: driver_sub['name'],
                    phone: driver_sub['phone'],
                    bks: driver_sub['bks'],
                    type: driver_sub['type'],
                    _csrf : $('meta[name="csrf-token"]').attr("content"),
                    trip_id : trip_id,
                    driver_id : driver_id,
                },
                success: function (data) {
                    let json = JSON.parse(data)
                    $('.driver-sub-info').html(json.html)
                    $('.box-driver-trip').attr('data-driversub', json.driver_id)
                    $('.box-driver-sub').addClass('hidden')
                },
                error:function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR.responseText);
                }
            });
        }
        return false;
    });
    
    $(document).on('change', '.change-driver-trip input', function(){
        let val = $(this).val()
        if(val == 1){
            $('.box-driver-sub').removeClass('hidden')
        }else{
            $('.box-driver-sub').addClass('hidden')
        }

        $.ajax({
            url: '/trip/change-status-driver-sub',
            type: 'post',
            data: {
                _csrf : $('meta[name="csrf-token"]').attr("content"),
                trip_id : $(this).parents('.box-driver-trip').attr("data-id"),
                driver_id : $(this).parents('.box-driver-trip').attr("data-driverid"),
                driver_sub : val,
            },
            success: function (data) {}
        });
    })

    $(document).on('click', '.btn-edit-driver-sub', function(){
        $('.box-driver-sub').removeClass('hidden')
        $('.change-driver-trip input[value=1]').trigger('click')
        return false;
    })

    $(".btn-send-message").click(function(){
        let check_driver_sub = $('.change-driver-trip input:checked').val()
        let drive_sub = $(this).parents('.box-driver-trip').attr("data-driversub");
        if(drive_sub > 0 && check_driver_sub == 1 || check_driver_sub == 0){
            $.ajax({
                url: '/trip/send-message-driver-zns',
                type: 'post',
                data: {
                    _csrf : $('meta[name="csrf-token"]').attr("content"),
                    trip_id : $(this).parents('.box-driver-trip').attr("data-id"),
                    driver_sub : check_driver_sub
                },
                success: function (data) {
                    let json = JSON.parse(data);
                    if(json.error){
                        alert(json.message)
                    }
                }
            });
        }else{
            alert("Xin vui lòng nhập thông tin lái xe phụ!");
        }
        return false;
    });
JS;
$this->registerJs($script);
?>