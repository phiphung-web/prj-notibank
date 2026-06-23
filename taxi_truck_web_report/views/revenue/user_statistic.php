<?php

use app\helpers\MyStringHelper;
use fedemotta\datatables\DataTables;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Thống kê số chuyến tổng đài viên tạo từ ngày ' . $searchModel->createTimeRange ?? (date('Y-m-1') . ' - ' . date('Y-m-d'));
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="revenue-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                        class="fa fa-minus"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $this->render('_search_user', [
                'model' => $searchModel,
            ]) ?>
        </div>
    </div>

    <?php echo DataTables::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'username',
                'label' => 'Tổng đài viên',
            ],
            [
                'attribute' => 'totalTrip',
                'label' => 'Số cuốc chốt được',
                'format' => 'integer',
            ],
            [
                'attribute' => 'countTrip',
                'label' => 'Số cuốc điều được',
                'format' => 'integer',
            ],
            [
                'label' => 'Doanh thu các chuyến',
                'value' => function ($model) {
                    return MyStringHelper::convertIntegerToPrice($model['revenue']) . ' đ';
                },
            ],
            [
                'label' => 'Tổng tiền thưởng',
                'value' => function ($model) {
                    return MyStringHelper::convertIntegerToPrice($model['money_bonus']) . ' đ';
                },
            ],
        ],
    ]); ?>

</div>
