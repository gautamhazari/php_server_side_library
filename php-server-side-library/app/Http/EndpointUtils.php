<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 3.12.18
 * Time: 13.08
 */

namespace App\Http;


use App\Http\Constants\Constants;
use MCSDK\Constants\DefaultOptions;
use MCSDK\MobileConnectRequestOptions;
use MCSDK\Web\ResponseConverter;

class EndpointUtils
{
    public static function startEndpointRequest($mobileConnect, $config, $discoveryResponse, $authResponse) {
        $status = null;
        $mobileConnectWebResponse = ResponseConverter::Convert($authResponse);
        $token = $mobileConnectWebResponse->getToken()[Constants::ACCESS_TOKEN];
        $apiVersion = $config->getApiVersion();
        $scopes = $config->getScopes();

        if ($apiVersion == (DefaultOptions::VERSION_1_1) & !empty($discoveryResponse->getOperatorUrls()->getUserInfoUrl())) {
            $status = EndpointUtils::requestUserInfo($mobileConnect, $discoveryResponse, $scopes, $token);
        } else if (($apiVersion == (DefaultOptions::VERSION_DI_2_3) || $apiVersion == (DefaultOptions::VERSION_2_0)) & !empty($discoveryResponse->getOperatorUrls()->getPremiumInfoUrl())) {
            $status = EndpointUtils::requestIdentity($mobileConnect, $discoveryResponse, $scopes, $token);
        }
        return $status;
    }

    private static function requestUserInfo($mobileConnect, $discoveryResponse, $scopes, $token) {
        foreach ( Constants::USERINFO_SCOPES as $userInfoScope) {
            if ( strpos($scopes, $userInfoScope) !== false) {
                $status = $mobileConnect->RequestUserInfoByDiscoveryResponse($discoveryResponse, $token, new MobileConnectRequestOptions());
                return $status;
            }
        }
    }

    private static function requestIdentity($mobileConnect, $discoveryResponse, $scopes, $token) {
        foreach (Constants::IDENTITY_SCOPES as $identityScope) {
            if (strpos($scopes, $identityScope) !== false) {
                $status = $mobileConnect->RequestIdentityByDiscoveryResponse($discoveryResponse, $token, new MobileConnectRequestOptions());
                return $status;
            }
        }
    }
}