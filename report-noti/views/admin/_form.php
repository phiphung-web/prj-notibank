<?php

use app\helpers\MyStringHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model app\models\Admin */
/* @var $dataAgency */

?>
<?php
$this->registerJsFile('/plugins/ckfinder/ckfinder.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerJsFile('/js/ckfinder.js', ['depends' => [\yii\web\YiiAsset::class]]);
?>
<div class="admin-form">

    <?php $form = ActiveForm::begin([
		'options' => ['enctype' => 'multipart/form-data']
	]); ?>
    <?php if ($model->isNewRecord) { ?>
    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-lg-6">
            <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
            <?php } ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'bonus', [
					'enableClientValidation' => false, //  clear client validation
				])->textInput([
					'type' => 'text',
					'value' => !$model->isNewRecord
						? MyStringHelper::convertIntegerToPrice($model->bonus)
						: '0',
					'class' => 'form-control input-count-character int',
					'data-max' => '10',
					'maxlength' => '10',
				]); ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'role')->widget(\kartik\select2\Select2::classname(), [
					'data' => [
						'NO_ROLE' => 'NO_ROLE',
						'ADMIN_ROLE' => 'ADMIN_ROLE',
						'MOD_ROLE' => 'MOD_ROLE',
						'SUPERMOD_ROLE' => 'SUPERMOD_ROLE',
						'NHAN_VIEN_ROLE' => 'NHAN_VIEN_ROLE',
						'NHAN_VIEN_2_ROLE' => 'NHAN_VIEN_2_ROLE',
						'DAI_LY_ROLE' => 'DAI_LY_ROLE',
						'KE_TOAN_ROLE' => 'KE_TOAN_ROLE',
						'QUAN_LY_ROLE' => 'QUAN_LY_ROLE',
						'APP_BANK_ROLE' => 'APP_BANK_ROLE',
						'QUAN_LY_LAI_XE' => 'QUAN_LY_LAI_XE',
						'MARKETING_ROLE' => 'MARKETING_ROLE'
					],
					'language' => 'en',
					'options' => [
						'options' => [
							'ADMIN_ROLE' => ['disabled' => true],
						],
					],
				]); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'agency_id')->dropDownList($dataAgency, [
					'prompt' => 'Chọn đại lý',
					'options' => [!empty($_GET['Admin']['agency_id']) ? $_GET['Admin']['agency_id'] : '' => ['selected' => true]],
				]) ?>
        </div>
    </div>

    <?= $form->field($model, 'status')->checkbox(['checked' => (isset($model->status) && !empty($model->status)) ? $model->status : false]) ?>

    <?php
		foreach (BANK_LIST as $key => $value) {
			?>
    <div class="row">
        <div class="col-lg-3 mb-10">
            <div>
                <?= Html::label("Token Telegram $value", '', ['class' => 'control-label']) ?>
            </div>
            <?= Html::textInput("bank_transaction[$key][token_tele]", (isset($bankTransaction[$key]['token_tele']) ? $bankTransaction[$key]['token_tele'] : ''), ['maxlength' => true, 'class' => 'form-control']) ?>
        </div>
        <div class="col-lg-3 mb-10">
            <div>
                <?= Html::label("Chat ID Telegram $value", '', ['class' => 'control-label']) ?>
            </div>
            <?= Html::textInput("bank_transaction[$key][chat_tele]", (isset($bankTransaction[$key]['chat_tele']) ? $bankTransaction[$key]['chat_tele'] : ''), ['maxlength' => true, 'class' => 'form-control']) ?>
        </div>
        <div class="col-lg-3 mb-10">
            <div>
                <?= Html::label("Số dư tài khoản $value", '', ['class' => 'control-label']) ?>
            </div>
            <?= Html::textInput("bank_transaction[$key][account_balance]", (isset($bankTransaction[$key]['account_balance']) ? $bankTransaction[$key]['account_balance'] : ''), ['maxlength' => true, 'class' => 'int form-control']) ?>
        </div>
        <div class="col-lg-3 mb-10">
            <div class="wrap-checkbox">
                <?= Html::checkbox("bank_transaction[$key][check_driver]", (isset($bankTransaction[$key]['check_driver']) ? $bankTransaction[$key]['check_driver'] : false), ['id' => "check_driver_$key"]) ?>
                <?= Html::label("Nạp tiền lái xe $value", "check_driver_$key", ['class' => 'control-label']) ?>
            </div>
            <div class="wrap-checkbox">
                <?= Html::checkbox("bank_transaction[$key][is_display]", (isset($bankTransaction[$key]['is_display']) ? $bankTransaction[$key]['is_display'] : false), ['id' => "is_display_$key"]) ?>
                <?= Html::label('Hiển thị trên app', "is_display_$key", ['class' => 'control-label']) ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3 mb-10">
            <div>
                <?= Html::label("Chủ tài khoản $value", '', ['class' => 'control-label']) ?>
            </div>
            <?= Html::textInput("bank_transaction[$key][account_holder]", (isset($bankTransaction[$key]['account_holder']) ? $bankTransaction[$key]['account_holder'] : ''), ['maxlength' => true, 'class' => 'form-control']) ?>
        </div>
        <div class="col-lg-3 mb-10">
            <div>
                <?= Html::label("Ảnh QRCode $value", "", ['class' => 'control-label']) ?>
            </div>
            <div class="input-group">
                <?= Html::textInput("bank_transaction[$key][qrcode_path]", (isset($bankTransaction[$key]['qrcode_path']) ? $bankTransaction[$key]['qrcode_path'] : ''), [
							'class' => 'form-control input-qrcode-path',
							'id' => "qrcode_path_$key",
							'readonly' => true
						]) ?>
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default btn-select-image"
                        data-input-id="qrcode_path_<?= $key ?>">Chọn ảnh</button>
                    <button type="button" class="btn btn-danger btn-delete-image"
                        data-input-id="qrcode_path_<?= $key ?>" style="display: none;">Xóa ảnh</button>
                </span>
            </div>

            <!-- Preview -->
            <div class="qr-preview" style="margin-top: 5px;">
                <?php if (!empty($bankTransaction[$key]['qrcode_path'])): ?>
                <img src="<?= $bankTransaction[$key]['qrcode_path'] ?>" alt="QR Preview" class="qr-thumb"
                    style="max-height:100px; cursor: pointer;">
                <?php else: ?>
                <div class="qr-empty"
                    style="width:150px;height:100px;border:1px dashed #ccc;display:flex;align-items:center;justify-content:center;color:#999;">
                    Chưa có ảnh
                </div>
                <?php endif; ?>
            </div>

        </div>
        <div class="col-lg-3 mb-10">
            <div>
                <?= Html::label("Số tài khoản $value", '', ['class' => 'control-label']) ?>
            </div>
            <?= Html::textInput("bank_transaction[$key][account_number]", (isset($bankTransaction[$key]['account_number']) ? $bankTransaction[$key]['account_number'] : ''), ['maxlength' => true, 'class' => 'form-control']) ?>
        </div>
    </div>
    <hr>
    <?php
		}
		?>

    <div id="qr-popup"
        style="display:none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7);">
        <span id="qr-popup-close"
            style="position: absolute; top: 20px; right: 30px; font-size: 40px; font-weight: bold; color: white; cursor: pointer;">&times;</span>
        <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
            <img id="qr-popup-img" src="" alt="QR Code"
                style="max-width: 90%; max-height: 90%; border: 5px solid white; border-radius: 8px;">
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$script = <<<JS
function openCKFinder(inputId) {
	CKFinder.popup({
		chooseFiles: true,
		width: 800,
		height: 600,
		onInit: function(finder) {
			finder.on('files:choose', function(evt) {
				var file = evt.data.files.first();
				var url = file.getUrl();
				$('#' + inputId).val(url);

				var previewContainer = $('#' + inputId).closest('.col-lg-3').find('.qr-preview');
				previewContainer.html('<img src="' + url + '" class="qr-thumb" style="max-height:100px; cursor:pointer;">');

				toggleDeleteButton(inputId);
			});
		}
	});
}

