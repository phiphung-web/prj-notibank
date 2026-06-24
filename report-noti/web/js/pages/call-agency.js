$(document).on('change', '.js-checkbox-round-trip', function(){
    var checkbox = $(this);
    var isChecked = checkbox.prop('checked');
    if (isChecked) {
        $('.js-checkbox-1-chieu').prop('checked', false)
    }
})

$(document).on('change', '.js-checkbox-1-chieu', function(){
    var checkbox = $(this);
    var isChecked = checkbox.prop('checked');
    if (isChecked) {
        $('.js-checkbox-round-trip').prop('checked', false)
    }
})

$(document).on('change', '.js-checkbox-schedule, .js-select-pickup-address, .js-select-address, .js-phone-call', function(){
    getDetailArea();
})

$(document).on('keyup', ' .js-phone-call', function(){
    getDetailArea();
})

if($(window).width() >= 768) $('.sidebar-toggle').trigger('click')

function getDetailArea() {
    var pickupAddress = $('.js-select-pickup-address').val();
    if (pickupAddress === '') {
        return;
    }

    var formData = $('#form-get-detail-area').serializeArray();
    var resultObject = {};
    var phone = $('.js-phone-call').val();
    formData.forEach(function(item) {
        if (resultObject.hasOwnProperty(item.name)) {
            if (!Array.isArray(resultObject[item.name])) {
                resultObject[item.name] = [resultObject[item.name]];
            }
            resultObject[item.name].push(item.value);
        } else {
            resultObject[item.name] = item.value;
        }
    });
    var checkboxes = $('.js-checkbox-schedule');
    var scheduleData = {};
    var counter = 0;

    checkboxes.each(function() {
        var checkbox = $(this);
        var isChecked = checkbox.prop('checked');
        if (isChecked) {
            var value = checkbox.val();
            var label = checkbox.next('label').text();
            scheduleData[counter++] = value;
        }
    });
    if(Object.keys(scheduleData).length > 0){
        $.ajax({
            url: '/call-agency/get-detail-area',
            type: 'GET',
            data: {
                schedule: scheduleData,
                area_id: resultObject.area_id,
                address: resultObject.address,
                phone: phone,
                scheduleList: scheduleList
            },
            dataType: 'json',
            success: function(json) {
                var html = json.dataArea;
                var tableList = $(".table-advise");
                tableList.html('');
                tableList.append(html);
            }
        });
    }
}