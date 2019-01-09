<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 6.12.18
 * Time: 11.20
 */

namespace App\Http\Auth;


use App\Http\Claims\KYCClaimsParameter;
use App\Http\McUtils;
use MCSDK\Constants\Parameters;
use MCSDK\Constants\Scope;

class AuthWithDiscovery
{
    public static function startAuth($mobileConnect, $response, $config) {
        $sdkSession = $response->getSDKSession();
        $subscriberId = $response->getDiscoveryResponse()->getResponseData()[Parameters::SUBSCRIBER_ID];

        if (strpos($config->getScopes(), Scope::KYC) !== false) {
            $status = AuthWithDiscovery::startKYC($mobileConnect, $sdkSession, $subscriberId, $config);
        } else if (strpos($config->getScopes(), Scope::AUTHZ) !== false) {
            $status = AuthWithDiscovery::startAuthorisation($mobileConnect, $sdkSession, $subscriberId, $config);
        } else {
            $status = AuthWithDiscovery::startAuthentication($mobileConnect, $sdkSession, $subscriberId, $config);
        }
        return McUtils::processAuthResponseResult($status, $response->getDiscoveryResponse());
    }

    private static function startAuthentication($mobileConnect, $sdkSession, $subscriberId, $config) {
        $options = McUtils::getMcOptions($config);
        $status = $mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
        return $status;
    }

    private static function startAuthorisation($mobileConnect, $sdkSession, $subscriberId, $config) {
        $options = McUtils::getMcOptions($config);
        $status = $mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
        return $status;
    }

    private static function startKYC($mobileConnect, $sdkSession, $subscriberId, $config) {
        $kycClaims = new KYCClaimsParameter();
        $kycClaims->setName($config->getName())
            ->setAddress($config->getAddress());
        $options = McUtils::getMcOptions($config);
        $options->setClaims($kycClaims);
        $status = $mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
        return $status;
    }

}