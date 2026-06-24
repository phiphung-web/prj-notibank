<?php

use app\models\Trip;
use app\models\TripGroup;
use kartik\select2\Select2;
use yii\web\YiiAsset;

$tripModel = new Trip();
$tripGroup = new TripGroup();
$this->title = 'Danh sách lịch';
$this->params['breadcrumbs'][] = $this->title;

// include js
$this->registerJsFile('/js/pages/trip-index-list.js', ['depends' => [YiiAsset::class]]);

/* @var $searchModel */
/* @var $dataProvider */

?>

<div class="trip-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                        class="fa fa-minus"></i></button>
            </div>
        </div>

        <div class="box-body">
            <?php echo $this->render('_search', [ 'model' => $searchModel, 'userList' => $userList ]) ?>
        </div>
    </div>

    <form class="filter-ajax">
        <div class="filter-ajax-search">
            <label>Tìm kiếm: </label>
            <input type="text" class="js-filter-data">
        </div>
        <div class="filter-ajax-time">
            <label>Bộ lọc:</label>
            <?php
            $data = [
                'price_customer ASC' => 'Tiền thu khách từ thấp đến cao',
                'price_customer DESC' => 'Tiền thu khách từ cao đến thấp',
            ];
            ?>
            <?php
                $selectedValue = isset(Yii::$app->request->get('SearchTrip')['filter_time_price']) ? Yii::$app->request->get('SearchTrip')['filter_time_price'] : '';
                $typeOfCarValue = (isset(Yii::$app->request->get('SearchTrip')['filter_type_of_car']) ? explode(',', Yii::$app->request->get('SearchTrip')['filter_type_of_car']) : []);
            ?>
            <select name="filter-time_price" class="js-time_price">
                <option selected value="" disabled>Sắp xếp theo</option>
                <option value="price_customer ASC" <?= $selectedValue === 'price_customer ASC' ? 'selected' : '' ?>>Tiền
                    thu khách từ thấp đến cao</option>
                <option value="price_customer DESC" <?= $selectedValue === 'price_customer DESC' ? 'selected' : '' ?>>
                    Tiền thu khách từ cao đến thấp</option>
            </select>
        </div>
        <div class="filter-ajax-type-car">
            <label>Bộ lọc:</label>
            <?php
                $data = [];

                foreach (TYPE_OF_CAR_LIST as $key => $value) {
                    $data[$key] = $value;
                }
            ?>
            <?= Select2::widget([
                'name' => 'filter_type_of_car[]',
                'data' => $data,
                'value' => $typeOfCarValue,
                'options' => [
                    'placeholder' => 'Sắp xếp theo',
                    'multiple' => true,
                    'class' => 'js-type_of_car select2',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>
    </form>

    <div class="table-view-list js-ajax-table">
        <?php
        $tripList = $dataProvider->getModels();

        echo $this->render('table', compact(['searchModel', 'tripList', 'tripModel', 'dataProvider']));
        ?>
    </div>
</div>

<?php
    echo $this->render('modal', ['model' => $tripGroup]);

    echo $this->render('modal-MesZns', ['model' => $tripGroup]);

    echo $this->render('modal-cancel');

    echo $this->render('modal-delete');
?>
<style>
.filter-ajax-type-car .selection {
    width: 300px !important;
    display: block;
}

.filter-ajax-type-car .select2-selection--multiple .select2-selection__choice {
    color: #555555;
    background: #f5f5f5;
    border: 1px solid #ccc;
    border-radius: 4px;
    cursor: default;
    float: none;
    margin: 5px 5px 0 6px;
    padding: 0 6px;
    width: 85%;
}

.filter-ajax-type-car .select2-selection--clearable {
    padding: 5px;
}

.filter-ajax-type-car .select2-selection--clearable .select2-selection__rendered {
    height: 100px;
    display: block;
    overflow-y: scroll;
}

.filter-ajax-type-car .select2-selection--multiple .select2-selection__clear {
    top: 10px;
    right: 30px;
}
</style>
