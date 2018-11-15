<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 14.11.18
 * Time: 16.33
 */

namespace App\Http\Config;

use App\Http\Constants;
use MCSDK\Discovery\OperatorUrls;
use MCSDK\MobileConnectConfig;

class ConfigWd
{
    private $client_id;
    private $clientSecret;
    private $redirectURL;
    private $apiVersion;
    private $clientName;
    private $scopes;

    private $context;
    private $binding_message;
    private $authURL;
    private $tokenURL;
    private $userInfoURl;
    private $premiumInfoURl;
    private $metadataURl;

    private $_operatorUrls;
    private $_config;
    private $_options;

    public function __construct() {
        $string = file_get_contents(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "withoutDiscoveryData.json");
        $json = json_decode($string, true);
        $this->client_id = $json[Constants::CLIENT_ID];
        $this->clientSecret = $json[Constants::CLIENT_SECRET];
        $this->redirectURL = $json[Constants::REDIRECT_URL];
        $this->apiVersion = $json[Constants::API_VERS];
        $this->clientName = $json[Constants::CLIENT_NAME];
        $this->scopes = $json[Constants::SCOPES];

        $this->context = $json[Constants::CONTEXT];
        $this->binding_message = $json[Constants::BIND_MSG];
        $this->authURL = $json[Constants::AUTH_URL];
        $this->tokenURL = $json[Constants::TOKEN_URL];
        $this->userInfoURl = $json[Constants::USERINFO_URL];
        $this->premiumInfoURl = $json[Constants::PREMIUMINFO_URL];
        $this->metadataURl = $json[Constants::METADATA_URL];
    }

    public function getMcConfig() {
        if ($this->_config == null) {
            $this->_config = new MobileConnectConfig();
            $this->_config->setClientId($this->client_id);
            $this->_config->setClientSecret($this->clientSecret);
            $this->_config->setRedirectUrl($this->redirectURL);
        }
        return $this->_config;
    }

    public function getOperatorUrls() {
        if ($this->_operatorUrls == null) {
            $this->_operatorUrls = new OperatorUrls();
            $this->_operatorUrls->setAuthorizationUrl($this->authURL);
            $this->_operatorUrls->setRequestTokenUrl($this->tokenURL);
            $this->_operatorUrls->setUserInfoUrl($this->userInfoURl);
            $this->_operatorUrls->setPremiumInfoUrl($this->premiumInfoURl);
            $this->_operatorUrls->setProviderMetadataUrl($this->metadataURl);
        }
        return $this->_operatorUrls;
    }

    public function getClientId() {
        return $this->client_id;
    }

    public function getClientSecret() {
        return $this->clientSecret;
    }

    public function getRedirectUrl() {
        return $this->redirectURL;
    }

    public function getApiVersion() {
        return $this->apiVersion;
    }

    public function getClientName() {
        return $this->clientName;
    }

    public function getScopes() {
        return $this->scopes;
    }

    public function getContext() {
        return $this->context;
    }

    public function getBindingMessage() {
        return $this->binding_message;
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

