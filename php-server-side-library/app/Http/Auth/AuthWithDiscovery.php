<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 6.12.18
 * Time: 11.20
 */

namespace App\Http\Auth;


use App\Http\Auth\MobileConnectWebInterface;
use App\Http\Claims\KYCClaimsParameter;
use App\Http\McUtils;
use MCSDK\Constants\DefaultOptions;
use MCSDK\Constants\Parameters;
use MCSDK\Constants\Scope;
use MCSDK\Discovery\VersionDetection;
use MCSDK\Exceptions\InvalidScopeException;

class AuthWithDiscovery
{
    public static function startAuth($mobileConnect, $response, $config) {
        $sdkSession = $response->getSDKSession();
        $subscriberId = $response->getDiscoveryResponse()->getResponseData()[Parameters::SUBSCRIBER_ID];
        $options = McUtils::getMcOptions($config, $response->getDiscoveryResponse());

        $loginHintTokenPreference = $config->isLoginHintTokenPreference();
        $subscriberIdToken = $response->getDiscoveryResponse()->getResponseData()[Parameters::SUBSCRIBER_ID_TOKEN];
        try {
            if (VersionDetection::getCurrentVersion($config->getApiVersion(), $config->getScopes(),
                    $response->getDiscoveryResponse()->getProviderMetadata()) == DefaultOptions::VERSION_DI_3_0) {
                $loginHintTokenPreference = false;
            }
        } catch (InvalidScopeException $e) {}

        if (!$loginHintTokenPreference && !empty($subscriberId)) {
            $subscriberIdToken = null;
        } else if (!empty($subscriberIdToken)) {
            $subscriberId = null;
        }
        $options->getAuthenticationOptions()->setLoginHintToken($subscriberIdToken);

        if (strpos($config->getScopes(), Scope::KYC) !== false) {
            $status = AuthWithDiscovery::startKYC($mobileConnect, $sdkSession, $subscriberId, $options, $config);
        } else if (strpos($config->getScopes(), Scope::AUTHZ) !== false) {
            $status = AuthWithDiscovery::startAuthorisation($mobileConnect, $sdkSession, $subscriberId, $options);
        } else {
            $status = AuthWithDiscovery::startAuthentication($mobileConnect, $sdkSession, $subscriberId, $options);
        }
        return McUtils::processAuthResponseResult($status, $response->getDiscoveryResponse());
    }

    private static function startAuthentication($mobileConnect, $sdkSession, $subscriberId, $options) {
        $status = $mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
        return $status;
    }

    private static function startAuthorisation($mobileConnect, $sdkSession, $subscriberId, $options) {
        $status = $mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
        return $status;
    }

    private static function startKYC($mobileConnect, $sdkSession, $subscriberId, $options, $config) {
        $kycClaims = new KYCClaimsParameter();
        $kycClaims->setName($config->getName())
            ->setAddress($config->getAddress());
        $options->setClaims($kycClaims);
        $status = $mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
        return $status;
    }

}