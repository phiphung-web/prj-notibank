<?php

use app\models\Agency;
use fedemotta\datatables\DataTables;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\jui\DatePicker;

$this->title = 'Danh sách nhật ký hoạt động';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pay-transaction-index">

    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            </div>
        </div>
        <div class="box-body">
            <form method="get" class="js-form">
                <div class="d-flex flex-column-mobile">
                    <div style="width: calc((100% - 60px) / 4); margin-right: 20px">
                        <?= Select2::widget([
                            'name' => 'agency_id',
                            'value' => Yii::$app->request->get('agency_id'),
                            'data' => ArrayHelper::map(Agency::find()->where(['status' => 1])->all(), 'id', 'name'),
                            'options' => [
                                'placeholder' => 'Chọn đại lý',
                                'class' => 'form-group form-control',
                                'style' => '',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]); ?>
                    </div>

                    <?= Html::dropDownList('action', Yii::$app->request->get('action'), $searchData['actions'], ['class' => 'form-group form-control', 'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px', 'prompt' => 'Chọn hành động']) ?>

                    <?= DatePicker::widget([
                        'name' => 'created_on',
                        'options' => ['class' => 'form-group form-control', 'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px', 'placeholder' => 'Select date ...'],
                        'dateFormat' => 'yyyy-MM-dd',
                        'value' => $searchData['created_on'],
                        'clientOptions' => ['defaultDate' => $searchData['created_on']],
                    ]) ?>
                </div>
                <div class="form-group">
                    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
                    <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
                </div>
            </form>
        </div>
    </div>

    <div class="js-ajax-table">
        <?php
        $columns[] = [
            'label' => 'Thời gian tạo',
            'value' => 'created_on',
            'headerOptions' => [
                'style' => 'max-width: 150px',
            ],
        ];
        $columns[] = [
            'label' => 'Mã đại lý',
            'value' => 'agency_id',
        ];
        $columns[] = [
            'label' => 'Tài khoản',
            'value' => 'user_name',
        ];
        $columns[] = [
            'label' => 'Ghi chú',
            'value' => 'message',
            'headerOptions' => [
                'style' => 'max-width: 50%',
            ],
            'format' => 'raw',
        ];
        $columns[] = [
            'label' => 'Hành động',
            'value' => function ($dataProvider) {
                return ACTION_LIST[$dataProvider['action']];
            },
        ];
        echo DataTables::widget([
            'dataProvider' => $dataProvider,
            'columns' => $columns,
            'clientOptions' => [
                'order' => [
                    [0, 'desc'],
                ],
            ],
        ]);
        ?>

    </div>
</div>

<?php
$script = <<< JS
     $('button[type=reset]').click(function(e) {
         e.preventDefault();
         $('.js-form select, .js-form input').val('');
         $('.js-form input.hasDatepicker').val(new Date().toLocaleDateString('vi', { year: 'numeric', month: '2-digit', day: '2-digit' }).split('/').reverse().join('-'));
     });
JS;
$this->registerJs($script);
?>