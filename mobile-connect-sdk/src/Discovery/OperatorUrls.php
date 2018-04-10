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
namespace MCSDK\Discovery;
use MCSDK\Constants\LinkRels;

/**
 * Object to hold the operator specific urls returned from a successful discovery process call
 */
class OperatorUrls
{
    /**
     * Url for authorization call
     */
    private $_authorizationUrl;

    /**
     * Url for token request call
     */
    private $_requestTokenUrl;

    /**
     * Url for user info call
     */
    private $_userInfoUrl;

    /**
     * Url for identity services call
     */
    private $_premiumInfoUrl;

    /**
     * Url for JWKS info
     */
    private $_JWKSUrl;

    /**
     * Url for Provider Metadata
     */
    private $_providerMetadataUrl;

    /**
     * Url for token refresh call
     */
    private $_refreshTokenUrl;

    /**
     * Url for token revoke call
     */
    private $_revokeTokenUrl;

    private $_scope;

    public function getAuthorizationUrl(){
        return $this->_authorizationUrl;
    }

    public function setAuthorizationUrl($_authorizationUrl){
        $this->_authorizationUrl = $_authorizationUrl;
    }

    public function getRequestTokenUrl(){
        return $this->_requestTokenUrl;
    }

    public function setRequestTokenUrl($_requestTokenUrl){
        $this->_requestTokenUrl = $_requestTokenUrl;
    }

    public function getUserInfoUrl(){
        return $this->_userInfoUrl;
    }

    public function setUserInfoUrl($_userInfoUrl){
        $this->_userInfoUrl = $_userInfoUrl;
    }

    public function getPremiumInfoUrl(){
        return $this->_premiumInfoUrl;
    }

    public function setPremiumInfoUrl($_premiumInfoUrl){
        $this->_premiumInfoUrl = $_premiumInfoUrl;
    }

    public function getJWKSUrl(){
        return $this->_JWKSUrl;
    }

    public function setJWKSUrl($_JWKSUrl){
        $this->_JWKSUrl = $_JWKSUrl;
    }

    public function getProviderMetadataUrl(){
        return $this->_providerMetadataUrl;
    }

    public function setProviderMetadataUrl($_providerMetadataUrl){
        $this->_providerMetadataUrl = $_providerMetadataUrl;
    }

    public function setRefreshTokenUrl($url) {
        $this->_refreshTokenUrl = $url;
    }

    public function getRefreshTokenUrl() {
        return $this->_refreshTokenUrl;
    }

    public function setRevokeTokenUrl($url) {
        $this->_revokeTokenUrl = $url;
    }

    public function getRevokeTokenUrl() {
        return $this->_revokeTokenUrl;
    }

    public function getIssuer(){
        $matches = [];
        preg_match('/^(http[s]?:\/?\/?[^:\/\s]+)/', $this->getAuthorizationUrl(), $matches);
        return $matches[0];
    }

    public function getJson(){
        $format = "\"link\": [{
					\"href\": \"%s\",
					\"rel\": \"authorization\"
				}, {
					\"href\": \"%s\",
					\"rel\": \"token\"
				}, {
					\"href\": \"%s\",
					\"rel\": \"userinfo\"
				}, {
					\"href\": \"%s\",
					\"rel\": \"tokenrevoke\"
				}, {
					\"href\": \"%s\",
					\"rel\": \"premiuminfo\"
				}, {
					\"href\": \"%s\",
					\"rel\": \"scope\"
				}, {
					\"href\": \"%s\",
					\"rel\": \"openid-configuration\"
				}, {
					\"href\": \"%s\",
					\"rel\": \"jwks\"
				}]";
        return sprintf($format, $this->_authorizationUrl, $this->_requestTokenUrl, $this->_userInfoUrl, $this->_revokeTokenUrl,
            $this->_premiumInfoUrl, $this->_scope, $this->_providerMetadataUrl, $this->_JWKSUrl);
    }

    /**
     * Parses the operator urls from the parsed DiscoveryResponseData
     * @param $links Data from the successful discovery response</param>
     * @returns OperatorUrls parsed  or null if no urls found
     */
    public static function Parse($links)
    {

        if (!isset($links["response"]["apis"]["operatorid"]["link"])) {
            return null;
        }

        $links = $links["response"]["apis"]["operatorid"]["link"];
        $operatorUrls = new OperatorUrls();
        $operatorUrls->setAuthorizationUrl(static::getUrl($links, LinkRels::AUTHORIZATION));
        $operatorUrls->setRequestTokenUrl(static::getUrl($links, LinkRels::TOKEN));
        $operatorUrls->setUserInfoUrl(static::getUrl($links, LinkRels::USERINFO));
        $operatorUrls->setPremiumInfoUrl(static::getUrl($links, LinkRels::PREMIUMINFO));
        $operatorUrls->setJWKSUrl(static::getUrl($links, LinkRels::JWKS));
        $operatorUrls->setProviderMetadataUrl(static::getUrl($links, LinkRels::OPENID_CONFIGURATION));
        $operatorUrls->setRefreshTokenUrl(static::getUrl($links, LinkRels::TOKENREFRESH));
        $operatorUrls->setRevokeTokenUrl(static::getUrl($links, LinkRels::TOKENREVOKE));
        return $operatorUrls;
    }

    private static function getUrl($links, $rel)
    {
        $key = array_search($rel, array_column($links, 'rel'));
        if ($key !== false) {
            return $links[$key]["href"];
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->_scope;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->_scope = $scope;
    }
}
