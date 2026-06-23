<?php

use yii\helpers\Url;

$updateTripUrl = Url::to(['trip/update-bid-price']);
?>

<div class="modal fade" id="modal-update-trip" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="form-update-trip">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h3 class="modal-title">Cập nhật giờ đi & giá</h3>
                    <p>Khi bạn thay đổi giá khách hàng, hệ thống sẽ:</p>
                    <ul>
                        <li>Hệ thống sẽ điều chỉnh tiền chênh lệch vào số dư tài xế</li>
                        <li>Không hủy chuyến, không tạo chuyến trả</li>
                    </ul>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>">
                    <input type="hidden" name="trip_id" id="update-trip-id">
                    <div class="form-group">
                        <label for="update-pickup-time">Giờ đi</label>
                        <input type="datetime-local" class="form-control" name="pickup_time" id="update-pickup-time"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="update-price-customer">Giá báo khách</label>
                        <input type="text" class="form-control" name="price_customer" id="update-price-customer"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="update-price-bid">Giá bán cho lái xe</label>
                        <input type="text" class="form-control" name="price_bid" id="update-price-bid" required>
                    </div>
                    <div class="alert alert-info" id="update-trip-hint" style="display:none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$js = <<<JS
(function ($) {
    function formatNumber(num) {
        num = num || '';
        return num.toString().replace(/\\D/g, '').replace(/\\B(?=(\\d{3})+(?!\\d))/g, '.');
    }

    $(document).on('click', '.js-open-update-trip', function () {
        $('#update-trip-id').val($(this).data('trip-id'));
        $('#update-pickup-time').val($(this).data('pickup-time'));
        $('#update-price-customer').val($(this).data('price-customer'));
        $('#update-price-bid').val($(this).data('price-bid'));
        $('#update-trip-hint').hide().text('');
    });

    $('#update-price-customer, #update-price-bid').on('input', function () {
        $(this).val(formatNumber($(this).val()));
    });

    $(document).on('submit', '#form-update-trip', function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $('#update-trip-hint').removeClass('alert-success alert-danger').hide();
        $.post('$updateTripUrl', formData, function (response) {
            if (response && response.success) {
                setTimeout(function () { location.reload(); }, 1200);
            } else {
                var message = response && response.message ? response.message : 'Không thể cập nhật.';
                $('#update-trip-hint').addClass('alert-danger').text(message).show();
            }
        }).fail(function () {
            $('#update-trip-hint').addClass('alert-danger').text('Có lỗi xảy ra, vui lòng thử lại.').show();
        });
    });
})(jQuery);
JS;

$this->registerJs($js);
?>