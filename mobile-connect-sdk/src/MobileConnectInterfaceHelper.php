<?php

/**
 *                          SOFTWARE USE PERMISSION
 *
 *  By downloading and accessing this software and associated documentation
 *  files ("Software") you are granted the unrestricted right to deal in the
 *  Software, including, without limitation the right to use, copy, modify,
 *  publish, sublicense and grant such rights to third parties, subject to the
 *  following conditions:
 *
 *  The following copyright notice and this permission notice shall be included
 *  in all copies, modifications or substantial portions of this Software:
 *  Copyright © 2016 GSM Association.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS," WITHOUT WARRANTY OF ANY KIND, INCLUDING
 *  BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 *  PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 *  IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE. YOU AGREE TO INDEMNIFY AND HOLD HARMLESS THE AUTHORS AND COPYRIGHT
 *  HOLDERS FROM AND AGAINST ANY SUCH LIABILITY.
 */

namespace MCSDK;
use MCSDK\Authentication\AuthenticationOptions;
use MCSDK\Authentication\AuthenticationService;
use MCSDK\Authentication\IAuthenticationService;
use MCSDK\Authentication\IJWKeysetService;
use MCSDK\Authentication\RequestTokenResponse;
use MCSDK\Discovery\DiscoveryResponse;
use MCSDK\Discovery\DiscoveryService;
use MCSDK\Discovery\VersionDetection;
use MCSDK\Exceptions\InvalidScopeException;
use MCSDK\Exceptions\OperationCancellationException;
use MCSDK\Identity\IIdentityService;
use MCSDK\Utils\MobileConnectResponseType;
use MCSDK\Utils\ValidationUtils;

/**
 * Helper function for MobileConnectWebInterface
 */
class MobileConnectInterfaceHelper {
    public static function AttemptDiscovery(DiscoveryService $discovery, $msisdn, $mcc, $mnc,
        MobileConnectConfig $config, MobileConnectRequestOptions $options, $cookies = array()) {

        $response = new DiscoveryResponse();
        try {
            $discoveryOptions = $options->getDiscoveryOptions();
            if (!isset($discoveryOptions)) {
                $discoveryOptions = new DiscoveryOptions();
            }
            $discoveryOptions->setMSISDN($msisdn);
            $discoveryOptions->setIdentifiedMCC($mcc);
            $discoveryOptions->setIdentifiedMNC($mnc);
            $discoveryOptions->setRedirectUrl($config->getRedirectUrl());

            $response = $discovery->StartAutomatedOperatorDiscoveryByPreferences($config, $config->getRedirectUrl(),
                $discoveryOptions, $cookies);
        } catch (\InvalidArgumentException $e) {
            return MobileConnectStatus::Error("invalid_argument", "An argument was found to be invalid during the process.".$e->getMessage(), $e);
        } catch (MCSDK\Exceptions\MobileConnectEndpointHttpException $e) {
            return MobileConnectStatus::Error("http_failure", "An HTTP failure occured while calling the discovery endpoint, the endpoint may be inaccessible", $e);
        } catch (Exception $e) {
            return MobileConnectStatus::Error("unknown_error", "An unknown error occured while calling the Discovery service to obtain operator details", $e);
        }

        return static::generateStatusFromDiscoveryResponse($discovery, $response);
    }

