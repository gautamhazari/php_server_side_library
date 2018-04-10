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

use MCSDK\Discovery\IPreferences;

/**
 * Class to hold the configuration for Mobile Connect.
 *
 * Only clientId, clientSecret, applicationURL, discoveryUrl and
 * discoveryRedirectURL are required everything else is optional.
 * Typically authorization state and authorization nonce would be set
 *
 * This class encapsulates all the parameters (including optional parameters)
 * for the Mobile Connect SDK in to a single object. Methods in the class can
 * then be used to create the optional parameter objects for the methods in the
 * SDK.
 */
class MobileConnectConfig implements IPreferences
{

    // Required
    private $clientId;
    private $clientSecret;
    private $discoveryUrl;
    private $redirectUrl;
    private $cacheResponsesWithSessionId;

    public function __construct() {
        $this->cacheResponsesWithSessionId = true;
    }
    /**
     * Get the registered Mobile Connect client id.
     *
     * Required.
     *
     * @return string The client id.
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set the registered Mobile Connect client id.
     *
     * Required.
     *
     * @param string $clientId The client id.
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Get the registered Mobile Connect client secret.
     *
     * Required.
     *
     * @return string The client secret.
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Set the registered Mobile Connect client secret.
     *
     * Required.
     *
     * @param string $clientSecret The client secret.
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * Get the URL of the Mobile Connect Discovery Service end point.
     *
     * Required
     *
     * @return string The Discovery Service end point URL.
     */
    public function getDiscoveryUrl()
    {
        return $this->discoveryUrl;
    }

    /**
     * Set the URL of the Mobile Connect Discovery Service end point.
     *
     * Required.
     *
     * @param string $discoveryUrl The Discovery Service end point URL.
     */
    public function setDiscoveryUrl($discoveryUrl)
    {
        $this->discoveryUrl = $discoveryUrl;
    }

    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function getCacheResponsesWithSessionId() {
        return $this->cacheResponsesWithSessionId;
    }

    public function setCacheResponsesWithSessionId($value) {
        $this->cacheResponsesWithSessionId = $value;
    }
}
