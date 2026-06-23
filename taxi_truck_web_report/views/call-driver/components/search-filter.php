<?php
$queryParams = Yii::$app->request->getQueryParams();
?>

<div class="box-body">
	<div class="trip-driver-search">
		<?php

        use yii\helpers\Html;
        use yii\widgets\ActiveForm;

        $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'class' => 'search-call-drive',
            ],
        ])
        ?>

		<div class="row">
			<div class="col-lg-4">
				<?= Html::dropDownList(
            'order',
            isset($queryParams['order']) ? $queryParams['order'] : 'pickup_time DESC',
            [
                        'pickup_time DESC' => 'Thời gian chuyến xe chạy từ cao đến thấp',
                        'pickup_time ASC' => 'Thời gian chuyến xe chạy từ thấp đến cao',
                    ],
            [
                        'class' => 'form-control order-search',
                    ]
        ) ?>

			</div>

			<div class="col-lg-4">
				<?= Html::textInput('keyword', isset($_GET['keyword']) ? $_GET['keyword'] : '', [
                    'placeholder' => 'Từ khóa',
                    'class' => 'form-control keyword-search',
                ]) ?>
			</div>

			<div class="col-lg-4">
				<div class="action-box">
					<?= Html::submitButton('Tìm kiếm', ['class' => 'btn btn-primary']) ?>
				</div>
			</div>
		</div>

		<?php ActiveForm::end() ?>
	</div>
</div>