    public static function StartAuthentication(IAuthenticationService $authentication, DiscoveryResponse $discoveryResponse,
        $encryptedMSISDN, $state, $nonce, MobileConnectConfig $config, MobileConnectRequestOptions $options)
    {
        try {
            $clientId = $discoveryResponse->getResponseData()['response']['client_id'];
            $authorizationUrl = $discoveryResponse->getOperatorUrls()->getAuthorizationUrl();
            $authOptions = empty($options) ? new AuthenticationOptions() : $options->getAuthenticationOptions();
            $version = VersionDetection::getCurrentVersion($authOptions->getVersion(), $authOptions->getScope(), $discoveryResponse->getProviderMetadata());
            $response = $authentication->StartAuthentication($clientId, $authorizationUrl, $config->getRedirectUrl(),
                $state, $nonce, $encryptedMSISDN, $version, $authOptions);
        } catch (\InvalidArgumentException $e) {
            return MobileConnectStatus::Error("invalid_argument", "An argument was found to be invalid during the process. " . $e->getMessage(), $e);
        } catch (MCSDK\Exceptions\MobileConnectEndpointHttpException $e) {
            return MobileConnectStatus::Error("http_failure", "An HTTP failure occured while calling the discovery endpoint, the endpoint may be inaccessible", $e);
        } catch (Exception $e) {
            return MobileConnectStatus::Error("unknown_error", "An unknown error occured while calling the Discovery service to obtain operator details", $e);
        } catch (InvalidScopeException $e) {
            return MobileConnectStatus::Error("invalid_scope", $e->getMessage());
        }
        return MobileConnectStatus::Authorization($response->getUrl(), $state, $nonce);
    }

    public static function RequestHeadlessAuthentication(IAuthenticationService $authentication,
        IJWKeysetService $jwks, DiscoveryResponse $discoveryResponse, $encryptedMSISDN, $state, $nonce,
        MobileConnectConfig $config, MobileConnectRequestOptions $options, $cancel = false) {

        if (!static::IsUsableDiscoveryResponse($discoveryResponse)) {
            return MobileConnectStatus::StartDiscovery();
        }

        try {
            $clientId = $discoveryResponse->getResponseData()['response']['client_id'];
            $clientSecret = $discoveryResponse->getResponseData()['response']['client_secret'];
            $authorizationUrl = $discoveryResponse->getOperatorUrls()->getAuthorizationUrl();
            $tokenUrl = $discoveryResponse->getOperatorUrls()->getRequestTokenUrl();
            $issuer = $discoveryResponse->getProviderMetadata()['issuer'];

            $authOptions = empty($options) ? new AuthenticationOptions() : $options->getAuthenticationOptions();
            $authOptions->setClientName($discoveryResponse->getApplicationShortName());
            $version = VersionDetection::getCurrentVersion($authOptions->getVersion(), $authOptions->getScope(), $discoveryResponse->getProviderMetadata());

            $response = $authentication->RequestHeadlessAuthentication($clientId, $clientSecret, $authorizationUrl,
                $tokenUrl, $config->getRedirectUrl(), $state, $nonce, $encryptedMSISDN, $version, $authOptions, $cancel);

            $jwKeySet = $jwks->RetrieveJWKS($discoveryResponse->getOperatorUrls()->getJWKSUrl());

            return static::HandleTokenResponse($authentication, $response, $clientId, $issuer, $nonce, $jwKeySet, $options);

        } catch (OperationCancellationException $e) {
            return MobileConnectStatus::Error("http_failure", "Operation cancelled", $e);
        } catch (\RuntimeException $e) {
            return MobileConnectStatus::Error("http_failure", "An HTTP failure occured while calling headless authentication.", $e);
        } catch (\InvalidArgumentException $e) {
            return MobileConnectStatus::Error("invalid_argument", "An argument was found to be invalid during the process.", $e);
        } catch (MCSDK\Exceptions\MobileConnectEndpointHttpException $e) {
            return MobileConnectStatus::Error("http_failure", "An HTTP failure occured while calling the discovery endpoint, the endpoint may be inaccessible", $e);
        } catch (Exception $e) {
            return MobileConnectStatus::Error("unknown_error", "An unknown error occured while calling the Discovery service to obtain operator details", $e);
        }
    }

    private static function IsUsableDiscoveryResponse(DiscoveryResponse $response) {
        return !empty($response) && !empty($response->getOperatorUrls() && !empty($response->getResponseData() &&
            !empty($response->getResponseData()['response'])));
    }

