//
// Tabs Toggler
//

(function ($) {
    // Variables
    const $tabLink = $('#tabs-section .tab-link');
    const $tabBody = $('#tabs-section .tab-body');
    let timerOpacity;

    // Toggle Class
    const init = () => {
        // Menu Click
        $tabLink.off('click').on('click', function (e) {
            // Prevent Default
            e.preventDefault();
            e.stopPropagation();

            // Clear Timers
            window.clearTimeout(timerOpacity);

            // Toggle Class Logic
            // Remove Active Classes
            $tabLink.removeClass('active');
            $tabBody.removeClass('active');
            $tabBody.removeClass('active-content');

            // Add Active Classes
            $(this).addClass('active');
            $($(this).attr('href')).addClass('active');

            // Opacity Transition Class
            timerOpacity = setTimeout(() => {
                $($(this).attr('href')).addClass('active-content');
            }, 50);
        });
    };

    // Document Ready
    $(function () {
        init();
    });
}(jQuery));


// Đếm số tài xế đang chờ duyệt là tài xế nhiều xe
if ($('#number-driver-sub').length > 0) {
    $(document).ready(function () {
        $.ajax({
            type: "get",
            url: "/count/count-driver-sub",
            data: {},
            success: function (data) {
                let json = JSON.parse(data)
                $('#number-driver-sub').html(json.count)
            }
        });
    })
}

// Đếm số tài xế đang chờ duyệt đăng ký
if ($('#number-driver-register').length > 0) {
    $(document).ready(function () {
        $.ajax({
            type: "get",
            url: "/count/count-driver-register",
            data: {},
            success: function (data) {
                let json = JSON.parse(data)
                $('#number-driver-register').html(json.count)
            }
        });
    })
}

// Đếm số lịch bán cần thanh toán
if ($('#countPassTrip').length > 0) {
    $(document).ready(function () {
        $.ajax({
            type: "get",
            url: "/count/count-pass-trip",
            data: {},
            success: function (data) {
                let json = JSON.parse(data)
                $('#countPassTrip').html(json.count)
            }
        });
    })
}

// Đếm số chuyến cần CSKH
if ($('#number-customer-service-new').length > 0 || $('#number-customer-service-rollback').length > 0) {
    $(document).ready(function () {
        $.ajax({
            type: "get",
            url: "/count/count-customer-service",
            data: {},
            success: function (data) {
                let json = JSON.parse(data)
                $('#number-customer-service-new').html(json.customer_new)
                $('#number-customer-service-rollback').html(json.customer_rollback)
                $('#number-customer-service-vip').html(json.customer_vip)
            }
        });
    })
}

// Đếm công nợ giữa đại lý và tổng đài
if ($('#number-admin-debt-agency').length > 0 || $('#number-agency-debt-admin').length > 0) {
    $(document).ready(function () {
        $.ajax({
            type: "get",
            url: "/count/count-agency-debt",
            data: {},
            success: function (data) {
                let json = JSON.parse(data)
                $('#number-admin-debt-agency').html(json.countAdminDebtAgency)
                $('#number-agency-debt-admin').html(json.countAgencyDebtAdmin)
            }
        });
    })
}

// Đếm lịch booking
if ($('#number-booking-create').length > 0 || $('#number-booking-waiting').length > 0) {
    $(document).ready(function () {
        $.ajax({
            type: "get",
            url: "/count/count-booking",
            data: {},
            success: function (data) {
                let json = JSON.parse(data)
                $('#number-booking-create').html(json.countBookingCreate)
                $('#number-booking-waiting').html(json.countBookingWaiting)
            }
        });
    })
}

// Đếm số công nợ
if ($('#countTripDriverSettlement').length > 0 || $('#countTripDriverCollection').length > 0 || $('#countTripDebtCustomers').length > 0) {
    $(document).ready(function () {
        $.ajax({
            type: "get",
            url: "/count/count-trip-debt",
            data: {},
            success: function (data) {
                let json = JSON.parse(data)
                $('#countTripDriverSettlement').html(json.countTripDriverSettlement)
                $('#countTripDriverCollection').html(json.countTripDriverCollection)
                $('#countTripDebtCustomers').html(json.countTripDebtCustomers)
            }
        });
    })
}

// Đếm số yêu cầu gọi lại
if ($('#countNumberPhoneWaiting').length > 0) {
    $(document).ready(function () {
        $.ajax({
            type: "get",
            url: "/count/count-request-callback",
            data: {},
            success: function (data) {
                let json = JSON.parse(data)
                $('#countNumberPhoneWaiting').html(json.countRequestCallBack)
            }
        });
    })
}

