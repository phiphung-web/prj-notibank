var STATUS_TRIP = {
    'OPEN': 'Đang bán',
    'EXPIRE': 'Hết hạn',
    'CREATE': 'Đang hẹn giờ mở bán',
    'DONE': 'Đã điều',
    'CANCEL': 'Đã hủy',
    'COMPLETE': 'Đã hoàn thành'
};

var STATUS_BOOKING = {
    'CREATE': 'Chưa xử lý',
    'WAITING': 'Lịch chờ',
    'REJECT': 'Lịch hủy',
    'CONFIRM': 'Đã xác nhận',
};

var source = new EventSource("https://c.taxitaitienchuyen.com/updates", {
    https: { rejectUnauthorized: false },
});
source.onopen = function () {
    console.log("connection to stream has been opened");
};
source.onerror = function (error) {
    console.log("An error has occurred while receiving stream", error);
};
source.onmessage = function (stream) {
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    var data = JSON.parse(stream.data);
    let phone = "";
    if (data.state && data.state == "answered") {
        if (data.direction == "outbound") {
            phone = data.to_number;
        } else {
            phone = data.from_number;
        }
        getData(phone, csrfToken, data)
    } else if (data.type && data.type == "call") {
        var regex = /^1599/;
        phone = data.phone.replace(regex, "0");
        getData(phone, csrfToken, data)
    }
};

function getData(phone, csrfToken, json) {
    $.ajax({
        url: "/call/getdata",
        type: "post",
        data: {
            phone: phone,
            _csrf: csrfToken,
        },
        success: function (data) {
            let html = "";
            if (!data.customer) {
                if (phone != "null") html = render_new_customer({ 
                    customer_phone: normalizePhone(phone), 
                    customer_name: "Khách mới", 
                    data: data,
                    hotline: json.hotline
                });
            } else {
                data.hotline = json.hotline;
                html = render_old_customer(data);
            }

            $("#list-call").prepend(html);
        },
    });
}

$(document).on('submit', '.form-search-phone', function () {
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    var phone = $('.input-search-phone').val();
    var idCallBack = getUrlParameter('idCallBack');
    var phoneCallBack = getUrlParameter('phone');
    if (phone != '') {
        $.ajax({
            url: "/call/getdata",
            type: "post",
            data: {
                phone: phone,
                _csrf: csrfToken,
                search: true,
                idCallBack: idCallBack != undefined ? idCallBack : 0,
                phoneCallBack: phoneCallBack != undefined ? phoneCallBack : 0
            },
            success: function (data) {
                let html = "";
                if (!data.customer) {
                    if (phone != "null") html = render_new_customer({ customer_phone: phone, customer_name: "Khách mới", data: data });
                } else {
                    html = render_old_customer(data);
                }

                $("#list-call").prepend(html);
            },
        });
    } else {
        alert("Xin vui lòng nhập số điện thoại cần tìm kiếm!");
    }
    return false;
})

if ($('.input-search-phone').val() > 0) {
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    var phone = $('.input-search-phone').val();
    var idCallBack = getUrlParameter('idCallBack');
    var phoneCallBack = getUrlParameter('phone');
    $.ajax({
        url: "/call/getdata",
        type: "post",
        data: {
            phone: phone,
            _csrf: csrfToken,
            search: true,
            idCallBack: idCallBack != undefined ? idCallBack : 0,
            phoneCallBack: phoneCallBack != undefined ? phoneCallBack : 0
        },
        success: function (data) {
            let html = "";
            if (!data.customer) {
                if (phone != "null") html = render_new_customer({ customer_phone: phone, customer_name: "Khách mới", data: data });
            } else {
                html = render_old_customer(data);
            }

            $("#list-call").prepend(html);
        },
    });
}

$(document).on('click', '.btn-click-advise', function () {
    let phone = $(this).attr('data-phone')
    $('.js-phone-call').html(phone)
    return false;
})