    public static function HandleTokenResponse(IAuthenticationService $authentication, RequestTokenResponse $response,
        $clientId, $issuer, $expectedNonce, $jwks, $version, MobileConnectRequestOptions $options = null) {
        if (!empty($response->getErrorResponse())) {
            $errorResponse = $response->getErrorResponse();
            if (array_key_exists('error_description', $errorResponse)) {
                return MobileConnectStatus::Error($errorResponse['error'], $errorResponse['error_description']);
            } else {
                return MobileConnectStatus::Error($errorResponse['error'], $errorResponse['description']);
            }
        }
        $maxAge = empty($options) ? null : $options->getMaxAge();

        $response->setValidationResult($authentication->ValidateTokenResponse($response, $clientId, $issuer, $expectedNonce, $jwks, $version, $maxAge));
        return MobileConnectStatus::Complete($response);
    }

    public static function generateStatusFromDiscoveryResponse(DiscoveryService $discovery, DiscoveryResponse $response) {
        if (!$response->isCached() && !empty($response->getErrorResponse()))
        {
            return MobileConnectStatus::Error($response->getErrorResponse()['error'], $response->getErrorResponse()['error_description'], $response);
        }

        $operatorSelectionUrl = $discovery->extractOperatorSelectionUrl($response);
//----------------------
        if (!empty($operatorSelectionUrl)) {
            return static::operatorSelection($operatorSelectionUrl);
        }
        //--------------------
        return MobileConnectStatus::StartAuthorization($response);
    }

    private static function operatorSelection($url) {

        $result = new MobileConnectStatus();
        $result->setUrl($url);
        $result->setResponseType(MobileConnectResponseType::OperatorSelection);

        return $result;
    }

    public static function HandleUrlRedirect(DiscoveryService $discovery, $jwks, $redirectedUrl, $expectedState, $expectedNonce, MobileConnectConfig $config,
        AuthenticationService $authentication = null, DiscoveryResponse $discoveryResponse = null,
        MobileConnectRequestOptions $options = null) {

        $query = parse_url($redirectedUrl, PHP_URL_QUERY);
        parse_str($query, $queryValue);
        if (isset($queryValue['code'])) {
            return static::RequestToken($authentication, $jwks, $discoveryResponse, $redirectedUrl, $expectedState, $expectedNonce, $config, $options);
        } else if(isset($queryValue['mcc_mnc'])) {
            return static::AttemptDiscoveryAfterOperatorSelection($discovery, $redirectedUrl, $config);
        }
        $errorCode = "invalid_request";
        if (isset($queryValue['error'])) {
            $errorCode = $queryValue['error'];
        }
        $errorDesc = "Unable to parse next step using " . $redirectedUrl;
        if (isset($queryValue['error_description'])) {
            $errorDesc = $queryValue['error_description'];
        }
        else {
            if (isset($queryValue['description'])) {
                $errorDesc = $queryValue['description'];
            }
        }
        return MobileConnectStatus::Error($errorCode, $errorDesc, null);
    }

