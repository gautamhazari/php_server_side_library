<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 14.11.18
 * Time: 16.32
 */

namespace App\Http\Config;

use App\Http\ConfigUtils;
use App\Http\Constants\Constants;

class Config extends BaseConfig
{
    public function __construct() {
        $json = ConfigUtils::getJsonFromFile(Constants::DATA_PATH);
        $this->getCommonValuesFromJson($json);
        $this->getKycValuesFromFile( Constants::KYC_CLAIMS_PATH);
        $this->getSpecValuesFromJson($json);
    }

    private function getSpecValuesFromJson($json) {
        $this->xRedirect = $json[Constants::X_REDIRECT];
        $this->includeRequestIP = $json[Constants::INCLUDE_REQ_IP];
        $this->discoveryURL = $json[Constants::DISCOVERY_URL];
    }

    public function getDiscoveryUrl() {
        return $this->discoveryURL;
    }
}

