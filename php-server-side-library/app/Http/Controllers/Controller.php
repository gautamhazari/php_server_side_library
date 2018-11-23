<?php

namespace App\Http\Controllers;
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

use App\Http\Config\Config;
use App\Http\Constants;
use App\Http\HttpUtils;
use App\Http\McUtils;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use MCSDK\Authentication\AuthenticationService;
use MCSDK\Authentication\JWKeysetService;
use MCSDK\Cache\Cache;
use MCSDK\Constants\Scope;
use MCSDK\Discovery\DiscoveryResponse;
use MCSDK\Discovery\DiscoveryService;
use MCSDK\Identity\IdentityService;
use MCSDK\MobileConnectRequestOptions;
use MCSDK\MobileConnectStatus;
use MCSDK\MobileConnectWebInterface;
use MCSDK\Utils\MobileConnectResponseType;
use MCSDK\Utils\RestClient;
use MCSDK\Web\ResponseConverter;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** @var MobileConnectWebInterface */
    private static $_mobileConnect;
    private static $_operatorUrls;
    private static $_subId;
    private static $_apiVersion;
//    private static $_xRedirect = "APP";
    private static $_includeReqIp;
    private static $_scopes;
    private static $_clientName;
    private static $json;
    private static $_config;
    private static $_context;
    private static $_bindingMessage;


    public function __construct()
    {
        $cache = new Cache();

        if (Controller::$_config == null) {
            Controller::$_config = new Config();
        }

        $discoveryService = new DiscoveryService(new RestClient(), $cache);
        $authentication = new AuthenticationService();
        $identity = new IdentityService(new RestClient());
        $jwks = new JWKeysetService(new RestClient(), $discoveryService->getCache());


//        Controller::$_xRedirect = Controller::$_config->isXredirect();
        Controller::$_includeReqIp = Controller::$_config->isIncludeRequestIP();
        Controller::$_apiVersion = Controller::$_config->getApiVersion();
        Controller::$_clientName = Controller::$_config->getClientName();
        Controller::$_scopes = Controller::$_config->getScopes();
        Controller::$_context = Controller::$_config->getContext();
        Controller::$_bindingMessage = Controller::$_config->getBindingMessage();

        if (Controller::$_mobileConnect == null) {
            Controller::$_mobileConnect = new MobileConnectWebInterface($discoveryService, $authentication, $identity, $jwks, Controller::$_config->getMcConfig());
        }
    }



    // Route "start_discovery"
    public function StartDiscovery(Request $request) {
        $msisdn = Input::get(Constants::MSISDN);
        $mcc = Input::get(Constants::MCC);
        $mnc = Input::get(Constants::MNC);
        $sourceIp = Input::get(Constants::SOURCE_IP);

        return $this->AttemptDiscoveryWrapper($msisdn, $mcc, $mnc, $sourceIp, $request);
    }

    private function AttemptDiscoveryWrapper($msisdn, $mcc, $mnc, $sourceIp, $request){
        $databaseHelper = new DatabaseHelper();
        $options = new MobileConnectRequestOptions();
        $options->setClientIp($sourceIp);
//TODO: Ask about it ???
//        $options->getDiscoveryOptions()->setXRedirect(Controller::$_xRedirect);
        $response = Controller::$_mobileConnect->AttemptDiscovery($request, $msisdn, $mcc, $mnc, Controller::$_includeReqIp, true, $options);

        if($response->getDiscoveryResponse() == null || ($_SERVER[Constants::REDIRECT_STATUS] != 200 )) {
            if (!empty($response->getUrl())) {
                return redirect($response->getUrl());
            } else {
                $response = Controller::$_mobileConnect->AttemptDiscovery($request, null, null, null, Controller::$_includeReqIp, false, $options);
                if (!empty($response->getUrl())) {
                    return redirect($response->getUrl());
                }
            }

        }

        if($response->getResponseType() == MobileConnectResponseType::StartAuthentication) {
            $this->setCacheByRequest($mcc, $mnc, $sourceIp, $msisdn, $response->getDiscoveryResponse());

            $authResponse = $this->StartAuth($response->getSDKSession(), $response->getDiscoveryResponse()->getResponseData()[Constants::SUB_ID],
                Controller::$_config);
            $databaseHelper->writeDiscoveryResponseToDatabase($authResponse->getState(), $response->getDiscoveryResponse());
            $databaseHelper->writeNonceToDatabase($authResponse->getState(), $authResponse->getNonce());
            return redirect($authResponse->getUrl());

        }

        return HttpUtils::CreateResponse($response);
    }

    private function setCacheByRequest($mcc, $mnc, $ip, $msisdn, $discoveryResponse){
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

    private function StartAuth($sdkSession, $subscriberId, $_config) {
        $_options = McUtils::getMcOptions($_config);
        if ($_config->getScopes() == Scope::AUTHZ) {
            $response = $this->StartAuthorisation($sdkSession, $subscriberId, $_options);
        } else {
            $response = $this->StartAuthentication($sdkSession, $subscriberId, $_options);
        }
        return $response;
    }

    public function StartAuthentication($sdkSession, $subscriberId, $_options) {
            $response = Controller::$_mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $_options);
            return HttpUtils::CreateResponse($response);
    }

    public function StartAuthorisation($sdkSession, $subscriberId, $_options) {
        $response = Controller::$_mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $_options);
        return HttpUtils::CreateResponse($response);
    }

    // Route "user_info"
    public function RequestUserInfo($sdkSession = null, $accessToken = null) {
        $response = Controller::$_mobileConnect->RequestUserInfo($sdkSession, $accessToken, new MobileConnectRequestOptions());
        return HttpUtils::CreateResponse($response);
    }

    // Route "identity"
    public function RequestIdentity($sdkSession = null, $accessToken = null) {
        $response =  Controller::$_mobileConnect->RequestIdentity($sdkSession, $accessToken, new MobileConnectRequestOptions());
        return HttpUtils::CreateResponse($response);
    }

    // Route ""
    public function HandleRedirect(Request $request) {
        $mcc_mnc = Input::get(Constants::MCC_MNC);
        $code = Input::get(Constants::CODE);
        $state = Input::get(Constants::STATE);
        $databaseHelper =  new DatabaseHelper();
        $requestUri = $request->getRequestUri();
        if(!empty($code)){
            $discoveryResponse = $databaseHelper->getDiscoveryResponseFromDatabase($state);
            $nonce = $databaseHelper->getNonceFromDatabase($state);
            $response = Controller::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, $discoveryResponse, $state, $nonce, new MobileConnectRequestOptions());
            $mobileConnectWebResponse = ResponseConverter::Convert($response);

//            if (Controller::$_apiVersion == (DefaultOptions::VERSION_1_1) & !empty($discoveryResponse->getOperatorUrls()->getUserInfoUrl())) {
//                foreach ( Constants::USERINFO_SCOPES as $userInfoScope) {
//                    if (Controller::$_scopes == $userInfoScope) {
//                        $status = Controller::$_mobileConnect->RequestUserInfo($response->getSDKSession(), $discoveryResponse,
//                            $mobileConnectWebResponse->getToken());
//                        return HttpUtils::CreateResponse($status);
//                    }
//                }
//
//            } else if ((Controller::$_apiVersion == (DefaultOptions::VERSION_DI_2_3) || apiVersion == (DefaultOptions::VERSION_2_0)) & !empty($discoveryResponse->getOperatorUrls()->getPremiumInfoUrl())) {
//                foreach (Constants::IDENTITY_SCOPES as $identityScope) {
//                    if (Controller::$_scopes == $identityScope) {
//                        $status = Controller::$_mobileConnect->RequestIdentity($response->getSDKSession(), $discoveryResponse,
//                            $mobileConnectWebResponse->getToken());
//                        return HttpUtils::CreateResponse($status);
//                    }
//                }
//            }

            return HttpUtils::CreateResponse($response);

        } elseif (!empty($mcc_mnc)){
            $response = Controller::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, null, $state, null, new MobileConnectRequestOptions());
            $authResponse = $this->StartAuth($response->getSDKSession(), $response->getDiscoveryResponse()->getResponseData()[Constants::SUB_ID],
                Controller::$_config);
            $databaseHelper->writeDiscoveryResponseToDatabase($authResponse->getState(), $response->getDiscoveryResponse());
            $databaseHelper->writeNonceToDatabase($authResponse->getState(), $authResponse->getNonce());
            return redirect($authResponse->getUrl());
        }
        else{
            $errorCode = Input::get(Constants::ERROR);
            $errorDesc = Input::get(Constants::ERROR_DESCR);
            $databaseHelper->clearDiscoveryCacheByState($state);
            return HttpUtils::CreateResponse(MobileConnectStatus::Error($errorCode, $errorDesc, null));

        }
    }

}