// Đếm số yêu cầu gọi lại
if ($('.count-driversub').length > 0) {
    $(document).ready(function () {
        $.ajax({
            type: "get",
            url: "/count/count-trip-have-driver-sub-no-info",
            data: {},
            success: function (data) {
                let json = JSON.parse(data)
                $('.count-driversub').html(json.countTripHaveDriverSubNoInfo)
            }
        });
    })
}

// Lấy paramter get
function getParameterByName(name) {
    const url = new URL(window.location.href);
    const paramValue = url.searchParams.get(name);
    return paramValue ? paramValue : '';
}

$(document).ready(function () {
    if ($('.date-time-picker').length) {
        $('.date-time-picker').each(function () {
            $(this).datetimepicker({
                format: 'Y-m-d H:i',
                allowTimes: generateAllowTimes()
            });
        });
    }

    if ($('.date-picker').length) {
        $('.date-picker').each(function () {
            $(this).datetimepicker({
                format: 'Y-m-d',
                timepicker: false,
                maxDate: 'tomorrow'
            });
        });
    }

    if ($('.input-count-character').length) {
        $('.input-count-character').each(function () {
            let max = $(this).attr('data-max')
            let val = $(this).val()
            $(this).parent('.form-group').append('<div class="wrap-count-character text-primary"><span>' + val.length + '</span>/' + max + '</div>')
        });

        $(document).on('keyup', '.input-count-character', function () {
            countCharacter($(this))
        });
    }

    $(document).on('click', '.float, .int', function () {
        let data = $(this).val();
        if (data == 0) {
            $(this).val('');
        }
    });

    $(document).on('keydown', '.float, .int', function (e) {
        let data = $(this).val();
        if (data == 0) {
            let unicode = e.keyCode || e.which;
            if (unicode != 190) {
                $(this).val('');
            }
        }
    });

    $(document).on('change keyup blur', '.int', function () {
        let data = $(this).val();
        if (data == '') {
            $(this).val('0');
            return false;
        }
        data = data.replace(/\./gi, "");
        $(this).val(addCommas(data));
        data = data.replace(/\./gi, "");
        if (isNaN(data)) {
            $(this).val('0');
            return false;
        }
    });

    if ($('.int').length > 0) {
        $('.int').trigger('change');
    }

    $(document).on('change', '#area-provinceid, #searcharea-provinceid', function (e, data) {
        let _this = $(this);
        let id = _this.val();
        let param = {
            'id': id,
            'text': 'Chọn quận/huyện',
            'table': 'vn_district',
            'trigger_district': (typeof (data) != 'undefined') ? true : false,
            'where': {
                'provinceid': id
            },
            'select': 'districtid as id, name',
            'object': '#area-districtid, #searcharea-districtid',
        };
        get_location(param);
    });

    if (typeof (cityid) != 'undefined' && cityid != '') {
        $('#area-provinceid, #searcharea-provinceid').val(cityid).trigger('change', [{
            'trigger': true
        }]);
    }

    $('.int-price-point').keyup(function () {
        let v = $(this).val();
        v = v.replace(/[^-\d]/g, '');
        v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        v = v ? v : '';
        $(this).val(v);
    });

    if ($('.int-price-point').length > 0) {
        $('.int-price-point').trigger('change');
    }

    // Handler for inputs that allow negative numbers with thousand separator formatting
    $(document).on('input change keyup blur', '.int-allow-negative', function () {
        let val = $(this).val();

        // Allow empty value
        if (val === '' || val === '-') {
            return;
        }

        // Check if negative
        let isNegative = val.indexOf('-') === 0;

        // Remove all non-digit characters except minus
        val = val.replace(/[^\d-]/g, '');

        // Ensure minus is only at the beginning
        if (isNegative) {
            val = '-' + val.replace(/-/g, '');
        } else {
            val = val.replace(/-/g, '');
        }

        // Get absolute value for formatting
        let absVal = val.replace('-', '');

        // Format with thousand separators
        if (absVal !== '') {
            absVal = absVal.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        }

        // Add back minus sign if negative
        let formattedVal = isNegative ? '-' + absVal : absVal;

        $(this).val(formattedVal);
    });

    if ($('.int-allow-negative').length > 0) {
        $('.int-allow-negative').trigger('change');
    }
})
// Tìm kiếm vị trí
$(document).click(function (event) {
    if (!$(event.target).closest('.input-search-address, .wrap-address-search').length) {
        $('.wrap-address-search').hide();
    }
});

$(document).on('click', '.input-search-address', function () {
    $(this).parents('.position-relative').find('.wrap-address-search').show();
    return false;
});

$(document).on('keyup', '.input-search-address', function () {
    let _this = $(this);
    let val = _this.val();
    let ajaxUrl = '/location-configuration/search';
    clearTimeout(_this.data('timeout'));
    _this.data('timeout', setTimeout(function () {
        $('#inputId').val('')
        if (val.length > 0) {
            $.get(ajaxUrl, {
                keyword: val
            }, function (response) {
                let wrapSearch = _this.parents('.position-relative').find('.wrap-address-search');
                wrapSearch.html(response.data.html)
                if (wrapSearch.find('.action-select-address').length > 0) {
                    wrapSearch.css('display', 'block');
                } else {
                    wrapSearch.css('display', 'none');
                }
            });
        }
    }, 200));
    return false;
});

