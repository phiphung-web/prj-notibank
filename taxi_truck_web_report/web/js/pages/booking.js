$(document).ready(function () {
    var value = $('#booking-type').val();
    if (value == 5) {
        $("#agency").removeClass("hidden");
    } else {
        $("#agency").addClass("hidden");
    }
});

$('#booking-type').on('change', function () {
    var value = $(this).val();
    if (value == 5) {
        $("#agency").removeClass("hidden");
    } else {
        $("#agency").addClass("hidden");
    }
});

$("input[name='Booking[status]']").on('change', function () {
    var value = $("input[name='Booking[status]']:checked").val();
    if (value == 'REJECT') {
        $(".wrap-reject").removeClass("hidden");
    } else {
        $(".wrap-reject").addClass("hidden");
    }
});