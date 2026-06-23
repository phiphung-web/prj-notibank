<?php

use app\helpers\MyHelper;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Cấu hình hệ thống';
?>
<div class="system-configuration-create">
    <?php $form = ActiveForm::begin(); ?>
    <div id="tab-panel">
        <section class="tabs-wrapper">
            <div class="tabs-container">
                <div class="tabs-block">
                    <div id="tabs-section" class="tabs">
                        <ul class="tab-head">
                            <?php foreach ($systemList as $key => $value) : ?>
                            <li>
                                <a href="#tab-<?= $key ?>"
                                    class="tab-link <?= $key == 'recharge' ? 'active' : '' ?>"><span
                                        class="tab-label"><?= $value['label'] ?></span></a>
                            </li>
                            <?php endforeach; ?>
                            <li>
                                <a href="#tab-driver-accept" class="tab-link ">
                                    <span class="tab-label">Cấu hình loại xe nhận chuyến</span>
                                </a>
                            </li>
                        </ul>
                        <?php foreach ($systemList as $key => $value) : ?>
                        <section id="tab-<?= $key ?>"
                            class="tab-body entry-content <?= $key == 'recharge' ? 'active active-content' : '' ?>">
                            <div class="row mb15">
                                <?php foreach ($value['value'] as $keyVal => $val) :
                                        $extend = isset($val['extend']) ? explode(' ', $val['extend']) : [];
                                        $keyword = $key . '_' . $keyVal;
                                    ?>
                                <?php if ($val['type'] == 'text') : ?>
                                <div class="col-lg-6 mb15">
                                    <label class="control-label text-left">
                                        <span>
                                            <?= $val['label'] ?>
                                        </span>
                                    </label>
                                    <input type="text" name="config[<?= $key . '_' . $keyVal ?>]"
                                        value="<?= isset($temp[$keyword]) ? $temp[$keyword] : '' ?>"
                                        placeholder="<?= isset($val['placeholder']) ? $val['placeholder'] : '' ?>"
                                        default="<?= isset($val['default']) ? $val['default'] : '' ?>"
                                        class="form-control" autocomplete="off" placeholder="">
                                </div>
                                <?php elseif ($val['type'] == 'number') : ?>
                                <div class="col-lg-6 mb15">
                                    <label class="control-label text-left">
                                        <span>
                                            <?= $val['label'] ?>
                                        </span>
                                    </label>
                                    <input type="number" name="config[<?= $key . '_' . $keyVal ?>]"
                                        value="<?= isset($temp[$keyword]) ? $temp[$keyword] : '' ?>"
                                        class="form-control" autocomplete="off" placeholder="">
                                </div>
                                <?php elseif ($val['type'] == 'checkbox') : ?>
                                <div class="col-lg-6 mb15">
                                    <label class="control-label text-left">
                                        <span>
                                            <?= $val['label'] ?>
                                        </span>
                                    </label>
                                    <div class="wrap-checkbox d-flex align-items-center flex-wrap">
                                        <?php if (isset($val['array']) && is_array($val['array']) && count($val['array'])) :  ?>
                                        <?php foreach ($val['array'] as $keyCheckbox => $valCheckbox) : ?>
                                        <div class="d-flex align-items-center mr-20">
                                            <?php
                                                                $arrayValue = isset($temp[$keyword]) ? explode(',', $temp[$keyword]) : [];
                                                                ?>
                                            <input type="checkbox" name="config[<?= $key . '_' . $keyVal ?>][]"
                                                value="<?= $keyCheckbox ?>" autocomplete="off" placeholder=""
                                                <?= isset($temp[$keyword]) && in_array($keyCheckbox, $arrayValue) ? 'checked' : '' ?>
                                                class="mt-0 mr-5"
                                                id="systemConfiguration-checkbox-<?= MyHelper::slug($val['label'] . '-' . $keyCheckbox) ?>">
                                            <label
                                                for="systemConfiguration-checkbox-<?= MyHelper::slug($val['label'] . '-' . $keyCheckbox) ?>"
                                                class="mb-0"><?= $valCheckbox ?></label>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php elseif ($val['type'] == 'textarea') : ?>
                                <div class="col-lg-6 mb15">
                                    <label class="control-label text-left">
                                        <span>
                                            <?= $val['label'] ?>
                                        </span>
                                    </label>
                                    <textarea name="config[<?= $key . '_' . $keyVal ?>]" cols="40" rows="10" value=""
                                        class="form-control" autocomplete="off" style="height:108px;" placeholder=""
                                        autocomplete="off"><?= isset($temp[$keyword]) ? $temp[$keyword] : '' ?></textarea>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                <div class="col-lg-12">
                                    <?= Html::submitButton('Lưu lại', ['class' => 'btn btn-success']) ?>
                                </div>
                            </div>
                        </section>
                        <?php endforeach; ?>
                        <section id="tab-driver-accept" class="tab-body entry-content">
                            <div class="row mb15">
                                <?php foreach (TYPE_OF_CAR_LIST as $key => $value) : ?>
                                <div class="col-lg-6 mb15">
                                    <label class="control-label text-left">
                                        <span>Với lái xe có loại xe là <?= $value ?>, những chuyến sau đây sẽ được
                                            nhận:</span>
                                    </label>
                                    <?=
                                        \kartik\select2\Select2::widget([
                                            'name' => "config[driver_accept_car_types][$key][]",
                                            'data' => TYPE_OF_CAR_LIST,
                                            'value' => isset($temp['driver_accept_car_types'][$key]) ? $temp['driver_accept_car_types'][$key] : [$key],
                                            'options' => [
                                                'multiple' => true,
                                                'placeholder' => 'Chọn loại xe nhận chuyến',
                                            ],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                        ]);
                                    ?>
                                </div>
                                <?php endforeach; ?>
                                <div class="col-lg-12">
                                    <?= Html::submitButton('Lưu lại', ['class' => 'btn btn-success']) ?>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php ActiveForm::end(); ?>
</div>
