(function () {
	'use strict';

	jQuery(document).ready(function ($) {

		$(document).on('change', '.js-onoff', function () {
			const id = $(this).data('id');
			$.ajax({
				type: "POST",
				url: "/trip/update-display",
				data: {
					id: id
				},
				success: function (response) { }
			});
		});

		$(document).on('click', '.js-collected-money', function () {
			let id = $(this).data('id');
			let isCollected = $(this).hasClass('collected');
			let thisText = $(this);
			let url = "/trip/update-collected-money";
			let jsMoney = ".js-collected-money-" + id;
			$.ajax({
				type: "POST",
				url: url,
				data: {
					id: id
				},
				success: function (response) {
					let data = JSON.parse(response);
					if (isCollected) {
						thisText.text('Chưa thu').toggleClass('text-info collected text-danger');
					} else {
						thisText.text('Đã thu').toggleClass('text-danger text-info collected');
					}
					let html = data['collectedMoneyAt'] != null ? "Thu tiền : <span class='text-success'>" + data['collectedMoneyAt'] + "</span>" : '';
					$(jsMoney).html(html);
				}
			});
		});

		let typingTimer;
		const doneTypingInterval = 500;

		function load_data_search(page = 0) {
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

			typingTimer = setTimeout(function () {
				let key_search = $('.js-filter-data').val();
				let filter_time_price = $('.js-time_price').val();
				let filter_type_of_car = $('.js-type_of_car').val();
				const formData = $('#w0').serializeArray();
				formData.push({
					name: 'SearchTrip[key_search]',
					value: key_search
				});
				formData.push({
					name: 'SearchTrip[filter_time_price]',
					value: filter_time_price
				});
				formData.push({
					name: 'SearchTrip[filter_type_of_car]',
					value: filter_type_of_car
				});
				formData.push({
					name: 'page',
					value: page + 1
				});


				var filteredData = $.grep(formData, function (item) {
					return item.name !== "SearchTrip[schedule]";
				});

				if ($('input[name="SearchTrip[schedule]"]:checked').val() != undefined) {
					filteredData.push({
						name: 'SearchTrip[schedule]',
						value: $('input[name="SearchTrip[schedule]"]:checked').val()
					});
				}

				let url_data;
				url_data = filteredData.map(function (field) {
					return field.name + '=' + field.value;
				}).join('&');

				let url = '/trip/search-data?' + url_data;
				history.pushState({}, '', url);

				$.ajax({
					type: "get",
					url: "/trip/search-data",
					data: filteredData,
					success: function (response) {
						$('.js-ajax-table').html(response);
					}
				});
			}, doneTypingInterval);
		}

		$('form#w0').on('keyup change paste', 'input, select, textarea', function () {
			load_data_search();
		});

		$('form.filter-ajax').on('keyup paste', 'input, textarea', function () {
			load_data_search();
		});

		$('form.filter-ajax').on('change keyup paste', 'select', function () {
			load_data_search();
		});

		$(document).on('click', 'form#w0 button[type="reset"]', function (e) {
			e.preventDefault();
			load_data_search();
		});

		$(document).on('click', '.trip-pagination-item', function (e) {
			e.preventDefault();
			const page = $(this).attr('data-page');
			load_data_search(parseInt(page));
		})

		$(document).on('click', '.js-modal-cancel-trip', function (e) {
			e.preventDefault();
			const tripId = $(this).data('id');
			$('#modal-cancel-trip').find('.js-btn-modal-cancel-trip').attr('data-id', tripId);
		});

		$(document).on('click', '.js-modal-delete-trip', function (e) {
			e.preventDefault();
			const tripId = $(this).data('id');
			$('#modal-delete-trip').find('.js-btn-delete-trip').attr('data-id', tripId);
		});
	});

})()
