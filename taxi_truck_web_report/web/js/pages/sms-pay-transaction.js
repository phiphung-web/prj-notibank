(function () {
  'use strict';

  jQuery(document).ready(function ($) {
    // handle accept sms
    $(document).on('click', '.btn-accept-sms', function () {
      let _this = $(this);
      let phone = _this.parents('tr').find('.select-phone-to-accept').val();
      let id = _this.parents('tr').attr('data-key');

      if (phone === '') {
        toastr.error('Vui lòng chọn tài xế cần nạp tiền!', 'Xin vui lòng thử lại!');
      } else {
        let text = "Bạn có chắc chắn khi nạp tiền cho tài xế này không?";

        if (confirm(text) === true) {
          $.ajax({
            type: "POST",
            url: "/pay/accept-recharge",
            data: {id: id, phone: phone},
            success: function (response) {
              let json = JSON.parse(response);
              if (json.code === 200) {
                toastr.success(json.message, 'Thành công!');
                window.location.reload();
              } else {
                toastr.error(json.message, 'Xin vui lòng thử lại!');
              }
            },
            error: function (jqXHR, textStatus, errorThrown) {
              toastr.error('Có lỗi xảy ra!', 'Xin vui lòng thử lại!');
            }
          });
        }
      }

      return false;
    });

    // handle delete accept sms
    $('.btn-delete-accept-sms').on('click', function () {
      const idAccept = $(this).closest('tr').data('key');
      const textConfirm = 'Bạn có chắc chắn xóa nạp tiền cho tài xế này không?';

      if (confirm(textConfirm) === true) {
        $.ajax({
          type: 'post',
          cache: false,
          data: {
            id: idAccept
          },
          url: '/pay/delete-accept-recharge',
          success: function (response) {
            let json = JSON.parse(response);

            if (json.code === 200) {
              toastr.success(json.message, 'Thành công!');
              window.location.reload();
            } else {
              toastr.error(json.message, 'Xin vui lòng thử lại!');
            }
          },
          error: function () {
            toastr.error('Có lỗi xảy ra!', 'Xin vui lòng thử lại!');
          },
        })
      }

    })
  });
})();