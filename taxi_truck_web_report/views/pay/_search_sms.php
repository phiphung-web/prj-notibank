<?php

use app\helpers\MyStringHelper;
use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SearchPayTransaction */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="pay-transaction-search">

    <?php $form = ActiveForm::begin([
        'action' => ['/pay/list-sms'],
        'method' => 'get',
    ]); ?>
    <div class="d-flex flex-column-mobile">
        <?= $form
            ->field($model, 'keyword', [
                'options' => ['style' => 'width: calc((100% - 60px) / 4); margin-right: 20px'],
            ])
            ->textInput() ?>
        <div class="drp-container form-group field-searchsmspaytransaction-createtimerange"
            style="width: calc((100% - 60px) / 4); margin-right: 20px">
            <label class="control-label d-flex" for="searchsmspaytransaction-createtimerange">
                Khoảng thời gian
                <div class="d-flex" style="align-items: center;margin-left: 5px;">
                    (
                    <label for="checkbox-isAll" style="margin:0; margin-right: 5px;">Hiển thị toàn bộ</label>
                    <?php echo Html::input('checkbox', 'isAll', $model->isAll, ['id' => 'checkbox-isAll', 'style' => 'margin: 0', 'checked' => (isset($_GET) && is_array($_GET) && count($_GET) && ! isset($_GET['isAll']) ? null : 'checked')]); ?>
                    )
                </div>
            </label>
            <div id="searchsmspaytransaction-createtimerange-container" class="kv-drp-container">
                <?= DateRangePicker::widget([
                    'name' => 'SearchSmsPayTransaction[createTimeRange]',
                    'presetDropdown' => true,
                    'convertFormat' => true,
                    'includeMonthsFilter' => true,
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d',
                            'separator' => ' - ',
                        ],
                    ],
                    'value' => $model->createTimeRange,
                    'options' => [
                        'placeholder' => 'Select range...',
                        'name' => 'time',
                        'class' => 'date-picker-dashboard',
                    ],
                ]) ?>
            </div>
            <div class="help-block"></div>
        </div>
        <?php
        $statusList = [
            '' => 'Chọn trạng thái giao dịch',
        ];

        $statusList = array_merge($statusList, STATUS_PAY_TRANSACTION_SMS);
        echo $form
            ->field($model, 'status', [
                'options' => [
                    'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px',
                ],
            ])
            ->dropDownList($statusList);
        ?>
        <?php
        $typeList = [
            0 => 'Tất cả',
        ];
        $admins = array_column($adminList, 'username', 'id');
        $typeList = $typeList + $admins;
        echo $form
            ->field($model, 'user_id', [
                'options' => [
                    'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px',
                ],
            ])
            ->dropDownList($typeList)->label('Chủ tài khoản ngân hàng');
        ?>
    </div>
    <div class="form-group row">
        <div class="col-lg-3">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary ']) ?>
            <?= Html::resetButton('Reset', ['class' => 'btn btn-default ']) ?>
        </div>
        <div class="col-lg-9">
            <div class="row">
                <?php
                if (isset($adminList) && is_array($adminList) && count($adminList)) {
                    foreach ($adminList as $key => $value) {
                        ?>
                        <div class="col-lg-4 mt-mb-2 p0">
                            Số dư <?php echo BANK_LIST[$value['type_bank']] . ' - ' . $value['username'] ?>: <span class="text-bold"
                                style="font-size: 16px"><?= MyStringHelper::convertIntegerToPrice((int)$value['account_balance']) ?>VND</span>
                        </div>
                <?php
                    }
                } ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