function render_new_customer(params) {
    let html = `<div class="alert alert-danger customer_new" data-phone="${params.customer_phone}">
        <div class="d-flex align-items-center" style="margin-bottom: 10px">
            <h4 class="margin-bottom-none">${params.customer_name}</h4>(SDT): ${params.customer_phone}
            <a target="_blank" href="/trip/create?phone=${params.customer_phone}${((params.data.idCallBack != undefined && params.data.idCallBack.length > 0) ? "&idCallBack=" + params.data.idCallBack : '')}" class="btn btn-success" style="margin-left: 10px;">Tạo lịch</a>
            <a class="btn btn-info btn-click-advise" data-phone=${params.customer_phone} style="margin-left: 10px;">Tư vấn</a>
            <a target="_blank" href="/statistic/create?status=reject&customer_phone=${params.customer_phone}${((params.data.idCallBack != undefined && params.data.idCallBack.length > 0) ? "&idCallBack=" + params.data.idCallBack : '')}" class="btn btn-warning btn-reject-call-search" data-hotline="${params.hotline != undefined ? params.hotline : ''}" data-phone=${params.customer_phone} style="margin-left: 10px;">Từ chối</a>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style="margin-left: auto;">×</button>
        </div>
        ${((params.data.booking.length > 0) ? renderBookingTable(params.data.booking, 'Thông tin lịch chờ', (params.data.idCallBack != undefined && params.data.idCallBack.length > 0 ? params.data.idCallBack : 0)) : '')}
    </div>`;
    return html;
}

function render_old_customer(json) {
    console.log(json);
    let html = `<div class="alert alert-dismissible ${json.customer.display_name} wrap-call-trip box box-call" style="background-color: #f5f4f0;">
        <div class="d-flex align-items-center" style="margin-bottom: 10px;">
            <h4 class="margin-bottom-none">Khách hàng cũ</h4>(SDT): ${json.customer.phone}
            <a target="_blank" href="/trip/create?phone=${json.customer.phone}${((json.idCallBack != undefined && json.idCallBack.length > 0) ? "&idCallBack=" + json.idCallBack : '')}" class="btn btn-success" style="margin-left: 10px;">Tạo lịch</a>
            <a class="btn btn-info btn-click-advise" data-phone=${json.customer.phone} style="margin-left: 10px;">Tư vấn</a>
            <a target="_blank" href="/statistic/create?status=reject&customer_phone=${json.customer.phone}${((json.idCallBack != undefined && json.idCallBack.length > 0) ? "&idCallBack=" + json.idCallBack : '')}" class="btn btn-warning btn-reject-call-search" data-hotline="${json.hotline != undefined ? json.hotline : ''}" data-phone=${json.customer.phone}  style="margin-left: 10px;">Từ chối</a>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true" style="margin-left: auto;">×</button>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <table class="table table-call-customer table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th colspan="2" class="text-center">Khách hàng cũ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Họ tên</td>
                            <td>${json.customer.display_name}</td>
                        </tr>
                        <tr>
                            <td>Số điện thoại</td>
                            <td>${json.customer.phone}</td>
                        </tr>
                        <tr>
                            <td>Số lần đi trong tháng</td>
                            <td>${json.count_month}</td>
                        </tr>
                        <tr>
                            <td>Tổng số lần đi</td>
                            <td>${json.count_all}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-lg-8">`;
    if (json.trip_future.length > 0) {
        html += renderTripTable(json.trip_future, 'Thông tin chuyến xe sắp tới');
    }
    if (json.booking.length > 0) {
        html += renderBookingTable(json.booking, 'Thông tin lịch booking', ((json.idCallBack != undefined && json.idCallBack.length > 0) ? json.idCallBack : ''));
    }
    if (json.trip_old.length > 0) {
        html += renderTripTable(json.trip_old, 'Thông tin chuyến xe gần đây');
    }

    html += `</div></div></div>`;
    return html;
}

