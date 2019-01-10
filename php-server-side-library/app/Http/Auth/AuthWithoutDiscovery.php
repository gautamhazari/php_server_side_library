<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 6.12.18
 * Time: 11.21
 */

namespace App\Http\Auth;


use App\Http\McUtils;
use MCSDK\Constants\Parameters;
use MCSDK\Constants\Scope;

class AuthWithoutDiscovery
{
    public static function startAuth($mobileConnect, $response, $config, $msisdn) {
        $options = McUtils::getMcOptions($config, $response);
        if (strpos($config->getScopes(), Scope::KYC) !== false) {
            $status = AuthWithoutDiscovery::startKYC($mobileConnect, $response, $msisdn, $options);
        } else if (strpos($config->getScopes(), Scope::AUTHZ) !== false) {
            $status = AuthWithoutDiscovery::startAuthorisation($mobileConnect, $response, $msisdn, $options);
        } else {
            $status = AuthWithoutDiscovery::startAuthentication($mobileConnect, $response, $msisdn, $options, $config);
        }

        return McUtils::processAuthResponseResult($status, $response);
    }

    private static function startAuthentication($mobileConnect, $discoveryResponse, $msisdn, $options) {
        $options->setLoginHint(McUtils::getParamWithName(Parameters::MSISDN, $msisdn));
        return $mobileConnect->Authentication($discoveryResponse, null, null, null, $options);
    }

    private static function startAuthorisation($mobileConnect, $discoveryResponse, $msisdn, $options) {
        $options->setLoginHint(McUtils::getParamWithName(Parameters::MSISDN, $msisdn));
        return $mobileConnect->Authentication($discoveryResponse, null, null, null, $options);
    }

    private static function startKYC($mobileConnect, $discoveryResponse, $msisdn, $options, $config) {
        $kycClaims = new KYCClaimsParameter();
        $kycClaims->setName($config->getName())
            ->setAddress($config->getAddress());
        $options->setLoginHint(McUtils::getParamWithName(Parameters::MSISDN, $msisdn));
        $options->setClaims($kycClaims);
        $status = $mobileConnect->Authentication($discoveryResponse, null, null, null, $options);
        return $status;
    }

}