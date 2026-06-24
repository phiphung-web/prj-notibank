<?php

namespace app\helpers;

use app\services\SystemConfigurationService;

class MyHelper
{
    public static function getCurrentDate()
    {
        return date('Y-m-d');
    }

    /**
     * Converts a time range string into an array of time ranges.
     *
     * @param string $time The time range string to convert.
     * @return array The array of time ranges.
     */
    public static function convertTimeRange($time = '')
    {
        $timeArr = [];
        $arr = explode("\n", $time);
        if (isset($arr) && is_array($arr) && count($arr)) {
            foreach ($arr as $key => $value) {
                $timeRange = explode('-', $value);
                if (count($timeRange) == 2) {
                    $timeArr[date('H:i', strtotime($timeRange[0])) . '-' . date('H:i', strtotime($timeRange[1]))] = [
                        'start' => date('H:i:s', strtotime($timeRange[0])),
                        'end' => date('H:i:s', strtotime($timeRange[1])),
                    ];
                }
            }
        }

        return $timeArr;
    }

    public static function getFeedbackConfigbie()
    {
        $feedbacks = [];
        $systemConfigurationService = new SystemConfigurationService();
        $configuration = $systemConfigurationService->getConfigByKeyword('point_feedback');
        $lines = explode("\n", $configuration);

        if (isset($lines) && is_array($lines) && count($lines)) {
            foreach ($lines as $key => $value) {
                $explode = explode('=', $value);
                $feedbacks[] = [
                    'text' => isset($explode[0]) ? trim($explode[0]) : '',
                    'point' => isset($explode[1]) ? (int)$explode[1] : 0,
                ];
            }
        }

        return $feedbacks;
    }

    public static function sendErrorToTelegramBot(string $message)
    {
        $systemConfigurationService = new SystemConfigurationService();
        $systemConfiguration = $systemConfigurationService->getAllConfiguration();
        $curl = curl_init('https://api.telegram.org/bot8204605759:AAEKsLjwcQL4RzXXmEgpbqyqKkQ3_UvAB40/sendMessage');
        $data = json_encode([
            'chat_id' => '-1003088600274',
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
            ],
        ]);
        curl_exec($curl);
        curl_close($curl);
        // pre($message);
    }

    /**
     * Check if the user has access to any of the specified routes.
     *
     * @param array $list_routes List of routes to check.
     * @param array $permissions_array List of user permissions.
     * @return bool Whether the user has access to any of the specified routes.
     */
    public static function check_user_can($list_routes, $permissions_array)
    {
        if (empty($list_routes)) {
            return false;
        }
        foreach ($list_routes as $route) {
            if (in_array($route, $permissions_array)) {
                return true;
            }
        }

        return false;
    }

    public static function slug($value = null)
    {
        $myHelper = new self();
        $value = $myHelper->removeutf8($value);
        $value = str_replace('-', ' ', trim($value));
        $value = preg_replace('/[^a-z0-9-]+/i', ' ', $value);
        $value = trim(preg_replace('/\s\s+/', ' ', $value));

        return strtolower(str_replace(' ', '-', trim($value)));
    }

    public static function removeutf8($value = null)
    {
        $chars = [
            'a' => ['ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'á', 'à', 'ả', 'ã', 'ạ', 'â', 'ă', 'Á', 'À', 'Ả', 'Ã', 'Ạ', 'Â', 'Ă'],
            'e' => ['ế', 'ề', 'ể', 'ễ', 'ệ', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê'],
            'i' => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị'],
            'o' => ['ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'Ố', 'Ồ', 'Ổ', 'Ô', 'Ộ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ơ', 'Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ơ'],
            'u' => ['ứ', 'ừ', 'ử', 'ữ', 'ự', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư'],
            'y' => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ'],
            'd' => ['đ', 'Đ'],
        ];
        foreach ($chars as $key => $arr) {
            foreach ($arr as $val) {
                $value = str_replace($val, $key, $value);
            }
        }

        return $value;
    }
}
