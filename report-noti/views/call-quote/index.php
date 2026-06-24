<?php

use app\models\AreaConfiguration;
use app\models\Booking;

$this->registerJsFile('/datetimepicker/jquery.datetimepicker.full.min.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerCssFile('/datetimepicker/jquery.datetimepicker.css');
app\assets\CallAsset::register($this);

// Google Maps API for address autocomplete
$this->registerJsFile('https://maps.googleapis.com/maps/api/js?key=' . GOOGLE_DISTANCE_API_KEY . '&libraries=places&callback=initGoogleMapsServices', ['position' => \yii\web\View::POS_HEAD]);

/* @var $this yii\web\View */
$this->title = '';

// Initialize data
$scheduleListTrip = [];
$timeList = [];
$areaConfigurations = AreaConfiguration::find()->all();
$modelBooking = new Booking();

// Process area configurations
foreach ($areaConfigurations as $areaConfiguration) {
    switch ($areaConfiguration->type) {
        case TIME_AREA_CONFIGURATION:
            $timeList[$areaConfiguration['id']] = $areaConfiguration['value'];
            break;
        case SCHEDULE_AREA_CONFIGURATION:
            $addressList[$areaConfiguration['id']] = $areaConfiguration['value'];
            break;
        default:
            break;
    }
}
?>

<div class="row call-wrap">
    <?php
    // Include search form
    echo $this->render('_search_form');

    // Include advise form
    echo $this->render('_advise_form');
    ?>
</div>

<?php
echo $this->render('_reject_modal', [
    'reason_reject_array' => $reason_reject_array ?? []
]);

echo $this->render('_scripts', [
    'source_call' => $source_call ?? []
]);
?>
