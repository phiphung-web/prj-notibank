<?php

use app\models\Agency;
use app\models\Admin;
use app\models\GroupZalo;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SearchTrip */
/* @var $form yii\widgets\ActiveForm */
?>

<style>
    .help-block {
        margin: 0;
    }
</style>
<div class="trip-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="d-flex trip-form-search">
        <?=
        $form->field($model, 'pickupTimeRange', [
            'options' => [
                'class' => 'drp-container form-group',
                'style' => 'width: calc((100% - 60px) / 5); margin-right:20px',
            ],
        ])->widget(
            DateRangePicker::className(),
            [
                'presetDropdown' => true,
                'hideInput' => true,
                'startAttribute' => 'pickupTimeStart',
                'endAttribute' => 'pickupTimeEnd',
                'pluginOptions' => [
                    'locale' => ['format' => 'Y-MM-DD'],
                ],
            ]
        );
        ?>
        <?php echo $form->field($model, 'status', [
            'options' => [
                'style' => 'width: calc((100% - 60px) / 5); margin-right: 20px',
            ],
        ])->dropDownList([
            'NOT_YET_SOLD' => 'Lịch chưa điều',
            'CREATE' => 'Lịch đang hẹn giờ mở bán',
            'OPEN' => 'Lịch đang bán',
            'READY_EXPIRE' => 'Lịch sắp hết hạn',
            'EXPIRE' => 'Lịch hết hạn mua',
            'DONE' => 'Lịch đã điều',
            'CANCEL' => 'Lịch đã hủy',
            'COMPLETE' => 'Lịch đã hoàn thành',
            'ALL' => 'ALL',
            'ALL_NOT_CANCEL' => 'ALL (trừ lịch hủy)',
        ])
        ?>
        <?php echo $form->field($model, 'source_trip', [
            'options' => [
                'style' => 'width: calc((100% - 60px) / 5); margin-right: 20px',
                'class' => 'change-source-trip',
            ],
        ])->dropDownList(SOURCE_TRIP_TYPE_LIST, ['prompt' => 'Tất cả'])
        ?>
        <div class="source-agency-trip" style="width: calc((100% - 60px) / 5);  margin-right: 20px;">
            <?php echo $form->field($model, 'agency_id')->widget(Select2::classname(), [])->dropDownList(ArrayHelper::map(Agency::find()->where(['status' => 1])->all(), 'id', 'name'), ['prompt' => 'Tất cả'])
            ?>
        </div>
        <div style="width: calc((100% - 60px) / 5);">
            <?= $form->field($model, 'group_zalo_id')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(GroupZalo::find()->all(), 'id', 'name'),
                'language' => 'vi',
                'options' => [
                    'placeholder' => 'Tất cả',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]); ?>
        </div>
    </div>
    <div class="d-flex trip-form-search">
        <div style="width: calc((100% - 60px) / 5); margin-right: 20px;">
            <div class="d-flex justify-content-between">
                <?= $form->field($model, 'round_trip', [
                    'options' => [
                        'style' => 'width: 100%;',
                        'class' => 'change-schedule-trip',
                    ],
                ])->dropDownList(
                    SCHEDULE_LIST_TRIP,
                    [
                        'prompt' => 'Tất cả',
                        'class' => 'form-control',
                    ]
                ); ?>
            </div>
        </div>

        <div style="width: calc((100% - 60px) / 5); margin-right: 20px;">
            <?= $form->field($model, 'userid_created')->widget(Select2::classname(), [
                'data' => $userList,
                'language' => 'vi',
                'options' => [
                    'placeholder' => 'Chọn người tạo',
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'closeOnSelect' => false,
                ],
            ]); ?>
        </div>

        <div style="width: calc((100% - 60px) / 5); margin-right: 20px;">
            <?= $form->field($model, 'customer_property')->dropDownList(
                CUSTOMER_PROPERTY_LIST,
                ['prompt' => 'Tất cả thuộc tính khách']
            ); ?>
        </div>

        <div style="width: calc((100% - 60px) / 5); margin-right: 20px;">
            <?= $form->field($model, 'service')->dropDownList(
                SERVICE_LIST,
                ['prompt' => 'Tất cả dịch vụ']
            ); ?>
        </div>

        <div style="width: calc((100% - 60px) / 5); margin-right: 20px; ">
            <?php
            echo $form->field($model, 'is_have_bill', [
                'options' => [
                    'class' => 'drp-container trip-search-checkbox ',
                ],
            ])->checkbox();
            echo $form->field($model, 'is_collect_money', [
                'options' => [
                    'class' => 'drp-container trip-search-checkbox ',
                ],
            ])->checkbox();
            echo $form->field($model, 'driver_ban', [
                'options' => [
                    'class' => 'drp-container trip-search-checkbox ' . ($model->status == 'DONE' || $model->status == 'COMPLETE' ? '' : 'hidden'),
                ],
            ])->checkbox();
            echo $form->field($model, 'room', [
                'options' => [
                    'class' => 'drp-container trip-search-checkbox ' . ($model->status == 'COMPLETE' ? '' : 'hidden'),
                ],
            ])->checkbox();
            ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php
$script = <<<JS
    $(document).on('change', '.change-source-trip select', function(){
        let _this = $(this);
        let val = _this.val();
        if(val ==5){
            $('.source-agency-trip').removeClass('hidden')
        }else{
            $('.source-agency-trip').addClass('hidden')
            $('.source-agency-trip select').val('')
        }
    })

    $(document).ready(function(){
        var checkboxId = 'filter_by_created_on';
        var checkboxHtml = `
            <span style="margin-left:10px" class="d-inline-flex align-items-center">
                <input type="checkbox" id="`+checkboxId+`" name="created_on" style="margin-top: 0; margin-right:4px">
                <label for="`+checkboxId+`" style="display:inline;cursor:pointer;color: red; font-size: 12px; margin-bottom: 0;">Lọc theo ngày tạo</label>
            </span>`;
        $('.field-searchtrip-pickuptimerange label').append(checkboxHtml);
    });

    $('#searchtrip-status').on('change', function() {
        updateCheckboxVisibility();
    });

    function updateCheckboxVisibility() {
        var status = $('#searchtrip-status').val();
        if (status === 'DONE' || status === 'COMPLETE') {
            $('.field-searchtrip-driver_ban').removeClass('hidden');
        } else {
            $('.field-searchtrip-driver_ban').addClass('hidden');
        }

        if (status === 'COMPLETE') {
            $('.field-searchtrip-room').removeClass('hidden');
        } else {
            $('.field-searchtrip-room').addClass('hidden');
        }
    }
JS;
$this->registerJs($script);
?>
