// js xử lý thay đổi thông tin input 
$(document).ready(function () {
	$(document).on('click', '.update_price', function () {
		$(this).removeAttr('readonly')
	})
})



// JS hide/show chart
$(document).ready(function () {
	$(".btn-openchart").on("click", function () {
		let _this = $(this);
		let date = _this.attr('data-date');
		$.get('revenue/detail-date', {
			date: date
		},
			function (data) {
				var chart_area_spline = new ApexCharts(
					document.querySelector(".revenue-" + date), getChartOptions([{
						name: "Khách mới",
						data: data.data.new,
					},
					{
						name: "Khách cũ",
						data: data.data.old,
					},
					{
						name: "Khách đại lý",
						data: data.data.agency,
					},
					])
				);
				chart_area_spline.render();
				_this.closest("tr").next(".chart").toggle();
			});
	});
});

function getChartOptions(data = []) {
	return {
		series: data,
		chart: {
			fontFamily: "inherit",
			height: 150,
			type: "area",
			toolbar: {
				show: false,
			},
			zoom: {
				enabled: false
			}
		},
		grid: {
			show: true,
			borderColor: "rgba(0,0,0,0.05)",
			xaxis: {
				lines: {
					show: true,
				},
			},
			yaxis: {
				lines: {
					show: true,
				},
			},
		},
		colors: ['#193c67', '#1ba1dc', '#b74424', '#fc6a22', '#81ae7e', '#ec413e', '#f39388', '#736964', '#951c58', '#0e6bb3', '#fc6a22', '#221f57', '#a49d9b', '#f8dc46', '#337355', '#d01925', '#fecb88'],
		dataLabels: {
			enabled: false,
		},
		stroke: {
			curve: "smooth",
			width: 2,
		},
		markers: {
			size: 3,
			strokeColors: "transparent",
		},
		xaxis: {
			categories: TIME_RANGE,
			labels: {
				style: {
					colors: Array(TIME_RANGE.length).fill("#adb0bb"),
				},
			},
		},
		yaxis: {
			show: true,
			labels: {
				style: {
					colors: Array(7).fill("#adb0bb"),
				},
				formatter: function (value) {
					return new Intl.NumberFormat('vi-VN').format(value);
				}
			},
		},
		tooltip: {
			x: {
				format: "dd/MM/yy HH:mm",
			},
			y: {
				formatter: function (value) {
					return new Intl.NumberFormat('vi-VN').format(value);
				}
			},
			theme: "dark",
		},
		legend: {
			show: true,
		},
	};
}

$(document).ready(function () {
	var range = $('.range-value').val()

	function createChart(selector, apiUrl, labels) {
		$.ajax({
			url: apiUrl,
			method: 'GET',
			data: {
				range: range
			},
			success: function (data) {
				if (data && data.data) {
					var numericData = data.data.map(function (value) {
						return parseFloat(value);
					});
					var options = {
						chart: {
							type: 'pie',
						},
						series: numericData,
						colors: ["#55a660", "#3b82b6", "#2e3e4f", "#374a5d", "#9168b5","#db8438", "#1976D2", "#d95f49", "#43956f", "#54b59a"],
						labels: labels,
						responsive: [{
							breakpoint: 480,
							options: {
								chart: {
									width: 200
								},
							}
						}]
					};
					var chart = new ApexCharts(document.querySelector(selector), options);
					chart.render();
				} else {
					console.error('Invalid data format:', data);
				}
			}
		});
	}

	createChart("#cancel", '/revenue/get-data-status', ['Hủy', 'Bán App', 'Bán Zalo']);
	createChart("#profit", '/revenue/get-revenue-and-expenditure-data', ['Thu', 'Chi']);
	createChart("#total", 'revenue/get-data-source', SOURCE_TRIP_TYPE_LIST);

	// AJAX update thông tin
	$(document).on('click', '.index_update_price button', function () {
		let _this = $(this);
		let input = _this.parents('.index_update_price').find('.update_price');
		let price = input.val();
		$.post('/revenue/update-price', {
			price: price,
			date: _this.attr('data-date'),
		},
			function (data) {
				if (data.status > 0) {
					toastr.success('Cập nhật giá chi thành công!');
					let profit = _this.parents('tr').attr('data-profit');
					_this.parents('tr').find('.price-receive').text(addCommas(profit - data.price))
					$('#profit').html("")
					createChart("#profit", '/revenue/get-revenue-and-expenditure-data', ['Thu', 'Chi'], []);
					input.attr('readonly', '')
				} else {
					toastr.error('Có lỗi xảy ra hoặc giá nhập vào trùng giá ban đầu!');
				}
			});
	})
});