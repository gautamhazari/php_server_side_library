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

use MCSDK\discovery\DiscoveryException;
use MCSDK\discovery\DiscoveryResponse;
use MCSDK\ParsedAuthorizationResponse;
use MCSDK\Authentication\RequestTokenResponse;
use MCSDK\Utils\MobileConnectResponseType;
use MCSDK\Identity\IdentityResponse;
use MCSDK\Identity\IIdentityResponse;
use MCSDK\Authentication\RevokeTokenResponse;

/**
 * Class to hold the response from calling a MobileConnectInterface method see
 * {@link MobileConnectInterface}.
 *
 * The return of the methods can be one of several types. Depending on the type
 * of response various properties are available.
 */
class MobileConnectStatus
{
    const INTERNAL_ERROR_CODE = "internal error";

    private $_responseType;
    private $_errorCode;
    private $_errorMessage;
    private $_url;
    private $_state;
    private $_nonce;
    private $_cookie;
    private $_sdkSession;
    private $_discoveryResponse;
    private $_tokenResponse;
    private $_identityResponse;
    private $_exception;

    /**
     * Creates a Status with ResponseType OperatorSelection and url for next process step.
     * Indicates that the next step should be navigating to the operator selection URL.
     * @param string $url Operator selection URL returned from {@link IDiscoveryService}
     * @return MobileConnectStatus with ResponseType OperatorSelection
     */
    public static function OperatorSelection($url) {
        $result = new MobileConnectStatus();
        $result->setUrl($url);
        $result->setResponseType(MobileConnectResponseType::OperatorSelection);
        return $result;
    }

    /**
     * Creates a Status with ResponseType StartAuthorization and the complete {@link DiscoveryService}
     * Indicates that the next step should be starting authorization.
     * @param DiscoveryResponse $response returned from {@link IDiscoveryService}
     * @return MobileConnectStatus with ResponseType StartAuthorization
     */
    public static function StartAuthorization(DiscoveryResponse $response) {
        $result = new MobileConnectStatus();
        $cookie = $response->getHeaders();
        $result->setDiscoveryResponse($response);
        $result->setResponseType(MobileConnectResponseType::StartAuthentication);
        $result->setCookie($cookie);
        return $result;
    }

    /**
     * Creates a Status with ResponseType StartDiscovery.
     * Indicates that some required data was missing and the discovery process needs to be restarted.
     * @param MobileConnectStatus with ResponseType StartDiscovery
     */
    public static function StartDiscovery() {
        $result = new MobileConnectStatus();
        $result->setResponseType(MobileConnectResponseType::StartDiscovery);
        return $result;
    }

    /**
     * Creates a Status with ResponseType Authorization and url for next process step.
     * Indicates that the next step should be navigating to the Authorization URL.
     * @param string $url Url returned from {@link IAuthenticationService}
     * @param string $state The unique state string generated or passed in for the authorization url
     * @param string $nonce The unique nonce string generated or passed in for the authorization url
     * @return MobileConnectStatus with ResponseType Authorization
     */
    public static function Authorization($url, $state, $nonce)
    {
        $mobileConnectStatus = new MobileConnectStatus();
        $mobileConnectStatus->setResponseType(MobileConnectResponseType::Authentication);
        $mobileConnectStatus->setUrl($url);
        $mobileConnectStatus->setState($state);
        $mobileConnectStatus->setNonce($nonce);

        return $mobileConnectStatus;
    }

    /**
     * Creates a Status with ResponseType Complete and the complete {@link RequestTokenResponse}
     * Indicates that the MobileConnect process is complete and the user is authenticated.
     * @param RequestTokenResponse $response returned from {@link IAuthenticationService}
     * @return MobileConnectStatus with ResponseType Complete
     */
    public static function Complete(RequestTokenResponse $response) {
        $mobileConnectStatus = new MobileConnectStatus();
        $mobileConnectStatus->setResponseType(MobileConnectResponseType::Complete);
        $mobileConnectStatus->setTokenResponse($response);
        return $mobileConnectStatus;
    }

    /**
     * Creates a status with ResponseType UserInfo and the complete {@link IdentityResponse}
     * Indicates that a user info request has been successful.
     * @param UserInfoResponse $response returned from {@link IIdentityService}
     * @return MobileConnectStatus with ResponseType UserInfo
     */
    public static function UserInfo(IdentityResponse $response) {
        $mobileConnectStatus = new MobileConnectStatus();
        $mobileConnectStatus->setResponseType(MobileConnectResponseType::UserInfo);
        $mobileConnectStatus->setIdentityResponse($response);
        return $mobileConnectStatus;
    }

