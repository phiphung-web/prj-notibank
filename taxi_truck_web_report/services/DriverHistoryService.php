<?php

namespace app\services;

use app\repositories\driver\DriverRepository;

class DriverHistoryService
{
    protected $driverRepository;

    public function __construct()
    {
        $this->driverRepository = new DriverRepository();
    }

    public function getDriverTransactionHistory($params)
    {
        return $this->driverRepository->getDriverTransactionHistory($params);
    }
}
