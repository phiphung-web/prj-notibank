$(document).on('click', '.update-status-btn-reject', function () {
    var id = $(this).attr('data-id');
    var reason = $(this).attr('data-reason');

    $('#modalReject').find('form')[0].reset();
    $('.note-driver-modal').val(reason);
    $('.type-reject-driver-modal').val(reason)
    $('#modalReject').find('form').attr('action', '/driver/update-status/' + id);
});

$(document).on('click', '.btn-submit-status', function () {
    var url = $('#form-update-status-driver').attr('action');
    let form = $('#form-update-status-driver').serializeArray()
    let formDataObject = new Object;
    for (var i = 0; i < form.length; i++) {
        var field = form[i];
        formDataObject[field.name] = field.value;
    }
    if (formDataObject['Driver[reason]'] == "") {
        toastr.error('Xin vui lòng chọn lý do khóa!');
    } else {
        $.ajax({
            type: "post",
            url: url,
            data: {
                reason: formDataObject['Driver[reason]'],
            },
            success: function () {
                toastr.success('Cập nhật trạng thái thành công!');
                window.location.reload()
                return false;
            },
        });
    }
    return false;
});

$(document).on('change', 'select#driver-reason', function () {
    let val = $(this).val()
    if (val == 999) {
        $('.note-driver-modal').find('textarea').removeAttr('disabled')
        $('.note-driver-modal').removeClass('hidden')
    } else {
        $('.note-driver-modal').find('textarea').attr('disabled', 'disabled')
        $('.note-driver-modal').addClass('hidden')
    }
    return false;
});

$(document).ready(function () {
    $("#close-btn").click(function () {
        $(".small-image").removeClass('active');
        $("#show_image_popup").slideUp();
    })

    $(document).on('click', ".small-image", function () {
        let image_path = $(this).parent('.control-label').siblings('input').val();
        if (image_path.length > 0) {
            $("#show_image_popup").fadeOut();
            $("#show_image_popup").fadeIn();
            $("#large-image").attr('src', image_path);
        }
        return false;
    })
})

$(document).ready(function () {
    const columns = [2, 3];

    function toggleColumns(isVisible) {
        $('#datatables_w0 tr').each(function () {
            columns.forEach(colIndex => {
                $(this).find('td, th').eq(colIndex).toggle(isVisible);
            });
        });
    }

    const isChecked = localStorage.getItem('driverPriceChecked') === 'true';

    $('input[name="driver-price"]').prop('checked', isChecked);
    toggleColumns(isChecked);

    $('input[name="driver-price"]').change(function () {
        const isVisible = $(this).is(':checked');
        toggleColumns(isVisible);

        localStorage.setItem('driverPriceChecked', isVisible);
    });
});


