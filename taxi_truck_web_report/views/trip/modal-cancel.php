<?php

?>
<div class="modal fade" id="modal-cancel-trip" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title js-modal-title">Hủy lịch</h4>
            </div>
            <div class="modal-body">
                <label for="">Lý do hủy</label>
                <textarea name="note-cancel-trip" class="js-note-cancel-trip" style="width: 100%;" rows="5"
                    required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Không</button>
                <button type="button" class="btn btn-danger js-btn-modal-cancel-trip">Hủy lịch</button>
            </div>
        </div>
    </div>
</div>
<?php
$script = <<<JS
    $(document).on('click', '.js-btn-modal-cancel-trip', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let note = $('.js-note-cancel-trip').val();
        $.ajax({
            type: "port",
            url: "/trip/cancel",
            data: {
                id: id,
                note: note,
            },
            success: function (response) {
                
            }
        });
    });
JS;
$this->registerJs($script);
?>