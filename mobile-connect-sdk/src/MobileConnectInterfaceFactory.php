<?php

namespace MCSDK;


use MCSDK\Authentication\AuthenticationService;
use MCSDK\Authentication\JWKeysetService;
use MCSDK\Cache\Cache;
use MCSDK\Discovery\DiscoveryService;
use MCSDK\Identity\IdentityService;
use MCSDK\Utils\RestClient;

class MobileConnectInterfaceFactory
{
    public static function buildMobileConnectWebInterface(){
        $restClient = new RestClient();
        $cache = new Cache();
        $discoveryService = new DiscoveryService($restClient, $cache);
        $jwkeysetService = new JWKeysetService($restClient, $cache);
        $mobileConnectConfig = new MobileConnectConfig();
        $authenticationService = new AuthenticationService($restClient);
        $identityService = new IdentityService($restClient);
        return new MobileConnectWebInterface($discoveryService, $authenticationService, $identityService,$jwkeysetService, $mobileConnectConfig);
    }

    public static function buildMobileConnectWebInterfaceWithConfig(MobileConnectConfig $mobileConnectConfig){
        $restClient = new RestClient();
        $cache = new Cache();
        $discoveryService = new DiscoveryService($restClient, $cache);
        $jwkeysetService = new JWKeysetService($restClient, $cache);
        $authenticationService = new AuthenticationService($restClient);
        $identityService = new IdentityService($restClient);
        return new MobileConnectWebInterface($discoveryService, $authenticationService, $identityService,$jwkeysetService, $mobileConnectConfig);
    }
}