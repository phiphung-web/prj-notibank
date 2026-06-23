<?php

namespace app\services;

use app\helpers\MyStringHelper;
use app\models\Driver;
use app\models\DriverRole;
use app\models\Role;
use app\models\SystemConfiguration;
use app\models\Trip;
use app\repositories\driver\DriverRepository;
use Yii;
use yii\base\Component;
use yii\web\NotFoundHttpException;
use app\jobs\OpenTripJob;

class DriverService extends Component
{
    protected $driverRepository;

    public function __construct()
    {
        $this->driverRepository = new DriverRepository();
    }

    public function findModel($id)
    {
        if (($model = Driver::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function getDriverByUsername($username = ''): Driver
    {
        return $this->driverRepository->getDriverByUsername($username);
    }

    public function updateDriverMoney($driverId, $currentMoney, $additionalMoney)
    {
        return Yii::$app->db->createCommand()
            ->update('driver', ['money' => $currentMoney + $additionalMoney], ['id' => $driverId])
            ->execute();
    }

    public function getStatisticDriver()
    {
        return [
            'total' => $this->getTotal('total_trip_bid'),
            'complete' => $this->getTotal('total_complete'),
            'cancel' => $this->getTotal('total_cancel'),
            'recharge' => $this->getTotal('total_recharge'),
        ];
    }

    /**
     * Tổng hợp dữ liệu cập nhật tài xế
     *
     * @param Driver $driver
     * @param string $pass_old
     *
     * @return Driver
     */
    public function storeUpdateDriver(Driver $driver, $pass_old = '')
    {
        if ($driver->password === $pass_old) {
            $driver->password = $pass_old;
        } else {
            $driver->password = md5($driver->password);
        }
        $driver = $this->addRoleIfNotExists($driver);
        $driver->register = false;

        return $driver;
    }

    /**
     * Thêm Role cho Driver nếu chưa có.
     * @param Driver $driver
     * @return Driver
     */
    public function addRoleIfNotExists($driver)
    {
        $role = DriverRole::find()->where(['driver_id' => $driver->id])->one();
        if (! $role) {
            $driver->link('roles', Role::findOne($driver->role));
        }

        return $driver;
    }

    public function getTotal($column)
    {
        return MyStringHelper::convertIntegerToPrice(Yii::$app->db->createCommand("SELECT SUM($column) FROM driver")->queryScalar());
    }

    public function insertDriver($driver, $car)
    {
        $driver->password = md5($driver->password);
        $driver->money = 0;
        $driver->link('car', $car);
        $driver->link('roles', Role::findOne($driver->role));

        return $driver;
    }

    public function validateDriver($params = []): bool
    {
        if (\app\models\Car::find()->where(['bks' => $params['carModel']->bks])->andWhere(['<>', 'id', $params['carModel']['id']])->one()) {
            $params['carModel']->addError('bks', Yii::t('app', 'Biển số xe ' . $params['carModel']->bks . ' đã tồn tại'));
        }

        return true;
    }

    public function getReasonReject()
    {
        $new_array = [];
        $reason_reject = SystemConfiguration::find()
            ->select('content')
            ->where(['keyword' => 'reason_lock'])
            ->scalar();
        $reason_reject = CHOOSE_REASON . '|' . $reason_reject;
        $reason_reject_array = explode('|', $reason_reject);
        foreach ($reason_reject_array as $key => $value) {
            if ($key == 0) {
                $new_array[''] = $value;
            } else {
                $new_array[$value] = $value;
            }
        }
        $new_array[999] = ADD_TYPE_REJECT;

        return $new_array;
    }

    public function getDriverManyTrips($params = [])
    {
        return $this->driverRepository->getDriverManyTrips($params);
    }

    public function getDriverNoTrips($params = [])
    {
        return $this->driverRepository->getDriverNoTrips($params);
    }

    public function getDriverNotActive($params = [])
    {
        return $this->driverRepository->getDriverNotActive($params);
    }
    public function getDriverSuitableForTripWithRank(Trip $trip)
    {
        $systemConfiguration = SystemConfiguration::find()
            ->where(['keyword' => 'driver_accept_car_types'])
            ->one();
        if (!$systemConfiguration) {
            Yii::error("Missing system config: driver_accept_car_types");
            return [];
        }
        $rules = json_decode($systemConfiguration->content, true);
        $tripType = (string)$trip->type_of_car;
        $drivers = Driver::find()
            ->joinWith('car')
            ->where(['driver.status' => Driver::STATUS_ACTIVE])
            ->all();
        $groupedDrivers = [];
        foreach ($drivers as $driver) {
            if (!$driver->car) continue;
            $driverCarType = (string)$driver->car->type_of_car;
            if (!isset($rules[$driverCarType])) continue;
            $acceptableTripTypes = $rules[$driverCarType];
            if (in_array($tripType, $acceptableTripTypes)) {
                $rank = $driver->driver_rank ?? 'unknown';
                $groupedDrivers[$rank][] = $driver;
            }
        }
        return $groupedDrivers;
    }

    /**
     * Queue OpenTripJob for drivers suitable for the trip
     *
     * @param Trip $model The trip model
     * @return void
     */
    public function queueOpenTripJobs(Trip $model): void
    {
        if (!$model->id) {
            Yii::warning("Cannot queue jobs: Trip ID is missing");
            return;
        }

        $groupedDrivers = $this->getDriverSuitableForTripWithRank($model);

        if (empty($groupedDrivers)) {
            Yii::info("No suitable drivers found for trip {$model->id}");
            return;
        }

        $getDelay = function($key) {
            $config = SystemConfiguration::find()->where(['keyword' => $key])->one();
            return $config ? (int)$config->content : 0;
        };

        $delayVip = $getDelay('driver_rank_VIP');
        $delayGold = $getDelay('driver_rank_GOLD');
        $delayAgency = $getDelay('driver_rank_AGENCY');
        $delayBlackList = $getDelay('driver_rank_BLACKLIST');

        $currentTime = time();
        $batchSize = 50;

        // Validate sell_start_time and pickup_time
        $sellStartTime = !empty($model->sell_start_time) ? strtotime($model->sell_start_time) : null;
        $pickupTime = !empty($model->pickup_time) ? strtotime($model->pickup_time) : null;

        if (!$sellStartTime && !$pickupTime) {
            Yii::warning("Cannot queue jobs: Both sell_start_time and pickup_time are missing for trip {$model->id}");
            return;
        }

        foreach ($groupedDrivers as $rank => $drivers) {
            if (empty($drivers)) {
                continue;
            }

            $sendTime = null;
            switch (strtoupper($rank)) {
                case 'VIP':
                    $sendTime = $sellStartTime ? ($sellStartTime - ($delayVip * 60)) : null;
                    break;
                case 'GOLD':
                    $sendTime = $sellStartTime ? ($sellStartTime - ($delayGold * 60)) : null;
                    break;
                case 'AGENCY':
                    $sendTime = $pickupTime ? ($pickupTime - ($delayAgency * 60)) : null;
                    break;
                case 'BLACKLIST':
                    $sendTime = $pickupTime ? ($pickupTime - ($delayBlackList * 60)) : null;
                    break;
                default:
                    $sendTime = $sellStartTime ?: $pickupTime;
            }

            if ($sendTime === null) {
                Yii::warning("Cannot calculate send time for rank '{$rank}' in trip {$model->id}");
                continue;
            }

            $delayInSeconds = max(0, $sendTime - $currentTime);
            $chunks = array_chunk($drivers, $batchSize);

            foreach ($chunks as $chunk) {
                $driverIds = array_column($chunk, 'id');
                if (empty($driverIds)) {
                    continue;
                }

                try {
                    Yii::$app->queue->delay($delayInSeconds)->push(new OpenTripJob([
                        'trip_id' => $model->id,
                        'driver_ids' => $driverIds
                    ]));
                } catch (\Exception $e) {
                    Yii::error("Failed to queue OpenTripJob for trip {$model->id}: " . $e->getMessage());
                }
            }
        }
    }
}
