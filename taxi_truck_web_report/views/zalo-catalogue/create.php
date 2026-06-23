<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\GroupZalo */

$this->title = 'Thêm nhóm nguồn bán';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="booking-create">


    <div class="driver-form">

        <?php $form = ActiveForm::begin(); ?>

        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Thông tin nhóm nguồn bán</h3>
                </div>
                <div class="box-body">
                    <?= $form->field($model, 'name')->textInput() ?>
                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
        <div class="clear" style="clear:both"></div>

    </div>

</div>
    