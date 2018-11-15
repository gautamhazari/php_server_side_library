<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 14.11.18
 * Time: 16.32
 */

namespace App\Http\Config;

use App\Http\Constants;
use MCSDK\MobileConnectConfig;

class Config
{
    private $client_id;
    private $clientSecret;
    private $discoveryURL;
    private $redirectURL;
    private $xRedirect;
    private $includeRequestIP;
    private $apiVersion;
    private $clientName;
    private $scopes;
    private $context;
    private $binding_message;

    private $_config;


    public function __construct() {
        $string = file_get_contents(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."data.json");
        $json = json_decode($string, true);
        $this->client_id = $json[Constants::CLIENT_ID];
        $this->clientSecret = $json[Constants::CLIENT_SECRET];
        $this->discoveryURL = $json[Constants::DISCOVERY_URL];
        $this->redirectURL = $json[Constants::REDIRECT_URL];
        $this->xRedirect = $json[Constants::X_REDIRECT];
        $this->includeRequestIP = $json[Constants::INCLUDE_REQ_IP];
        $this->apiVersion = $json[Constants::API_VERS];
        $this->clientName = $json[Constants::CLIENT_NAME];
        $this->scopes = $json[Constants::SCOPES];
        $this->context = $json[Constants::CONTEXT];
        $this->binding_message = $json[Constants::BIND_MSG];
    }

    public function getMcConfig() {
        if ($this->_config == null) {
            $this->_config = new MobileConnectConfig();
            $this->_config->setClientId($this->client_id);
            $this->_config->setClientSecret($this->clientSecret);
            $this->_config->setDiscoveryUrl($this->discoveryURL);
            $this->_config->setRedirectUrl($this->redirectURL);
        }
        return $this->_config;
    }

    public function getClientId() {
        return $this->client_id;
    }

    public function getClientSecret() {
        return $this->clientSecret;
    }

    public function getDiscoveryUrl() {
        return $this->discoveryURL;
    }

    public function getRedirectUrl() {
        return $this->redirectURL;
    }

    public function isXredirect() {
        return $this->xRedirect;
    }

    public function isIncludeRequestIP() {
        return $this->includeRequestIP;
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
}

