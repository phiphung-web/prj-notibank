<?php

use app\helpers\MyStringHelper;
use app\models\GroupZaloCatalogue;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\GroupZalo */

$this->title = 'Thêm nguồn bán';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="booking-create">


    <div class="driver-form">

        <?php $form = ActiveForm::begin(); ?>

        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Thông tin nguồn bán</h3>
                </div>
                <div class="box-body">
                    <?= $form->field($model, 'name')->textInput() ?>

                    <?= $form->field($model, 'group_zalo_catalogue')->dropDownList(ArrayHelper::map(GroupZaloCatalogue::find()->where(['status' => '1'])->all(), 'id', 'name'), ['prompt' => 'Không thuộc nhóm nguồn', 'class' => 'action-change-group-zalo form-control']) ?>

                    <?= $form->field($model, 'note')->textarea() ?>

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
