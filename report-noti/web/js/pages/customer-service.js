$(document).ready(function () {
    $(document).on('click', '.update-feedback-customer', function () {
        let id = $(this).attr('data-id')
        let driver = $(this).attr('data-driver')
        let customer = $(this).attr('data-phone')
        $('#form-update-feedback')[0].reset()
        $('#form-update-feedback').find('#customerservice-trip_id').val(id)
        $('#form-update-feedback').find('#customerservice-driver_id').val(driver)
        $.ajax({
            type: "get",
            url: "/customer-service/view",
            data: {
                'trip_id': id,
                'customer_phone': customer
            },
            success: function (json) {
                $('#form-update-feedback').find('#customerservice-customer_id').val(json.customer_id)
                if (json.id && json.id != 0 && json.id != '') {
                    let feedbackDriver = JSON.parse(json.cus_feedback_driver)
                    var valSelect2 = Object.keys(feedbackDriver);
                    $('#form-update-feedback').find('#customerservice-cus_feedback_driver').val(valSelect2).trigger('change');
                    $('#form-update-feedback').find('#customerservice-driver_feedback_cus').val(json.driver_feedback_cus);
                    $('#form-update-feedback').find('#customerservice-cus_feedback_trip').val(json.cus_feedback_trip)
                    $('#form-update-feedback').find('#customerservice-type').val(json.type).trigger('change');
                    $('#form-update-feedback').find('#customerservice-point').val(json.point)
                    $('#form-update-feedback').find('#customerservice-id').val(json.id)
                    $('#form-update-feedback').find('input[name="CustomerService[status]"][value="' + json.status + '"]').prop('checked', true);
                    toastr.success('Thu thập dữ liệu thành công!');
                }
            },
            error: function (response) {
                toastr.error('Error:' + response);
            }
        });
    })

    $('.btn-delete-admin').on('click', function () {
        if (confirm('Bạn có chắc chắn muốn xóa nhanh không?')) {
            let id = [];
            $('.checkbox-item:checked').each(function () {
                let _this = $(this);
                id.push(_this.val());
            });
            $.ajax({
                type: 'POST',
                url: '/customer-service/delete-all',
                data: {
                    'id': id,
                },
                success: function (response) {
                    if (response.status == 'success') {
                        alert('Xóa nhanh thành công!');
                        window.location.reload()
                    } else {
                        alert('Đã xảy ra lỗi khi xóa nhanh!');
                    }
                },
                error: function (error) {
                    alert('Đã xảy ra lỗi khi xóa nhanh!');
                }
            });
        }
    });

    $(document).on('click', '.btn-select-admin', function () {
        let _this = $(this);
        let id = [];
        let admin = $('#select-admin-deliver').val();
        let date = $('#customerservicesearch-pickuptimerange-container .range-value').val()
        if (admin > 0) {
            $('.checkbox-item:checked').each(function () {
                let _this = $(this);
                id.push(_this.val());
            });
            if (id.length > 0) {
                $.ajax({
                    type: "post",
                    url: "/customer-service/deliver",
                    data: {
                        'id': id,
                        'admin': admin,
                        'date': date,
                    },
                    success: function (data) {
                        if (data > 0) {
                            toastr.success('Bàn giao cho tổng đài viên thành công!');
                            window.location.reload()
                        } else {
                            toastr.error('Có lỗi xảy ra, xin vui lòng thử lại');
                        }
                    },
                    error: function (response) {
                        toastr.error('Error:' + response);
                    }
                });
            } else {
                toastr.error('Xin vui lòng chọn ít nhất 1 bản ghi!');
            }
        } else {
            toastr.error('Xin vui lòng chọn tổng đài viên!');
        }
        return false;
    })

    $('#customerservice-point').on('input', function () {
        var value = parseInt($(this).val());
        console.log(value);
        if (isNaN(value)) {
            // Nếu giá trị không phải là số, đặt giá trị là 0
            $(this).val(0);
        } else {
            // Nếu giá trị nằm ngoài khoảng 0-10, điều chỉnh lại
            if (value < 0) {
                $(this).val(0);
            } else if (value > 10) {
                $(this).val(10);
            } else {
                $('#customerservice-point').parent('.field-customerservice-point').removeClass('has-error')
            }
        }
    });

    // $(document).on('submit', '#form-update-feedback', function () {
    //     let point = $('#customerservice-point').val()
    //     let type = $('#customerservice-type').val()
    //     let count = 0;
    //     if (point.length == 0 || point == 0) {
    //         $('#customerservice-point').parent('.field-customerservice-point').addClass('has-error')
    //         count++;
    //     }
    //     if (type == '') {
    //         $('#customerservice-type').parent('.field-customerservice-type').addClass('has-error')
    //         count++;
    //     }

    //     if (count > 0) {
    //         toastr.error('Xin vui lòng điền đầy đủ thông tin các trường!');
    //         return false;
    //     }
    // })

    $('#customerservice-type').on('change', function () {
        var value = $(this).val();
        if (value > 0) {
            $('#customerservice-type').parent('.field-customerservice-type').removeClass('has-error')
        }
    });

    $(document).on('click', '#checkbox-all', function () {
        let _this = $(this);
        _this.siblings('input').trigger('click');
        check(_this);
        check_all(_this);
    });

    function check(object) {
        if (object.hasClass('checked')) {
            object.removeClass('checked');
        } else {
            object.addClass('checked');
        }
    }

    function check_all(_this) {
        let table = _this.parents('table');
        if ($('#checkbox-all').length) {
            if (table.find('#checkbox-all').prop('checked')) {
                table.find('.checkbox-item').prop('checked', true);
                table.find('.label-checkboxitem').addClass('checked');

            }
            else {
                table.find('.checkbox-item').prop('checked', false);
                table.find('.label-checkboxitem').removeClass('checked');
            }
        }
    }
})