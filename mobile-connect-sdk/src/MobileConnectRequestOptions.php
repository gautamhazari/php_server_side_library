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
use MCSDK\Discovery\DiscoveryOptions;

/**
 * Options for a single request to <see cref="MobileConnectInterface"/>.
 * Not all options are valid for all calls that accept an instance of this class, only options that are relevant to the method being called will be used.
 */

class MobileConnectRequestOptions {
    private $_discoveryOptions;
    private $_authOptions;
    private $_correlationId;

    public function __construct() {
        $this->_discoveryOptions = new DiscoveryOptions();
        $this->_authOptions = new AuthenticationOptions();
    }

    public function getClientSideVersion() {
        return $this->_discoveryOptions->getClientSideVersion();
    }

    public function setClientSideVersion($value) {
        if (!empty($value)) {
            $this->_discoveryOptions->setClientSideVersion($value);
        }
    }

    public function getServerSideVersion() {
        return $this->_discoveryOptions->getServerSideVersion();
    }

    public function setServerSideVersion($value) {
        if (!empty($value)) {
            $this->_discoveryOptions->setServerSideVersion($value);
        }
    }

    public function getDiscoveryOptions() {
        return $this->_discoveryOptions;
    }

    public function getAuthenticationOptions() {
        return $this->_authOptions;
    }

    public function getClientIp() {
        return $this->_discoveryOptions->getClientIp();
    }

    public function setClientIp($value) {
        $this->_discoveryOptions->setCLientIp($value);
    }

    public function getIsUsingMobileData() {
        return $this->_discoveryOptions->IsUsingMobileData();
    }

    public function setIsUsingMobileData($value) {
        $this->_discoveryOptions->setUsingMobileData($value);
    }

    public function getScope() {
        return $this->_authOptions->getScope();
    }

    public function setScope($value) {
        $this->_authOptions->setScope($value);
    }

    public function getContext() {
        return $this->_authOptions->getContext();
    }

    public function setContext($value) {
        $this->_authOptions->setContext($value);
    }

    public function getBindingMessage() {
        return $this->_authOptions->getBindingMessage();
    }

    public function setBindingMessage($value) {
        $this->_authOptions->setBindingMessage($value);
    }

    public function setLocalClientIP($value) {
        $this->_discoveryOptions->setLocalClientIP($value);
    }

    public function getLocalClientIP() {
        return $this->_discoveryOptions->getLocalClientIP();
    }

    public function setDisplay($value) {
        return $this->_authOptions->setDisplay($value);
    }

    public function getDisplay() {
        return $this->_authOptions->getDisplay();
    }

    public function setPrompt($value) {
        $this->_authOptions->setPrompt($value);
    }

    public function getPrompt() {
        return $this->_authOptions->getPrompt();
    }

    public function setUiLocales($value) {
        $this->_authOptions->setUiLocales($value);
    }

    public function getUiLocales() {
        return $this->_authOptions->getUiLocales();
    }

    public function getClaimsLocales() {
        return $this->_authOptions->getClaimsLocales();
    }

    public function setClaimsLocales($value) {
        $this->_authOptions->setClaimsLocales($value);
    }

    public function getIdTokenHint() {
        return $this->_authOptions->getIdTokenHint();
    }

    public function setIdTokenHint($value) {
        $this->_authOptions->setIdTokenHint($value);
    }

    public function getLoginHint() {
        return $this->_authOptions->getLoginHint();
    }

    public function setLoginHint($value) {
        $this->_authOptions->setLoginHint($value);
    }

    public function getDtbs() {
        return $this->_authOptions->getDtbs();
    }

    public function setDtbs($value) {
        $this->_authOptions->setDtbs($value);
    }

    public function getAcrValues() {
        return $this->_authOptions->getAcrValues();
    }

    public function setAcrValues($values) {
        $this->_authOptions->setAcrValues($values);
    }

    public function getMaxAge() {
        return $this->_authOptions->getMaxAge();
    }

    public function setMaxAge($value) {
        $this->_authOptions->setMaxAge($value);
    }

    public function getClaimsJson() {
        return $this->_authOptions->getClaimsJson();
    }

    public function setClaimsJson($value){
        $this->_authOptions->setClaimsJson($value);
    }

    public function getClaims() {
        return $this->_authOptions->getClaims();
    }

    public function setClaims($value) {
        $this->_authOptions->setClaims($value);
    }

    /**
     * @return mixed
     */
    public function getClientName()
    {
        return $this->_authOptions->getClientName();
    }

    /**
     * @param mixed $clientName
     */
    public function setClientName($clientName)
    {
        $this->_authOptions->setClientName($clientName);
    }

    /**
     * @return mixed
     */
    public function getCorrelationId()
    {
        return $this->_correlationId;
    }

    /**
     * @param mixed $correlationId
     */
    public function setCorrelationId($correlationId): void
    {
        $this->_correlationId = $correlationId;
    }
}
