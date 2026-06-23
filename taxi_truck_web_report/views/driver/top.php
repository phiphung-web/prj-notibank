<?php

use app\models\Driver;
use fedemotta\datatables\DataTables;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Top tài xế';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="driver-top">


    <?= DataTables::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'driver_id'  ,
                'label' => 'Tài xế',
                'value' => function ($model) {
                    return Driver::findOne($model['driver_id'])->toString();
                },
            ],
            [
                'attribute' => 'sum',
                'label' => 'Tổng tiền',
                'format' => 'integer',
            ],
            [
                'attribute' => 'count',
                'label' => 'Số chuyến',
                'format' => 'integer',
            ],

        ],
        'clientOptions' => [
            'responsive' => true,
            'scrollX' => true,
        ],
    ]);?>
</div>
