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

namespace MCSDK\Authentication;

use App\Http\Constants\Constants;
use MCSDK\Constants\DefaultOptions;
use MCSDK\Constants\GrantTypes;
use MCSDK\Constants\Parameters;
use MCSDK\Constants\Scope;
use MCSDK\Discovery\SupportedVersions;
use MCSDK\Exceptions\MobileConnectEndpointHttpException;
use MCSDK\Exceptions\OperationCancellationException;
use MCSDK\MobileConnectConstants;
use MCSDK\Utils\CurlRestClient;
use MCSDK\Utils\HttpUtils;
use MCSDK\Utils\RestAuthentication;
use MCSDK\Utils\RestClient;
use MCSDK\Utils\Scopes;
use MCSDK\Utils\UriBuilder;
use MCSDK\Utils\ValidationUtils;

/**
 * Concrete implementation of IAuthenticationService
 */
class AuthenticationService implements IAuthenticationService {
    private $_client;
    const REVOKE_TOKEN_SUCCESS = "Revoke token successful";
    const UNSUPPORTED_TOKEN_TYPE_ERROR = "Unsupported token type";

    public function __construct(RestClient $client = null) {
        if (empty($client)) {
            $this->_client = new RestClient();
        } else {
            $this->_client = $client;
        }
    }

    public function StartAuthentication($clientId, $authorizeUrl, $redirectUrl, $state, $nonce,
        $encryptedMSISDN, $version, AuthenticationOptions $options = null) {
        ValidationUtils::validateParameter($clientId, "clientId");
        ValidationUtils::validateParameter($authorizeUrl, "authorizeUrl");
        ValidationUtils::validateParameter($redirectUrl, "redirectUrl");
        ValidationUtils::validateParameter($state, "state");
        ValidationUtils::validateParameter($nonce, "nonce");

        $this->validateKycParams($options);

        if (empty($options)) {
            $options = new AuthenticationOptions();
        }

        if (empty($options->getScope())) {
            $options->setScope("");
        }

        $shouldUseAuthorize = $this->shouldUseAuthorize($options);

        if ($shouldUseAuthorize) {
            ValidationUtils::validateParameter($options->getContext(), "options->getContext()");
            ValidationUtils::validateParameter($options->getClientName(), "options->getClientName()");
        }

        $options->setState($state);
        $options->setNonce($nonce);
        if ($options->getLoginHintToken() === null && $options->getLoginHint() === null) {
            if (!empty($encryptedMSISDN)) {
                $options->setLoginHint(LoginHint::GenerateForEncryptedMSISDN($encryptedMSISDN));
            }
        }
        $options->setRedirectUrl($redirectUrl);
        $options->setClientId($clientId);

// We set scope in config. Please use it if you need to set it in automode.
//        $scope = $this->CoerceAuthenticationScope($options->getScope(), $shouldUseAuthorize, $version, $versions);
//        $options->setScope($scope);

        $build = new UriBuilder($authorizeUrl);
        $build->AddQueryParams($this->getAuthenticationQueryParams($options, $shouldUseAuthorize, $version));

        $response = new StartAuthenticationResponse();
        $response->setUrl($build->getUri());
        return $response;
    }

