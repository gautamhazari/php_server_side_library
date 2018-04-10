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
use MCSDK\Utils\RestResponse;
use MCSDK\Utils\ValidationUtils;
use MCSDK\Utils\RestClient;
use MCSDK\Constants\Parameters;
use MCSDK\Discovery\ParsedDiscoveryRedirect;
use MCSDK\Cache\ICache;
use MCSDK\Cache\CacheImpl;
use MCSDK\Utils\RestAuthentication;

/**
 * Concrete implementation of IDiscoveryService
 */
class DiscoveryService implements IDiscoveryService {
    private $_cache;
    private $_client;

    public function __construct(RestClient $client, ICache $cache = null) {
        $this->_client = $client;
        $this->_cache = $cache;
    }

    public function getCache() {
        return $this->_cache;
    }

    public function StartAutomatedOperatorDiscovery($clientId, $clientSecret,
        $discoveryUrl, $redirectUrl, DiscoveryOptions $options = null, array $cookies = null) {

        if(!isset($options)) {
            $options = new DiscoveryOptions();
        }
        $options->setRedirectUrl($redirectUrl);
        return $this->callDiscoveryEndpoint($clientId, $clientSecret, $discoveryUrl,
            $options, true, $cookies);
    }

    public function startAutomatedOperatorDiscoveryByPreferences(IPreferences $preferences,
        $redirectUrl, DiscoveryOptions $options, array $cookies = null) {

        ValidationUtils::validateParameter($preferences, "preferences");
        return $this->startAutomatedOperatorDiscovery($preferences->getClientId(), $preferences->getClientSecret(),
            $preferences->getDiscoveryURL(), $redirectUrl, $options, $cookies);
    }

    public function callDiscoveryEndpoint($clientId, $clientSecret, $discoveryUrl,
        DiscoveryOptions $options, $cacheDiscoveryResponse, array $cookies = null) {

        ValidationUtils::validateParameter($clientId, "clientId");
        ValidationUtils::validateParameter($clientSecret, "clientSecret");
        ValidationUtils::validateParameter($discoveryUrl, "discoveryUrl");
        ValidationUtils::validateParameter($options->getRedirectUrl(), "redirectUrl");
        if ($cacheDiscoveryResponse) {
            $cachedValue = $this->getCachedValue($options);
            if (!empty($cachedValue)) {
                return $cachedValue;
            }
        }
        try {
            $queryParams = $this->getDiscoveryQueryParams($options);
            $authentication = RestAuthentication::Basic($clientId, $clientSecret);
            $response = new RestResponse();

            if (empty($options->getMSISDN())) {
                $response = $this->_client->get($discoveryUrl, $authentication, $options->getClientIp(),$queryParams, $options->getXRedirect(), $cookies);
            } else {
                $response = $this->_client->post($discoveryUrl, $authentication, $queryParams, $options->getClientIp(), $options->getXRedirect(), $cookies);
            }

            $discoveryResponse = new DiscoveryResponse($response);
            $providerMetadata = null;
            if ($discoveryResponse->getOperatorUrls() !== null) {
                $providerMetadata = $this->retrieveProviderMetadata($discoveryResponse->getOperatorUrls()->getProviderMetadataUrl());
            }
            if (!empty($providerMetadata)) {
                $discoveryResponse->setProviderMetadata($providerMetadata);
            }

            if ($cacheDiscoveryResponse) {
                $this->addCachedValue($options, $discoveryResponse);
            }

            return $discoveryResponse;
        } catch (Zend\Http\Exception\RuntimeException $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        } catch (Zend\Http\Client\Exception\RuntimeException $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        } catch (Exception $ex) {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        }
    }

    public function retrieveProviderMetadata($url, $forceCacheBypass = false) {
        if (!isset($url)) {
            return array ();
        }
        $cached = null;
        if (!$forceCacheBypass) {
            $cached = $this->getCachedProviderMetadata($url);
            if (isset($cached)) {
                return json_decode($cached->getMetadata(), true);
            }
        }
        $metadata = null;
        try {
            $response = $this->_client->get($url);

            if ($response->getStatusCode() < 400) {
                $metadata = $response->getContent();
                $providerMetadata = new ProviderMetadata($metadata);
                $this->cacheProviderMetadata($url, $providerMetadata);
            } else if ($cached !== null) {
                $metadata = $cached;
            }

        } catch (Zend\Http\Exception\RuntimeException $ex) {
            if ($cached !== null) {
                $metadata = $cached;
            }
        } catch (Zend\Http\Client\Exception\RuntimeException $ex) {
            if ($cached !== null) {
                $metadata = $cached;
            }
        } catch (Exception $ex) {
            if ($cached !== null) {
                $metadata = $cached;
            }
        }

        return $providerMetadata === null ? array () : json_decode($providerMetadata->getMetadata(), true);
    }

    private function cacheProviderMetadata($url, ProviderMetadata $metadata) {
        if (empty($this->_cache)) {
            return null;
        }
        $this->_cache->addKey(md5($url), $metadata);
    }

    private function getCachedProviderMetadata($url) {
        if (empty($this->_cache)) {
            return null;
        }
        return $this->_cache->getKey(md5($url));
    }

