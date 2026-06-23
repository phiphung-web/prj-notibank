<?php

namespace app\services;

use app\helpers\MyHelper;
use app\models\customerService\CustomerService;
use app\models\Driver;
use app\models\Trip;
use Yii;
use yii\base\Component;

class CustomerServiceService extends Component
{
    public $systemConfigurationService;

    public function init()
    {
        $this->systemConfigurationService = new SystemConfigurationService();
    }

    public function actionConfirm($customerServiceId = 0, $tripId = 0)
    {
        $customerService = CustomerService::findOne($customerServiceId);
        if ($customerService == null) {
            $customerService = new CustomerService();
            $customerService->userid_created = Yii::$app->user->id;
        }

        $customerService->status = 1;
        $customerService->times = CUSTOMER_SERVICE_TIMES_SUCCESS;
        $customerService->trip_id = $tripId;
        $this->updateCustomerService($customerService);
    }

    public function updateCustomerService($customerService)
    {
        $customerService->userid_updated = Yii::$app->user->id;
        $feedbacks = MyHelper::getFeedbackConfigbie();
        $listFeedback = [];
        if (isset($customerService->cus_feedback_driver) && is_array($customerService->cus_feedback_driver) && count($customerService->cus_feedback_driver)) {
            foreach ($customerService->cus_feedback_driver as $value) {
                if (isset($feedbacks[$value])) {
                    $feedback = $feedbacks[$value];
                    $text = $feedback['text'];
                    $point = $feedback['point'] > 0 ? '+' . $feedback['point'] : $feedback['point'];
                    $listFeedback[$value] = "$text ($point điểm)";
                }
            }
        }
        $customerService->cus_feedback_driver = json_encode($listFeedback);

        if (empty($customerService->status)) {
            $customerService->times = ++$customerService->times;
            if ($customerService->times == CUSTOMER_SERVICE_TIMES_SUCCESS && ! empty($customerService->status)) {
                $customerService->status = STATUS_CUSTOMER_SERVICE_SUCCESS;
            }
        } else {
            $customerService->times = CUSTOMER_SERVICE_TIMES_SUCCESS;
        }
        $customerService->save();

        return $customerService;
    }

    public function updateDriverPoints($customerService, $customerServiceType)
    {
        if (! empty($customerService->point) && ! empty($customerService->driver_id) && $customerServiceType == STATUS_CUSTOMER_SERVICE_NO_PROCESS) {
            $driver = Driver::findOne($customerService->driver_id);
            $pointVip = $this->systemConfigurationService->getConfigByKeyword('point_vip');
            $driver->point = $driver->point + $customerService->point;
            if ($driver->point >= $pointVip && $driver->driver_rank == NORMAL_RANK_DRIVER) {
                $driver->driver_rank = VIP_RANK_DRIVER;
            } elseif ($driver->driver_rank == VIP_RANK_DRIVER && $driver->point < $pointVip) {
                $driver->driver_rank = NORMAL_RANK_DRIVER;
            }
            $driver->save();
        }
    }

    public function upsertCustomerServiceBatch($params = []): int
    {
        $query = Trip::find();
        $action = ['create' => [], 'update' => []];
        // Lấy ra danh sách chuyến xe
        $query->select([
            'trip.*',
            'customer_service.id as customer_service_id',
            'bid.driver_id',
        ])->innerJoinWith(['bid'])->andWhere(['bid.status' => STATUS_BID_SUCCESS])
            ->joinWith(['customerService']);
        $query->andWhere(['trip.id' => $params['id']])
            ->andWhere([
                'or',
                ['customer_service.status' => STATUS_CUSTOMER_SERVICE_NO_PROCESS],
                ['customer_service.status' => null],
            ]);
        if (empty($_POST['date'])) {
            $query->andFilterWhere(['between', 'trip.pickup_time', '2024-01-09 00:00:00', gmdate('Y-m-d H:i:s', time() + 7 * 3600)]);
        } else {
            $date = explode(' - ', $_POST['date']);
            $pickupTimeStart = date('Y-m-d 00:00:00', strtotime(trim($date[0])));
            $pickupTimeEnd = date('Y-m-d 23:59:59', strtotime(trim($date[1])));
            $query->andFilterWhere(['between', 'trip.pickup_time', $pickupTimeStart, $pickupTimeEnd]);
        }
        $query->groupBy(['trip.id']);
        $customerServiceList = $query->createCommand()->queryAll();
        if (isset($customerServiceList) && is_array($customerServiceList) && count($customerServiceList)) {
            foreach ($customerServiceList as $key => $value) {
                if (isset($value['customer_service_id']) && $value['customer_service_id'] > 0) {
                    $action['update'][] = $value;
                } else {
                    $action['create'][] = $value;
                }
            }
        }
        $count = 0;
        $count += $this->createBatch($action['create'], $params['admin']);
        $count += $this->updateBatch($action['update'], $params['admin']);

        return $count;
    }

    private function createBatch($createData, $admin)
    {
        $data = [];
        if (isset($createData) && is_array($createData) && count($createData)) {
            foreach ($createData as $key => $value) {
                $data[] = $this->storeCustomerService($value, $admin);
            }
            $connection = Yii::$app->db;
            $command = $connection->createCommand()->batchInsert('customer_service', ['trip_id', 'driver_id', 'userid_created', 'created_at'], $data);

            return $command->execute();
        } else {
            return 0;
        }
    }

    private function updateBatch($updateData, $admin)
    {
        $count = 0;
        if (isset($updateData) && is_array($updateData) && count($updateData)) {
            $connection = Yii::$app->db;
            $command = $connection->createCommand();
            foreach ($updateData as $key => $value) {
                $count += $command->update('customer_service', $this->storeCustomerService($value, $admin), ['id' => $value['customer_service_id']])->execute();
            }

            return $count;
        } else {
            return 0;
        }
    }

    private function storeCustomerService($customerServicceItem = [], $admin = 0)
    {
        $data = [
            'trip_id' => $customerServicceItem['id'],
            'driver_id' => $customerServicceItem['driver_id'],
            'userid_created' => $admin,
            'created_at' => gmdate('Y-m-d H:i:s', time() + 7 * 3600),
        ];

        return $data;
    }
}