    private function validateKycParams($options) {
        $kycClaims = $options->getClaims();
        if((strpos($options->getScope(), Scope::KYC_PLAIN) !== false) && ($options->getVersion() == DefaultOptions::VERSION_DI_2_3)) {
            if (!empty($kycClaims->getName())) {
                ValidationUtils::validateParameter($kycClaims->getAddress(), Parameters::ADDRESS);
            }
            else if (!empty($kycClaims->getGivenName())) {
                $params = implode(Constants::SPACE, array(
                    empty($kycClaims->getFamilyName())? Parameters::FAMILY_NAME: null,
                    empty($kycClaims->getHousenoOrHousename())? Parameters::HOUSENO_OR_HOUSENAME: null,
                    empty($kycClaims->getPostalCode())? Parameters::POSTAL_CODE: null,
                    empty($kycClaims->getCountry())? Parameters::COUNTRY: null,
                    empty($kycClaims->getTown())? Parameters::TOWN: null));
                ValidationUtils::validateParameter(null, $params);
            }
            else {
                ValidationUtils::validateParameter(null, Parameters::NAME.Constants::OR.Parameters::GIVEN_NAME );
           }
        }

        if((strpos($options->getScope(), Scope::KYC_HASHED) !== false) && ($options->getVersion() == DefaultOptions::VERSION_DI_2_3)) {
            if (!empty($kycClaims->getNameHashed())) {
                ValidationUtils::validateParameter($kycClaims->getAddressHashed(), Parameters::ADDRESS_HASHED);
            }
            else if (!empty($kycClaims->getGivenNameHashed())) {
                $params = implode(Constants::SPACE, array(
                    empty($kycClaims->getFamilyNameHashed())? Parameters::FAMILY_NAME_HASHED: null,
                    empty($kycClaims->getHousenoOrHousenameHashed())? Parameters::HOUSENO_OR_HOUSENAME_HASHED: null,
                    empty($kycClaims->getPostalCodeHashed())? Parameters::POSTAL_CODE_HASHED: null,
                    empty($kycClaims->getCountryHashed())? Parameters::COUNTRY_HASHED: null,
                    empty($kycClaims->getTownHashed())? Parameters::TOWN_HASHED: null));
                ValidationUtils::validateParameter(null, $params);
            }
            else {
                ValidationUtils::validateParameter(null, Parameters::NAME_HASHED.Constants::OR.Parameters::GIVEN_NAME_HASHED);
            }
        }
    }

    public function RequestToken($clientId, $clientSecret, $requestTokenUrl, $redirectUrl, $code, $isBasicAuth=true) {
        ValidationUtils::validateParameter($clientId, "clientId");
        ValidationUtils::validateParameter($clientSecret, "clientSecret");
        ValidationUtils::validateParameter($requestTokenUrl, "requestTokenUrl");
        ValidationUtils::validateParameter($redirectUrl, "redirectUrl");
        ValidationUtils::validateParameter($code, "code");

        try {
            $formData = array (
                Parameters::AUTHENTICATION_REDIRECT_URI => $redirectUrl,
                Parameters::CODE => $code,
                Parameters::GRANT_TYPE => DefaultOptions::GRANT_TYPE,
            );
            if (!$isBasicAuth) {
                $formData = array_merge($formData, array(
                    Parameters::CLIENT_ID => $clientId,
                    Parameters::CLIENT_SECRET => $clientSecret));
            }
            $authentication = $isBasicAuth ? RestAuthentication::Basic($clientId, $clientSecret) : null;
            $response = $this->_client->post($requestTokenUrl, $authentication, $formData, null, null, null, null, null);

            return new RequestTokenResponse($response);
        } catch (Zend\Http\Exception\RuntimeException $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        } catch (Zend\Http\Client\Exception\RuntimeException $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        } catch (Exception $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        }
    }

    public function ValidateTokenResponse(RequestTokenResponse $tokenResponse, $clientId, $issuer, $nonce, $keyset, $version, $maxAge = null) {
        if (empty($tokenResponse->getResponseData())) {
            return TokenValidationResult::IncompleteTokenResponse;
        }
        $result = TokenValidation::ValidateAccessToken($tokenResponse->getResponseData());
        if ($result != TokenValidationResult::Valid) {
            return $result;
        }
        return TokenValidation::ValidateIdToken($tokenResponse->getResponseData()["id_token"], $clientId, $issuer, $nonce, $maxAge, $keyset, $version);
    }

    private function shouldUseAuthorize(AuthenticationOptions $options) {
        $authnIndex = stripos($options->getScope(), Scope::AUTHN);
        $authnRequested = ($authnIndex !== false);
        if(!$authnRequested && !empty($options->getContext())&& $options->getScope()!= Scope::OPENID)
        {
            return true;
        }
        return false;
    }

    private function encodeValue($object) {
//        return $object !== null? urlencode($object): null;
        return $object !== null? $object: null;
    }

