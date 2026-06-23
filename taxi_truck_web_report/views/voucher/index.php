<?php

use yii\grid\GridView;

?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        'code',
        'quantity',
        'value',
        'type',
        'is_send:boolean',
        [
            'attribute' => 'status',
            'value' => function ($model) {
                return ($model->status == 1) ? 'Active' : 'Inactive';
            },
        ],
        'expired_at:datetime',
        'created_at:datetime',
        'updated_at:datetime',
        [
            'class' => 'yii\grid\ActionColumn',
        ],
    ],
]); ?>
