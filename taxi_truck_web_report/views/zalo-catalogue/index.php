<?php

use fedemotta\datatables\DataTables;
use yii\helpers\Html;

$this->title = 'Danh sách nhóm nguồn bán';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="zalo-index">
    <div class="form-group d-flex justify-content-end">
        <?= Html::a('Thêm mới', ['create'], ['class' => 'btn btn-success']) ?>
    </div>
    <div class="bg-danger " <?= isset($_GET['message']) ? 'style="padding:10px;margin-bottom: 15px"' : 'style="display:none"' ?>>
        <div class="text-danger"><?= isset($_GET['message']) ? $_GET['message'] : '' ?></div>
    </div>
    <div class="table-view-list table-custom-display">
        <?php
        $columns[] = 'name';

        $columns[] = [
            'class' => 'yii\grid\ActionColumn',
            'buttons' => [
                'update' => function ($url, $dataProvider, $key) {
                    return Html::a('<span class="btn-primary btn mb2 glyphicon glyphicon-pencil" aria-hidden="true"></span> ', [
                        'zalo-catalogue/update',
                        'id' => $dataProvider->id,
                        [
                            'title' => 'Chỉnh sửa',
                        ],
                    ]);
                },
                'delete' => function ($url, $dataProvider, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['zalo-catalogue/delete', 'id' => $dataProvider->id], [
                        'title' => 'Xóa nhóm nguồn bán',
                        'data-confirm' => Yii::t('yii', 'Xóa nhóm nguồn này?'),
                        'data-method' => 'post',
                        'class' => 'btn-danger btn mb2',
                    ]);
                },
            ],
            'headerOptions' => [
                'style' => 'width: 150px;',
            ],
            'template' => '<div class="d-flex justify-content-evenly">{update} {delete}</div>',
        ];

        echo DataTables::widget([
            'dataProvider' => $dataProvider,
            'columns' => $columns,
            'clientOptions' => [
                'scrollX' => true,
            ],
        ]);
        ?>
    </div>
</div>