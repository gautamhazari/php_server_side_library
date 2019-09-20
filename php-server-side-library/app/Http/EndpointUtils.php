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
use MCSDK\Constants\Parameters;
use MCSDK\Discovery\VersionDetection;
use MCSDK\Exceptions\InvalidScopeException;
use MCSDK\MobileConnectRequestOptions;
use MCSDK\MobileConnectStatus;
use MCSDK\Web\ResponseConverter;

class EndpointUtils
{
    public static function startEndpointRequest($mobileConnect, $config, $discoveryResponse, $authResponse) {
        try {
            $status = null;
            $mobileConnectWebResponse = ResponseConverter::Convert($authResponse);
            $token = $mobileConnectWebResponse->getToken()[Parameters::ACCESS_TOKEN_HINT];
            $scopes = $config->getScopes();
            $apiVersion = VersionDetection::getCurrentVersion($config->getApiVersion(), $scopes, $discoveryResponse->getProviderMetadata());

            if ($apiVersion == (DefaultOptions::VERSION_1_1) & !empty($discoveryResponse->getOperatorUrls()->getUserInfoUrl())) {
                $status = EndpointUtils::requestUserInfo($mobileConnect, $discoveryResponse, $scopes, $token);
            } else if (($apiVersion == (DefaultOptions::VERSION_DI_2_3) || $apiVersion == (DefaultOptions::VERSION_2_0)
                    || $apiVersion == (DefaultOptions::VERSION_DI_3_0)) & !empty($discoveryResponse->getOperatorUrls()->getPremiumInfoUrl())) {
                $status = EndpointUtils::requestIdentity($mobileConnect, $discoveryResponse, $scopes, $token);
            }
        } catch (InvalidScopeException $e) {
            return MobileConnectStatus::Error("invalid_scope", $e->getMessage());
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