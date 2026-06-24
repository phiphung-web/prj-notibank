<?php

use yii\helpers\Html;
use yii\web\YiiAsset;

$this->registerCssFile('/css/pages/agency.css');
$this->registerJsFile('/js/pages/agency.js', ['depends' => [YiiAsset::class]]);
?>
<div class="row">
    <div class="col-lg-6">
        <?= $form->field($agency, 'name')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-lg-6">
        <?= $form->field($agency, 'contact_person')->textInput() ?>
    </div>
    <div class="col-lg-6">
        <?= $form->field($agency, 'phone')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-lg-6">
        <?= $form->field($agency, 'email')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-lg-12">
        <?= $form->field($agency, 'address')->textInput(['maxlength' => true]) ?>

        <?= $form->field($agency, 'note')->textarea() ?>
    </div>
    <div class="col-lg-6">
        <div class="form-row relative">
            <label class="control-label text-left">
                <span>Hoa hồng (VNĐ)</span>
            </label>
            <input type="text" name="Agency[price]"
                value="<?= isset($_POST['Agency']['price']) ? htmlspecialchars($_POST['Agency']['price']) : (isset($agency->price) ? htmlspecialchars($agency->price) : '0'); ?>"
                class="form-control int" placeholder="" autocomplete="off">
            <div style="" class="btn btn-white extend text-promotion-agency">VNĐ</div>
        </div>
        <div class="help-block" style="margin-top: 5px; color: #777;">
            <i class="fa fa-info-circle"></i> Số tiền cố định cho mỗi chuyến. Áp dụng khi lợi nhuận > số tiền này.
        </div>
    </div>

    <div class="col-lg-6 mb15">
        <div class="form-row relative">
            <label class="control-label text-left">
                <span>Hoa hồng (%)</span>
            </label>
            <input type="text" name="Agency[percent]"
                value="<?= isset($_POST['Agency']['percent']) ? htmlspecialchars($_POST['Agency']['percent']) : (isset($agency->percent) ? htmlspecialchars($agency->percent) : '0'); ?>"
                class="form-control int" placeholder="" autocomplete="off">
            <div style="" class="btn btn-white extend text-promotion-agency">%</div>
        </div>
        <div class="help-block" style="margin-top: 5px; color: #777;">
            <i class="fa fa-info-circle"></i> Phần trăm trên giá khách. Ưu tiên áp dụng nếu lợi nhuận đủ lớn.
        </div>
    </div>

    <div class="col-lg-12">
        <div class="alert alert-info" style="margin-bottom: 15px;">
            <strong><i class="fa fa-lightbulb-o"></i> Cách tính hoa hồng:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li><strong>Ưu tiên 1:</strong> Nếu % được set và lợi nhuận (Giá khách - Giá tài xế) > (Giá khách × %),
                    hệ thống sẽ tính theo %</li>
                <li><strong>Ưu tiên 2:</strong> Nếu không đủ điều kiện ưu tiên 1, kiểm tra lợi nhuận > Số tiền cố định →
                    áp dụng số tiền cố định</li>
                <li><strong>Mặc định:</strong> Nếu không thỏa mãn cả 2 điều kiện trên, hoa hồng = 0</li>
                <li><strong>Lưu ý:</strong> Có thể set cả 2 giá trị để linh hoạt cho các loại chuyến đi khác nhau</li>
            </ul>
        </div>
    </div>


    <div class="col-lg-6">
        <?= $form->field($agency, 'status')->dropDownList(
            AGENCY_STATUS,
            [
                'options' => [
                    $agency->status => ['selected' => true],
                ]
            ]
        )
            ?>
    </div>
    <div class="col-lg-6">
        <?= $form->field($agency, 'send_price')->checkbox() ?>

        <?= $form->field($agency, 'agency_debt')->checkbox() ?>
    </div>
    <div class="col-lg-12">
        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>
