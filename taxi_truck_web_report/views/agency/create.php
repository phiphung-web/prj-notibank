<?php

use yii\bootstrap\ActiveForm;

$this->title = 'Thêm nhóm Đại lý';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="booking-create">
    <div class="driver-form">
        <?php $form = ActiveForm::begin(); ?>

        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Thông tin Đại Lý</h3>
                </div>
                <div class="box-body">
                    <?php echo $this->render('_form', [
                        'agency' => $model,
                        'form' => $form,
                    ]) ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

        <div class="clear" style="clear:both"></div>
    </div>

</div>