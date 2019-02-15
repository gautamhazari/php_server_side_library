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

use MCSDK\Constants\DefaultOptions;
use MCSDK\Exceptions\MobileConnectProviderMetadataUnavailableException;
use MCSDK\Utils\RestResponse;
use Zend\Http\Headers;

/**
 * Class to hold a Discovery Response.
 *
 * This potentially holds cached data as indicated by the cached property.
 */
class DiscoveryResponse
{
    private $_cached;
    private $_responseCode;
    private $_headers;
    private $_responseData;
    private $_ttl = null;
    private $_providerMetadata;
    private $_operatorUrls;
    private $_cookie;
    private $_applicationShortName;
    private $_errorResponse;
    private $_timeCachedUtc;
    private $_markedExpiredByCache;
    private $_expirationUTCTimestamp;

    public function __construct(RestResponse $rawResponse = null) {
        if (isset($rawResponse)) {
            $this->_cached = false;
            $this->_responseCode = $rawResponse->getStatusCode();
            $this->_headers = $rawResponse->getHeaders();
            $this->_responseData = json_decode($rawResponse->getContent(), true);

            $this->parseResponseData($this->_responseData);
        }
    }

    public function setExpirationUTCTimestamp() {
        if (isset($this->_responseData["ttl"])) {
            $this->_expirationUTCTimestamp = $this->_responseData["ttl"];
        }
    }

    public function getExpirationUTCTimestamp() {
        return $this->_expirationUTCTimestamp;
    }

    public function getTimeCachedUtc() {
        return $this->_timeCachedUtc;
    }

    public function setTimeCachedUtc($dateTimeStamp) {
        $this->_timeCachedUtc = $dateTimeStamp;
    }

    private function parseResponseData(array $responseData = null) {

        if (empty($_ttl) && isset($responseData["ttl"])) {
            $this->_ttl = $this->CalculateTTL($responseData["ttl"]);
        }

        if ($responseData === null) {
            return;
        }

        $this->_operatorUrls = OperatorUrls::Parse($responseData);

        if (isset($responseData["response"]["client_name"])) {
            $this->_applicationShortName = $responseData["response"]["client_name"];
        }

        if (isset($responseData["error"])) {
            $this->_errorResponse = array (
                "error" => $responseData["error"],
                "error_description" => $responseData["description"]
            );
        }

        if (isset($responseData["response"]["provider_metadata"])) {
            $this->_providerMetadata = $responseData["response"]["provider_metadata"];
        }

    }

    public function getErrorResponse() {
        return $this->_errorResponse;
    }

    public function getApplicationShortName() {
        return $this->_applicationShortName;
    }

    /**
     * Is the data from a local cache?
     *
     * @return True if the data is cached data, false otherwise
     */
    public function isCached()
    {
        return $this->_cached;
    }

    public function setCached($value) {
        $this->_cached = $value;
        if ($this->_cached && !empty($this->_responseData) && !empty($this->_responseData["subscriber_id"])) {
            $this->_responseData["subscriber_id"] = null;
        }
    }

    /**
     * Time to live of the response, if specified
     *
     * @return \DateTime The ttl of the response
     */
    public function getTtl()
    {
        return $this->_ttl;
    }

    /**
     * Has the response expired?
     *
     * If no ttl is specified then it is assumed that the the response has not
     * expired. Otherwise compare the ttl against the current time.
     *
     * @return True if the response has expired
     */
    public function hasExpired()
    {
        return $this->_markedExpiredByCache || (!empty($this->_ttl) && $this->_ttl <= \DateTime());
    }

    public function MarkExpired($isExpired) {
        $this->_markedExpiredByCache = $isExpired;
    }

    /**
     * Return the Http responseCode
     *
     * @return int The Http response code, 0 if cached data.
     */
    public function getResponseCode()
    {
        return $this->_responseCode;
    }

    /**
     * Return the list of Http headers in the response
     *
     * @return Headers The response Http headers, null if cached data.
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * The Json discovery response.
     *
     * This could be operator endpoints, operator selection or an error
     *
     * @return \stdClass The response from the call to the Discovery service.
     */
    public function getResponseData()
    {
        return $this->_responseData;
    }

