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

use MCSDK\Utils\MobileConnectResponseType;
use MCSDK\MobileConnectStatus;

/**
 * Helper class to convert from a heavyweight MobileConnectStatus instance to a Lightweight serializable MobileConnectWebResponse instance
 */
class ResponseConverter
{
    const STATUS_SUCCESS = "success";
    const STATUS_FAILURE = "failure";

    private static $_actionDict = array(
        MobileConnectResponseType::Error => "error",
        MobileConnectResponseType::OperatorSelection => "operator_selection",
        MobileConnectResponseType::StartDiscovery => "discovery",
        MobileConnectResponseType::StartAuthentication => "start_authentication",
        MobileConnectResponseType::Authentication => "authentication",
        MobileConnectResponseType::Complete => "complete",
        MobileConnectResponseType::UserInfo => "user_info",
        MobileConnectResponseType::Identity => "identity",
        MobileConnectResponseType::TokenRevoked => "token_revoked",
    );

    /**
     * Convert to lightweight serializable MobileConnectWebResponse
     * @param Input status instance
     * @return Serializable response instanceof
     */
    public static function Convert(MobileConnectStatus $status)
    {
        $response = new MobileConnectWebResponse();
        $response->setStatus(($status->getResponseType() == MobileConnectResponseType::Error) ? static::STATUS_FAILURE : static::STATUS_SUCCESS);
        $response->setAction(static::$_actionDict[$status->getResponseType()]);
        if (!empty($status->getDiscoveryResponse())) {
            $response->setApplicationShortName($status->getDiscoveryResponse()->getApplicationShortName());
        }
        $response->setNonce($status->getNonce());
        $response->setState($status->getState());
        $response->setUrl($status->getUrl());
        $response->setSdkSession($status->getSDKSession());

        if ($status->getDiscoveryResponse() !== null) {
            if ($status->getDiscoveryResponse()->getResponseData() !== null) {
                $data = $status->getDiscoveryResponse()->getResponseData();
                if (isset($data["subscriber_id"])) {
                    $response->setSubscriberId($data["subscriber_id"]);
                }
            }
        }
        if (!empty($status->getTokenResponse())) {
            $response->setToken($status->getTokenResponse()->getResponseData());
        }
        if (!empty($status->getIdentityResponse()) && !empty($status->getIdentityResponse()->getResponseData())) {
            $response->setIdentity($status->getIdentityResponse()->getResponseData());
        }

        if($status->getResponseType() == MobileConnectResponseType::Error)
        {
            $response->setError($status->getErrorCode());
            $response->setDescription($status->getErrorMessage());
        }
        return $response;
    }
}

