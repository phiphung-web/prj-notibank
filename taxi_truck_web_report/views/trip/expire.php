<?php

use fedemotta\datatables\DataTables;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Lịch hết hạn';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trip-expire">


    <?= DataTables::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],


            'created_on',
            'count',
            'customer_name',
            'customer_phone',
            'description',
            'pickup_time',
            'price_customer:integer',

            ['class' => 'yii\grid\ActionColumn',
            'buttons' => [
                'additional_icon' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> ', ['trip/add-manual', 'id' => $model->id]);
                },
            ],
            'template' => '{update} {view} {additional_icon}',


        ],

        ],
        'clientOptions' => [
            'responsive' => true,
            'scrollX' => true,
        ],
    ]);?>
</div>