    public function getAuthenticationQueryParams(AuthenticationOptions $options, $useAuthorize, $version) {
        $authParamters = array(
            Parameters::AUTHENTICATION_REDIRECT_URI => $this->encodeValue($options->getRedirectUrl()),
            Parameters::CLIENT_ID => $this->encodeValue($options->getClientId()),
            Parameters::RESPONSE_TYPE => $this->encodeValue(DefaultOptions::AUTHENTICATION_RESPONSE_TYPE),
            Parameters::SCOPE => $this->encodeValue($options->getScope()),
            Parameters::ACR_VALUES => $this->encodeValue($options->getAcrValues()),
            Parameters::STATE => $this->encodeValue($options->getState()),
            Parameters::NONCE => $this->encodeValue($options->getNonce()),
            Parameters::DISPLAY => $this->encodeValue($options->getDisplay()),
            Parameters::PROMPT => $this->encodeValue($this->getPrompt($options->getPrompt(), $version)),
            Parameters::MAX_AGE => $this->encodeValue($options->getMaxAge()),
            Parameters::UI_LOCALES => $this->encodeValue($options->getUiLocales()),
            Parameters::CLAIMS_LOCALES => $this->encodeValue( $options->getClaimsLocales()),
            Parameters::ID_TOKEN_HINT => $this->encodeValue($options->getIdTokenHint()),
            Parameters::DTBS => $this->encodeValue($options->getDtbs()),
            Parameters::CLAIMS => $this->encodeValue($this->getClaimsString($options)),
            Parameters::VERSION => $this->encodeValue($version)
        );
        if ($options->getLoginHintToken() === null) {
            $authParamters[Parameters::LOGIN_HINT] = $this->encodeValue($options->getLoginHint());
        }
        else{
            $authParamters[Parameters::LOGIN_HINT_TOKEN] = $this->encodeValue($options->getLoginHintToken());
        }

        if ($useAuthorize) {
            $authParamters[Parameters::CLIENT_NAME] = $this->encodeValue($options->getClientName());
            $authParamters[Parameters::CONTEXT] = $this->encodeValue($options->getContext());
            $authParamters[Parameters::BINDING_MESSAGE] = $this->encodeValue($options->getBindingMessage());
        }

        return $authParamters;
    }

    private function getPrompt($currentPrompt, $currentVersion) {
        if (empty($currentPrompt)) {
            return null;
        }
        if ($currentVersion != DefaultOptions::VERSION_DI_3_0) {
            if ($currentPrompt == Parameters::CONSENT || $currentPrompt == Parameters::SELECT_ACCOUNT) {
                return $currentPrompt;
            }
        }
        if ($currentPrompt == Parameters::NONE || $currentPrompt == Parameters::LOGIN || $currentPrompt == Parameters::NO_SEAM) {
            return $currentPrompt;
        }
        return null;
    }

    private function getClaimsString($options) {
        if ($options->getClaims() != null)
            return $options->getClaims()->toJson();
    }

    /**
     * Returns a modified scope value based on the version required. Depending on the version the value mc_authn may be added or removed
     * @param string $scopeRequested Request scope value
     * @param SupportedVersions $versions SupportedVersions from ProviderMetadata, used for finding the supported version for the requested auth type
     * @param bool $shouldUseAuthorize If mc_authz should be used over mc_authn
     * @param string $version Supported version of the scope selected to use
     * @return Returns a modified scope value with mc_authn removed or added
     */
    private function CoerceAuthenticationScope($scopeRequested, $shouldUseAuthorize, &$version, SupportedVersions $versions = null) {
        $requiredScope = $shouldUseAuthorize === true ? MobileConnectConstants::MOBILECONNECTAUTHORIZATION : MobileConnectConstants::MOBILECONNECTAUTHENTICATION;
        $disallowedScope = $shouldUseAuthorize === true ? Scope::AUTHN : Scope::AUTHZ;

        $versions = empty($versions) ? new SupportedVersions() : $versions;
        $version = $versions->GetSupportedVersion($requiredScope);
        $splitScope = explode(" ", $scopeRequested);

        $splitScope = Scopes::CoerceOpenIdScope($splitScope, $requiredScope);

        $key = array_search($disallowedScope, $splitScope);
        if($key !== false){
            unset($splitScope[$key]);
        }

        if (!$shouldUseAuthorize && ($version == DefaultOptions::VERSION_MOBILECONNECTAUTHN)) {
            $key = array_search(Scope::AUTHN, $splitScope);
            if($key !== false){
                unset($splitScope[$key]);
            }
        }
        return Scopes::CreateScope($splitScope);
    }