    public static function RequestToken(IAuthenticationService $authentication, IJWKeysetService $jwks, DiscoveryResponse $discoveryResponse,
        $redirectedUrl, $expectedState, $expectedNonce, MobileConnectConfig $config, MobileConnectRequestOptions $options = null) {
        $response = null;
        $query = parse_url($redirectedUrl, PHP_URL_QUERY);
        parse_str($query, $queryValue);

        if(empty($expectedState)) {
            return MobileConnectStatus::Error("required_arg_missing", "ExpectedState argument was not supplied, this is needed to prevent Cross-Site Request Forgery", null);
        }
        if (empty($expectedNonce)) {
            return MobileConnectStatus::Error("required_arg_missing", "expectedNonce argument was not supplied, this is needed to prevent Replay Attacks", null);
        }
        $actualState = $queryValue['state'];
        if ($expectedState != $actualState) {
            return MobileConnectStatus::Error("invalid_state", "State values do not match, this could suggest an attempted Cross-Site Request Forgery", null);
        }
        try {
            $code = $queryValue['code'];
            $clientId = $config->getClientId();
            if (!empty($discoveryResponse) && !empty($discoveryResponse->getResponseData()) && isset($discoveryResponse->getResponseData()['response']['client_id'])) {
                $clientId = $discoveryResponse->getResponseData()['response']['client_id'];
            }
            $clientSecret = $config->getClientSecret();
            if (!empty($discoveryResponse) && !empty($discoveryResponse->getResponseData()) && isset($discoveryResponse->getResponseData()['response']['client_secret'])) {
                $clientSecret = $discoveryResponse->getResponseData()['response']['client_secret'];
            }
            $requestTokenUrl = null;
            if (!empty($discoveryResponse) && !empty($discoveryResponse->getOperatorUrls()) && !empty($discoveryResponse->getOperatorUrls()->getRequestTokenUrl())) {
                $requestTokenUrl = $discoveryResponse->getOperatorUrls()->getRequestTokenUrl();
            }
            $issuer = $discoveryResponse->getProviderMetadata()["issuer"];

            $response = $authentication->RequestToken($clientId, $clientSecret, $requestTokenUrl, $config->getRedirectUrl(), $code);
            $jwKeySet = $jwks->RetrieveJWKS($discoveryResponse->getOperatorUrls()->getJWKSUrl());

            $authOptions = empty($options) ? new AuthenticationOptions() : $options->getAuthenticationOptions();
            $version = VersionDetection::getCurrentVersion($authOptions->getVersion(), $authOptions->getScope(), $discoveryResponse->getProviderMetadata());

            return static::HandleTokenResponse($authentication, $response, $clientId, $issuer, $expectedNonce, $jwKeySet,
                $version, $options);

        } catch(Exception $ex) {
            return MobileConnectStatus::Error("unknown_error", "A failure occured while requesting a token", $ex);
        }
        return MobileConnectStatus::Complete($response);
    }

    public static function AttemptDiscoveryAfterOperatorSelection(DiscoveryService $discovery, $redirectedUrl, MobileConnectConfig $config) {

        $parsedRedirect = $discovery->parseDiscoveryRedirect($redirectedUrl);
        if (!$parsedRedirect->HasMCCAndMNC()) {
            return MobileConnectStatus::StartDiscovery();
        }
        $response = new DiscoveryResponse();
        try {
            $response = $discovery->CompleteSelectedOperatorDiscoveryByPreferences($config->getRedirectUrl(), $parsedRedirect->getSelectedMCC(), $parsedRedirect->getSelectedMNC(), $config);
            $responseData = $response->getResponseData();
            if (!isset($responseData['subscriber_id'])) {
                $responseData['subscriber_id'] = $parsedRedirect->getEncryptedMSISDN();
                $response->setResponseData($responseData);
            }

        }
        catch (Exception $ex) {
            return MobileConnectStatus::Error("unknown_error", "An unknown error occured while calling the Discovery service to obtain operator details", $ex);
        }
        return static::generateStatusFromDiscoveryResponse($discovery, $response);
    }

    public static function RequestUserInfo(IIdentityService $identity, DiscoveryResponse $discoveryResponse, $accessToken, MobileConnectConfig $_config, MobileConnectRequestOptions $options)
    {
        $userInfoUrl = null;
        if (!empty($discoveryResponse) && !empty($discoveryResponse->getOperatorUrls()) && !empty($discoveryResponse->getOperatorUrls()->getUserInfoUrl())) {
            $userInfoUrl = $discoveryResponse->getOperatorUrls()->getUserInfoUrl();
        }
        if (empty($userInfoUrl))
        {
            return MobileConnectStatus::Error("not_supported", "UserInfo not supported with current operator", null);
        }

        $response = $identity->RequestUserInfo($userInfoUrl, $accessToken);
        return MobileConnectStatus::UserInfo($response);
    }