$('.btn-select-image').on('click', function() {
	var inputId = $(this).data('input-id');
	openCKFinder(inputId);
});

$(document).on('click', '.btn-delete-image', function() {
	var inputId = $(this).data('input-id');
	var input = $('#' + inputId);
	var currentPath = input.val();

	if (confirm('Bạn có chắc chắn muốn xóa ảnh này? Ảnh sẽ được xóa khi bạn lưu form.')) {
		input.val('');

		$(this).hide();
		$(this).siblings('.btn-select-image').show();

		var previewContainer = input.closest('.col-lg-3').find('.qr-preview');
		previewContainer.html('<div class="qr-empty" style="width:150px;height:100px;border:1px dashed #ccc;display:flex;align-items:center;justify-content:center;color:#999;">Chưa có ảnh</div>');

		var deleteInput = $('<input>').attr({
			'type': 'hidden',
			'name': 'delete_images[]',
			'value': currentPath
		});
		$('form').append(deleteInput);
	}
});


function toggleDeleteButton(inputId) {
	var input = $('#' + inputId);
	var deleteBtn = input.siblings('.input-group-btn').find('.btn-delete-image');
	var selectBtn = input.siblings('.input-group-btn').find('.btn-select-image');

	if (input.val().trim() !== '') {
		deleteBtn.show();
		selectBtn.hide();
	} else {
		deleteBtn.hide();
		selectBtn.show();
	}
}


$('.input-qrcode-path').each(function() {
	toggleDeleteButton($(this).attr('id'));
});


$(document).on('click', '.qr-thumb', function() {
    var src = $(this).attr('src');
    $('#qr-popup-img').attr('src', src);
    $('#qr-popup').fadeIn(150);
});


$(document).on('click', '#qr-popup, #qr-popup-close', function(e) {
    if (e.target.id === 'qr-popup' || e.target.id === 'qr-popup-close') {
        $('#qr-popup').fadeOut(150);
    }
});

$(document).on('keyup', function(e) {
    if (e.key === 'Escape') {
        $('#qr-popup').fadeOut(150);
    }
});

JS;
$this->registerJs($script);
?>