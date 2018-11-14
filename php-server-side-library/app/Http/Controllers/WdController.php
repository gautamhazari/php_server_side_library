<?php

namespace App\Http\Controllers;
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

use App\Http\Config\ConfigWd;
use App\Http\Constants;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use MCSDK\MobileConnectInterfaceFactory;
use MCSDK\MobileConnectRequestOptions;
use MCSDK\MobileConnectStatus;
use MCSDK\MobileConnectWebInterface;
use MCSDK\Utils\JsonUtils;
use MCSDK\Web\ResponseConverter;


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


    // Route "start_authentication_wd" and "start_authorization_wd"
    public function StartAuthenticationWithoutDiscovery(Request $request) {
        $msisdn = Input::get(Constants::MSISDN);
        if (!empty($msisdn)) {
            $loginHint = sprintf("%s:%s", strtoupper (Constants::MSISDN), $msisdn);
            $_options = WdController::$_config->getMcOptions();
            $_options->setLoginHint($loginHint);
        }
        $discoveryResponse = WdController::$_mobileConnect->makeDiscoveryWithoutCall(WdController::$_config->getClientId(), WdController::$_config->getClientSecret(),
            WdController::$_config->getOperatorUrls(), WdController::$_config->getClientName());

        $state = WdController::$_mobileConnect->generateUniqueString();
        $nonce = WdController::$_mobileConnect->generateUniqueString();
        WdController::$_databaseHelper->writeDiscoveryResponseToDatabase($state, $discoveryResponse);
        WdController::$_databaseHelper->writeNonceToDatabase($state, $nonce);

        $response = WdController::$_mobileConnect->Authentication($discoveryResponse, null, $state, $nonce, $_options);
        return redirect($response->getUrl());
    }

    // Route ""
    public function HandleRedirect(Request $request) {
        $code = Input::get(Constants::CODE);
        $state = Input::get(Constants::STATE);
        $errorCode = Input::get(Constants::ERROR);
        $errorDesc = Input::get(Constants::ERROR_DESCR);
        $requestUri = $request->getRequestUri();
        if(!empty($code)){
            $discoveryResponse = WdController::$_databaseHelper->getDiscoveryResponseFromDatabase($state);
            $nonce = WdController::$_databaseHelper->getNonceFromDatabase($state);
            $response = WdController::$_mobileConnect->HandleUrlRedirectWithDiscoveryResponse($requestUri, $discoveryResponse, $state, $nonce, new MobileConnectRequestOptions());
            return $this->CreateResponse($response);
        }
        else{
            return $this->CreateResponse(MobileConnectStatus::Error($errorCode, $errorDesc, null));
        }
    }

    // Route "user_info_wd"
    public function RequestUserInfo(Request $request) {
        $state = Input::get(Constants::STATE);
        $accessToken = Input::get(Constants::ACCESS_TOKEN);
        $discoveryResponse = WdController::$_databaseHelper->getDiscoveryResponseFromDatabase($state);
        $response = WdController::$_mobileConnect->RequestUserInfoByDiscoveryResponse($discoveryResponse, $accessToken, new MobileConnectRequestOptions());
        return $this->CreateResponse($response);
    }

    // Route "identity_wd"
    public function RequestIdentity(Request $request) {
        $state = Input::get(Constants::STATE);
        $accessToken = Input::get(Constants::ACCESS_TOKEN);
        $discoveryResponse = WdController::$_databaseHelper->getDiscoveryResponseFromDatabase($state);
        $response =  WdController::$_mobileConnect->RequestIdentityByDiscoveryResponse($discoveryResponse, $accessToken, new MobileConnectRequestOptions());
        return $this->CreateResponse($response);
    }

    public static function CreateResponse(MobileConnectStatus $status)
    {
        if ($status->getState() !== null) return $status;
        else {
            $json = json_decode(JsonUtils::toJson(ResponseConverter::Convert($status)));
            $clear_json = (object)array_filter((array)$json);
            return response()->json($clear_json);
        }

    }

}
