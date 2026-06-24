<?php

use app\models\Driver;
use fedemotta\datatables\DataTables;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Danh sách nạp tiền';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pay-transaction-index">

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

            'id',
            'created_on',
            'modified_on',
            [
                'attribute' => 'description',
                'value' => function ($model) {
                    $desc = isset($model['description']) ? trim($model['description']) : '';
                    return $desc === '' ? ' Nạp tiền hệ thống' : $desc;
                },
            ],
            'money:integer',
            [
                'attribute' => 'driver_id',
                'value' => function ($model) {
                    $driver = Driver::findOne($model['driver_id']);

                    return $driver ? $driver->toString() : '';
                },
            ],

        ],
    ]); ?>
    
</div>

