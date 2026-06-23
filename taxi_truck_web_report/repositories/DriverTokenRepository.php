<?php

namespace app\repositories;

use app\models\Driver;
use app\models\DriverToken;

class DriverTokenRepository
{
    /**
     * Tìm DriverToken theo driver_id với token khác null và không rỗng
     *
     * @param int $driverId
     * @return []
     */
    public function findValidTokenByDriverId(int $driverId)
    {
        return DriverToken::find()
            ->where(['driver_id' => $driverId])
            ->andWhere(['not', ['token' => null]])
            ->andWhere(['<>', 'token', ''])
            ->asArray()
            ->all();
    }

    /**
     * Lấy tất cả DriverToken hợp lệ của tài xế không bị khóa (status != STATUS_LOCK)
     *
     * @return array
     */
    public function findAllValidTokens()
    {
        return DriverToken::find()
            ->alias('dt')
            ->select(['dt.token'])
            ->distinct()
            ->innerJoin(['d' => Driver::tableName()], 'dt.driver_id = d.id AND d.is_sub_driver = ' . DRIVER_TYPE_NORMAL)
            ->where(['not', ['dt.token' => null]])
            ->andWhere(['<>', 'dt.token', ''])
            ->andWhere(['<>', 'd.status', Driver::STATUS_LOCK])
            ->column();
    }

    /**
     * Xóa tất cả bản ghi DriverToken theo token (một hoặc nhiều token)
     *
     * @param string[] $tokens
     * @return int số bản ghi đã xóa
     */
    public function deleteAllByTokens(array $tokens): int
    {
        if (empty($tokens)) {
            return 0;
        }
        return DriverToken::deleteAll(['token' => $tokens]);
    }

    // /**
    //  * Xóa một bản ghi DriverToken theo token
    //  *
    //  * @param string $token
    //  * @return int số bản ghi đã xóa
    //  */
    // public function deleteByToken(string $token): int
    // {
    //     if ($token === '') {
    //         return 0;
    //     }
    //     return DriverToken::deleteAll(['token' => $token]);
    // }
}
