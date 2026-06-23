<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\VarDumper;
use Yii;

/**
 * This is the model class for table "calculation_formula".
 *
 * @property int $id
 * @property int $type_of_car
 * @property string $time_start
 * @property string $time_end
 * @property string $schedule
 * @property string $price
 * @property string $price_by_km
 * @property string $km_start
 * @property string $km_end
 * @property string $surcharge
 * @property string $price_wait
 * @property string $overnight_fee
 * @property string $description
 */
class CalculationFormula extends ActiveRecord
{
    public $keyword;

    // Default values

    public const DEFAULT_TYPE_OF_CAR = 0;
    public const SCHEDULE_URBAN = 1;  // nội thành
    public const SCHEDULE_INTER_PROVINCE = 2;  // liên tỉnh
    public const DEFAULT_KM = 0;
    public const DEFAULT_PRICE = 0;
    public const DEFAULT_PRICE_BY_KM = 0;
    public const DEFAULT_SURCHARGE = 0;
    public const DEFAULT_PRICE_WAIT = 0;
    public const DEFAULT_OVERNIGHT_FEE = 0;
    public const DEFAULT_DESCRIPTION = '';
    // Business rule
    public const SCHEDULE_THRESHOLD_KM = 40;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'calculation_formula';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_of_car', 'time_start', 'time_end', 'schedule', 'price', 'price_by_km', 'km_start', 'km_end', 'surcharge', 'price_wait', 'description', 'overnight_fee'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_of_car' => 'Loại xe',
            'time_start' => 'Thời gian bắt đầu',
            'time_end' => 'Thời gian kết thúc',
            'schedule' => 'Lịch trình',
            'price' => 'Giá / km',
            'price_by_km' => 'Giá niêm yết',
            'km_start' => 'Số km bắt đầu',
            'km_end' => 'Số km kết thúc',
            'surcharge' => 'Phụ phí',
            'price_wait' => 'Phí chờ (xxx/h)',
            'overnight_fee' => 'Phí lưu đêm',
            'description' => 'Mô tả',
        ];
    }

    public function getListCalculationFormulas()
    {
        return CalculationFormula::find()->all();
    }

    public function getCalculationFormula($params)
    {
        $query = CalculationFormula::find();
        if (isset($params['schedule']) && is_array($params['schedule'])) {
            $query->andWhere(['IN', 'schedule', $params['schedule']]);
        }

        // Filter by km range
        if (isset($params['km'])) {
            $km = $params['km'];
            // (km between km_start and km_end) OR (km_end = 0 and km > km_start)
            $query->andWhere([
                'or',
                [
                    'and',
                    ['<=', 'km_start', $km],
                    ['>=', 'km_end', $km],
                    ['<>', 'km_end', 0]
                ],
                [
                    'and',
                    ['=', 'km_end', 0],
                    ['<', 'km_start', $km]
                ]
            ]);
        }

        $query->groupBy(['type_of_car', 'schedule']);

        $results = $query->asArray()->all();

        Yii::info(VarDumper::export($results), __METHOD__);
        return $results;
    }

    public function getNewCalculationFormula($params)
    {
        $distance = isset($params['distance']) ? max(0.0, (float) $params['distance']) : 0.0;
        $groupedRanges = $this->buildGroupedRanges($params);
        if (!$groupedRanges)
            return [];
        $totalsByGroup = $this->calculateDistancePrice($groupedRanges, $distance);
        $result = $this->buildFinalDetailRows($params, $totalsByGroup);
        $final = $this->sortResul($result);

        return $final;
    }

    // Filter out a data file grouped by vehicle type and schedule, 1 column containing price frames for calculation
    private function buildGroupedRanges($params)
    {
        $query = CalculationFormula::find()->alias('c');

        if (isset($params['schedule'])) {
            $schedule = is_array($params['schedule']) ? $params['schedule'] : [(int) $params['schedule']];
            $query->andWhere(['IN', 'schedule', $schedule]);
        }

        return $query
            ->select([
                'c.type_of_car',
                'c.schedule',
                'ranges_json' => new Expression(
                    "CONCAT('[', GROUP_CONCAT("
                    . "CONCAT('{',"
                    . "CONCAT('\"km_start\":', IFNULL(c.km_start, 'null'), ','),"
                    . "CONCAT('\"km_end\":', IFNULL(c.km_end, 'null'), ','),"
                    . "CONCAT('\"price\":', IFNULL(c.price, '0'), ','),"
                    . "CONCAT('\"price_by_km\":', IFNULL(c.price_by_km, '0')),"
                    . "'}')"
                    . " ORDER BY c.km_start SEPARATOR ','), ']')"
                )
            ])
            ->groupBy(['c.type_of_car', 'c.schedule'])
            ->orderBy(['c.type_of_car' => SORT_ASC, 'c.schedule' => SORT_ASC])
            ->asArray()
            ->all();
    }

    // calculate price_distance
    private function calculateDistancePrice($rows, $distance)
    {
        $grouped = [];
        foreach ($rows as $row) {
            $type = $row['type_of_car'];
            $schedule = $row['schedule'];
            $ranges = json_decode($row['ranges_json'], true) ?: [];
            usort($ranges, fn($a, $b) => ((float) $a['km_start']) <=> ((float) $b['km_start']));

            $grouped[$type][$schedule] = $ranges;
        }

        $result = [];
        foreach ($grouped as $type => $schedules) {
            $urban = $schedules[CalculationFormula::SCHEDULE_URBAN] ?? [];
            $inter = $schedules[CalculationFormula::SCHEDULE_INTER_PROVINCE] ?? [];

            $allRanges = array_merge($urban, $inter);
            $price = $this->calculateMixedRanges($allRanges, $distance);

            $scheduleKey = ($distance <= CalculationFormula::SCHEDULE_THRESHOLD_KM)
                ? CalculationFormula::SCHEDULE_URBAN
                : CalculationFormula::SCHEDULE_INTER_PROVINCE;

            $result["$type|$scheduleKey"] = $price;
        }

        return $result;
    }

    // Buil respone price_distance
    private function buildFinalDetailRows($params, $totalsByGroup)
    {
        $query = CalculationFormula::find()->alias('c');

        if (isset($params['schedule'])) {
            $schedule = is_array($params['schedule']) ? $params['schedule'] : [(int) $params['schedule']];
            $query->andWhere(['IN', 'schedule', $schedule]);
        }

        $detailRows = $query
            ->select(['c.*'])
            ->orderBy(['c.type_of_car' => SORT_ASC, 'c.schedule' => SORT_ASC, 'c.km_start' => SORT_ASC, 'c.id' => SORT_ASC])
            ->asArray()
            ->all();

        $seenGroup = [];
        $final = [];

        foreach ($detailRows as $r) {
            $k = (string) $r['type_of_car'] . '|' . (string) $r['schedule'];

            $priceDistance = isset($totalsByGroup[$k]) ? (float) $totalsByGroup[$k] : 0.0;

            if ($priceDistance <= 0.0) {
                continue;
            }

            if (isset($seenGroup[$k]))
                continue;
            $seenGroup[$k] = true;

            $final[] = [
                'id' => (string) $r['id'],
                'type_of_car' => (string) $r['type_of_car'],
                'time_start' => $r['time_start'],
                'time_end' => $r['time_end'],
                'schedule' => (string) $r['schedule'],
                'price' => (string) $r['price'],
                'price_by_km' => (string) $r['price_by_km'],
                'km_end' => isset($r['km_end']) ? (string) $r['km_end'] : null,
                'km_start' => isset($r['km_start']) ? (string) $r['km_start'] : null,
                'surcharge' => isset($r['surcharge']) ? (string) $r['surcharge'] : '0',
                'price_wait' => isset($r['price_wait']) ? (string) $r['price_wait'] : '0',
                'description' => (string) ($r['description'] ?? ''),
                'overnight_fee' => isset($r['overnight_fee']) ? (string) $r['overnight_fee'] : '0',
                'price_distance' => $totalsByGroup[$k] ?? 0.0,
            ];
        }

        return $final;
    }

    private function sortResul($result)
    {
        if (empty($result)) {
            return [];
        }
        foreach ($result as &$item) {
            $item['type_of_car'] = trim($item['type_of_car']);
            $item['type_of_car_label'] = TYPE_OF_CAR_LIST[(int) $item['type_of_car']] ?? 'Không rõ';
        }
        unset($item);

        usort($result, function ($a, $b) {
            $extractTon = function ($label) {
                if (preg_match('/([\d.]+)\s*tấn/i', $label, $m)) {
                    return (float) $m[1];
                }
                return -1;
            };
            return $extractTon($a['type_of_car_label']) <=> $extractTon($b['type_of_car_label']);
        });

        return $result;
    }

    private function calculateMixedRanges(array $ranges, float $distance): float
    {
        if (empty($ranges)) return 0.0;

        // Sort by km_start
        usort($ranges, fn($a, $b) => ((float)($a['km_start'] ?? 0)) <=> ((float)($b['km_start'] ?? 0)));

        $count = count($ranges);

        // CASE 1: Xử lý từng segment
        foreach ($ranges as $i => $seg) {
            $start = (float)($seg['km_start'] ?? 0);
            $end   = (float)($seg['km_end'] ?? 0); // 0 nghĩa là vô hạn

            $inRange = ($distance >= $start) && ($end == 0 || $distance <= $end);
            // Nếu distance rơi vào segment này
            if ($inRange) {

                // Item 0 → lấy giá cố định price_by_km
                if ($i === 0) {
                    return (float)($seg['price_by_km'] ?? 0.0);
                }

                // Nếu là item cuối và km_end = 0 → lấy price * distance
                if ($i === $count - 1 && $end == 0) {
                    return (float)($seg['price'] ?? 0) * $distance;
                }

                // Item 1 đến item N: tính tích lũy
                $total = (float)($ranges[0]['price_by_km'] ?? 0.0); // Bắt đầu từ giá cố định của item 0

                // Lặp qua từ item 1 → i
                for ($k = 1; $k <= $i; $k++) {
                    $prevEnd = (float)($ranges[$k - 1]['km_end'] ?? 0.0);
                    $currEnd = (float)($ranges[$k]['km_end'] ?? 0.0);
                    $price   = (float)($ranges[$k]['price'] ?? 0.0);

                    if ($k < $i) {
                        // Các item trước item hiện tại: tính FULL chiều dài range đó
                        // = km_end(item k) - km_end(item k-1)
                        $len = max(0.0, $currEnd - $prevEnd);
                    } else {
                        // Item hiện tại: chỉ tính phần dư từ km_end của item trước
                        $len = max(0.0, $distance - $prevEnd);
                    }

                    $total += $price * $len;
                }

                return $total;
            }
        }

        return 0.0; // Không khớp segment nào
    }
}
