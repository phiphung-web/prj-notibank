$(document).on('click', '.js-btn-pass-booking', function () {
    let id = $(this).data('id');
    let payment_method = $(this).data('payment-method');
    let price = $(this).data('price');

    if (payment_method == 1) {
        if (confirm('Xác nhận thanh toán bán lịch qua hình thức chuyển khoản?')) {
            sendAjaxRequest(id, payment_method, price);
        }
    } else {
        let price_driver = prompt("Xác nhận số tiền trả cho lái xe qua hình thức nạp bid:", price);
        if (price_driver !== null) {
            sendAjaxRequest(id, payment_method, price_driver);
        }
    }
});

function sendAjaxRequest(id, payment_method, price) {
    $.ajax({
        type: "post",
        url: "/trip-driver/update-pass-trip",
        data: {
            id: id,
            payment_method: payment_method,
            price: price,
        },
        success: function (response) {
            if (response.status == 200) {
                toastr.success(response.message);
                reloadPage();
            } else {
                toastr.error(response.message);
            }
        },
        error: function (response) {
            toastr.error('Error:' + response);
        }
    });
}

function reloadPage() {
    setTimeout(function () {
        window.location.replace(window.location.href);
    }, 300);
}