    public function setResponseData($data) {
        $this->_responseData = $data;
    }

    /**
     * Return the ProviderMetadata object
     *
     * @return ProviderMetadata or null of does not exist
     */
    public function getProviderMetadata()
    {
        return $this->_providerMetadata;
    }

    public function setProviderMetadata($value) {
        $this->_providerMetadata = $value;
        $this->overrideOperatorUrls($this->_providerMetadata);
    }

    public function overrideUrls($metadata) {
        if ($metadata === null) {
            return null;
        }
        $operatorUrls = new OperatorUrls();

        if (isset($metadata["authorization_endpoint"])) {
            $operatorUrls->setAuthorizationUrl($metadata["authorization_endpoint"]);
        }
        if (isset($metadata["token_endpoint"])) {
            $operatorUrls->setRequestTokenUrl($metadata["token_endpoint"]);
        }
        if (isset($metadata["userinfo_endpoint"])) {
            $operatorUrls->setUserInfoUrl($metadata["userinfo_endpoint"]);
        }
        if (isset($metadata["premiuminfo_endpoint"])) {
            $operatorUrls->setPremiumInfoUrl($metadata["premiuminfo_endpoint"]);
        }
        if (isset($metadata["jwks_uri"])) {
            $operatorUrls->setJWKSUrl($metadata["jwks_uri"]);
        }
        if (isset($metadata["refresh_endpoint"])) {
            $operatorUrls->setRefreshTokenUrl($metadata["refresh_endpoint"]);
        }
        if (isset($metadata["revocation_endpoint"])) {
            $operatorUrls->setRevokeTokenUrl($metadata["revocation_endpoint"]);
        }

        return $operatorUrls;
    }

    public function IsMobileConnectServiceSupported($scope) {
        if (empty($scope)) {
            return true;
        }

        if (empty($this->_providerMetadata)) {
            throw new MobileConnectProviderMetadataUnavailableException();
        } else if (!isset($this->_providerMetadata["scopes_supported"]) || count($this->_providerMetadata["scopes_supported"]) == 0) {
            throw new MobileConnectProviderMetadataUnavailableException("ScopesSupported");
        }
        return $this->containsAllValues($scope, $this->_providerMetadata["scopes_supported"]);
    }

    private function containsAllValues($scopes, $scopesSupported) {
        $pattern = '[\s]';
        $splittedScopes = preg_split($pattern, $scopes);
        $splittedScopes = array_map('strtolower', $splittedScopes);
        return count(array_intersect($splittedScopes, $scopesSupported)) == count($splittedScopes);
    }

    private function overrideOperatorUrls($metadata) {
        if ($metadata === null) {
            return null;
        }
        $this->_operatorUrls = $this->overrideUrls($metadata);
    }

    /**
     * Set operator urls
     *
     */
    public function setOperatorUrls($operatorUrls)
    {
        $this->_operatorUrls = $operatorUrls;
    }

    /**
     * Get operator urls
     *
     * @return OperatorUrls containing operator urls
     */
    public function getOperatorUrls()
    {
        return $this->_operatorUrls;
    }

    private function CalculateTTL($responseTtl) {
        $now = new \DateTime();
        $epoch = new \DateTime('1970-1-1');
        $min = $now;
        $min->modify("+" . $this->fromMillisecondsToSeconds(DefaultOptions::MIN_TTL_MS) . " seconds");
        $max = $now;
        $max->modify("+" . $this->fromMillisecondsToSeconds(DefaultOptions::MAX_TTL_MS) . " seconds");

        if (empty($responseTtl)) {
            return null;
        }

        $currentTtl = $epoch->modify("+" . $this->fromMillisecondsToSeconds($responseTtl) . " seconds");
        return $currentTtl < $min ? $min : ($currentTtl > $max ? $max : $currentTtl);
    }

    private function fromMillisecondsToSeconds($milliseconds) {
        return substr($milliseconds, 0, -3);
    }
}
