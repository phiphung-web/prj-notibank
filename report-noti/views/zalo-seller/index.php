<?php
    use fedemotta\datatables\DataTables;
    use yii\helpers\Html;

$this->title = 'Danh sách người bán ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zalo-index">
    <div class="form-group">
        <?= Html::a('Thêm mới', ['create'], ['class' => 'btn btn-success']) ?>
    </div>
    <div class="bg-danger " <?= isset($_GET['message']) ? 'style="padding:10px;margin-bottom: 15px"' : 'style="display:none"' ?>>
        <div class="text-danger"><?= isset($_GET['message']) ? $_GET['message'] : '' ?></div>
    </div>
    <div class="table-view-list">
        <?php

        $columns[] = 'name';
        $columns[] = [
            'label' => 'Nhóm nguồn',
            'value' => function ($model) {
                $model->group_zalo_catalogue_id = json_decode($model->group_zalo_catalogue_id, true);
                $html = '';
                if (! empty($model->group_zalo_catalogue_id)) {
                    $groupZalo = \app\models\GroupZaloCatalogue::find()->where(['in', 'id', array_values($model->group_zalo_catalogue_id)])->andWhere(['status' => 1])->asArray()->all();
                    foreach ($groupZalo as $key => $value) {
                        $html .= $value['name'] . ($key + 1 == count($groupZalo) ? '' : ', ');
                    }
                }

                return $html;
            },
            'format' => 'html',
            'headerOptions' => ['class' => 'thead-time-filter'],
        ];

        $columns[] = [
            'class' => 'yii\grid\ActionColumn',
            'buttons' => [
                'update' => function ($url, $zaloSeller, $key) {
                    return Html::a('<span class="btn-primary btn mb2 glyphicon glyphicon-pencil" aria-hidden="true"></span> ', ['zalo-seller/update', 'id' => $zaloSeller->id, [
                        'title' => 'Chỉnh sửa',
                    ]]);
                },
                'delete' => function ($url, $zaloSeller, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['zalo-seller/delete', 'id' => $zaloSeller->id], [
                        'title' => 'Xóa group zalo',
                        'data-confirm' => Yii::t('yii', 'Xóa group này?'),
                        'data-method' => 'post',
                        'class' => 'btn-danger btn mb2',
                    ]);
                },
            ],
            'headerOptions' => [
                'style' => 'width: 150px;',
            ],
            'template' => '{update} {delete}',
        ];

        echo DataTables::widget([
            'dataProvider' => $zaloSeller,
            'columns' => $columns,
            'clientOptions' => [
                'scrollX' => true,
            ],
        ]);
        ?>
    </div>
</div>