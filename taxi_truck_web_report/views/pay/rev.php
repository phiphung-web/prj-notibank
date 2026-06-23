<?php

use fedemotta\datatables\DataTables;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Doanh thu nạp tiền';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pay-rev">


    <?= DataTables::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'dtDate',
            [
                'attribute' => 'sum',
                'label' => 'Tổng tiền',
                'format' => 'integer',
            ],
            [
                'attribute' => 'count',
                'label' => 'Số lần nạp',
                'format' => 'integer',
            ],

        ],
        'clientOptions' => [
            'responsive' => true,
            'scrollX' => true,
        ],
    ]);?>
</div>
