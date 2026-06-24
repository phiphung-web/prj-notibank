<div class="modal fade" id="modal-delete-trip" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title js-modal-title">Xóa lịch</h4>
            </div>
            <div class="modal-body">
                <label for="">Lý do xóa</label>
                <textarea name="note-delete-trip" class="js-note-delete-trip" style="width: 100%;" rows="5" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Không</button>
                <button type="button" class="btn btn-danger js-btn-delete-trip">Xóa lịch</button>
            </div>
        </div>
    </div>
</div>
<?php
$script = <<<JS
    $(document).on('click', '.js-btn-delete-trip', function(e) {
        let id = $(this).data('id');
        let note = $('.js-note-delete-trip').val();
        if (confirm('Bạn có chắc xóa chuyến xe này?')) {
            $.ajax({
                type: "POST",
                url: "/trip/delete",
                data: {
                    id: id,
                    note: note,
                },
                success: function (response) {
                    console.log(response);
                    $('#modal-delete-trip').modal('hide');
                    toastr.success('Đã xóa chuyến xe!');
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                },
                error: function (response) {
                    toastr.error('Error:'+ response);
                }
            });
        }
    });
JS;
$this->registerJs($script);
?>