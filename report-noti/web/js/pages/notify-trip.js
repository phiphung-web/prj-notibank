$(document).ready(function () {
    $(document).on('click', '.copy-button', function () {
        var row = $(this).closest('tr');
        var textToCopy = row.find('.copy-info-trip').text();
        console.log(textToCopy);
        copyTextToClipboard(textToCopy);
        return false;
    });

    $(document).on('click', '.accept-button', function () {
        var row = $(this).closest('tr');
        var id = row.attr('data-id');
        var type = row.attr('data-type');
        $.ajax({
            type: "post",
            url: '/notify-trip/update-status',
            data: {
                id: id,
                type: type,
            },
            success: function (response) {
                if (response.success) {
                    toastr.success('Cập nhật trạng thái chuyến xe thành công!');
                    row.remove()
                } else {
                    toastr.error(response.error);
                }
                return false;
            },
        });
        return false;
    });

    $(document).ready(function () {
        setInterval(function () {
            $.ajax({
                type: "GET",
                url: "/notify-trip/reload",
                dataType: "json",
                success: function (response) {
                    if (response.GOLD.length > 0) {
                        response.GOLD.forEach(element => {
                            renderTrip(element, "GOLD")
                        });
                    }
                    if (response.VIP.length > 0) {
                        response.VIP.forEach(element => {
                            renderTrip(element, "VIP")
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }, 10000);
    });

//     function renderTrip(trip, type) {
//         let classTable = (type == "GOLD" ? '.table-trip-gold' : '.table-trip-vip')
//         if ($(classTable + ' tr[data-id="' + trip.id + '"]').length == 0) {
//             var tripHtml = `<tr data-id="${trip.id}" data-type="${type}">
//         <td class="copy-info-trip">
// <div>Thành viên ${type} đã được quyền mua trước chuyến này</div>
// <div>Giờ đi: ${formatDateTime(trip.pickup_time)}</div>
// <div>Lịch trình: ${trip.pickup_address} ➜ ${trip.destination_address}</div>
// <div>Loại lịch: (${scheduleList[trip.round_trip]})${trip.is_have_bill ? ' - (Hóa đơn)' : ''}${trip.is_collect_money ? ' - (Thu tiền)' : ' - (Không thu tiền)'}</div>`;
//             if (trip.description) {
//                 tripHtml += `
// <div>Mô tả: ${trip.description}</div>`;
//             }
//             tripHtml += `
// <div>Loại xe: ${typeOfCarList[trip.type_of_car]}</div>
// <div>Giờ mở bán: ${formatDateTime(trip.new_time)}</div>
//         </td>
//         <td class="text-center text-bold">${formatCurrency(trip.price_customer)}₫</td>
//         <td class="text-center text-bold">${formatCurrency(trip.price_bid)}₫</td>
//         <td>
//             <button class="copy-button btn btn-primary"><i class="fa fa-copy"></i></button>
//             <button class="accept-button btn btn-success"><i class="fa fa-check"></i></button>
//         </td>
//     </tr>`;
//             if (type == "GOLD") {
//                 $('.table-trip-gold tbody').append(tripHtml);
//             } else if (type == "VIP") {
//                 $('.table-trip-vip tbody').append(tripHtml);
//             }
//         }
//     }
//     
    function renderTrip(trip, type) {
        let classTable = (type == "GOLD" ? '.table-trip-gold' : '.table-trip-vip')
        if ($(classTable + ' tr[data-id="' + trip.id + '"]').length == 0) {
            if (type == "GOLD") {
                var tripHtml = `<tr data-id="${trip.id}" data-type="${type}">
            <td class="copy-info-trip">
    <div>Thành viên ${type} đã được quyền mua trước chuyến này</div>
    <div>Giờ đi: ${formatDateTime(trip.pickup_time)}</div>
    <div>Lịch trình: ${trip.pickup_address} ➜ ${trip.destination_address}</div>
    <div>Loại lịch: (${scheduleList[trip.round_trip]})${trip.is_have_bill ? ' - (Hóa đơn)' : ''}${trip.is_collect_money ? ' - (Thu tiền)' : ' - (Không thu tiền)'}</div>`;
                if (trip.description) {
                    tripHtml += `
    <div>Mô tả: ${trip.description}</div>`;
                }
                tripHtml += `
    <div>Loại xe: ${typeOfCarList[trip.type_of_car]}</div>
    <div>Giờ mở bán: ${formatDateTime(trip.new_time)}</div>
            </td>
            <td class="text-center text-bold">${formatCurrency(trip.price_customer)}₫</td>
            <td class="text-center text-bold">${formatCurrency(trip.price_bid)}₫</td>
            <td>
                <button class="copy-button btn btn-primary"><i class="fa fa-copy"></i></button>
                <button class="accept-button btn btn-success"><i class="fa fa-check"></i></button>
            </td>
        </tr>`;
                $('.table-trip-gold tbody').append(tripHtml);
            } else if (type == "VIP") {
                var tripHtml = `<tr data-id="${trip.id}" data-type="${type}">
            <td class="copy-info-trip">
    <div>Còn 15 phút nữa lịch này sẽ được mở bán trên app Hùng Dương mời anh em vào app để mua chuyến.</div>
    <div>Giờ đi: ${formatDateTime(trip.pickup_time)}</div>
    <div>Lịch trình: ${trip.pickup_address} ➜ ${trip.destination_address}</div>
    <div>Loại lịch: (${scheduleList[trip.round_trip]})${trip.is_have_bill ? ' - (Hóa đơn)' : ''}${trip.is_collect_money ? ' - (Thu tiền)' : ' - (Không thu tiền)'}</div>`;
                if (trip.description) {
                    tripHtml += `
    <div>Mô tả: ${trip.description}</div>`;
                }
                tripHtml += `
    <div>Loại xe: ${typeOfCarList[trip.type_of_car]}</div>
    <div>Giờ mở bán: ${formatDateTime(trip.new_time)}</div>
            </td>
            <td class="text-center text-bold">${formatCurrency(trip.price_customer)}₫</td>
            <td class="text-center text-bold">${formatCurrency(trip.price_bid)}₫</td>
            <td>
                <button class="copy-button btn btn-primary"><i class="fa fa-copy"></i></button>
                <button class="accept-button btn btn-success"><i class="fa fa-check"></i></button>
            </td>
        </tr>`;
                $('.table-trip-vip tbody').append(tripHtml);
            }
        }
    }

    function copyTextToClipboard(text) {
        var textArea = $('<textarea></textarea>');
        textArea.val(text);
        $('body').append(textArea);
        textArea.select();
        document.execCommand("copy");
        textArea.remove();
        toastr.success('Copy thành công!');
    }

    function formatDateTime(dateTime) {
        var date = new Date(dateTime);

        var day = date.getDate().toString().padStart(2, '0');
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var year = date.getFullYear();
        var hours = date.getHours().toString().padStart(2, '0');
        var minutes = date.getMinutes().toString().padStart(2, '0');

        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }

    function formatCurrency(number) {
        var formattedNumber = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return formattedNumber;
    }
});