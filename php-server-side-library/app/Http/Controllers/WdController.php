<?php

namespace App\Http\Controllers;
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

use App\Http\Config\ConfigWd;
use App\Http\Constants;
use App\Http\HttpUtils;
use App\Http\McUtils;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use MCSDK\Constants\DefaultOptions;
use MCSDK\Constants\Scope;
use MCSDK\MobileConnectInterfaceFactory;
use MCSDK\MobileConnectRequestOptions;
use MCSDK\MobileConnectStatus;
use MCSDK\MobileConnectWebInterface;
use MCSDK\Web\ResponseConverter;


class WdController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** @var MobileConnectWebInterface */
    private static $_mobileConnect;
    private static $_databaseHelper;
    private static $_config;
    private static $_apiVersion;
    private static $_scopes;
    private static $_clientName;
    private static $_context;
    private static $_bindingMessage;

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
        WdController::$_apiVersion = WdController::$_config->getApiVersion();
        WdController::$_scopes = WdController::$_config->getScopes();
        WdController::$_clientName = WdController::$_config->getClientName();
        WdController::$_context = WdController::$_config->getContext();
        WdController::$_bindingMessage = WdController::$_config->getBindingMessage();

    }

    // Route "start_discovery_manually"
    public function StartAuthenticationWithoutDiscovery(Request $request)
    {
        $msisdn = Input::get(Constants::MSISDN);
        $state = WdController::$_mobileConnect->generateUniqueString();
        $nonce = WdController::$_mobileConnect->generateUniqueString();

        $discoveryResponse = WdController::$_mobileConnect->makeDiscoveryWithoutCall(WdController::$_config->getClientId(), WdController::$_config->getClientSecret(),
            WdController::$_config->getOperatorUrls(), WdController::$_config->getClientName());

        WdController::$_databaseHelper->writeDiscoveryResponseToDatabase($state, $discoveryResponse);
        WdController::$_databaseHelper->writeNonceToDatabase($state, $nonce);

        $response = $this->StartAuth($discoveryResponse, $state, $nonce, WdController::$_config, $msisdn);

        return redirect($response->getUrl());

    }

    private function StartAuth($discoveryResponse, $state, $nonce, $_config, $msisdn) {
        $_options = McUtils::getMcOptions(WdController::$_config);
        if (!empty($msisdn)) {
            $loginHint = sprintf("%s:%s", strtoupper(Constants::MSISDN), $msisdn);
            $_options->setLoginHint($loginHint);
        }

        if ($_config->getScopes() == Scope::AUTHZ) {
            $response = $this->StartAuthorisation($discoveryResponse, $state, $nonce, $_options);
        } else {
            $response = $this->StartAuthentication($discoveryResponse, $state, $nonce, $_options);
        }
        return $response;
    }

    private function StartAuthentication($discoveryResponse, $state, $nonce, $_options) {
        return WdController::$_mobileConnect->Authentication($discoveryResponse, null, $state, $nonce, $_options);
    }

    private function StartAuthorisation($discoveryResponse, $state, $nonce, $_options) {
        return WdController::$_mobileConnect->Authentication($discoveryResponse, null, $state, $nonce, $_options);
    }

    // Route '/callback_wd'
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

            $mobileConnectWebResponse = ResponseConverter::Convert($response);
            if (WdController::$_apiVersion == (DefaultOptions::VERSION_1_1) & !empty($discoveryResponse->getOperatorUrls()->getUserInfoUrl())) {
                foreach ( Constants::USERINFO_SCOPES as $userInfoScope) {
                    if ( strpos(WdController::$_scopes, $userInfoScope) !== false) {
                        $status = WdController::$_mobileConnect->RequestUserInfoByDiscoveryResponse($discoveryResponse, $mobileConnectWebResponse->getToken()["access_token"], new MobileConnectRequestOptions());
                        return HttpUtils::CreateResponse($status);
                    }
                }

            } else if ((WdController::$_apiVersion == (DefaultOptions::VERSION_DI_2_3) || apiVersion == (DefaultOptions::VERSION_2_0)) & !empty($discoveryResponse->getOperatorUrls()->getPremiumInfoUrl())) {
                foreach (Constants::IDENTITY_SCOPES as $identityScope) {
                    if (strpos(WdController::$_scopes, $identityScope) !== false) {
                        $status = WdController::$_mobileConnect->RequestIdentityByDiscoveryResponse($discoveryResponse, $mobileConnectWebResponse->getToken()["access_token"], new MobileConnectRequestOptions());

                        return HttpUtils::CreateResponse($status);
                    }
                }
            }
            return HttpUtils::CreateResponse($response);
        }
        else{
            return HttpUtils::CreateResponse(MobileConnectStatus::Error($errorCode, $errorDesc, null));
        }
    }

    // Route "user_info_wd"
    public function RequestUserInfo(Request $request) {
        $state = Input::get(Constants::STATE);
        $accessToken = Input::get(Constants::ACCESS_TOKEN);
        $discoveryResponse = WdController::$_databaseHelper->getDiscoveryResponseFromDatabase($state);
        $response = WdController::$_mobileConnect->RequestUserInfoByDiscoveryResponse($discoveryResponse, $accessToken, new MobileConnectRequestOptions());
        return HttpUtils::CreateResponse($response);
    }

    // Route "identity_wd"
    public function RequestIdentity(Request $request) {
        $state = Input::get(Constants::STATE);
        $accessToken = Input::get(Constants::ACCESS_TOKEN);
        $discoveryResponse = WdController::$_databaseHelper->getDiscoveryResponseFromDatabase($state);
        $response =  WdController::$_mobileConnect->RequestIdentityByDiscoveryResponse($discoveryResponse, $accessToken, new MobileConnectRequestOptions());
        return HttpUtils::CreateResponse($response);
    }

}
