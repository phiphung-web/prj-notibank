<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="trip-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'keyword')->textInput() ?>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label class="control-label" for="customer-vip">Phân loại khách trong 6 tháng</label>
                <div class="d-flex align-items-center">
                    <?php echo $form->field($model, 'vip')->checkbox(['class' => 'form-checkbox', 'style' => 'margin: 0; margin-right: 10px'])->label(false); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>