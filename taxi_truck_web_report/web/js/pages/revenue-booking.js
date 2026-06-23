(function () {
    'use strict';

    jQuery(document).ready(function ($) {
        // handle get param search
        const formSearchBooking = $('.booking-search')
        const createTimeRange = formSearchBooking.find('input[name="Revenue[createTimeRange]"]').val()
        const createTimeStart = formSearchBooking.find('input[name="Revenue[createTimeStart]"]').val()
        const createTimeEnd = formSearchBooking.find('input[name="Revenue[createTimeEnd]"]').val()

        // set value booking export
        const formBookingExport = $('.booking-export')
        formBookingExport.find('input[name="createTimeRange"]').val(createTimeRange)
        formBookingExport.find('input[name="createTimeStart"]').val(createTimeStart)
        formBookingExport.find('input[name="createTimeEnd"]').val(createTimeEnd)
    });
})();