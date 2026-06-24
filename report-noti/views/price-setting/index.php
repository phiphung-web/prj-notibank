<?php

use app\models\Agency;
use kartik\select2\Select2;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Danh sách giá theo event';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zalo-index">
	<div class="box box-green">
		<div class="box-header with-border">
			<h3 class="box-title">Filter</h3>
			<div class="box-tools pull-right">
				<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
			</div>
		</div>
		<div class="box-body">
			<div class="group_zalo-search">

				<?php $form = ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                ]); ?>
				<div class="d-flex flex-column-mobile">
					<div style="width: 25%; min-width: 300px; margin-bottom: 15px">
						<?= Select2::widget([
                            'name' => 'agency_id',
                            'value' => Yii::$app->request->get('agency_id'),
                            'data' => ArrayHelper::map(Agency::find()->where(['status' => 1])->all(), 'id', 'name'),
                            'options' => [
                                'placeholder' => 'Chọn đại lý...',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]); ?>
					</div>
				</div>
				<div class="form-group">
					<?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
					<?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
					<?= Html::a('Thêm mới', ['create'], ['class' => 'btn btn-success']) ?>
				</div>
				<?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
	<div class="form-group">
	</div>
	<div class="table-view-list">
		<div class="d-card">
			<div class="card-body">
				<?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => [
                        'class' => 'table table-striped table-bordered table-hover',
                        'style' => 'verticle-align: middle',
                    ],
                    'columns' => [
                        'id',
                        [
                            'attribute' => 'agency_id',
                            'value' => function ($model) {
                                return ! empty($model->agency_id) ? Agency::findOne($model->agency_id)->name : 'Hệ thống xevip';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'price',
                            'value' => function ($model) {
                                return Yii::$app->formatter->asCurrency($model->price, 'VND');
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'percent',
                            'value' => function ($model) {
                                return $model->percent * 100 . '%';
                            },
                            'format' => 'raw',
                        ],
                        'start_date',
                        'end_date',
                        [
                            'attribute' => 'active',
                            'value' => function ($model) {
                                return $model->active ? 'Kích hoạt' : 'Không kích hoạt';
                            },
                            'contentOptions' => function ($model) {
                                return [
                                    'style' => $model->active ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;',
                                ];
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update} {delete}',
                            'contentOptions' => ['class' => 'text-center'], // Căn giữa nút hành động
                            'buttons' => [
                                'update' => function ($url, $model) {
                                    return Html::a('<i class="fa fa-edit"></i>', $url, [
                                        'class' => 'btn btn-sm btn-primary',
                                        'title' => 'Cập nhật',
                                    ]);
                                },
                                'delete' => function ($url, $model) {
                                    return Html::a('<i class="fa fa-trash"></i>', $url, [
                                        'class' => 'btn btn-sm btn-danger',
                                        'title' => 'Xóa',
                                        'data-confirm' => 'Bạn có chắc chắn muốn xóa?',
                                        'data-method' => 'post',
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]) ?>

				<?php $pagination = $dataProvider->getPagination(); ?>
			</div>
		</div>
	</div>
</div>