(function () {
  'use strict';

  jQuery(document).ready(function ($) {
    // handle click show modal cancel
    $('.btn-modal-cancel-phone').on('click', function () {
      const requestCallBackId = $(this).data('id');

      $('#cancelPhoneModal').find('#searchrequestcallback-id').val(requestCallBackId);
    });

    // event modal
    $('#cancelPhoneModal').on('hidden.bs.modal', function (e) {
      $('#cancelPhoneModal').find('#searchrequestcallback-id').val('');
      $('.form-request-call-back-cancel')[0].reset();
    });

  });
})();