<?php

namespace App\Http\Controllers;
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

use App\Http\Claims\KYCClaimsParameter;
use App\Http\Config\Config;
use App\Http\Config\JsonFromFile;
use App\Http\ConfigUtils;
use App\Http\Constants\Constants;
use App\Http\DatabaseHelper;
use App\Http\EndpointUtils;
use App\Http\HttpUtils;
use App\Http\McUtils;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use MCSDK\Constants\Scope;
use MCSDK\MobileConnectInterfaceFactory;
use MCSDK\MobileConnectRequestOptions;
use MCSDK\MobileConnectStatus;
use MCSDK\MobileConnectWebInterface;
use MCSDK\Utils\MobileConnectResponseType;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** @var MobileConnectWebInterface */
    private static $_mobileConnect;
    private static $_operatorUrls;
    private static $_apiVersion;
//    private static $_xRedirect = "APP";
    private static $_includeReqIp;
    private static $_scopes;
    private static $_clientName;
    private static $_config;
    private static $_context;
    private static $_bindingMessage;


    public function __construct()
    {
        if (Controller::$_config == null) {
            Controller::$_config = new Config();
        }
//        Controller::$_xRedirect = Controller::$_config->isXredirect();
        Controller::$_includeReqIp = Controller::$_config->isIncludeRequestIP();
        Controller::$_apiVersion = Controller::$_config->getApiVersion();
        Controller::$_clientName = Controller::$_config->getClientName();
        Controller::$_scopes = Controller::$_config->getScopes();
        Controller::$_context = Controller::$_config->getContext();
        Controller::$_bindingMessage = Controller::$_config->getBindingMessage();

        if (Controller::$_mobileConnect == null) {
            Controller::$_mobileConnect = MobileConnectInterfaceFactory::buildMobileConnectWebInterfaceWithConfig(Controller::$_config->getMcConfig());
        }
    }

    // Route "start_discovery"
    public function startDiscovery(Request $request) {
        $msisdn = Input::get(Constants::MSISDN);
        $mcc = Input::get(Constants::MCC);
        $mnc = Input::get(Constants::MNC);
        $sourceIp = Input::get(Constants::SOURCE_IP);
        return $this->AttemptDiscoveryWrapper($msisdn, $mcc, $mnc, $sourceIp, $request);
    }

    private function attemptDiscoveryWrapper($msisdn, $mcc, $mnc, $sourceIp, $request)
    {
        $databaseHelper = new DatabaseHelper();
        $options = new MobileConnectRequestOptions();
        $options->setClientIp($sourceIp);

//TODO: Ask about it ???
//        $options->getDiscoveryOptions()->setXRedirect(Controller::$_xRedirect);
        $response = Controller::$_mobileConnect->AttemptDiscovery($request, $msisdn, $mcc, $mnc, Controller::$_includeReqIp, true, $options);

        if ($response->getDiscoveryResponse() == null || ($_SERVER[Constants::REDIRECT_STATUS] != Response::HTTP_OK)) {
            if (empty($response->getUrl())) {
                $response = Controller::$_mobileConnect->AttemptDiscovery($request, null, null, null, Controller::$_includeReqIp, false, $options);
            }
            if (!empty($response->getUrl())) {
                return redirect($response->getUrl());
            }
        }

        if ($response->getResponseType() == MobileConnectResponseType::StartAuthentication) {
            McUtils::setCacheByRequest($mcc, $mnc, $sourceIp, $msisdn, $response->getDiscoveryResponse());
            $authResponse = $this->StartAuth($response->getSDKSession(), $response->getDiscoveryResponse()->getResponseData()[Constants::SUB_ID], Controller::$_config);
            if (McUtils::isErrorInResponse($authResponse)) {
                return HttpUtils::createResponse($authResponse);
            } else {
                $databaseHelper->writeDiscoveryResponseToDatabase($authResponse->getState(), $response->getDiscoveryResponse());
                $databaseHelper->writeNonceToDatabase($authResponse->getState(), $authResponse->getNonce());
                return redirect($authResponse->getUrl());
            }
        }
        return HttpUtils::createResponse($response);
    }

    // Route ""
    public function handleRedirect(Request $request) {
        $mcc_mnc = Input::get(Constants::MCC_MNC);
        $code = Input::get(Constants::CODE);
        $state = Input::get(Constants::STATE);
        $databaseHelper =  new DatabaseHelper();
        $requestUri = $request->getRequestUri();
        if(!empty($code)){
            $discoveryResponse = $databaseHelper->getDiscoveryResponseFromDatabase($state);
            $nonce = $databaseHelper->getNonceFromDatabase($state);
            $authStatus = Controller::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, $discoveryResponse, $state, $nonce, new MobileConnectRequestOptions());
            $endPointStatus = EndpointUtils::startEndpointRequest(Controller::$_mobileConnect, Controller::$_config, $discoveryResponse, $authStatus);
            return !empty($endPointStatus) ? HttpUtils::createResponse($endPointStatus): HttpUtils::createResponse($authStatus);

        } elseif (!empty($mcc_mnc)){
            $response = Controller::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, null, $state, null, new MobileConnectRequestOptions());
            $authResponse = $this->StartAuth($response->getSDKSession(), $response->getDiscoveryResponse()->getResponseData()[Constants::SUB_ID],
                Controller::$_config);
            if (McUtils::isErrorInResponse($authResponse)) {
                return HttpUtils::createResponse($authResponse);
            } else {
                $databaseHelper->writeDiscoveryResponseToDatabase($authResponse->getState(), $response->getDiscoveryResponse());
                $databaseHelper->writeNonceToDatabase($authResponse->getState(), $authResponse->getNonce());
                return redirect($authResponse->getUrl());
            }
        }
        else{
            $errorCode = Input::get(Constants::ERROR);
            $errorDesc = Input::get(Constants::ERROR_DESCR);
            $databaseHelper->clearDiscoveryCacheByState($state);
            return HttpUtils::createResponse(MobileConnectStatus::Error($errorCode, $errorDesc, null));
        }
    }

    private function startAuth($sdkSession, $subscriberId, $_config) {
        $_options = McUtils::getMcOptions($_config);

        if (strpos($_config->getScopes(), Scope::KYC) !== false) {
            $status = $this->StartKYC($sdkSession, $subscriberId, $_options);
        } else if (strpos($_config->getScopes(), Scope::AUTHZ) !== false) {
            $status = $this->StartAuthorisation($sdkSession, $subscriberId, $_options);
        } else {
            $status = $this->StartAuthentication($sdkSession, $subscriberId, $_options);
        }
        return $status;
    }

    private function startAuthentication($sdkSession, $subscriberId, $options) {
        $status = Controller::$_mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
        return $status;
    }

    private function startAuthorisation($sdkSession, $subscriberId, $options) {
        $status = Controller::$_mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
        return $status;
    }

    private function startKYC($sdkSession, $subscriberId, $options) {
        $kycClaims = new KYCClaimsParameter();
        $kycClaims->setName(Controller::$_config->getName())
            ->setAddress(Controller::$_config->getAddress());
        $options->setClaims($kycClaims);
        $status = Controller::$_mobileConnect->StartAuthentication($sdkSession, $subscriberId, null, null, $options);
        return $status;
    }

    // Route "sector_identifier_uri"
    public function getSectorIdentifierUri()  {
        return ConfigUtils::getStringFromFile(Constants::SECTOR_IDENTIFIER_PATH);
    }

}
