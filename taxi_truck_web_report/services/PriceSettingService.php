<?php

namespace app\services;

use app\models\PriceSetting;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;

class PriceSettingService
{
    /**
     * Lấy dữ liệu PriceSetting theo đối tượng Agency
     * @param Agency $agency
     * @return array|PriceSetting
     */
    public function getPriceSettingByAgency($agency, $pickupTime)
    {
        $query = PriceSetting::find();

        if (! empty($agency) && isset($agency->id)) {
            $query->andWhere(['agency_id' => $agency->id]);
        } else {
            $query->andWhere(['agency_id' => null])->orWhere(['agency_id' => 0]);
        }

        $query->andWhere([
            'or',
            ['<=', 'start_date', $pickupTime],
            ['start_date' => null],
        ])->andWhere([
            'or',
            ['>=', 'end_date', $pickupTime],
            ['end_date' => null],
        ]);

        $query->andWhere(['active' => 1]);

        return $query->one();
    }

    /**
     * Lấy tất cả dữ liệu với phân trang
     * @param int $pageSize
     * @return ActiveDataProvider
     */
    public function getPaginatedData($pageSize = 20, $agencyId = null, $sort = 'id')
    {
        $query = PriceSetting::find();

        if (! empty($agencyId)) {
            $query->andWhere(['agency_id' => $agencyId]);
        }

        // Xử lý sắp xếp (dynamic sorting)
        $sortOrder = SORT_DESC;
        $sortColumn = 'id';

        if (! empty($sort)) {
            if (str_starts_with($sort, '-')) {
                $sortOrder = SORT_DESC;
                $sortColumn = substr($sort, 1);
            } else {
                $sortOrder = SORT_ASC;
                $sortColumn = $sort;
            }
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
            'sort' => [
                'defaultOrder' => [$sortColumn => $sortOrder],
            ],
        ]);
    }

    /**
     * Xử lý dữ liệu từ form và lưu model
     *
     * @param PriceSettings $model
     * @param array $postData
     * @return bool
     */
    public function processAndSave(PriceSetting $model, array $postData)
    {
        // Xử lý dữ liệu từ form
        if (isset($postData['price'])) {
            $model->price = str_replace('.', '', $postData['price']);
        }

        if (isset($postData['percent'])) {
            $model->percent = $postData['percent'] / 100;
        }

        if (isset($postData['start_date'])) {
            $model->start_date = $postData['start_date'];
        }

        if (isset($postData['end_date'])) {
            $model->end_date = $postData['end_date'];
        }

        if (isset($postData['active'])) {
            $model->active = $postData['active'];
        }

        // Lưu dữ liệu
        return $model->save();
    }

    /**
     * Tính giá sau khi cộng thêm giá từ response và nhân phần trăm
     * @param float $initialPrice Giá ban đầu
     * @param array $response Dữ liệu response chứa price và percent
     * @return float
     */
    public function calculateFinalPrice($initialPrice, $response)
    {
        if (isset($response) && ! empty($response)) {
            $additionalPrice = (float)$response['price'];
            $percent = (float)$response['percent'];

            // Tính toán giá mới: (Giá ban đầu) * Phần trăm  + Giá từ response
            $finalPrice = $additionalPrice + $initialPrice * $percent;
            $finalPriceRounded = round($finalPrice, -3);
        } else {
            $finalPriceRounded = $initialPrice;
        }

        return $finalPriceRounded;
    }


    /**
     * Chuyển đổi định dạng ngày giờ nếu cần
     */
    private function formatDateTime($date)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i', $date);

        return $dateTime ? $dateTime->format('Y-m-d H:i:s') : null;
    }

    /**
     * Lấy tất cả bản ghi từ bảng price_settings
     */
    public function getAll()
    {
        return PriceSetting::find()->all();
    }

    /**
     * Tìm bản ghi theo ID
     */
    public function getById($id)
    {
        return PriceSetting::findOne($id);
    }

    /**
     * Tạo hoặc cập nhật bản ghi
     */
    public function save(PriceSetting $model)
    {
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Lưu thành công!');

            return true;
        }
        Yii::$app->session->setFlash('error', 'Có lỗi xảy ra!');

        return false;
    }

    /**
     * Xóa bản ghi
     */
    public function delete($id)
    {
        $model = PriceSetting::findOne($id);
        if ($model && $model->delete()) {
            Yii::$app->session->setFlash('success', 'Xóa thành công!');

            return true;
        }
        Yii::$app->session->setFlash('error', 'Không thể xóa bản ghi này!');

        return false;
    }
}
