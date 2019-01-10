<?php

namespace App\Http\Controllers;
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

use App\Http\Auth\AuthRunner;
use App\Http\Auth\AuthWithDiscovery;
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
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use MCSDK\Constants\Parameters;
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
    private static $_databaseHelper;
    private static $_operatorUrls;
    private static $_includeReqIp;
    private static $_config;

    public function __construct() {
        if (Controller::$_config == null) {
            Controller::$_config = new Config();
        }
        if (Controller::$_databaseHelper == null) {
            Controller::$_databaseHelper = new DatabaseHelper();
        }
        Controller::$_includeReqIp = Controller::$_config->isIncludeRequestIP();

        if (Controller::$_mobileConnect == null) {
            Controller::$_mobileConnect = MobileConnectInterfaceFactory::buildMobileConnectWebInterfaceWithConfig(Controller::$_config->getMcConfig());
        }
    }

    // Route "start_discovery"
    public function startDiscovery(Request $request) {
        $msisdn = Input::get(strtolower(Parameters::MSISDN));
        $mcc = Input::get(Parameters::MCC);
        $mnc = Input::get(Parameters::MNC);
        $sourceIp = Input::get(Parameters::SOURCE_IP);
        return $this->attemptDiscoveryWrapper($msisdn, $mcc, $mnc, $sourceIp, $request);
    }

    private function attemptDiscoveryWrapper($msisdn, $mcc, $mnc, $sourceIp, $request) {
        $response = Controller::$_mobileConnect->AttemptDiscovery($request, $msisdn, $mcc, $mnc, $sourceIp, Controller::$_includeReqIp, true, new MobileConnectRequestOptions());
        if(empty($response->getDiscoveryResponse())){
            Controller::$_mobileConnect = MobileConnectInterfaceFactory::buildMobileConnectWebInterfaceWithConfig(Controller::$_config->getMcConfig());
            $response = Controller::$_mobileConnect->AttemptDiscovery($request, null, null, null, null, false, false, new MobileConnectRequestOptions());
        }
            if (!empty($response->getUrl())) {
                return redirect($response->getUrl());
            }

        if ($response->getResponseType() == MobileConnectResponseType::StartAuthentication) {
            McUtils::setCacheByRequest($mcc, $mnc, $sourceIp, $msisdn, $response->getDiscoveryResponse());
            return  AuthWithDiscovery::startAuth(Controller::$_mobileConnect, $response, Controller::$_config);
        }
        return HttpUtils::createResponse($response);
    }

    // Route ""
    public function handleRedirect(Request $request) {
        $mcc_mnc = Input::get(Parameters::MCC_MNC);
        $code = Input::get(Parameters::CODE);
        $state = Input::get(Parameters::STATE);
        $requestUri = $request->getRequestUri();
        if(!empty($code)){
            $discoveryResponse = Controller::$_databaseHelper->getDiscoveryResponseFromDatabase($state);
            $nonce = Controller::$_databaseHelper->getNonceFromDatabase($state);
            $authStatus = Controller::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, $discoveryResponse, $state, $nonce, new MobileConnectRequestOptions());
            $endPointStatus = EndpointUtils::startEndpointRequest(Controller::$_mobileConnect, Controller::$_config, $discoveryResponse, $authStatus);
            Controller::$_databaseHelper->clearDiscoveryCacheByState($state);
            return HttpUtils::createResponse(!empty($endPointStatus) ? $endPointStatus:$authStatus);

        } elseif (!empty($mcc_mnc)){
            $response = Controller::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, null, $state, null, new MobileConnectRequestOptions());
            return  AuthWithDiscovery::startAuth(Controller::$_mobileConnect, $response, Controller::$_config);
        }
        else{
            $errorCode = Input::get(Parameters::ERROR);
            $errorDesc = Input::get(Parameters::ERROR_DESCRIPTION);
            Controller::$_databaseHelper->clearDiscoveryCacheByState($state);
            return HttpUtils::createResponse(MobileConnectStatus::Error($errorCode, $errorDesc, null));
        }
    }

    // Route "sector_identifier_uri"
    public function getSectorIdentifierUri()  {
        return ConfigUtils::getStringFromFile(Constants::SECTOR_IDENTIFIER_PATH);
    }

}
