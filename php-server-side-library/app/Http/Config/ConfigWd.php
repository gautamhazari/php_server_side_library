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
use MCSDK\MobileConnectRequestOptions;

class ConfigWd
{
    private static $client_id;
    private static $clientSecret;
    private static $redirectURL;
    private static $apiVersion;
    private static $clientName;
    private static $scopes;

    private static $context;
    private static $binding_message;
    private static $authURL;
    private static $tokenURL;
    private static $userInfoURl;
    private static $premiumInfoURl;
    private static $metadataURl;

    private static $_operatorUrls;
    private static $_config;
    private static $_options;

    public function __construct() {
        $string = file_get_contents(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "withoutDiscoveryData.json");
        $json = json_decode($string, true);
        ConfigWd::$client_id = $json[Constants::CLIENT_ID];
        ConfigWd::$clientSecret = $json[Constants::CLIENT_SECRET];
        ConfigWd::$redirectURL = $json[Constants::REDIRECT_URL];
        ConfigWd::$apiVersion = $json[Constants::API_VERS];
        ConfigWd::$clientName = $json[Constants::CLIENT_NAME];
        ConfigWd::$scopes = $json[Constants::SCOPES];

        ConfigWd::$context = $json[Constants::CONTEXT];
        ConfigWd::$binding_message = $json[Constants::BIND_MSG];
        ConfigWd::$authURL = $json[Constants::AUTH_URL];
        ConfigWd::$tokenURL = $json[Constants::TOKEN_URL];
        ConfigWd::$userInfoURl = $json[Constants::USERINFO_URL];
        ConfigWd::$premiumInfoURl = $json[Constants::PREMIUMINFO_URL];
        ConfigWd::$metadataURl = $json[Constants::METADATA_URL];
    }

    public function getMcConfig() {
        if (ConfigWd::$_config == null) {
            ConfigWd::$_config = new MobileConnectConfig();
            ConfigWd::$_config->setClientId(ConfigWd::$client_id);
            ConfigWd::$_config->setClientSecret(ConfigWd::$clientSecret);
            ConfigWd::$_config->setRedirectUrl(ConfigWd::$redirectURL);
        }
        return ConfigWd::$_config;
    }

    public function getOperatorUrls() {
        if (ConfigWd::$_operatorUrls == null) {
            ConfigWd::$_operatorUrls = new OperatorUrls();
            ConfigWd::$_operatorUrls->setAuthorizationUrl(ConfigWd::$authURL);
            ConfigWd::$_operatorUrls->setRequestTokenUrl(ConfigWd::$tokenURL);
            ConfigWd::$_operatorUrls->setUserInfoUrl(ConfigWd::$userInfoURl);
            ConfigWd::$_operatorUrls->setPremiumInfoUrl(ConfigWd::$premiumInfoURl);
            ConfigWd::$_operatorUrls->setProviderMetadataUrl(ConfigWd::$metadataURl);
        }
        return ConfigWd::$_operatorUrls;
    }

    public function getMcOptions() {
        if (ConfigWd::$_options == null) {
            ConfigWd::$_options = new MobileConnectRequestOptions();
            ConfigWd::$_options->getAuthenticationOptions()->setVersion(ConfigWd::$apiVersion);
            ConfigWd::$_options->setScope(ConfigWd::$scopes);
            ConfigWd::$_options->setContext(ConfigWd::$context);
            ConfigWd::$_options->setBindingMessage(ConfigWd::$binding_message);
            ConfigWd::$_options->setClientName(ConfigWd::$clientName);
        }
        return ConfigWd::$_options;
    }

    public function getClientId() {
        return ConfigWd::$client_id;
    }

    public function getClientSecret() {
        return ConfigWd::$clientSecret;
    }

    public function getRedirectUrl() {
        return ConfigWd::$redirectURL;
    }

    public function getApiVersion() {
        return ConfigWd::$apiVersion;
    }

    public function getClientName() {
        return ConfigWd::$clientName;
    }

    public function getScopes() {
        return ConfigWd::$scopes;
    }

    public function getContext() {
        return ConfigWd::$context;
    }

    public function getBindingMessage() {
        return ConfigWd::$binding_message;
    }

    public function getAuthURL() {
        return ConfigWd::$authURL;
    }

    public function getTokenUrl() {
        return ConfigWd::$tokenURL;
    }

    public function getUserInfoURl() {
        return ConfigWd::$userInfoURl;
    }

    public function getPremiumInfoURl() {
        return ConfigWd::$premiumInfoURl;
    }

    public function getMetadataURl() {
        return ConfigWd::$metadataURl;
    }

}

