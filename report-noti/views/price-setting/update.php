<?php

use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Cập nhật giá theo event';
$this->params['breadcrumbs'][] = ['label' => 'Trips', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="agency-update">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-6">

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Cập nhật giá theo event</h3>
            </div>
            <div class="box-body">
                <?php echo $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>

    <div class="clear" style="clear:both"></div>


    <?php ActiveForm::end(); ?>

</div>