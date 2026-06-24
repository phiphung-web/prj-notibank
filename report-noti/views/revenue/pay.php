<?php

use fedemotta\datatables\DataTables;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Doanh thu nạp tiền';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="revenue-pay">

    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $this->render('_search', [
                'model' => $searchModel,
            ]) ?>
        </div>
    </div>

    <?php echo DataTables::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'dtDate',
            [
                'attribute' => 'sum',
                'label' => 'Tổng tiền tài xế nạp',
                'format' => 'integer',
            ],
            [
                'attribute' => 'count',
                'label' => 'Số lần nạp',
                'format' => 'integer',
            ],
            [
                'attribute' => 'sum_customer',
                'label' => 'Tổng tiền khách trả',
                'format' => 'integer',
            ],
            [
                'attribute' => 'count_customer',
                'label' => 'Số lần khách trả',
                'format' => 'integer',
            ],
        ],
    ]); ?>

</div>