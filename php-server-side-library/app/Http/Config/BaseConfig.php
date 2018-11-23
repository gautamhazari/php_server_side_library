<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 20.11.18
 * Time: 12.33
 */

namespace App\Http\Config;


use App\Http\Constants;
use MCSDK\MobileConnectConfig;

abstract class BaseConfig
{
    protected $client_id;
    protected $clientSecret;
    protected $discoveryURL;
    protected $redirectURL;
    protected $xRedirect;
    protected $includeRequestIP;
    protected $apiVersion;
    protected $clientName;
    protected $scopes;
    protected $context;
    protected $binding_message;

    public function getJsonFromFile(String $fileName)
    {
        $string = file_get_contents(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.Constants::CONFIG_DIR_NAME.DIRECTORY_SEPARATOR.$fileName);
        return json_decode($string, true);
    }

    public function getCommonValuesFromJson($json) {
        $this->client_id = $json[Constants::CLIENT_ID];
        $this->clientSecret = $json[Constants::CLIENT_SECRET];
        $this->redirectURL = $json[Constants::REDIRECT_URL];
        $this->apiVersion = $json[Constants::API_VERS];
        $this->clientName = $json[Constants::CLIENT_NAME];
        $this->scopes = $json[Constants::SCOPES];
        $this->context = $json[Constants::CONTEXT];
        $this->binding_message = $json[Constants::BIND_MSG];
    }

    public function getMcConfig()
    {
        $config = new MobileConnectConfig();
        $config->setClientId($this->client_id);
        $config->setClientSecret($this->clientSecret);
        $config->setDiscoveryUrl($this->discoveryURL);
        $config->setRedirectUrl($this->redirectURL);

        return $config;
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