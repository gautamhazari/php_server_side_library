<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 14.11.18
 * Time: 16.32
 */

namespace App\Http\Config;

use App\Http\Constants;

class Config extends BaseConfig
{
    public function __construct() {
        $json = $this->getJsonFromFile("data.json");
        $this->getCommonValuesFromJson($json);
        $this->xRedirect = $json[Constants::X_REDIRECT];
        $this->includeRequestIP = $json[Constants::INCLUDE_REQ_IP];
        $this->discoveryURL = $json[Constants::DISCOVERY_URL];
    }

    public function getDiscoveryUrl() {
        return $this->discoveryURL;
    }
}

