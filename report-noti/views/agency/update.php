<?php

use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $agency app\models\Trip */

$this->title = 'Update agency: ' . $agency->id;
$this->params['breadcrumbs'][] = ['label' => 'Trips', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $agency->id, 'url' => ['view', 'id' => $agency->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="agency-update">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-6">

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Thông tin đại lý</h3>
            </div>
            <div class="box-body">
                <?php echo $this->render('_form', [
                    'agency' => $agency,
                    'form' => $form,
                ]) ?>
            </div>
        </div>
    </div>

    <div class="clear" style="clear:both"></div>


    <?php ActiveForm::end(); ?>

</div>