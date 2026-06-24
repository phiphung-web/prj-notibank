<div class="wrap-ckfinder-general">
    <div class="d-flex flex-row-mb d-flex-center justify-content-between flex-wrap">
        <label class="control-label"><?= $title ?></label>
        <button class="btn ckfinder-select-car" data-model="<?= $model_name ?>" data-name="<?= $name ?>" style="margin-left: 10px">
            Chọn ảnh
        </button>
    </div>
    <div class="w-100">
        <div class="d-flex wrap-image-sub-car flex-wrap">
            <?php
            if (isset($album) && is_array($album) && count($album)) {
                foreach ($album as $keyItem => $valueItem) {
                    ?>
                    <div class="wrap-image-ckfinder"><img class="img-driver" src="<?= $valueItem ?>"><input name="cars[<?= $key ?>][<?= $name ?>][]" data-name="<?= $name ?>" value="<?= $valueItem ?>" type="hidden">
                        <div class="remove-img"><i class="fa fa-trash" aria-hidden="true"></i></div>
                    </div>
            <?php
                }
            } ?>
        </div>
    </div>
</div>