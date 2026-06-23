var price_vat = parseFloat($('#trip-price_vat').val());
console.log(price_vat);

// Gọi AJAX khi sự kiện keyup hoặc change
$('#trip-price_customer, #trip-voucher').on('change', function () {
    var priceCustomer = $('#trip-price_customer').val();
    var voucherCode = $('#trip-voucher').val();
    let data = parseFloat(priceCustomer.replace(/[,.]/g, ''));

    $.ajax({
        url: '/voucher/search-by-code',
        method: 'GET',
        data: {
            code: voucherCode
        },
        success: function (response) {
            data = data - (response.type == 0 ? parseFloat(response.value) : 0);
            data = Math.max(data, 0);
            $(this).val(addCommas(data));
            $('#trip-price-voucher').val(data).trigger('change')
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
});

$(document).ready(function () {
    // $('#trip-is_have_bill').on('change', function () {
    //     let adjustedPrice = calculationPriceWithVAT();
    //     $('#trip-price_customer').val(adjustedPrice);
    // });

    // $('#trip-price_customer').on('change', function () {
    //     if ($('#trip-is_have_bill').is(':checked')) {
    //         let adjustedPrice = calculationPriceWithVAT();
    //         $('#trip-price_customer').val(adjustedPrice);
    //     }
    //     currentPrice = 0;
    // });

    function calculationPriceWithVAT() {
        let isBillChecked = $('#trip-is_have_bill').is(':checked');
        let priceString = $('#trip-price_customer').val().replace(/\./g, '');
        let price = parseFloat(priceString);

        if (isBillChecked) price_vat = price * (VAT_VALUE / 100);
        if (isNaN(price)) {
            price = 0;
        }
        $('#trip-price_vat').val(isBillChecked ? price_vat : 0)
        let adjustedPrice = isBillChecked ? price + price_vat : price - price_vat;
        adjustedPrice = Math.round(adjustedPrice / 1000) * 1000;
        return addCommas(adjustedPrice);
    }
});