    public function RequestHeadlessAuthentication($clientId, $clientSecret, $authorizeUrl, $tokenUrl, $redirectUrl,
        $state, $nonce, $encryptedMSISDN, $version, AuthenticationOptions $options = null, $cancel = false) {

        $options = empty($options) ? new AuthenticationOptions() : $options;
        $shouldUseAuthorize = $this->shouldUseAuthorize($options);

        if ($shouldUseAuthorize) {
            $options->setPrompt("mobile");
        }

        $authUrl = $this->StartAuthentication($clientId, $authorizeUrl, $redirectUrl, $state, $nonce, $encryptedMSISDN,
            $version, $options)->getUrl();

        $curlRestClient = new CurlRestClient();
        try {
            $finalRedirect = $curlRestClient->followRedirects(rawurldecode($authUrl), $redirectUrl);
        } catch (OperationCancellationException $ex) {
            throw new OperationCancellationException($ex->getMessage());
        } catch (\RuntimeException $ex) {
            throw new \RuntimeException($ex);
        } catch (\Exception $ex) {
            throw new \RuntimeException($ex);
        }
        $code = HttpUtils::ExtractQueryValue($finalRedirect, "code");

        return $this->RequestToken($clientId, $clientSecret, $tokenUrl, $redirectUrl, $code);
    }

    public function RefreshToken($clientId, $clientSecret, $refreshTokenUrl, $refreshToken, $isBasicAuth=true) {
        ValidationUtils::validateParameter($clientId, "clientId");
        ValidationUtils::validateParameter($clientSecret, "clientSecret");
        ValidationUtils::validateParameter($refreshTokenUrl, "refreshTokenUrl");
        ValidationUtils::validateParameter($refreshToken, "refreshToken");

        try {
            $formData = array (
                Parameters::REFRESH_TOKEN => $refreshToken,
                Parameters::GRANT_TYPE => GrantTypes::REFRESH_TOKEN
            );
            $authentication =  $isBasicAuth ? RestAuthentication::Basic($clientId, $clientSecret) : null;
            $response = $this->_client->post($refreshTokenUrl, $authentication, $formData, null, null, null, null, null);

            return new RequestTokenResponse($response);
        } catch (Zend\Http\Exception\RuntimeException $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        } catch (Zend\Http\Client\Exception\RuntimeException $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        } catch (Exception $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        }
    }

    public function RevokeToken($clientId, $clientSecret, $revokeTokenUrl, $token, $tokenTypeHint, $isBasicAuth=true) {
        ValidationUtils::validateParameter($clientId, "clientId");
        ValidationUtils::validateParameter($clientSecret, "clientSecret");
        ValidationUtils::validateParameter($revokeTokenUrl, "revokeTokenUrl");
        ValidationUtils::validateParameter($token, "token");

        try {
            $formData = array (
                Parameters::TOKEN => $token,
            );
            if (!empty($tokenTypeHint)) {
                $formData[Parameters::TOKEN_TYPE_HINT] = $tokenTypeHint;
            }
            $authentication =  $isBasicAuth ? RestAuthentication::Basic($clientId, $clientSecret) : null;
            $response = $this->_client->post($revokeTokenUrl, $authentication, $formData, null, null, null, null, null);
            return new RevokeTokenResponse($response);
        } catch (Zend\Http\Exception\RuntimeException $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        } catch (Zend\Http\Client\Exception\RuntimeException $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        } catch (Exception $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        }
    }
}
