<?php

use fedemotta\datatables\DataTables;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Doanh thu theo ngày';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trip-rev">


    <?= DataTables::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'dtDate',
            [
                'attribute' => 'customerPrice',
                'label' => 'Thu khách',
                'format' => 'integer',
            ],
            [
                'attribute' => 'driverPrice',
                'label' => 'Lái xe thu',
                'format' => 'integer',
            ],
            [
                'label' => 'Tiền nhận',
                'format' => 'integer',
                'value' => function ($model) {
                    return $model['customerPrice'] - $model['driverPrice'];
                },
            ],

        ],
        'clientOptions' => [
            'responsive' => true,
            'scrollX' => true,
        ],
    ]);?>
</div>
