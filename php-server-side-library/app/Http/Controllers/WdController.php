<?php

namespace App\Http\Controllers;
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

use App\Http\Claims\KYCClaimsParameter;
use App\Http\Config\ConfigWd;
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
use MCSDK\Constants\Scope;
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
    public function startAuthenticationWithoutDiscovery(Request $request)
    {
        $msisdn = Input::get(strtolower(Parameters::MSISDN));
        $state = WdController::$_mobileConnect->generateUniqueString();
        $nonce = WdController::$_mobileConnect->generateUniqueString();

        $discoveryResponse = WdController::$_mobileConnect->makeDiscoveryWithoutCall(WdController::$_config->getClientId(), WdController::$_config->getClientSecret(),
            WdController::$_config->getOperatorUrls(), WdController::$_config->getClientName());
        $authResponse = $this->startAuth($discoveryResponse, $state, $nonce, WdController::$_config, $msisdn);
        if (McUtils::isErrorInResponse($authResponse)) {
            return HttpUtils::createResponse($authResponse);
        } else {
            WdController::$_databaseHelper->writeDiscoveryResponseToDatabase($state, $discoveryResponse);
            WdController::$_databaseHelper->writeNonceToDatabase($state, $nonce);
            return redirect($authResponse->getUrl());
        }
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

            if (!empty($endPointStatus)) {
                return HttpUtils::createResponse($endPointStatus);
            } else {
                return HttpUtils::createResponse($authStatus);
            }

        } else {
            return HttpUtils::createResponse(MobileConnectStatus::Error($errorCode, $errorDesc, null));
        }
    }

    private function startAuth($discoveryResponse, $state, $nonce, $config, $msisdn) {
        $options = McUtils::getMcOptions($config);
        if (!empty($msisdn)) {
            $loginHint = sprintf("%s:%s", Parameters::MSISDN, $msisdn);
            $options->setLoginHint($loginHint);
        }
        if (strpos($config->getScopes(), Scope::KYC) !== false) {
            $status = $this->startKYC($discoveryResponse, $state, $nonce, $options);
        } else if (strpos($config->getScopes(), Scope::AUTHZ) !== false) {
            $status = $this->startAuthorisation($discoveryResponse, $state, $nonce, $options);
        } else {
            $status = $this->startAuthentication($discoveryResponse, $state, $nonce, $options);
        }
        return $status;
    }

    private function startAuthentication($discoveryResponse, $state, $nonce, $options) {
        return WdController::$_mobileConnect->Authentication($discoveryResponse, null, $state, $nonce, $options);
    }

    private function startAuthorisation($discoveryResponse, $state, $nonce, $options) {
        return WdController::$_mobileConnect->Authentication($discoveryResponse, null, $state, $nonce, $options);
    }

    private function startKYC($discoveryResponse, $state, $nonce, $options) {
        $kycClaims = new KYCClaimsParameter();
        $kycClaims->setName(WdController::$_config->getName())
                ->setAddress(WdController::$_config->getAddress());
        $options->setClaims($kycClaims);
        $status = WdController::$_mobileConnect->Authentication($discoveryResponse, null, $state, $nonce, $options);
        return $status;
    }

}
