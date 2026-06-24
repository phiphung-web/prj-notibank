<?php

namespace app\services;

use yii\base\Component;

class LocationConfigurationService extends Component
{
    public function renderListLocation($params)
    {
        return '<li class="action-select-address" data-address="' . $this->removeString($params['display_name']) . '" data-lat="' . (isset($params['latitude']) ? $params['latitude'] : $params['lat']) . '" data-long="' . (isset($params['longitude']) ? $params['longitude'] : $params['lon']) . '">' . $this->removeString($params['display_name']) . '</li>';
    }

    public function removeString($inputString)
    {
        $pattern = '/,\s*\d{5}\b/';
        $inputString = str_replace(', Việt Nam', '', $inputString);
        $inputString = preg_replace($pattern, '', $inputString);

        return $this->removeAddressPartsRegex($inputString);
    }

    public function removeAddressPartsRegex($inputAddress)
    {
        $addressPartsToRemove = [' Phường', ' Xã', ' Quận', ' Huyện', ' Tỉnh', ' Thành phố'];
        $address = str_replace($addressPartsToRemove, '', $inputAddress);

        return $address;
    }
}