function renderBookingTable(trips, caption, idCallBack = 0) {
    var html = `<table class="table table-call-trip table-striped table-bordered">
        <thead class="thead-primary">
            <tr>
                <th style="width: 50%">${caption}</th>
                <th class="text-center" style="width: 20%">Thu khách</th>
                <th class="text-center" style="width: 20%">Trạng thái</th>
                <th class="text-center" style="width: 10%"></th>
            </tr>
        </thead>
        <tbody>`;

    trips.forEach(trip => {
        html += `<tr>
            <td>
                <div>Thời gian đi: <span class="text-primary">${trip.pickup_time}</span></div>
                <div>
                    <span class="text-primary">${trip.pickup_address}</span> 
                    <span style="font-size: 15px;">➜</span>
                    <span class="text-danger">${trip.destination_address}</span>
                </div>
                <div>Loại xe: <span class="text-primary">${TYPE_OF_CAR_LIST[trip.type_of_car] ? TYPE_OF_CAR_LIST[trip.type_of_car] : 'Không xác định'} </span></div>
            </td>
            <td class="text-center">
                <div>${format_curency(trip.price_customer ? trip.price_customer : '0')}đ</div>
                <div>${(trip.is_have_bill == 1 ? '<span class="text-primary">Hóa đơn</span>' : '')}</div>
            </td>
            <td class="text-center"><div><span class="text-primary">` + STATUS_BOOKING[trip.status] + `</span></div></td>
            <td class="text-center"><a id="2284" class="btn-success btn mb2" target="_blank" href="/trip/create?id=`+ trip.id + `${((idCallBack != undefined && idCallBack.length > 0) ? "&idCallBack=" + idCallBack : '')}" title="Thêm chuyến đi"><span class="glyphicon glyphicon-check" aria-hidden="true"></span> </a></td>
        </tr>`;
    });

    html += `</tbody></table>`;
    return html;
}

function renderTripTable(trips, caption) {
    var html = `<table class="table table-call-trip table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th style="width: 50%">${caption}</th>
                <th class="text-center" style="width: 25%">Thu khách</th>
                <th class="text-center" style="width: 25%">Trạng thái</th>
            </tr>
        </thead>
        <tbody>`;

    trips.forEach(trip => {
        var status_text = check_status(trip.status, trip.sell_start_time);
        html += `<tr>
            <td>
                <div>Thời gian đi: <span class="text-primary">${trip.pickup_time}</span></div>
                <div>
                    <span class="text-primary">${trip.pickup_address}</span> 
                    <span style="font-size: 15px;">➜</span>
                    <span class="text-danger">${trip.destination_address}</span>
                </div>
                <div>Loại xe: <span class="text-primary">${TYPE_OF_CAR_LIST[trip.type_of_car] ? TYPE_OF_CAR_LIST[trip.type_of_car] : 'Không xác định'} </span></div>
            </td>
            <td class="text-center">
                <div>${format_curency(trip.price_customer)}đ</div>
                <div>${(trip.is_have_bill == 1 ? '<span class="text-primary">Hóa đơn</span>' : '')}</div>
            </td>
            <td class="text-center">${status_text}</td>
        </tr>`;
    });

    html += `</tbody></table>`;
    return html;
}

function format_curency(data) {
    let format = data.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    return format;
}

function check_status(status, sellStartTime) {
    var html = '';
    var now = new Date();
    var currentDateTime = now.getTime();
    if (status === "OPEN" && new Date(sellStartTime).getTime() > currentDateTime) {
        html += '<div><span class="text-primary">' + STATUS_TRIP.CREATE + '</span></div>';
    } else {
        html += '<div><span class="text-primary">' + STATUS_TRIP[status] + '</span></div>';
    }
    return html;
}


function check_status_booking(status, sellStartTime) {
    return '<div><span class="text-primary">' + STATUS_BOOKING[status] + '</span></div>';
}

function getUrlParameter(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
    var results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function normalizePhone(phone) {
  // Loại bỏ ký tự không phải số
  phone = phone.replace(/\D+/g, '').trim();

  // Nếu chuỗi bắt đầu bằng '084'
  if (phone.startsWith('084')) {
    // Cắt bỏ 3 ký tự đầu ('084') rồi thêm '0' vào trước
    phone = '0' + phone.slice(3);
  }

  return phone;
}