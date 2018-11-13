<?php

namespace App\Http\Controllers;
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input;
use MCSDK\Discovery\OperatorUrls;
use MCSDK\MobileConnectConfig;
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
    private static $_operatorUrls;
    private static $_databaseHelper;
    private static $_config;
    private static $_options;

    public function __construct()
    {

        $string = file_get_contents(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "withoutDiscoveryData.json");
        $json = json_decode($string, true);

        if (WdController::$_databaseHelper == null) {
            WdController::$_databaseHelper = new DatabaseHelper();
        }

        if (WdController::$_config == null) {
            WdController::$_config = new MobileConnectConfig();
            WdController::$_config->setClientId($json["clientID"]);
            WdController::$_config->setClientSecret($json["clientSecret"]);
            WdController::$_config->setRedirectUrl($json["redirectURL"]);
        }

        if (WdController::$_mobileConnect == null) {
            WdController::$_mobileConnect = MobileConnectInterfaceFactory::buildMobileConnectWebInterfaceWithConfig(WdController::$_config);
        }

        if (WdController::$_operatorUrls == null) {
            WdController::$_operatorUrls = new OperatorUrls();
            WdController::$_operatorUrls->setAuthorizationUrl($json["authURL"]);
            WdController::$_operatorUrls->setRequestTokenUrl($json["tokenURL"]);
            WdController::$_operatorUrls->setUserInfoUrl($json["userInfoURl"]);
            WdController::$_operatorUrls->setPremiumInfoUrl($json["premiumInfoURl"]);
            WdController::$_operatorUrls->setProviderMetadataUrl($json["metadataURl"]);
        }

        if (WdController::$_options == null) {
            WdController::$_options = new MobileConnectRequestOptions();
            WdController::$_options->getAuthenticationOptions()->setVersion($json["apiVersion"]);
            WdController::$_options->setScope($json["scopes"]);
            WdController::$_options->setContext($json["context"]);
            WdController::$_options->setBindingMessage($json["binding_message"]);
            WdController::$_options->setClientName($json["clientName"]);
        }
    }


    // Route "start_authentication_wd" and "start_authorization_wd"
    public function StartAuthenticationWithoutDiscovery(Request $request) {
        $msisdn = Input::get("msisdn");
        if (!empty($msisdn)) {
            $loginHint = sprintf("%s:%s", "MSISDN", $msisdn);
            WdController::$_options->setLoginHint($loginHint);
        }
        $discoveryResponse = WdController::$_mobileConnect->makeDiscoveryWithoutCall(WdController::$_config->getClientId(), WdController::$_config->getClientSecret(),
            WdController::$_operatorUrls, WdController::$_options->getClientName());

        $state = WdController::$_mobileConnect->generateUniqueString();
        $nonce = WdController::$_mobileConnect->generateUniqueString();
        WdController::$_databaseHelper->writeDiscoveryResponseToDatabase($state, $discoveryResponse);
        WdController::$_databaseHelper->writeNonceToDatabase($state, $nonce);

        $response = WdController::$_mobileConnect->Authentication($discoveryResponse, null, $state, $nonce, WdController::$_options);
        return redirect($response->getUrl());
    }

    // Route ""
    public function HandleRedirect(Request $request) {
        $code = Input::get("code");
        $state = Input::get("state");
        $errorCode = Input::get("error");
        $errorDesc = Input::get("error_description");
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
        $state = Input::get("state");
        $accessToken = Input::get("accessToken");
        $discoveryResponse = WdController::$_databaseHelper->getDiscoveryResponseFromDatabase($state);
        $response = WdController::$_mobileConnect->RequestUserInfoByDiscoveryResponse($discoveryResponse, $accessToken, new MobileConnectRequestOptions());
        return $this->CreateResponse($response);
    }

    // Route "identity_wd"
    public function RequestIdentity(Request $request) {
        $state = Input::get("state");
        $accessToken = Input::get("accessToken");
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
