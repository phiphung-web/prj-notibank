$(document).on('click', '.js-btn-trip-agency', function () {
    let id = $(this).data('id');
    if (confirm('Xác nhận tổng đài trả nợ đại lý?')) {
        $.ajax({
            type: "post",
            url: "/trip-driver/update-trip-agency-debt",
            data: {
                id: id,
            },
            success: function () {
                toastr.success('Đã trả nợ thành công!');
                setTimeout(function () {
                    window.location.replace(window.location.href)
                }, 300)
            },
            error: function (response) {
                toastr.error('Error:' + response);
            }
        });
    }
});

$(document).on('click', '.js-btn-detail', function () {
    let id = $(this).data('id');
    $('#tableDetail .js-tbody').html("");
    $.ajax({
        type: "post",
        url: "/trip-driver/get-detail-trip-agency",
        data: {
            id: id,
        },
        success: function (data) {
            $('#tableDetail .js-tbody').html(data);
        },
    });
});


$(document).on('click', '.btn-accept-debt', function () {
    let id = $(this).data('id');
    let agency_id = $(this).data('agency-id');
    if (confirm("Bạn có chắc chắn muốn tiếp tục?")) {
        $.ajax({
            url: '/trip-driver/accept-debt-trip',
            type: 'POST',
            data: { trip_id: id, agency_id: agency_id },
            success: function (response) {
                let json = JSON.parse(response)
                $('.tr-agency-debt[data-key=' + agency_id + ']').find('.price-rose').html(json.total_price_rose + 'đ')
                $('.tr-agency-debt[data-key=' + agency_id + ']').find('.price-total').html(json.total_price + 'đ')
                $('.tr-agency-debt-detail[data-key=' + id + ']').remove()
                toastr.success('Thanh toán chuyến xe thành công!');
            }
        });
    }
    return false;
});


