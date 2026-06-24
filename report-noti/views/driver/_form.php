<?php

use app\helpers\MyStringHelper;
use app\models\Driver;
use app\models\Role;
use app\models\VnDistrict;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->registerJsFile('/plugins/ckfinder/ckfinder.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerJsFile('/js/ckfinder.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerJsFile('/js/pages/driver.js', ['depends' => [\yii\web\YiiAsset::class]]);
$lastInsertedID = Driver::find()
    ->select('id')
    ->orderBy(['id' => SORT_DESC])
    ->scalar();
?>

<div class="driver-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Thông tin tài khoản</h3>
                </div>
                <div class="box-body row">
                    <div class="col-lg-6 col-md-12">
                        <?= $form->field($model, 'display_name')->textInput(['maxlength' => true, 'class' => 'form-control input-driver-name']) ?>
                        <?= $form->field($model, 'driver_rank')->dropDownList(RANK_DRIVER_LIST) ?>
                        <?= $form->field($model, 'certificate_type')->dropDownList(CERTIFICATE_TYPE_LIST) ?>
                        <?= $form
                            ->field($model, 'avatar')
                            ->textInput([
                                'maxlength' => true,
                                'class' => 'form-control input-ckfinder',
                                'data-name' => 'avatar',
                            ])
                            ->label('Ảnh đại diện <a class="cursor-pointer small-image ml-10">(<i class="fa fa-eye"></i>)</a>') ?>
                        <?= $form->field($model, 'status')->dropDownList(STATUS_LIST, ['class' => 'form-control status-driver']) ?>
                        <?= $form->field($model, 'point')->textInput(
                            [
                                'type' => 'text',
                                'value' => !$model->isNewRecord
                                    ? MyStringHelper::convertIntegerToPrice($model->point)
                                    : '0',
                                'class' => 'form-control int',
                                'maxlength' => '10',
                            ]
                        );
                        ?>
                        <?= $form
                            ->field($model, 'driver_license_front')
                            ->textInput([
                                'maxlength' => true,
                                'class' => 'form-control input-ckfinder',
                                'data-name' => 'driver_license_front',
                            ])
                            ->label('Ảnh bằng lái xe phía trước <a class="cursor-pointer small-image ml-10">(<i class="fa fa-eye"></i>)</a>') ?>
                        <?= $form
                            ->field($model, 'identity_front_image')
                            ->textInput([
                                'maxlength' => true,
                                'class' => 'form-control input-ckfinder',
                                'data-name' => 'identity_front_image',
                            ])
                            ->label('Ảnh CMND/CCCD phía trước  <a class="cursor-pointer small-image ml-10">(<i class="fa fa-eye"></i>)</a>') ?>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'class' => 'form-control input-driver-phone']) ?>
                        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'english')->dropDownList(ENGLISH_LIST) ?>
                        <?= $form->field($model, 'activity_area')->widget(Select2::class, [
                            'data' => ArrayHelper::map(
                                VnDistrict::find(['provinceid' => '01TTT'])
                                    ->asArray()
                                    ->all(),
                                'districtid',
                                'name'
                            ),
                            'options' => ['placeholder' => 'Select activity area', 'class' => 'form-control'],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]) ?>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <?php
                        $query = new Query();
                        $listDriver = $query->select('display_name, username, car.bks, driver.id')->from('driver')
                            ->leftJoin('car', 'driver.car_id = car.id')
                            ->where(['driver_ban' => STATUS_DRIVER_BAN])
                            ->all();
                        $data = ['0' => 'Chọn tài xế chính'];
                        foreach ($listDriver as $driver) {
                            $data[$driver['id']] = $driver['display_name'] . ' - ' . $driver['bks'] . '(' . $driver['username'] . ')';
                        }
                        ?>
                        <?= $form->field($model, 'parent_id')->widget(Select2::classname(), [
                            'data' => $data,
                            'language' => 'vi',
                            'options' => ['placeholder' => 'Tài xế chính', 'disabled' => isset($model->driver_ban) && $model->driver_ban],
                            'pluginOptions' => [],
                        ]); ?>
                        <?= $form
                            ->field($model, 'driver_license_behind')
                            ->textInput([
                                'maxlength' => true,
                                'class' => 'form-control input-ckfinder',
                                'data-name' => 'driver_license_behind',
                            ])
                            ->label('Ảnh bằng lái xe phía sau  <a class="cursor-pointer small-image ml-10">(<i class="fa fa-eye"></i>)</a>') ?>
                        <?= $form
                            ->field($model, 'identity_back_image')
                            ->textInput([
                                'maxlength' => true,
                                'class' => 'form-control input-ckfinder',
                                'data-name' => 'identity_back_image',
                            ])
                            ->label('Ảnh CMND/CCCD phía sau  <a class="cursor-pointer small-image ml-10">(<i class="fa fa-eye"></i>)</a>') ?>


                        <div class="hidden">
                            <?= $form->field($model, 'enabled')->checkBox(['class' => 'checkbox-enabled']) ?>
                        </div>
                        <?= $form->field($model, 'driver_ban')->checkbox() ?>
                    </div>
                    <div class="col-lg-6 col-md-12 hidden">
                        <?= $form->field($model, 'role')->dropDownList(
                            ArrayHelper::map(
                                Role::find()
                                    ->asArray()
                                    ->all(),
                                'id',
                                'name'
                            )
                        ) ?>
                        <?= $form
                            ->field($model, 'folder_image')
                            ->textInput([
                                'maxlength' => true,
                                'class' => 'form-control input-folder-image',
                                'value' => ! empty($model->folder_image) && ! empty($model->id) ? $model->folder_image : (! empty($model->id) ? 'driver_' . $model->id : 'driver_' . ($lastInsertedID + 1)),
                            ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Thông tin xe chính</h3>
                </div>
                <div class="box-body ">
                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <?= $form->field($carModel, 'type_of_car')->dropDownList(TYPE_OF_CAR_LIST, ['prompt' => 'Chọn loại xe']) ?>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <?= $form->field($carModel, 'bks')->textInput() ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <?= $form->field($carModel, 'color')->textInput() ?>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <?= $form->field($carModel, 'type')->textInput() ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <?= $form->field($carModel, 'car_year')->textInput() ?>
                        </div>
                        <div class="row">
                            <div class="col-lg-3 col-md-12">
                                <?= $form->field($carModel, 'license_type')->radioList(LICENSE_TYPE_LIST, [
                                    'itemOptions' => [
                                        'class' => 'radio-inline',
                                    ],
                                ])->label(false) ?>
                            </div>
                            <div class="col-lg-3 col-md-12">
                                <?= $form->field($carModel, 'car_type')->radioList(CAR_TYPE_LIST, [
                                    'itemOptions' => [
                                        'class' => 'radio-inline',
                                    ],
                                ])->label(false) ?>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <?= $form
                                ->field($carModel, 'album_insurance')
                                ->textInput(['maxlength' => true, 'class' => 'form-control input-ckfinder', 'data-name' => 'album_insurance'])
                                ->label('Ảnh đăng kiểm <a class="cursor-pointer small-image ml-10">(<i class="fa fa-eye"></i>)</a>') ?>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <?= $form
                                ->field($carModel, 'album_registration_certificate')
                                ->textInput(['maxlength' => true, 'class' => 'form-control input-ckfinder', 'data-name' => 'album_registration_certificate'])
                                ->label('Ảnh đăng kiểm 2 <a class="cursor-pointer small-image ml-10">(<i class="fa fa-eye"></i>)</a>') ?>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div style="margin-bottom:10px">
                                <?= $form
                                    ->field($carModel, 'registration_certificate_front')
                                    ->textInput(['class' => 'form-control input-ckfinder', 'data-name' => 'registration_certificate_front'])
                                    ->label('Mặt trước ảnh đăng ký xe <a class="cursor-pointer small-image ml-10">(<i class="fa fa-eye"></i>)</a>') ?>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div style="margin-bottom:10px">
                                <?= $form
                                    ->field($carModel, 'registration_certificate_behind')
                                    ->textInput(['class' => 'form-control input-ckfinder', 'data-name' => 'registration_certificate_behind'])
                                    ->label('Mặt sau ảnh đăng ký xe <a class="cursor-pointer small-image ml-10">(<i class="fa fa-eye"></i>)</a>') ?>
                            </div>
                        </div>
                        <!-- <div class="col-lg-6 col-md-12">
                            <?= $form
                                ->field($carModel, 'vehicle_plate_image')
                                ->textInput(['maxlength' => true, 'class' => 'form-control input-ckfinder', 'data-name' => 'vehicle_plate_image'])
                                ->label('Ảnh biển số xe <a class="cursor-pointer small-image ml-10">(<i class="fa fa-eye"></i>)</a>') ?>
                        </div> -->
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <?= $form->field($carModel, 'note')->textArea(['rows' => 5]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group col-md-12">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            <?php if (!$model->isNewRecord && $model->status == 0): ?>
                <?= Html::a(
                    '<i class="fa fa-check"></i> Accept',
                    ['driver/accept', 'id' => $model->id],
                    [
                        'class' => 'btn btn-primary',
                        'style' => 'margin-left:10px',
                        'data-confirm' => 'Duyệt tài khoản này?',
                        'data-method' => 'post',
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<div id="show_image_popup">
    <div class="close-btn-area">
        <button id="close-btn">X</button>
    </div>
    <div id="image-show-area">
        <img id="large-image" src="" alt="">
    </div>
</div>
<?php
$script = <<<JS
    $(document).on("click", ".status-driver", function () {
        let val = $(this).val();
        if(val == 2){
            $('.checkbox-enabled').prop('checked', false)
        }else{
            $('.checkbox-enabled').prop('checked', true)
        }
        return false;
    })

    $(document).on("change", "input#driver-driver_ban", function () {
        let val = $('input#driver-driver_ban:checked').val();
        if(val != true){
            $('#driver-parent_id').prop("disabled", false);
        }else{
            $('#driver-parent_id').prop("disabled", true);
        }
        return false;
    })
JS;
$this->registerJs($script);
?>
