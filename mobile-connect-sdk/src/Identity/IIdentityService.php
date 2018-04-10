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

/**
 * Interface for Mobile Connect UserInfo and Identity related requests
 */
interface IIdentityService {
    /**
     * Request the user info for the provided access token. Some of the information returned by the user info service requires the authorization/authentication to be
     * executed with additional scope values e.g. email => openid email
     * @param $userInfoUrl Url for accessing user info (Returned in discovery response)
     * @param $accessToken Access token for authorising user info request
     * @return UserInfo object if request succeeds
     */
    public function RequestUserInfo($userInfoUrl, $accessToken);

    /**
     * Request the identity for the provided access token. Information returned by the identity service requires the authorization to be
     * executed with additional scope values e.g. phone number <see cref="MobileConnectConstants.MOBILECONNECTIDENTITYPHONE"/>
     * @param $premiumInfoUrl Url for accessing premium info identity services (Returned in discovery response)
     * @param $accessToken Access token for authorising identity request
     * @return UserInfo object if request succeeds
     */
    public function RequestIdentity($premiumInfoUrl, $accessToken);
}