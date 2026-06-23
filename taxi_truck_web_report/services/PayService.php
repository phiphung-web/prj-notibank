<?php

namespace app\services;

use app\models\PayTransaction;
use Yii;

/**
 * Class PayService
 *
 * This class handles the trip-related operations.
 */
class PayService
{
    public $systemConfigurationService;

    public function __construct()
    {
        $this->systemConfigurationService = new SystemConfigurationService();
    }

    public function createTransaction($params)
    {
        $model = new PayTransaction();
        $model->description = 'Hệ thống tự động nạp tiền bán lịch!';
        $model->money = $params['price'];
        $model->driver_id = $params['driver_id'];

        return $model->save();
    }

    /**
     * Update the model and driver attributes.
     *
     * @param object $model The transaction model to be updated
     * @param object $driver The driver object to be updated
     * @return array An associative array containing the updated model and driver objects
     */
    public function updateModelAndDriver($model, $driver)
    {
        $money = $this->getPromotionRecharge($model->money);
        $model->admin_id_accepted = Yii::$app->user->id;
        $model->accepted_at = date('Y-m-d H:i:s');
        $model->modified_on = date('Y-m-d H:i:s');
        $model->status = 1;
        $model->driver_id = $driver->id;
        $model->money_before = $driver->money;
        $driver->money += $model->money + $money;
        $model->money = (string)$model->money;
        $model->money_after = $driver->money;

        return [
            'model' => $model,
            'driver' => $driver,
        ];
    }

    public function getPromotionRecharge($money = 0)
    {
        $currentTime = time() + 7 * 3600;
        $promotion = 0;
        $systemConfiguration = $this->systemConfigurationService->getAllConfiguration();
        if (isset($systemConfiguration['recharge_promotion']) && ! empty($systemConfiguration['recharge_promotion'])) {
            if (strtotime($systemConfiguration['recharge_time_start']) <= $currentTime && strtotime($systemConfiguration['recharge_time_end']) >= $currentTime) {
                $discountInfo = json_decode($systemConfiguration['recharge_promotion'], true);
                foreach ($discountInfo as $key => $info) {
                    if ($money >= $info['value'] && (isset($discountInfo[$key + 1]) && $money < $discountInfo[$key + 1]['value'])) {
                        $promotion = $money * ($info['percent'] / 100);

                        break;
                    } elseif ($money >= $info['value'] && ! isset($discountInfo[$key + 1])) {
                        $promotion = $money * ($info['percent'] / 100);

                        break;
                    }
                }
            }
        }

        return round($promotion);
    }

    /**
     * Save the model and driver to the database.
     *
     * @param object $model The transaction model to be saved
     * @param object $driver The driver object to be saved
     * @return bool True if both model and driver are saved successfully, false otherwise
     */
    public function saveModelAndDriver($model, $driver): bool
    {
        return $model->save() && $driver->save();
    }


    /**
     * Generates a JSON response with the provided status code and message.
     *
     * @param int $code The HTTP status code
     * @param string $message The message to include in the response
     *
     * @return string The JSON-encoded response
     */
    public function jsonResponse($code, $message)
    {
        return json_encode([
            'code' => $code,
            'message' => $message,
        ]);
    }
}