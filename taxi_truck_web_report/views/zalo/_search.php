<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="group_zalo-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="d-flex flex-column-mobile">
        <?= $form->field($model, 'keyword', [
            'options' => [
                'class' => 'form-group',
                'style' => 'width: calc((100% - 60px) / 4); margin-right:20px',
            ],
        ])->textInput() ?>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>