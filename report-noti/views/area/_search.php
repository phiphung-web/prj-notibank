<?php

use app\models\VnProvince;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<script>
    var cityid = '<?php echo isset($_GET['SearchArea']['provinceid']) ? $_GET['SearchArea']['provinceid'] : 0 ?>';
    var districtid = '<?php echo isset($_GET['SearchArea']['districtid']) ? $_GET['SearchArea']['districtid'] : 0 ?>'
</script>

<div class="trip-search">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
    ]); ?>
    <?php
    $vnProvinceList = VnProvince::find()->all();
    $data = ['0' => 'Chọn tỉnh/thành phố'];
    foreach ($vnProvinceList as $province) {
        $data[$province->provinceid] = $province->name;
    }
    ?>
    <div class="d-flex flex-column-mobile">
        <?php echo $form->field($model, 'provinceid', [
            'options' => [
                'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px',
                'id' => 'city',
            ],
        ])->dropDownList($data)
        ?>
        <?php echo $form->field($model, 'districtid', [
            'options' => [
                'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px',
                'id' => 'district',
            ],
        ])->dropDownList([0 => 'Chọn quận/huyện'])
        ?>
        <?php echo $form->field($model, 'keyword', [
            'options' => [
                'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px',
                'id' => 'district',
            ],
        ])->textInput()
        ?>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
        <?= Html::a('Thêm mới', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>