(function () {
  'use strict';

  jQuery(document).ready(function ($) {

    let typingTimer;
    const doneTypingInterval = 500;

    function load_data_search(page = 0){
      let html_loading = `<div class="loading-area">
                                <div class="loader">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </div>`;
      $('.js-ajax-table').html(html_loading);

      clearTimeout(typingTimer);

      typingTimer = setTimeout(function() {
        const formData = $('#w0').serializeArray();
        let filter_time = $('.js-filter_time').val();
        let filter_keyword = $('.js-filter_keyword').val();
        let debt_type = $('.debt-type').val();

        formData.push({ name: 'this_table', value: 'table-driver-debt-settlement' });
        formData.push({ name: 'SearchTripDriver[filter_time]', value: filter_time });
        formData.push({ name: 'SearchTripDriver[keyword]', value: filter_keyword });
        formData.push({ name: 'SearchBooking[debt_type]', value: debt_type });
        formData.push({ name: 'SearchTripDriver[driver_debt]', value: 'driver_debt_settlement' });
        formData.push({ name: 'page', value: page + 1 });

        let url_data;
        url_data = formData.map(function(field) {
          return field.name + '=' + field.value;
        }).join('&');

        let url = '/trip-driver/driver-debt-settlement?' + url_data;

        history.pushState({}, '', url);
        $.ajax({
          type: "get",
          url: "/trip-driver/search-data",
          data: formData,
          success: function(response) {
            console.log(response);
            $('.js-ajax-table').html(response);
          },
          error: function(response) {
            console.log(response);
          }
        });
      }, doneTypingInterval);
    }

    $(document).on('change', 'form.filter-trip-driver select', function() {
      load_data_search();
    });

    $(document).on('keyup paste', 'form.filter-trip-driver input', function() {
      load_data_search();
    });

    $(document).on('click', '.js-btn-collection-money', function() {
      let id = $(this).data('id');
      if (confirm('Đã trả nợ tài xế?')) {
        $.ajax({
          type: "post",
          url: "/trip-driver/update-driver-debt-settlement",
          data: {
            id: id,
          },
          success: function () {
            toastr.success('Đã trả nợ thành công!');
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
    });

  });

})()