    private function getDiscoveryQueryParams(DiscoveryOptions $options) {
        return array (
            Parameters::MSISDN => ltrim($options->getMSISDN(), '+'),
            Parameters::REDIRECT_URL => $options->getRedirectUrl(),
            Parameters::IDENTIFIED_MCC => $options->getIdentifiedMCC(),
            Parameters::IDENTIFIED_MNC => $options->getIdentifiedMNC(),
            Parameters::SELECTED_MCC => $options->getSelectedMCC(),
            Parameters::SELECTED_MNC => $options->getSelectedMNC(),
            Parameters::LOCAL_CLIENT_IP => $options->getLocalClientIp(),
            Parameters::USING_MOBILE_DATA => $options->isUsingMobileData() ? "1" : "0"
        );
    }

    public function getOperatorSelectionURL($clientId, $clientSecret, $discoveryUrl, $redirectUrl) {
        $options = new DiscoveryOptions();
        $options->setRedirectUrl($redirectUrl);
        return $this->callDiscoveryEndpoint($clientId, $clientSecret, $discoveryUrl, $options, false, null);
    }

    public function getOperatorSelectionURLByPreferences($redirectUrl, IPreferences $preferences = null) {
        ValidationUtils::validateParameter($preferences, "preferences");
        return $this->GetOperatorSelectionURL($preferences->getClientId(), $preferences->getClientSecret(),
            $preferences->getDiscoveryUrl(), $redirectUrl);
    }

    public function parseDiscoveryRedirect($redirectUrl) {
        ValidationUtils::validateParameter($redirectUrl, "redirectUrl");
        parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $query);
        if (empty($query)) {
            return new ParsedDiscoveryRedirect(null, null, null);
        }
        $mcc_mnc = $query[Parameters::MCC_MNC];
        $encryptedMSISDN = isset($query[Parameters::SUBSCRIBER_ID]) ? $query[Parameters::SUBSCRIBER_ID] : null;

        $mcc = null;
        $mnc = null;
        if (!empty($mcc_mnc)) {
            $parts = explode('_', $mcc_mnc);
            if (count($parts) == 2) {
                $mcc = $parts[0];
                $mnc = $parts[1];
            }
        }
        return new ParsedDiscoveryRedirect($mcc, $mnc, $encryptedMSISDN);
    }

    public function completeSelectedOperatorDiscovery($clientId, $clientSecret, $discoveryUrl,
        $redirectUrl, $selectedMCC, $selectedMNC) {
        ValidationUtils::validateParameter($selectedMCC, "selectedMCC");
        ValidationUtils::validateParameter($selectedMNC, "selectedMNC");
        $discoveryOptions = new DiscoveryOptions();
        $discoveryOptions->setRedirectUrl($redirectUrl);
        $discoveryOptions->setSelectedMCC($selectedMCC);
        $discoveryOptions->setSelectedMNC($selectedMNC);

        return $this->callDiscoveryEndpoint($clientId, $clientSecret, $discoveryUrl, $discoveryOptions, true, null);
    }

    public function completeSelectedOperatorDiscoveryByPreferences(
        $redirectUrl, $selectedMCC, $selectedMNC, IPreferences $preferences = null) {
            ValidationUtils::validateParameter($preferences, "preferences");
            return $this->completeSelectedOperatorDiscovery($preferences->getClientId(), $preferences->getClientSecret(),
                $preferences->getDiscoveryUrl(), $redirectUrl, $selectedMCC, $selectedMNC);
        }

    public function extractOperatorSelectionURL(DiscoveryResponse $result) {
        $data = $result->getResponseData();
        if (isset($data['links'][0]['href'])) {
            return $data['links'][0]['href'];
        }
    }

    public function getCachedDiscoveryResult($mcc, $mnc) {
        return empty($this->_cache) ? null : $this->_cache->get($mcc, $mnc);
    }

    public function clearDiscoveryCache($mcc = null, $mnc = null) {
        if (empty($this->_cache)) {
            return;
        }
        if (!empty($mcc) && !empty($mnc)) {
            $this->_cache->remove($mcc, $mnc);
            return;
        }
        $this->_cache->clear();
    }

    private function getCachedValue($options) {
        $mcc = $options->getSelectedMCC();
        if (!empty($options->getIdentifiedMCC())) {
            $mcc = $options->getIdentifiedMCC();
        }

        $mnc = $options->getSelectedMNC();
        if (!empty($options->getIdentifiedMNC())) {
            $mnc = $options->getIdentifiedMNC();
        }

        return empty($this->_cache) ? null : $this->_cache->get($mcc, $mnc);
    }

    private function addCachedValue(DiscoveryOptions $options, DiscoveryResponse $response) {
        if (!empty($this->_cache)) {

            $mcc = $options->getSelectedMCC();
            if (!empty($options->getIdentifiedMCC())) {
                $mcc = $options->getIdentifiedMCC();
            }

            $mnc = $options->getSelectedMNC();
            if (!empty($options->getIdentifiedMNC())) {
                $mnc = $options->getIdentifiedMNC();
            }
            if (!empty($response->getErrorResponse()) || empty($mcc) || empty($mnc)) {
                return;
            }

            $this->_cache->add($mcc, $mnc, $response);
        }
    }
}
