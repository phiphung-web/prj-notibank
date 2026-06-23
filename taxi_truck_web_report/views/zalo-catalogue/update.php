<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Cập nhật nhóm nguồn bán: ' . $zaloCatalogue->id;
$this->params['breadcrumbs'][] = ['label' => 'Group Zalo Catalogue', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $zaloCatalogue->id];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="zalo-update">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-6">

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Thông tin nhóm nguồn bán</h3>
            </div>
            <div class="box-body">
                <?= $form->field($zaloCatalogue, 'name')->textInput(['maxlength' => true]) ?>
                <div class="form-group">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="clear" style="clear:both"></div>

    <?php ActiveForm::end(); ?>

</div>