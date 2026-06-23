<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SearchBooking */
/* @var $form yii\widgets\ActiveForm */
/* @var $agencyList */
?>

<div class="booking-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="d-flex">
        <?= $form->field($model, 'keyword', [
            'options' => ['style' => 'width: calc((100% - 60px) / 4); margin-right: 20px'],
        ])->textInput() ?>

        <?php if (! Yii::$app->user->can('DAI_LY_ROLE')) :  ?>

            <?= $form->field($model, 'agency_id', [
                'options' => ['style' => 'width: calc((100% - 60px) / 4); margin-right: 20px'],
            ])->dropDownList($agencyList, [
                'prompt' => 'Chọn đại lý',
                'options' => [! empty($_GET['SearchBooking']['agency_id']) ? $_GET['SearchBooking']['agency_id'] : '' => ['selected' => true]],
            ]) ?>

        <?php endif; ?>

        <?= $form->field($model, 'createTimeRange', [
            'options' => ['class' => 'drp-container form-group', 'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px'],
        ])->widget(
            DateRangePicker::class,
            [
                'presetDropdown' => true,
                'hideInput' => true,
                'startAttribute' => 'createTimeStart',
                'endAttribute' => 'createTimeEnd',
                'pluginOptions' => [
                    'locale' => ['format' => 'Y-MM-DD'],
                ],

            ]
        ); ?>

        <?php echo $form->field($model, 'status', [
            'options' => [
                'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px',
                'class' => 'js-status',
            ],
        ])->dropDownList([
            'CREATE' => 'Chưa xử lý',
            'ALL' => 'Tất cả',
            'CONFIRM' => 'Đã xác nhận',
            'REJECT' => 'Đã từ chối',
            'WAITING' => 'Đang chờ',
        ])
        ?>

        <?php echo $form->field($model, 'type_reject', [
            'options' => [
                'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px',
                'class' => 'js-type_reject hidden',
            ],
        ])->dropDownList(
            isset($reason_reject_array) ? $reason_reject_array : [0 => ADD_TYPE_REJECT],
            ['options' => [0 => ['selected' => true]]]
        );
        ?>

        <?php
        $options = [
            '0' => 'Tất cả',
        ];

        foreach (SOURCE_TRIP_TYPE_LIST as $key => $value) {
            $options[$key] = $value;
        }
        echo $form->field($model, 'type', [
            'options' => [
                'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px',
            ],
        ])->dropDownList($options, [
            'options' => [! empty($_GET['SearchBooking']['type']) ? $_GET['SearchBooking']['type'] : '' => ['selected' => true]],
        ])
        ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
        <?= Html::a('Thêm mới', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$script = <<<JS
    $('.js-del-selection').click(function(e) {
        e.preventDefault();
        let arr = [];
        $( 'input.js-select-item:checked').each(function( index ) {
            arr.push($(this).val());
        });
        $('#modalDeleteList').find('form').attr('action', '/statistic/deletelist?arr_id=' + arr);
    });

    $(document).ready(function () {
        let val = $('.js-status option:selected').val();
        if(val == 'REJECT'){
            $('.js-type_reject').removeClass('hidden')
        }else{
            $('.js-type_reject').addClass('hidden')
            $('.js-type_reject select').val(0)
        }
    });
    $(document).on('change', '.js-status select', function(){
        let _this = $(this);
        let val = _this.val();
        if(val == 'REJECT'){
            $('.js-type_reject').removeClass('hidden')
        }else{
            $('.js-type_reject').addClass('hidden')
            $('.js-type_reject select').val(0)
        }
    });
JS;
$this->registerJs($script);
?>