<?php

use fedemotta\datatables\DataTables;
use yii\helpers\Html;

$this->title = 'Danh sách tài khoản';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-index">
    <p>
        <?= Html::a('Thêm tài khoản', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    
    <?php
    $columns[] = 'username';
    $columns[] = 'role';
    $columns[] = [
        'label' => 'Trạng thái',
        'value' => function ($model) {
            $html = isset($model['status']) ? ($model['status'] == 1 ? 'Kích hoạt' : 'Khóa') : 'Không xác định.';

            return $html;
        },
    ];
    $columns[] = [
      'label' => 'Đại lý',
      'value' => function ($model) {
          return isset($model['agency_name']) ? $model['agency_name'] : '';
      },
      'headerOptions' => [
        'style' => 'width: 215px;',
      ],
    ];
    $columns[] = [
        'class' => 'yii\grid\ActionColumn',
        'buttons' => [
            'update' => function ($url, $model, $key) {
                return Html::a('<span class="btn btn-primary mb2 glyphicon glyphicon-pencil" aria-hidden="true"></span> ', ['admin/update', 'id' => $model['id']], [
                    'title' => 'Update',
                ]);
            },
            'changepw' => function ($url, $model, $key) {
                return Html::a('<span class="btn btn-warning mb2 glyphicon glyphicon-lock" aria-hidden="true"></span> ', ['admin/change-pw', 'id' => $model['id']], [
                    'title' => 'Change Password',
                ]);
            },
            'delete' => function ($url, $model, $key) {
                return Html::a(
                    '<span class="btn btn-danger mb2 glyphicon glyphicon-trash" aria-hidden="true"></span> ',
                    ['admin/delete', 'id' => $model['id']],
                    [
                        'title' => 'Xóa tài khoản',
                        'data-confirm' => Yii::t('yii', 'Tài khoản ' . $model['username'] . ' sẽ bị xóa vĩnh viễn?'),
                        'data-method' => 'post',
                    ]
                );
            },
        ],
        'template' => '{update} {changepw} {delete}',
        'headerOptions' => [
            'style' => 'width:110px;',
        ],
    ];
    ?>
    
    <?php echo DataTables::widget([
        'dataProvider' => $dataProvider,
        'columns' => $columns,
    ]); ?>
</div>