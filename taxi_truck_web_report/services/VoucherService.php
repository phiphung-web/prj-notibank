<?php

namespace app\services;

use app\models\Trip;
use app\models\Voucher;

class VoucherService
{
    public function searchCodeNotSend()
    {
        return Voucher::find()
            ->where(['is_send' => false])
            ->one();
    }

    public function updateVoucherIsSend($voucher)
    {
        $voucher->is_send = true;
        $voucher->save();
    }

    public function searchByCodeAndNotUsed($code)
    {
        $count = Trip::find()
            ->where(['voucher' => $code])
            ->andWhere(['not in', 'status', ['CANCEL', 'PENDING']])
            ->count();

        $voucher = Voucher::find()
            ->where([
                'code' => $code,
                'status' => true,
            ])
            ->andWhere(['>', 'quantity', $count])
            ->one();

        if ($voucher !== null) {
            return $voucher;
        } else {
            return null;
        }
    }
}
