(function () {
    'use strict';

    jQuery(document).ready(function ($) {
        $('.btn-collection-call-driver').on('click', function () {
            const id = $(this).data('id');

            if ( confirm('Đã gọi tài xế?') ) {
                $.ajax({
                    type: "post",
                    url: "/call-driver/update-call-driver",
                    data: {
                        id: id,
                    },
                    success: function () {
                        toastr.success('Xác nhận thành công!');
                    },
                    error: function (response) {
                        toastr.error('Error:'+ response);
                    },
                    complete: function() {
                        setTimeout(function() {
                            window.location.replace(window.location.href)
                        }, 300)
                    }
                });
            }
        })
    });
})();

$(document).ready(function () {
    setInterval(function () {
        let keyword = getParameterByName('keyword');
        let order = getParameterByName('order');
        $.ajax({
            type: "GET",
            url: "/call-driver/reload",
            data: {
                keyword: keyword,
                order: order
            },
            dataType: "json",
            success: function (response) {
                $('.wrap-table-normal').html(response.normal)
                $('.wrap-table-late').html(response.late)
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }, 10000);
});