<?php

namespace app\services;

use app\helpers\MyStringHelper;
use app\models\Customer;
use app\models\Trip;
use Yii;
use yii\base\Component;

class CustomerService extends Component
{
    public function getStatisticCustomer()
    {
        return [
            'total' => $this->getTotal('total_trip'),
            'complete' => $this->getTotal('total_complete'),
            'cancel' => $this->getTotal('total_cancel'),
            'paid' => $this->getTotal('total_paid'),
        ];
    }

    public function getTotal($column)
    {
        return MyStringHelper::convertIntegerToPrice(Yii::$app->db->createCommand("SELECT SUM($column) FROM customer")->queryScalar());
    }

    /**
     * Create customer
     * @param $tripModel Trip
     * @return $customer
     */
    public function createCustomer($tripModel)
    {
        $customer = Customer::find()->where(['LIKE', 'phone', $tripModel->customer_phone])->one();
        if (! $customer instanceof Customer) {
            $customer = new Customer();
            $customer->phone = trim($tripModel->customer_phone);
        }
        $customer->display_name = $tripModel->customer_name;
        $customer->save();

        return $customer;
    }
}
