<?php

namespace App\Http\Controllers;
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

use App\Http\Config\Config;
use App\Http\Constants;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use MCSDK\Authentication\AuthenticationService;
use MCSDK\Authentication\JWKeysetService;
use MCSDK\Cache\Cache;
use MCSDK\Discovery\DiscoveryResponse;
use MCSDK\Discovery\DiscoveryService;
use MCSDK\Identity\IdentityService;
use MCSDK\MobileConnectRequestOptions;
use MCSDK\MobileConnectStatus;
use MCSDK\MobileConnectWebInterface;
use MCSDK\Utils\MobileConnectResponseType;
use MCSDK\Utils\RestClient;


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

//        Controller::$_xRedirect = $json["xRedirect"];
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
// Ask about it ???
//        $options->getDiscoveryOptions()->setXRedirect(Controller::$_xRedirect);
        $response = Controller::$_mobileConnect->AttemptDiscovery($request, $msisdn, $mcc, $mnc, Controller::$_includeReqIp, true, $options);

        if($response->getDiscoveryResponse() == null || ($_SERVER['REDIRECT_STATUS'] != 200 )) {
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

            $authResponse = $this->StartAuthentication($response->getSDKSession(), $response->getDiscoveryResponse()->getResponseData()['subscriber_id'],
                Controller::$_scopes, Controller::$_clientName);
            $databaseHelper->writeDiscoveryResponseToDatabase($authResponse->getState(), $response->getDiscoveryResponse());
            $databaseHelper->writeNonceToDatabase($authResponse->getState(), $authResponse->getNonce());
            return redirect($authResponse->getUrl());

        }

        return WdController::CreateResponse($response);
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


    // Route "start_authentication"
    public function StartAuthentication($sdkSession, $subscriberId) {
            $response = Controller::$_mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, Controller::getMcOptions());
            return WdController::CreateResponse($response);
    }

    // Route "start_authorisation"
    public function StartAuthorisation($sdkSession, $subscriberId) {
        $response = Controller::$_mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, Controller::getMcOptions());
        return WdController::CreateResponse($response);
    }

    // Route "user_info"
    public function RequestUserInfo($sdkSession = null, $accessToken = null) {
        $response = Controller::$_mobileConnect->RequestUserInfo($sdkSession, $accessToken, new MobileConnectRequestOptions());
        return WdController::CreateResponse($response);
    }

    // Route "identity"
    public function RequestIdentity($sdkSession = null, $accessToken = null) {
        $response =  Controller::$_mobileConnect->RequestIdentity($sdkSession, $accessToken, new MobileConnectRequestOptions());
        return WdController::CreateResponse($response);
    }

    // Route ""
    public function HandleRedirect(Request $request) {
        $mcc_mnc = Input::get("mcc_mnc");
        $code = Input::get("code");
        $state = Input::get("state");
        $databaseHelper =  new DatabaseHelper();
        $requestUri = $request->getRequestUri();
        if(!empty($code)){
            $discoveryResponse = $databaseHelper->getDiscoveryResponseFromDatabase($state);
            $nonce = $databaseHelper->getNonceFromDatabase($state);
            $response = Controller::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, $discoveryResponse, $state, $nonce, new MobileConnectRequestOptions());
            return WdController::CreateResponse($response);
        } elseif (!empty($mcc_mnc)){
            $response = Controller::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, null, $state, null, new MobileConnectRequestOptions());
            $data = explode("_",$mcc_mnc);
            $mcc = $data[0];
            $mnc = $data[1];
            $authResponse = $this->StartAuthentication($response->getSDKSession(), $response->getDiscoveryResponse()->getResponseData()['subscriber_id'],
                Controller::$_scopes, Controller::$_clientName);
            $databaseHelper->writeDiscoveryResponseToDatabase($authResponse->getState(), $response->getDiscoveryResponse());
            $databaseHelper->writeNonceToDatabase($authResponse->getState(), $authResponse->getNonce());
            return redirect($authResponse->getUrl());
        }
        else{
            $errorCode = Input::get("error");
            $errorDesc = Input::get("error_description");
            $databaseHelper->clearDiscoveryCacheByState($state);
            return WdController::CreateResponse(MobileConnectStatus::Error($errorCode, $errorDesc, null));

        }
    }

    private static function getMcOptions()    {
        $_options = new MobileConnectRequestOptions();
        $_options->getAuthenticationOptions()->setVersion(Controller::$_apiVersion);
        $_options->setScope(Controller::$_scopes);
        $_options->setContext((Controller::$_apiVersion == Constants::VERSION_2_0 || Controller::$_apiVersion == Constants::VERSION_DI_2_3) ? Controller::$_context : null);
        $_options->setBindingMessage((Controller::$_apiVersion == Constants::VERSION_2_0 || Controller::$_apiVersion == Constants::VERSION_DI_2_3) ? Controller::$_bindingMessage : null);
        $_options->setClientName(Controller::$_clientName);
        return $_options;
    }
}
