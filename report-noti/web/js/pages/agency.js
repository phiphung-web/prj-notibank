$(document).on('change', '.select-promotion-agency', function () {
    let val = $(this).val()
    if (val == 'percent') {
        $('.text-promotion-agency').html('%')
    } else if (val == 'price') {
        $('.text-promotion-agency').html('VNĐ')
    }
})