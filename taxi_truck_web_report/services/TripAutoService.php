<?php

namespace app\services;

use app\models\SystemConfiguration;
use yii\base\Component;

class TripAutoService extends Component
{
    public function autoIncreasePriceBid($trip)
    {
        $currentTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600);
        $systemConfigurations = SystemConfiguration::getAllConfigurations();
        $priceBid = 0;
        // Thời gian cho phép bán muộn lớn nhất
        $sellLateBigEnd = date('Y-m-d H:i:s', strtotime($trip->pickup_time) - $this->convertTimeToSeconds($systemConfigurations['auto_time_sell_late_1']));

        // Thời gian cho phép bán muộn lớn vừa
        $sellLateMediumEnd = date('Y-m-d H:i:s', strtotime($trip->pickup_time) - $this->convertTimeToSeconds($systemConfigurations['auto_time_sell_late_2']));

        // Thời gian cho phép bán muộn sát giờ nhất
        $sellLateSmallEnd = date('Y-m-d H:i:s', strtotime($trip->pickup_time) - $this->convertTimeToSeconds($systemConfigurations['auto_time_sell_late_free']));

        // Tính toán thời gian mở bán sớm
        $timeSellSoonEnd = date('Y-m-d H:i:s', strtotime($trip->sell_start_time) + $this->convertTimeToSeconds($systemConfigurations['auto_time_sell_soon']));

        // Tính toán thời gian mở bán sớm
        $timeSellSoonAcceptEnd = date('Y-m-d H:i:s', strtotime($trip->sell_start_time) + $this->convertTimeToSeconds($systemConfigurations['auto_time_accept_sell_soon']));
        if ($sellLateSmallEnd < $currentTime && $currentTime < $trip->pickup_time) {
            // Nếu thời gian bán sát giờ thì sẽ lấy giá bằng với giá báo khách
            $priceBid = $trip->price_customer;
        } elseif ($sellLateMediumEnd < $currentTime && $currentTime < $sellLateSmallEnd) {
            // Nếu thời gian trước thời gian bán sát giờ thì sẽ tăng tiền theo bước nhảy
            $priceBid = $this->handleSellLateMedium($trip, $sellLateMediumEnd, $sellLateBigEnd, $currentTime, $systemConfigurations);
        } elseif ($sellLateBigEnd < $currentTime && $currentTime < $sellLateMediumEnd) {
            // Nếu thời gian sau thời gian bán đúng thì sẽ tăng tiền theo bước nhảy
            $priceBid = $this->handleSellLateBig($trip, $sellLateBigEnd, $currentTime, $systemConfigurations);
        } elseif ($trip->sell_start_time < $currentTime && $currentTime < $timeSellSoonEnd && $trip->sell_start_time < $timeSellSoonAcceptEnd && $timeSellSoonAcceptEnd < $trip->pickup_time) {
            // Kiểm tra thời gian bán sớm
            $priceBid = $this->handleTripSellSoon($trip, $timeSellSoonEnd, $currentTime, $trip->sell_start_time, $systemConfigurations);
        } else {
            $priceBid = $trip->price_bid;
        }

        return $priceBid;
    }

    private function handleSellLateMedium($trip, $sellLateMediumEnd, $sellLateBigEnd, $currentTime, array $systemConfigurations)
    {
        // Tính toán thời gian chênh lệch tính bằng phút
        $moneySellLateMedium = (int)$systemConfigurations['auto_money_sell_late_2'];
        $moneySellLateBig = (int)$systemConfigurations['auto_money_sell_late_1'];
        $step = (int)floor($this->calculateTimeMinusMinutes($currentTime, $sellLateMediumEnd));
        $stepBig = $this->convertTimeToMinute($systemConfigurations['auto_time_sell_late_1']) - $this->convertTimeToMinute($systemConfigurations['auto_time_sell_late_2']);
        $pricePlus = (int)($trip->price_bid + $moneySellLateBig * $stepBig + $moneySellLateMedium * $step);

        return $pricePlus >= $trip->price_customer ? $trip->price_customer : $pricePlus;
    }

    private function handleSellLateBig($trip, $sellLateBigEnd, $currentTime, array $systemConfigurations)
    {
        // Tính toán thời gian chênh lệch tính bằng phút
        $step = (int)floor($this->calculateTimeMinusMinutes($currentTime, $sellLateBigEnd));
        $moneySellLateBig = (int)$systemConfigurations['auto_money_sell_late_1'];

        $pricePlus = (int)($trip->price_bid + $moneySellLateBig * $step);

        return $pricePlus >= $trip->price_customer ? $trip->price_customer : $pricePlus;
    }

    private function handleTripSellSoon($trip, $sellStartTimeEnd, $currentTime, $sellStartTime, array $systemConfigurations)
    {
        // Khởi tạo dữ liệu trong cấu hình chung
        $minuteSellSoon = (int)$this->convertTimeToMinute($systemConfigurations['auto_minute_sell_soon']);
        $moneySellSoon = (int)$systemConfigurations['auto_money_sell_soon'];
        // Tính toán số tiền bán sớm lớn nhất
        $timeSellSoonBigest = $this->calculateTimeMinusMinutes($sellStartTimeEnd, $sellStartTime);
        $stepSellSoonBigest = (int)floor($timeSellSoonBigest / $minuteSellSoon);
        $moneySellSoonBigest = $trip->price_bid + $moneySellSoon * $stepSellSoonBigest;
        $moneySellSoonBigest = $moneySellSoonBigest >= $trip->price_customer ? $trip->price_customer : $moneySellSoonBigest;

        // Tính toán thời gian chênh lệch tính bằng phút
        $timeMinusMinutes = $this->calculateTimeMinusMinutes($currentTime, $sellStartTime);

        // Tính toán số bước tăng giá
        $step = (int)floor($timeMinusMinutes / $minuteSellSoon);
        $priceBid = $moneySellSoonBigest - $moneySellSoon * $step;

        return $priceBid <= $trip->price_bid ? $trip->price_bid : $priceBid;
    }

    private function calculateTimeMinusMinutes($timeMinus, $time)
    {
        return (strtotime($timeMinus) - strtotime($time)) / 60;
    }

    private function convertTimeToMinute($timeStr)
    {
        list($hours, $minutes) = explode(':', $timeStr);

        return ($hours * 3600) + $minutes;
    }

    private function convertTimeToSeconds($timeStr)
    {
        list($hours, $minutes) = explode(':', $timeStr);

        return ($hours * 3600) + ($minutes * 60);
    }
}
