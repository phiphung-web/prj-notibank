<?php

use app\helpers\MyStringHelper;
use app\models\GroupZaloCatalogue;
use yii\bootstrap\ActiveForm;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $zalo app\models\Trip */

$this->title = 'Cập nhật nguồn bán: ' . $zalo->id;
$this->params['breadcrumbs'][] = ['label' => 'Zalo', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $zalo->id];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="zalo-update">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-6">

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Thông tin Zalo</h3>
            </div>
            <div class="box-body">

                <?= $form->field($zalo, 'name')->textInput(['maxlength' => true]) ?>

                <?= $form->field($zalo, 'group_zalo_catalogue')->dropDownList(ArrayHelper::map(GroupZaloCatalogue::find()->where(['status' => '1'])->all(), 'id', 'name'), ['prompt' => 'Không thuộc nhóm Zalo', 'class' => 'action-change-group-zalo form-control']) ?>

                <?= $form->field($zalo, 'note')->textarea() ?>

                <div class="form-group">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="clear" style="clear:both"></div>


    <?php ActiveForm::end(); ?>

</div>
