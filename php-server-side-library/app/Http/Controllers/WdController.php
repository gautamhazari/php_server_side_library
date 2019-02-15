<?php

namespace App\Http\Controllers;
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

use App\Http\Auth\AuthRunner;
use App\Http\Auth\AuthWithoutDiscovery;
use App\Http\Config\ConfigWd;
use App\Http\Constants\Status;
use App\Http\DatabaseHelper;
use App\Http\EndpointUtils;
use App\Http\HttpUtils;
use App\Http\McUtils;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use MCSDK\Constants\Parameters;
use MCSDK\MobileConnectInterfaceFactory;
use MCSDK\MobileConnectRequestOptions;
use MCSDK\MobileConnectStatus;
use MCSDK\MobileConnectWebInterface;


class WdController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** @var MobileConnectWebInterface */
    private static $_mobileConnect;
    private static $_databaseHelper;
    private static $_config;

    public function __construct()
    {
        if (WdController::$_config == null) {
            WdController::$_config = new ConfigWd();
        }
        if (WdController::$_databaseHelper == null) {
            WdController::$_databaseHelper = new DatabaseHelper();
        }
        if (WdController::$_mobileConnect == null) {
            WdController::$_mobileConnect = MobileConnectInterfaceFactory::buildMobileConnectWebInterfaceWithConfig(WdController::$_config->getMcConfig());
        }
    }

    // Route "start_discovery_manually"
    public function startAuthenticationWithoutDiscovery(Request $request)
    {
        $msisdn = Input::get(strtolower(Parameters::MSISDN));
        $discoveryResponse = WdController::$_mobileConnect->makeDiscoveryWithoutCall(WdController::$_config->getClientId(), WdController::$_config->getClientSecret(),
            WdController::$_config->getOperatorUrls(), WdController::$_config->getClientName());
        return  AuthWithoutDiscovery::startAuth(WdController::$_mobileConnect, $discoveryResponse, WdController::$_config, $msisdn);
    }

    // Route '/callback_wd'
    public function handleRedirect(Request $request)
    {
        $code = Input::get(Parameters::CODE);
        $state = Input::get(Parameters::STATE);
        $errorCode = Input::get(Parameters::ERROR);
        $errorDesc = Input::get(Parameters::ERROR_DESCRIPTION);
        $requestUri = $request->getRequestUri();
        if (!empty($code)) {
            $discoveryResponse = WdController::$_databaseHelper->getDiscoveryResponseFromDatabase($state);
            $nonce = WdController::$_databaseHelper->getNonceFromDatabase($state);
            $authStatus = WdController::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, $discoveryResponse, $state, $nonce, new MobileConnectRequestOptions());
            $endPointStatus = EndpointUtils::startEndpointRequest(WdController::$_mobileConnect, WdController::$_config, $discoveryResponse, $authStatus);
            $isPremiumInfo = !empty($endPointStatus);
            return HttpUtils::redirectToView($isPremiumInfo ? $endPointStatus : $authStatus, $isPremiumInfo ? Status::PREMIUMINFO: Status::TOKEN);
        } else {
            return HttpUtils::redirectToView(MobileConnectStatus::Error($errorCode, $errorDesc, null), McUtils::getAuthName(WdController::$_config->getScopes()));
        }
    }

}
