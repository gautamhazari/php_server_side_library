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

        if (strpos($config->getScopes(), Scope::KYC) !== false) {
            $status = AuthWithoutDiscovery::startKYC($mobileConnect, $response, $msisdn, $config);
        } else if (strpos($config->getScopes(), Scope::AUTHZ) !== false) {
            $status = AuthWithoutDiscovery::startAuthorisation($mobileConnect, $response, $msisdn, $config);
        } else {
            $status = AuthWithoutDiscovery::startAuthentication($mobileConnect, $response, $msisdn, $config);
        }

        return McUtils::processAuthResponseResult($status, $response);
    }

    private static function startAuthentication($mobileConnect, $discoveryResponse, $msisdn, $config) {
        $options = McUtils::getMcOptions($config);
        $options->setLoginHint(McUtils::getParamWithName(Parameters::MSISDN, $msisdn));
        return $mobileConnect->Authentication($discoveryResponse, null, null, null, $options);
    }

    private static function startAuthorisation($mobileConnect, $discoveryResponse, $msisdn, $config) {
        $options = McUtils::getMcOptions($config);
        $options->setLoginHint(McUtils::getParamWithName(Parameters::MSISDN, $msisdn));
        return $mobileConnect->Authentication($discoveryResponse, null, null, null, $options);
    }

    private static function startKYC($mobileConnect, $discoveryResponse, $msisdn, $config) {
        $kycClaims = new KYCClaimsParameter();
        $kycClaims->setName($config->getName())
            ->setAddress($config->getAddress());
        $options = McUtils::getMcOptions($config);
        $options->setLoginHint(McUtils::getParamWithName(Parameters::MSISDN, $msisdn));
        $options->setClaims($kycClaims);
        $status = $mobileConnect->Authentication($discoveryResponse, null, null, null, $options);
        return $status;
    }

}