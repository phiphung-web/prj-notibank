<?php

use fedemotta\datatables\DataTables;
use yii\grid\ActionColumn;
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $searchModel app\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Url;

$this->title = 'Messages';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="message-index">
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

    <?php
    $columns = [
        'phone',
        'title',
        'content:ntext',
        'time',
        [
            'class' => ActionColumn::class,
            'visibleButtons' => [
                'view' => false, // hide the "View" button
                'update' => false, // hide the "Update" button
            ],
            'buttons' => [
                'delete' => function ($url, $dataProvider, $key) {
                    $url = Url::to(['message/delete', 'id' => $dataProvider->id]);

                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                        'title' => 'Xóa thông báo',
                        'data-confirm' => Yii::t('yii', 'Xóa thông báo này?'),
                        'data-method' => 'post',
                        'class' => 'btn-danger btn mb2',
                    ]);
                },
            'template' => '{delete}',
            ],
        ],
    ];
    ?>
    <?php
        if (isset($dataProvider)) {
            ?>
    <?= DataTables::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $columns,
        'clientOptions' => [
            //            "responsive"=>true,
            'scrollX' => true,
        ],
    ]); ?>

    <?php
        }
    ?>
</div>