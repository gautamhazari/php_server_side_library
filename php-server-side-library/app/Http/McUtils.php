<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 21.11.18
 * Time: 12.24
 */

namespace App\Http;


use App\Http\Config\BaseConfig;
use MCSDK\Constants\DefaultOptions;
use MCSDK\MobileConnectRequestOptions;

class McUtils
{
    public static function getMcOptions(BaseConfig $config) {
        $apiVersion = $config->getApiVersion();
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

}