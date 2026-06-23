<div class="wrap-ckfinder-general">
    <div class="d-flex flex-row-mb d-flex-center justify-content-between flex-wrap">
        <label class="control-label"><?= $title ?></label>
        <button class="btn btn-delete-bus open-ckfinder-driver" data-model="<?= $model_name ?>" data-name="<?= $name ?>" style="margin-left: 10px">
            Chọn ảnh
        </button>
    </div>
    <div class="w-100">
        <div class="d-flex list-img-general flex-wrap" style="margin-top: 10px;">
            <?php
            if (isset($album) && is_array($album) && count($album)) {
                foreach ($album as $key => $value) {
                    ?>
                    <div class="wrap-image-ckfinder"><img class="img-driver" src="<?= $value ?>" alt="<?= $value ?>"><input name="<?= $model_name ?>[<?= $name ?>][]" value="<?= $value ?>" type="hidden">
                        <div class="remove-img"><i class="fa fa-trash" aria-hidden="true"></i></div>
                    </div>
            <?php
                }
            } ?>
        </div>
    </div>
</div>