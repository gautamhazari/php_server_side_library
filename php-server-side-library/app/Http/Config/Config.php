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
    private static $client_id;
    private static $clientSecret;
    private static $discoveryURL;
    private static $redirectURL;
    private static $xRedirect;
    private static $includeRequestIP;
    private static $apiVersion;
    private static $clientName;
    private static $scopes;

    private static $_config;


    public function __construct() {
        $string = file_get_contents(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."data.json");
        $json = json_decode($string, true);
        Config::$client_id = $json[Constants::CLIENT_ID];
        Config::$clientSecret = $json[Constants::CLIENT_SECRET];
        Config::$discoveryURL = $json[Constants::DISCOVERY_URL];
        Config::$redirectURL = $json[Constants::REDIRECT_URL];
        Config::$xRedirect = $json[Constants::X_REDIRECT];
        Config::$includeRequestIP = $json[Constants::INCLUDE_REQ_IP];
        Config::$apiVersion = $json[Constants::API_VERS];
        Config::$clientName = $json[Constants::CLIENT_NAME];
        Config::$scopes = $json[Constants::SCOPES];
    }

    public function getMcConfig() {
        if (Config::$_config == null) {
            Config::$_config = new MobileConnectConfig();
            Config::$_config->setClientId(Config::$client_id);
            Config::$_config->setClientSecret(Config::$clientSecret);
            Config::$_config->setDiscoveryUrl(Config::$discoveryURL);
            Config::$_config->setRedirectUrl(Config::$redirectURL);
        }
        return Config::$_config;
    }

    public function getClientId() {
        return Config::$client_id;
    }

    public function getClientSecret() {
        return Config::$clientSecret;
    }

    public function getDiscoveryUrl() {
        return Config::$discoveryURL;
    }

    public function getRedirectUrl() {
        return Config::$redirectURL;
    }

    public function isXredirect() {
        return Config::$xRedirect;
    }

    public function isIncludeRequestIP() {
        return Config::$includeRequestIP;
    }

    public function getApiVersion() {
        return Config::$apiVersion;
    }

    public function getClientName() {
        return Config::$clientName;
    }

    public function getScopes() {
        return Config::$scopes;
    }
}