    public static function RequestIdentity(IIdentityService $identity, DiscoveryResponse $discoveryResponse, $accessToken, MobileConnectConfig $_config, MobileConnectRequestOptions $options)
    {
        $identityUrl = null;
        if (!empty($discoveryResponse) && !empty($discoveryResponse->getOperatorUrls()) && !empty($discoveryResponse->getOperatorUrls()->getPremiumInfoUrl())) {
            $identityUrl = $discoveryResponse->getOperatorUrls()->getPremiumInfoUrl();
        }
        if (empty($identityUrl))
        {
            return MobileConnectStatus::Error("not_supported", "Identity not supported with current operator", null);
        }

        $response = $identity->RequestIdentity($identityUrl, $accessToken);
        return MobileConnectStatus::Identity($response);
    }

    public static function RefreshToken(IAuthenticationService $authentication, $refreshToken, DiscoveryResponse $discoveryResponse, MobileConnectConfig $config) {
        ValidationUtils::validateParameter($discoveryResponse, "discoveryResponse");
        ValidationUtils::validateParameter($refreshToken, "refreshToken");
        if (!static::IsUsableDiscoveryResponse($discoveryResponse)) {
            return MobileConnectStatus::StartDiscovery();
        }
        $refreshTokenUrl = $discoveryResponse->getOperatorUrls()->getRefreshTokenUrl();
        if (empty($refreshTokenUrl)) {
            $refreshTokenUrl = $discoveryResponse->getOperatorUrls()->getRevokeTokenUrl();
        }

        $notSupported = static::IsSupported($refreshTokenUrl, "Refresh", $discoveryResponse->getProviderMetadata()["issuer"]);
        if (!empty($notSupported)) {
            return $notSupported;
        }

        $clientId = $discoveryResponse->getResponseData()['response']['client_id'];
        $clientSecret = $discoveryResponse->getResponseData()['response']['client_secret'];

        try {
            $response = $authentication->RefreshToken($clientId, $clientSecret, $refreshTokenUrl, $refreshToken);
            if (!empty($response->getErrorResponse())) {
                return MobileConnectStatus::Error($response->getErrorResponse()['error'], $response->getErrorResponse()['error_description']);
            } else {
                return MobileConnectStatus::Complete($response);
            }
        } catch(\Exception $e) {
            return MobileConnectStatus::Error("unknown_error", "Refresh token error", $e);
        }
    }

    public static function RevokeToken(IAuthenticationService $authentication, $token, $tokenTypeHint, DiscoveryResponse $discoveryResponse, MobileConnectConfig $config) {
        ValidationUtils::validateParameter($discoveryResponse, "discoveryResponse");
        ValidationUtils::validateParameter($token, "token");

        $revokeTokenUrl = $discoveryResponse->getOperatorUrls()->getRevokeTokenUrl();
        $clientId = $discoveryResponse->getResponseData()['response']['client_id'];
        $clientSecret = $discoveryResponse->getResponseData()['response']['client_secret'];

        $notSupported = static::IsSupported($revokeTokenUrl, "Revoke", $discoveryResponse->getProviderMetadata()["issuer"]);
        if (!empty($notSupported)) {
            return $notSupported;
        }

        try {
            $response = $authentication->RevokeToken($clientId, $clientSecret, $revokeTokenUrl, $token, $tokenTypeHint);
            return MobileConnectStatus::TokenRevoked($response);
        } catch (\Exception $e) {
            return MobileConnectStatus::Error("unknown_error", "Revoke token error", $e);
        }
    }

    private static function isSupported($serviceUrl, $service, $issuer) {
        if (empty($serviceUrl)) {
            return MobileConnectStatus::Error("not_supported", $service . " not supported with current operator", null);
        }
        return null;
    }
}
