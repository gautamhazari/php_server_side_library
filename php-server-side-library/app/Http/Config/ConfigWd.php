<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 14.11.18
 * Time: 16.33
 */

namespace App\Http\Config;

use App\Http\ConfigUtils;
use App\Http\Constants\Constants;
use MCSDK\Discovery\OperatorUrls;

class ConfigWd extends BaseConfig
{
    private $authURL;
    private $tokenURL;
    private $userInfoURl;
    private $premiumInfoURl;
    private $metadataURl;
    private $operatorUrls;

    public function __construct() {
        $json = ConfigUtils::getJsonFromFile(Constants::WD_DATA_PATH);
        $this->getCommonValuesFromJson($json);
        $this->getKycValuesFromFile( Constants::KYC_CLAIMS_PATH);
        $this->getSpecValuesFromJson($json);
    }

    private function getSpecValuesFromJson($json) {
        $this->authURL = $json[Constants::AUTH_URL];
        $this->tokenURL = $json[Constants::TOKEN_URL];
        $this->userInfoURl = $json[Constants::USERINFO_URL];
        $this->premiumInfoURl = $json[Constants::PREMIUMINFO_URL];
        $this->metadataURl = $json[Constants::METADATA_URL];
    }

    public function getOperatorUrls() {
        $this->operatorUrls = new OperatorUrls();
        $this->operatorUrls->setAuthorizationUrl($this->authURL);
        $this->operatorUrls->setRequestTokenUrl($this->tokenURL);
        $this->operatorUrls->setUserInfoUrl($this->userInfoURl);
        $this->operatorUrls->setPremiumInfoUrl($this->premiumInfoURl);
        $this->operatorUrls->setProviderMetadataUrl($this->metadataURl);

        return $this->operatorUrls;
    }

    public function getAuthURL() {
        return $this->authURL;
    }

    public function getTokenUrl() {
        return $this->tokenURL;
    }

    public function getUserInfoURl() {
        return $this->userInfoURl;
    }

    public function getPremiumInfoURl() {
        return $this->premiumInfoURl;
    }

    public function getMetadataURl() {
        return $this->metadataURl;
    }

}

