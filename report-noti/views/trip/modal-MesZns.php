<?php
?>
<div class="modal fade" id="modalMesZns" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title js-MesZns-title" id="myModalLabel"></h4>
            </div>
            <div class="modal-body table-responsive">
                <table id="tableMessageZns" class="table-striped table-bordered" width="100%" style="background: #fff;">
                    <thead class="js-thead">
                        <tr>
                            <th class="mes-item text-center">Loại tin</th>
                            <th class="mes-item text-center">Tin zalo</th>
                            <th class="mes-item text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="js-tbody">
                    </tbody>
                    <tfoot class="js-foot"></tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <!-- <button type="button" class="btn btn-primary js-resend">Gửi lại</button> -->
            </div>
        </div>
    </div>
</div>
<?php
$script = <<< JS
    $(document).ready(function () {
        $.ajax({
            type: "get",
            url: "/message-zns/get-key-zalo/",
            success: function (response) {
                let textZaloMes = {
                    zalo_template_1 : 'Tin xác nhận đặt xe khách',
                    zalo_template_2 : 'Tin gửi thông tin lái xe',
                    zalo_template_3 : 'Tin hoàn thành chuyến đi',
                };

                let data = JSON.parse(response);
                let html = '';
                Object.keys(data).forEach(function (key) {
                    if (data.hasOwnProperty(key) && textZaloMes.hasOwnProperty(key)) {
                    html += '<tr class="js-mess" data-key="' + data[key] + '" role="row">' +
                                '<td class="mes-item js-mes-type">' + textZaloMes[key] + '</td>' +
                                '<td class="mes-item js-mes-message bg-warning"></td>' +
                                '<td class="mes-item text-center">' +
                                    '<button class="btn btn-primary btn-sm js-resend-zns" ' +
                                            'data-template="' + data[key] + '" ' +
                                            'data-type="' + key + '">' +
                                        'Gửi lại' +
                                    '</button>' +
                                '</td>' +
                            '</tr>';
                    }
                });
                if (data.length !== 0 && html !== '') {
                    let values = Object.values(data).join('_');
                    $("#tableMessageZns .js-tbody").attr('data-listkey', values).html(html);
                }
                else{
                    $('#tableMessageZns').html('<div class="js-error-div bg-danger mes-item"><span class="">Hệ thống zalo chưa được cấu hình!</span></div>');
                }
            }
        });
    });
    $(document).on('click', '.js-modal-mes-zns', function() {
        $("#tableMessageZns .js-mes-message").removeClass('bg-success').addClass('bg-warning').html('Tin chưa được gửi');
        $(".js-MesZns-title").html('Tin Zalo');
        $.ajax({
            type: "get",
            data: {
                tripid: $(this).attr('data-id'),
            },
            url: "/message-zns/get-message/",
            success: function (response) {
                let data = JSON.parse(response);
                if (data.length !== 0 && $('#tableMessageZns .js-tbody').data('listkey')!== undefined) {
                    $('.js-warning-div').remove();
                    $('#tableMessageZns .js-tbody, .js-resend').css('display', '');
                    $('#tableMessageZns .js-foot').html('');
                    $(".js-MesZns-title").html("Tin gửi zalo đến SĐT: " + data[0]['phone'] + " - Mã chuyến xe: " + (data[0]['trip_id']));
                    let key_arr = $('.js-tbody').data('listkey').split("_");
                    data.forEach(element => {
                        if (key_arr.includes(String(element['template_id']))) {
                            $(".js-mess[data-key='" + element['template_id'] + "'] .js-mes-message")
                                .html(element['message'] + (element['reason'] ? ' <br>Lý do: ' + element['reason'] : ''))
                                .toggleClass('bg-success', element['code'] === 0)
                                .toggleClass('bg-warning', element['code'] !== 0);
                        }
                    });
                }else{
                    $('#tableMessageZns .js-thead, #tableMessageZns .js-tbody, .js-resend').css('display', 'none');
                    $('#tableMessageZns .js-foot').html('<div class="js-warning-div bg-warning mes-item"><span class="">Chuyến xe đã bị hủy!</span></div>');
                }
            }
        });
    });
        $(document).on('click', '.js-resend-zns', function() {
        let templateId = $(this).data('template');
        let type = $(this).data('type');
        let tripId = $('.js-modal-mes-zns').attr('data-id');
        if (!tripId) {
            alert("Không tìm thấy tripId!");
            return;
        }

        $.ajax({
            type: "post",
            url: "/trip/resend-message/",
            data: {
                template_id: templateId,
                type: type,
                trip_id: tripId
            },
            success: function (response) {
                let data = response;
                if (data.success) {
                    alert("Đã gửi lại thành công!");
                } else {
                    alert("Gửi thất bại: " + data.message);
                }
            }
        });
    });

JS;
$this->registerJs($script);
?>
