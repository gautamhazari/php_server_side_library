<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 9.1.19
 * Time: 13.02
 */

namespace MCSDK\Discovery;


use MCSDK\Constants\Scope;
use MCSDK\Exceptions\InvalidScopeException;
use Zend\Stdlib\StringUtils;

class VersionDetection
{
    public static function getCurrentVersion($version, $scope, $providerMetadata) {
        $supportedVersions = VersionDetection::getSupportedVersions($providerMetadata);
        if (!empty($version) && VersionDetection::isVersionSupported($version)) {
            return $version;
        } else {
            $currentScopes = StringUtils::convertToListBySpace($scope);
            if (in_array(Version::MC_DI_R2_V2_3, $supportedVersions) && VersionDetection::containsScopesV2_3($currentScopes)) {
                return Version::MC_DI_R2_V2_3;
            } else if (in_array(Version::MC_V2_0, $supportedVersions) && VersionDetection::containsScopesV2_0($currentScopes)) {
                return Version::MC_V2_0;
            } else if (in_array(Version::MC_V1_1, $supportedVersions) && VersionDetection::containsOpenidScope($currentScopes) && sizeof($currentScopes) == 1) {
                return Version::MC_V1_1;
            } else if(in_array(Version::MC_V1_2, $supportedVersions) && sizeof($supportedVersions) == 1 && VersionDetection::containsOpenidScope($currentScopes)) {
                return Version::MC_V1_2;
            } else {
                throw new InvalidScopeException($scope);
            }
        }
    }

    private static function isVersionSupported($version) {
        return $version == Version::MC_V1_1 || $version == Version::MC_V1_2 || $version == Version::MC_V2_0
            || $version == Version::MC_DI_R2_V2_3;
    }

    private static function containsOpenidScope($currentScopes) {
        return in_array(Scope::OPENID, $currentScopes);
    }

    private static function containsScopesV2_0($currentScopes) {
        return VersionDetection::containsOpenidScope($currentScopes) & (in_array(Scope::AUTHN, $currentScopes) || in_array(Scope::AUTHZ, $currentScopes) ||
                    in_array(Scope::IDENTITY_PHONE, $currentScopes) || in_array(Scope::IDENTITY_NATIONALID, $currentScopes) ||
                    in_array(Scope::IDENTITY_SIGNUP, $currentScopes) || in_array(Scope::IDENTITY_SIGNUPPLUS, $currentScopes));
    }

    private static function containsScopesV2_3($currentScopes) {
        return VersionDetection::containsOpenidScope($currentScopes) & (VersionDetection::containsScopesV2_0($currentScopes) || in_array(Scope::KYC_HASHED, $currentScopes)
                || in_array(Scope::KYC_PLAIN, $currentScopes));
    }

    private static function getSupportedVersions($providerMetadata) {
        $supportedVersions = array();
        if (empty($providerMetadata) || empty($providerMetadata['mc_version'])) {
            array_push($supportedVersions,Version::MC_V1_1);
        } else {
            $supportedVersions = $providerMetadata['mc_version'];
        }
        return $supportedVersions;
    }
}