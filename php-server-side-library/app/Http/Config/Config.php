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
use MCSDK\Constants\Parameters;

class Config extends BaseConfig
{
    public function __construct() {
        $json = ConfigUtils::getJsonFromFile(Constants::DATA_PATH);
        $this->getCommonValuesFromJson($json);
        $this->getKycValuesFromFile( Constants::KYC_CLAIMS_PATH);
        $this->getSpecValuesFromJson($json);
    }

    private function getSpecValuesFromJson($json) {
        $this->xRedirect = $json[Parameters::X_REDIRECT];
        $this->includeRequestIP = $json[Parameters::INCLUDE_REQ_IP];
        $this->loginHintTokenPreference = $json[Parameters::LOGIN_HINT_TOKEN_PREFERENCE];
        $this->discoveryURL = $json[Constants::DISCOVERY_URL];
    }

    public function getDiscoveryUrl() {
        return $this->discoveryURL;
    }
}

