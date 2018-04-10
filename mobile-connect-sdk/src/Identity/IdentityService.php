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

namespace MCSDK\Identity;
use MCSDK\Utils\RestAuthentication;
use MCSDK\Utils\ValidationUtils;
use MCSDK\Utils\RestResponse;
use MCSDK\Utils\RestClient;

/**
 * Implemnetation of IIdentityService
 */
class IdentityService implements IIdentityService
{
    private $_client;

    /**
     * Creates a new instance of the class IdentityService using the specified RestClient for all HTTP requests
     */
    public function __construct(RestClient $client)
    {
        $this->_client = $client;
    }

    public function RequestIdentity($premiumInfoUrl, $accessToken)
    {
        ValidationUtils::validateParameter($premiumInfoUrl, "premiumInfoUrl");
        return $this->RequestUserInfo($premiumInfoUrl, $accessToken);
    }

    public function RequestUserInfo($userInfoUrl, $accessToken)
    {
        ValidationUtils::validateParameter($userInfoUrl, "userInfoUrl");
        ValidationUtils::validateParameter($accessToken, "accessToken");

        try
        {
            $response = new RestResponse();
            $auth = RestAuthentication::Bearer($accessToken);
            $response = $this->_client->get($userInfoUrl, $auth, null, null, null);
            return new IdentityResponse($response);
        }
        catch (Exception $ex)
        {
            throw new MobileConnectEndpointHttpException($ex->getMessage(), $ex);
        }
    }
}
