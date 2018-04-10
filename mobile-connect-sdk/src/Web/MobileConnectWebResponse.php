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

namespace MCSDK\Web;

/**
 * Lightweight object to be serialized and returned through a web api
 */
class MobileConnectWebResponse
{
    /**
     * "success" or "failure", if "success" the next step should be attempted
     */
    private $_status;
    /**
     * Action to take for next step
     */
    private $_action;
    /**
     * Application short name returned by discovery service, this identifies the application requesting authorization
     */
    private $_applicationShortName;
    /**
     * If next step requires visiting a url it will be returned with this property
     */
    private $_url;
    /**
     * If caching is enabled this will be required in the steps following discovery
     */
    private $_sdkSession;
    /**
     * State value used during Authorization, should be passed when handling the next redirect
     */
    private $_state;
    /**
     * Nonce value used during Authorization, should be passed when handling the next redirect
     */
    private $_nonce;
    /**
     * Encrypted MSISDN value returned from a successful Discovery call
     */
    private $_subscriberId;
    /**
     * Token data returned from a successful RequestToken call
     */
    private $_token;
    /**
     * Identity data returned from successful RequestUserInfo or RequestIdentityInfo call
     */
    private $_identity;

    /**
     * Error code if available
     */
    private $_error;
    /**
     * Error user friendly description if available
     */
    private $_description;

    public function getStatus() {
        return $this->_status;
    }

    public function setStatus($_status) {
        $this->_status = $_status;
    }

    public function getAction() {
        return $this->_action;
    }

    public function setAction($_action) {
        $this->_action = $_action;
    }

    public function getApplicationShortName() {
        return $this->_applicationShortName;
    }

    public function setApplicationShortName($_applicationShortName) {
        $this->_applicationShortName = $_applicationShortName;
    }

    public function getUrl() {
        return $this->_url;
    }

    public function setUrl($_url) {
        $this->_url = $_url;
    }

    public function getSdkSession() {
        return $this->_sdkSession;
    }

    public function setSdkSession($_sdkSession) {
        $this->_sdkSession = $_sdkSession;
    }

    public function getState() {
        return $this->_state;
    }

    public function setState($_state) {
        $this->_state = $_state;
    }

    public function getNonce() {
        return $this->_nonce;
    }

    public function setNonce($_nonce) {
        $this->_nonce = $_nonce;
    }

    public function getSubscriberId() {
        return $this->_subscriberId;
    }

    public function setSubscriberId($_subscriberId) {
        $this->_subscriberId = $_subscriberId;
    }

    public function getError() {
        return $this->_error;
    }

    public function setError($error) {
        $this->_error = $error;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function setDescription($_description) {
        $this->_description = $_description;
    }

    public function setToken($token) {
        $this->_token = $token;
    }

    public function getToken() {
        return $this->_token;
    }

    public function getIdentity() {
        return $this->_identity;
    }

    public function setIdentity($identity) {
        $this->_identity = $identity;
    }

    public function toArray(MobileConnectWebResponse $obj) {

    }
}
