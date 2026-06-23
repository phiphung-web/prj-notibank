<?php

namespace app\services;

use app\models\SystemConfiguration;

/**
 * Class SystemConfigurationService
 *
 * This class handles the trip-related operations.
 */
class SystemConfigurationService
{
    /**
     * Retrieve all configuration data from the "SystemConfiguration" table.
     *
     * @return array An associative array where "keyword" is used as keys and "content" as values.
     */
    public function getAllConfiguration()
    {
        $system = SystemConfiguration::find()->asArray()->all();
        $result = array_column($system, 'content', 'keyword');

        // Handle special case for driver_accept_car_types - decode JSON to array
        if (isset($result['driver_accept_car_types'])) {
            $decodedValue = json_decode($result['driver_accept_car_types'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedValue)) {
                $result['driver_accept_car_types'] = $decodedValue;
            }
        }

        return $result;
    }

    public function getConfigByKeyword($keyword)
    {
        $system = SystemConfiguration::find()->where([
            'keyword' => $keyword,
        ])->one();

        return $system['content'];
    }
}