    /**
     *  Creates a status with ResponseType Identity and the complete {@link IdentityResponse}
     * Indicates that an identity request has been successful.
     * @param UserInfoResponse $response returned from {@link IIdentityService}
     * @return MobileConnectStatus with ResponseType Identity
     */
    public static function Identity(IdentityResponse $response) {
        $mobileConnectStatus = new MobileConnectStatus();
        $mobileConnectStatus->setResponseType(MobileConnectResponseType::Identity);
        $mobileConnectStatus->setIdentityResponse($response);
        return $mobileConnectStatus;
    }


    public static function TokenRevoked(RevokeTokenResponse $response) {

        if (!empty($response->getErrorResponse())) {
            return static::Error($response->getErrorResponse());
        }
        $mobileConnectStatus = new MobileConnectStatus();
        $mobileConnectStatus->setResponseType(MobileConnectResponseType::TokenRevoked);
        return $mobileConnectStatus;
    }

    /**
     * The RequestTokenResponse
     *
     * Available when {@link MobileConnectStatus#isComplete()} returns true
     *
     * @return RequestTokenResponse The authorization token response
     */
	public function getTokenResponse() {
		return $this->_tokenResponse;
	}

    public function setTokenResponse($response) {
        $this->_tokenResponse = $response;
    }

    public function setCookie($cookie) {
        $this->_cookie = $cookie;
    }

    public function setUrl($url) {
        $this->_url = $url;
    }

    public function setState($state) {
        $this->_state = $state;
    }

    public function setNonce($nonce) {
        $this->_nonce = $nonce;
    }

    public function setResponseType($responseType) {
        $this->_responseType = $responseType;
    }
    /**
     * Used for testing to make sure the construct sets the response type
     *
     * @return ResponseType
     */
    public function getResponseType()
    {
        return $this->_responseType;
    }

    public function getNonce() {
        return $this->_nonce;
    }

    public function getState() {
        return $this->_state;
    }

    public function getSDKSession() {
        return $this->_sdkSession;
    }

    public function setSDKSession($session) {
        $this->_sdkSession = $session;
    }

    public function setDiscoveryResponse($response) {
        $this->_discoveryResponse = $response;
    }

    public function setErrorCode($code) {
        $this->_errorCode = $code;
    }

    public function getErrorCode() {
        return $this->_errorCode;
    }

    public function setErrorMessage($message) {
        $this->_errorMessage = $message;
    }

    public function getErrorMessage() {
        return $this->_errorMessage;
    }

    public function setException($ex) {
        $this->_exception = $ex;
    }

    public static function Error($error, $message, $ex = null) {
        $status = new MobileConnectStatus();
        $status->setErrorCode(empty($error) ? INTERNAL_ERROR_CODE : $error);
        $status->setErrorMessage($message);
        $status->setException($ex);
        $status->setResponseType(MobileConnectResponseType::Error);
        return $status;
    }

    /**
     * Error type.
     *
     * Available when {@link MobileConnectStatus#isError()} returns true.
     *
     * @return bool The error.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Error description.
     *
     * Available when {@link MobileConnectStatus#isError()} returns true.
     *
     * @return string A description of the error.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * An optional Exception causing an error.
     *
     * Optionally available when {@link MobileConnectStatus#isError()} returns true.
     *
     * @return DiscoveryException An exception that caused the error.
     */
    public function getException()
    {
        return $this->_exception;
    }

    /**
     * Url to be redirected to.
     *
     * Available when {@link MobileConnectStatus#isOperatorSelection()} or {@link MobileConnectStatus#isAuthorization()} return true.
     *
     * @return string Url to be redirected to.
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Discovery Response of the selected operator.
     *
     * Available when {@link MobileConnectStatus#isStartAuthorization()} or {@link MobileConnectStatus#isAuthorization()} return true.
     *
     * @return DiscoveryResponse Discovery Response for the selected operator.
     */
    public function getDiscoveryResponse()
    {
        return $this->_discoveryResponse;
    }

    /**
     * The ParsedAuthorizationResponse
     *
     * Available when {@link MobileConnectStatus#isComplete()} returns true
     *
     * @return ParsedAuthorizationResponse The ParsedAuthorizationResponse
     */
    public function getParsedAuthorizationResponse()
    {
        return $this->parsedAuthorizationResponse;
    }

    /**
     * The RequestTokenResponse
     *
     * Available when {@link MobileConnectStatus#isComplete()} returns true
     *
     * @return RequestTokenResponse The authorization token response
     */
    public function getRequestTokenResponse()
    {
        return $this->requestTokenResponse;
    }

    public function getIdentityResponse() {
        return $this->_identityResponse;
    }

    public function setIdentityResponse($response) {
        $this->_identityResponse = $response;
    }

    /**
     * Get the screen mode
     *
     * @return string the current screen mode
     */
    public function getScreenMode()
    {
        return $this->screenMode;
    }

    /**
     * Make sure that a null value is returned as an empty string
     *
     * @param string|null $input value to be tested and converted to string
     * @return string the converted value
     */
    public static function ensureStringHasValue($input)
    {
        if (is_null($input)) {
            return '';
        } else {
            return $input;
        }
    }

}
