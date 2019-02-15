<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 21.11.18
 * Time: 12.24
 */

namespace App\Http;


use App\Http\Config\BaseConfig;
use App\Http\Constants\Status;
use MCSDK\Constants\DefaultOptions;
use MCSDK\Constants\Scope;
use MCSDK\Discovery\DiscoveryResponse;
use MCSDK\Discovery\VersionDetection;
use MCSDK\MobileConnectRequestOptions;

class McUtils
{
    public static function getMcOptions(BaseConfig $config, DiscoveryResponse $discoveryResponse) {
        $apiVersion = VersionDetection::getCurrentVersion($config->getApiVersion(), $config->getScopes(), $discoveryResponse->getProviderMetadata());
        $scopes = $config->getScopes();
        $clientName = $config->getClientName();
        $context = $config->getContext();
        $bindingMessage = $config->getBindingMessage();

        $options = new MobileConnectRequestOptions();
        $options->getAuthenticationOptions()->setVersion($apiVersion);
        $options->setScope($scopes);
        $options->setContext(($apiVersion == DefaultOptions::VERSION_2_0 || $apiVersion == DefaultOptions::VERSION_DI_2_3) ? $context : null);
        $options->setBindingMessage(($apiVersion == DefaultOptions::VERSION_2_0 || $apiVersion == DefaultOptions::VERSION_DI_2_3) ? $bindingMessage : null);
        $options->setClientName($clientName);
        return $options;
    }

    public static function isErrorInResponse($response) {
        return !empty($response->getErrorCode())? true : false;
    }

    public static function setCacheByRequest($mcc, $mnc, $ip, $msisdn, $discoveryResponse){
        if($discoveryResponse instanceof DiscoveryResponse) {
            $databaseHelper = new DatabaseHelper();
            if (!empty($msisdn)) {
                $databaseHelper->setCachedDiscoveryResponseByMsisdn($msisdn, $discoveryResponse);
            }
            if (!empty($mcc) && !empty($mnc)) {
                $databaseHelper->setCachedDiscoveryResponseByMccMnc($mcc, $mnc, $discoveryResponse);
            }
            if (!empty($ip)) {
                $databaseHelper->setCachedDiscoveryResponseByIp($ip, $discoveryResponse);
            }
        }
    }

    public static function processAuthResponseResult($authResponse, $discoveryResponse) {
        $databaseHelper = new DatabaseHelper();
        if (McUtils::isErrorInResponse($authResponse)) {
            return HttpUtils::redirectToView($authResponse, McUtils::getAuthName(WdController::$_config->getScopes()));
        } else {
            $databaseHelper->writeDiscoveryResponseToDatabase($authResponse->getState(), $discoveryResponse);
            $databaseHelper->writeNonceToDatabase($authResponse->getState(), $authResponse->getNonce());
            return redirect($authResponse->getUrl());
        }
    }

    public static function getParamWithName(String $paramName = null, String $paramVal = null)
    {
        if (!empty($paramVal) && !empty($paramName)) {
            return sprintf("%s:%s", $paramName, $paramVal);
        }
    }

    public static function getAuthName($currentScope) {
        if (strpos($currentScope, Scope::AUTHN) !== false || $currentScope == Scope::OPENID) {
            return Status::AUTHENTICATION;
        } else if (strpos($currentScope, Scope::AUTHZ) !== false) {
            return Status::AUTHORISATION;
        }
    }
}