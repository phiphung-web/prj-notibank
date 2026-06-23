<?php

namespace app\controllers;

use app\models\customerService\CustomerService;
use app\models\debt\TripAgency;
use app\models\Booking;
use app\models\Driver;
use app\models\RequestCallBack;
use app\models\SearchBooking;
use app\models\SearchTrip;
use app\models\SearchTripDriver;

class CountController extends BaseController
{
    protected $customerService;

    public function init()
    {
        parent::init();
        $this->customerService = new CustomerService();
    }

    public function actionCountDriverSub()
    {
        $count = Driver::find()->joinWith('car')->where(['driver_ban' => STATUS_DRIVER_BAN_WAIT_REVIEW, 'is_sub_driver' => DRIVER_TYPE_NORMAL])->count();

        return json_encode(['count' => $count]);
    }

    public function actionCountPassTrip()
    {
        $count = Booking::find()
            ->alias('b')
            ->innerJoin('trip t', 't.booking_id = b.id AND t.status IN ("DONE", "COMPLETE")')
            ->innerJoin('bid bi', 't.id = bi.trip_id AND bi.status = "SUCCESS"')
            ->innerJoin('driver d', 'b.driver_id_created = d.id')
            ->where(['b.type' => 1, 'b.paid_driver_on' => null])
            ->andWhere(['>', 't.pickup_time', '2026-01-01 00:00:00'])
            ->groupBy('b.id')
            ->count();

        return json_encode(['count' => $count]);
    }

    public function actionCountDriverRegister()
    {
        $count = Driver::find()->joinWith('car')->where(['status' => 0, 'is_sub_driver' => DRIVER_TYPE_NORMAL])->count();

        return json_encode(['count' => $count]);
    }

    public function actionCountCustomerService()
    {
        $countCustomerService = $this->customerService->countCustomerService();

        return json_encode($countCustomerService);
    }

    public function actionCountAgencyDebt()
    {
        $tripAgencyModel = new TripAgency();

        return json_encode([
            'countAdminDebtAgency' => $tripAgencyModel->countAgencyDebt(ADMIN_DEBT_AGENCY),
            'countAgencyDebtAdmin' => $tripAgencyModel->countAgencyDebt(AGENCY_DEBT_ADMIN),
        ]);
    }

    public function actionCountBooking()
    {
        $bookingModel = new SearchBooking();

        return json_encode([
            'countBookingCreate' => $bookingModel->countBookingCreate(),
            'countBookingWaiting' => $bookingModel->countBookingWaiting(),
        ]);
    }

    public function actionCountTripDebt()
    {
        $tripDriverModel = new SearchTripDriver();

        return json_encode([
            'countTripDriverSettlement' => $tripDriverModel->countTripDriverSettlement(),
            'countTripDriverCollection' => $tripDriverModel->countTripDriverCollection(),
            'countTripDebtCustomers' => $tripDriverModel->countTripDebtCustomers(),
        ]);
    }

    public function actionCountRequestCallback()
    {
        $requestCallbackModel = new RequestCallBack();

        return json_encode([
            'countRequestCallBack' => $requestCallbackModel->countNumberPhoneWaiting(),
        ]);
    }

    public function actionCountTripHaveDriverSubNoInfo()
    {
        $modelSearchTrip = new SearchTrip();

        return json_encode([
            'countTripHaveDriverSubNoInfo' => $modelSearchTrip->countTripHaveDriverSubNoInfo(),
        ]);
    }
}
