<?php
use app\models\Trip;
use app\models\TripGroup;
use yii\web\YiiAsset;

$tripModel = new Trip();
$tripGroup = new TripGroup();
$this->title = 'Danh sách nợ tài xế';
$this->params['breadcrumbs'][] = $this->title;

// include js
$this->registerJsFile('/js/pages/trip-driver-debt-settlement.js', ['depends' => [YiiAsset::class]]);

/* @var $dataProvider */
/* @var $searchModel */
/* @var $debtType */
?>

<div class="trip-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                  <i class="fa fa-minus"></i></button>
            </div>
        </div>
        
        <?php echo $this->render('components/search-filter', compact(['debtType'])); ?>
    </div>
  
    <div class="table-view-list js-ajax-table">
        <?php
        $tripList = $dataProvider->getModels();

        echo $this->render('table-driver-debt-settlement', compact(['searchModel', 'tripList', 'tripModel', 'dataProvider']));
        ?>
    </div>
</div>