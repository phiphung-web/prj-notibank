<?php

use fedemotta\datatables\DataTables;

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
            <?php echo $this->render('_search', compact('searchData')) ?>
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
            'label' => 'Id',
            'value' => 'user_id',
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