$(document).on('click', '.action-select-address', function () {
    let _this = $(this);
    let check = true;
    let input_address = _this.parents('.position-relative').find('.input-search-address');
    input_address.val(_this.attr('data-address'))
    input_address.attr('data-lat', _this.attr('data-lat'))
    input_address.attr('data-long', _this.attr('data-long'))
    input_address.trigger('change')
    countCharacter(input_address)

    $('.input-search-address').each(function () {
        if ($(this).val().length == 0) check = false;
    })
    if (check) {
        var url = 'http://router.project-osrm.org/route/v1/driving/';
        $('.input-search-address').each(function (e) {
            url += $(this).attr('data-long') + ',' + $(this).attr('data-lat') + (e == 0 ? ';' : '');
        })
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (check) {
                    $('.distance-general').val(Math.ceil(response.routes[0].distance / 1000).toFixed(1));
                    $('.distance-general').trigger('change')
                }
            },
            error: function (error) {
                console.error('Lỗi khi gọi API:', error);
            }
        });
    }

    $('.wrap-address-search').css('display', 'none');
});

$('.type-of-car-general select, .time-general, .distance-general, .schedule-general input').on('change', function () {
    updatePriceCustomer();
});

$(document).on('change', '.check-voucher', function () {
    let voucherCode = $(this).val()
    $.ajax({
        url: '/voucher/search-by-code',
        method: 'GET',
        data: {
            code: voucherCode
        },
        success: function (response) {
            if (response.error) {
                toastr.error('Mã Voucher không tồn tại hoặc hết thời gian sử dụng!');
            } else {
                toastr.success('Áp dụng mã voucher thành công!');
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
})

function countCharacter(object) {
    let max = object.attr('data-max');
    let val = object.val();
    object.siblings('.wrap-count-character').find('span').html(val.length);
    object.parent('.form-group').toggleClass('has-error', val.length > max || val.length === 0);
}

async function updatePriceCustomer() {
    let time = new Date($(".time-general").val());
    let params = {
        'schedule': $('.schedule-general input:checked').val(),
        'time': time.getHours() + ':' + time.getMinutes(),
        'type_of_car': $(".type-of-car-general select").val(),
        'distance': Number($('.distance-general').val())
    }
    if (params.distance > 0) {
        await getListCalculationFormulas(params);
    }
}

function getListCalculationFormulas(params) {
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: '/calculation-formula/find-price',
            type: 'get',
            data: params,
            success: function (data) {
                let dataArray = JSON.parse(data);
                $('.price-customer-general').val(Math.round(Math.ceil(dataArray.price / 1000) * 1000))
                $('.price-customer-general').trigger('change')
            },
            error: function (jqXHR, textStatus, errorThrown) { }
        });
    });
}

function get_location(param) {
    if (districtid == '' || param.trigger_district == false) districtid = 0;

    let formURL = '/area/get-location';
    $.post(formURL, {
        param: param
    },
        function (data) {
            if (param.object == '#area-districtid, #searcharea-districtid') {
                $(param.object).html(data.html).val(districtid).trigger('change');
            }
        });
}

function generateAllowTimes() {
    var allowTimes = [];
    var startTime = new Date();
    startTime.setHours(0, 0, 0, 0);
    var endTime = new Date();
    endTime.setHours(23, 55, 0, 0);
    var interval = 15;

    while (startTime.getTime() <= endTime.getTime()) {
        allowTimes.push(formatTime(startTime));
        startTime.setTime(startTime.getTime() + interval * 60000);
    }

    return allowTimes;
}

function formatTime(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();

    if (hours < 10) {
        hours = '0' + hours;
    }

    if (minutes < 10) {
        minutes = '0' + minutes;
    }

    return hours + ':' + minutes;
}

function addCommas(nStr) {
    nStr = String(nStr);
    nStr = nStr.replace(/\./gi, "");
    let str = '';
    for (i = nStr.length; i > 0; i -= 3) {
        a = ((i - 3) < 0) ? 0 : (i - 3);
        str = nStr.slice(a, i) + '.' + str;
    }
    str = str.slice(0, str.length - 1);
    return str;
}

// $(document).ready(function() {
//     $('.form-control').on('input', function() {
//     var sentence = $(this).val();
//     if (sentence.length > 0) {
//         var capitalizedSentence = sentence.charAt(0).toUpperCase() + sentence.slice(1);
//         $(this).val(capitalizedSentence);
//     }
//     });
// });
