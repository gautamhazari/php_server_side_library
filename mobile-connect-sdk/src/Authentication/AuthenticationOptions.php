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
use MCSDK\Constants\DefaultOptions;

class AuthenticationOptions
{
    private $_clientId;
    private $_redirectUrl;
    private $_acrValues;
    private $_scope;
    private $_nonce;
    private $_state;
    private $_maxAge;
    private $_display;
    private $_prompt;
    private $_uiLocales;
    private $_claimsLocales;
    private $_idTokenHint;
    private $_loginHint;
    private $_dtbs;
    private $_clientName;
    private $_context;
    private $_bindingMessage;
    private $_claimsJson;
    private $_claims;
    private $_loginTokenHint;

    public function __construct() {
        $this->_acrValues = DefaultOptions::AUTHENTICATION_ACR_VALUES;
        $this->_scope = DefaultOptions::AUTHENTICATION_SCOPE;
        $this->_maxAge = DefaultOptions::AUTHENTICATION_MAX_AGE;
        $this->_display = DefaultOptions::DISPLAY;

    }

    public function getClientId() {
        return $this->_clientId;
    }

    public function setClientId($clientId) {
        $this->_clientId = $clientId;
    }

    public function getRedirectUrl() {
        return $this->_redirectUrl;
    }

    public function setRedirectUrl($redirectUrl) {
        $this->_redirectUrl = $redirectUrl;
    }

    public function getAcrValues() {
        return $this->_acrValues;
    }

    public function setAcrValues($acrValues) {
        $this->_acrValues = $acrValues;
    }

    public function getScope() {
        return $this->_scope;
    }

    public function setScope($scope) {
        $this->_scope = $scope;
    }

    public function getNonce() {
        return $this->_nonce;
    }

    public function setNonce($nonce) {
        $this->_nonce = $nonce;
    }

    public function getState() {
        return $this->_state;
    }

    public function setState($state) {
        $this->_state = $state;
    }

    public function getMaxAge() {
        return $this->_maxAge;
    }

    public function setMaxAge($maxAge) {
        $this->_maxAge = $maxAge;
    }

    public function getDisplay() {
        return $this->_display;
    }

    public function setDisplay($display) {
        $this->_display = $display;
    }

    public function getPrompt() {
        return $this->_prompt;
    }

    public function setPrompt($prompt) {
        $this->_prompt = $prompt;
    }

    public function getUiLocales() {
        return $this->_uiLocales;
    }

    public function setUiLocales($uiLocales) {
        $this->_uiLocales = $uiLocales;
    }

    public function getClaimsLocales() {
        return $this->_claimsLocales;
    }

    public function setClaimsLocales($claimsLocales) {
        $this->_claimsLocales = $claimsLocales;
    }

    public function getIdTokenHint() {
        return $this->_idTokenHint;
    }

    public function setIdTokenHint($idTokenHint) {
        $this->_idTokenHint = $idTokenHint;
    }

    public function getLoginHint() {
        return $this->_loginHint;
    }

    public function setLoginHint($loginHint) {
        $this->_loginHint = $loginHint;
    }

    public function getDtbs() {
        return $this->_dtbs;
    }

    public function setDtbs($dtbs) {
        $this->_dtbs = $dtbs;
    }

    public function getClientName() {
        return $this->_clientName;
    }

    public function setClientName($clientName) {
        $this->_clientName = $clientName;
    }

    public function getContext() {
        return $this->_context;
    }

    public function setContext($context) {
        $this->_context = $context;
    }

    public function getBindingMessage() {
        return $this->_bindingMessage;
    }

    public function setBindingMessage($bindingMessage) {
        $this->_bindingMessage = $bindingMessage;
    }

    public function getClaimsJson() {
        return $this->_claimsJson;
    }

    public function setClaimsJson($claimsJson) {
        $this->_claimsJson = $claimsJson;
    }

    public function getClaims() {
        return $this->_claims;
    }

    public function setClaims($claims) {
        $this->_claims = $claims;
    }

    /**
     * @return mixed
     */
    public function getLoginTokenHint()
    {
        return $this->_loginTokenHint;
    }

    /**
     * @param mixed $loginTokenHint
     */
    public function setLoginTokenHint($loginTokenHint)
    {
        $this->_loginTokenHint = $loginTokenHint;
    }

}

