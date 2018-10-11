<?php

namespace App\Http\Controllers;
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Input;
use MCSDK\Authentication\FakeDiscoveryOptions;
use MCSDK\Discovery\DiscoveryResponse;
use MCSDK\Discovery\OperatorUrls;
use MCSDK\MobileConnectInterfaceFactory;
use MCSDK\MobileConnectWebInterface;
use MCSDK\MobileConnectRequestOptions;
use MCSDK\Discovery\DiscoveryService;
use MCSDK\Discovery\IDiscoveryService;
use MCSDK\MobileConnectConfig;
use MCSDK\Utils\MobileConnectResponseType;
use MCSDK\Utils\RestResponse;
use MCSDK\Web\ResponseConverter;
use MCSDK\MobileConnectStatus;
use MCSDK\Utils\JsonUtils;
use MCSDK\Authentication\AuthenticationService;
use MCSDK\Identity\IIdentityService;
use MCSDK\Identity\IdentityService;
use MCSDK\Cache\Cache;
use MCSDK\Utils\RestClient;
use MCSDK\Authentication\JWKeysetService;
use Symfony\Component\VarDumper\Cloner\Data;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** @var MobileConnectWebInterface */
    private static $_mobileConnect;
    private static $_operatorUrls;
    private static $_subId;
    private static $_apiVersion;
    private static $_xRedirect = "APP";
    private static $_includeReqIp;
    private static $_scopes;
    private static $_clientName;
    private static $json;


    public function __construct() {
        $cache = new Cache();

        $discoveryService = new DiscoveryService(new RestClient(), $cache);
        $authentication = new AuthenticationService();
        $identity = new IdentityService(new RestClient());
        $jwks = new JWKeysetService(new RestClient(), $discoveryService->getCache());
        $config = new MobileConnectConfig();
        $string = file_get_contents(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."data.json");
        $json = json_decode($string, true);

        $config->setClientId($json["clientID"]);
        $config->setClientSecret($json["clientSecret"]);
        $config->setDiscoveryUrl($json["discoveryURL"]);
        $config->setRedirectUrl($json["redirectURL"]);
        Controller::$_xRedirect = $json["xRedirect"];
        Controller::$_apiVersion = $json["apiVersion"];
        Controller::$_clientName = $json["clientName"];
        Controller::$_scopes = $json["scopes"];
        if(Controller::$_mobileConnect == null) {
            Controller::$_mobileConnect = new MobileConnectWebInterface($discoveryService, $authentication, $identity, $jwks, $config);
        }

    }

    // Route "start_discovery"
    public function StartDiscovery(Request $request) {
        $msisdn = Input::get("msisdn");
        $mcc = Input::get("mcc");
        $mnc = Input::get("mnc");
        $sourceIp = $request->header("X-Source-IP");
        return $this->AttemptDiscoveryWrapper($request, $msisdn, $mcc, $mnc, $sourceIp);
    }

    private function AttemptDiscoveryWrapper($request, $msisdn, $mcc, $mnc, $sourceIp){
        $databaseHelper = new DatabaseHelper();
        $options = new MobileConnectRequestOptions();
        if($sourceIp!=""){
            $options->setClientIp($sourceIp);
        }
        $options->getDiscoveryOptions()->setXRedirect(Controller::$_xRedirect);
        $response = Controller::$_mobileConnect->AttemptDiscovery($request, $msisdn, $mcc, $mnc, Controller::$_includeReqIp, true, $options);
        if(empty($response->getDiscoveryResponse())){
            $response = Controller::$_mobileConnect->AttemptDiscovery($request, null, null, null, Controller::$_includeReqIp, true, $options);
        }
        if($response->getResponseType() == MobileConnectResponseType::StartAuthentication) {
            if(empty($mcc)&&empty($mnc)) {
                $this->setCacheByRequest($mcc, $mnc, $sourceIp, $msisdn, $response->getDiscoveryResponse());
            }
            $authResponse = $this->StartAuthentication($response->getSDKSession(), $response->getDiscoveryResponse()->getResponseData()['subscriber_id'],
                Controller::$_scopes, Controller::$_clientName);
            $databaseHelper->writeDiscoveryResponseToDatabase($authResponse->getState(), $response->getDiscoveryResponse());
            $databaseHelper->writeNonceToDatabase($authResponse->getState(), $authResponse->getNonce());
            return redirect($authResponse->getUrl());
        }
        if($response->getResponseType() == MobileConnectResponseType::OperatorSelection)
            return redirect($response->getUrl());
        return $this->CreateResponse($response);
    }

    private function setCacheByRequest($mcc, $mnc, $ip, $msisdn, $discoveryResponse){
        if($discoveryResponse instanceof DiscoveryResponse) {
            $databaseHelper = new DatabaseHelper();
            if (!empty($msisdn)) {
                $databaseHelper->setCachedDiscoveryResponseByMsisdn($msisdn, $discoveryResponse);
            }
            if (!empty($mcc) && !empty($mnc)) {
                $databaseHelper->setCachedDiscoveryResponseByMccMnc($msisdn, $mcc, $mnc, $discoveryResponse);
            }
            if (!empty($ip)) {
                $databaseHelper->setCachedDiscoveryResponseByIp($ip, $discoveryResponse);
            }
        }
    }


    // Route "start_authentication"
    public function StartAuthentication($sdkSession, $subscriberId , $scope, $clientName ) {
        if(Controller::$_apiVersion != 'mc_v1.1') {
            $options = new MobileConnectRequestOptions();
            $options->setScope($scope);
            $options->setContext("demo");
            $options->setBindingMessage("demo auth");
            $options->setClientName($clientName);
            $response = Controller::$_mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
            return $this->CreateResponse($response);
        }
        else{
            $options = new MobileConnectRequestOptions();
            $options->setScope("openid");
            $response = Controller::$_mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
            return $this->CreateResponse($response);
        }
    }

    // Route "user_info"
    public function RequestUserInfo($sdkSession = null, $accessToken = null) {
        $response = Controller::$_mobileConnect->RequestUserInfo($sdkSession, $accessToken, new MobileConnectRequestOptions());
        return $this->CreateResponse($response);
    }

    // Route "identity"
    public function RequestIdentity($sdkSession = null, $accessToken = null) {
        $response =  Controller::$_mobileConnect->RequestIdentity($sdkSession, $accessToken, new MobileConnectRequestOptions());
        return $this->CreateResponse($response);
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
            return $this->CreateResponse($response);
        }
        if(!empty($mcc_mnc)){
            $response = Controller::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, null, $state, null, new MobileConnectRequestOptions());
            $data = explode("_",$mcc_mnc);
            $mcc = $data[0];
            $mnc = $data[1];
            $this->setCacheByRequest($mcc, $mnc, null, null, $response->getDiscoveryResponse());
            $authResponse = $this->StartAuthentication($response->getSDKSession(), $response->getDiscoveryResponse()->getResponseData()['subscriber_id'],
                Controller::$_scopes, Controller::$_clientName);
            $databaseHelper->writeDiscoveryResponseToDatabase($authResponse->getState(), $response->getDiscoveryResponse());
            $databaseHelper->writeNonceToDatabase($authResponse->getState(), $authResponse->getNonce());
            return redirect($authResponse->getUrl());
        }
        $databaseHelper->clearDiscoveryCache($databaseHelper->getDiscoveryResponseFromDatabase($state));
        return $this->AttemptDiscoveryWrapper($request,null, null, null, null);
    }

    private function CreateResponse(MobileConnectStatus $status)
    {
        if ($status->getState() !== null) return $status;
        else {
            $json = json_decode(JsonUtils::toJson(ResponseConverter::Convert($status)));
            $clear_json = (object)array_filter((array)$json);
            return response()->json($clear_json);
        }

    }

}
