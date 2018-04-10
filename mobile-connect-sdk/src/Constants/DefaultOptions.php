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

namespace MCSDK\Constants;
use MCSDK\Constants\Scope;
use MCSDK\Constants\GrantTypes;

class DefaultOptions
{
    const TIMEOUT = 300000;

    const MANUAL_SELECT = false;
    const COOKIES_ENABLED = true;

    const DISPLAY = "page";

    const CHECK_ID_TOKEN_SIGNATURE = true;

    const MIN_TTL_MS = 300000;
    const MAX_TTL_MS = 15552000000;

    const AUTHENTICATION_ACR_VALUES = "2";
    const AUTHENTICATION_SCOPE = Scope::OPENID;
    const AUTHENTICATION_MAX_AGE = 3600;
    const AUTHENTICATION_RESPONSE_TYPE = "code";
    const AUTHENTICATION_DEFAULT_VERSION = "mc_v1.1";

    const GRANT_TYPE = GrantTypes::AUTH_CODE;

    const PROVIDER_METADATA_TTL_SECONDS = 900;
    const JWKEYSET_TTL_SECONDS = 900;

    const VERSION_MOBILECONNECT = "mc_v1.1";
    const VERSION_MOBILECONNECTAUTHN = "mc_v1.1";
    const VERSION_MOBILECONNECTAUTHZ = "mc_v1.2";
    const VERSION_MOBILECONNECTIDENTITY = "mc_v1.2";
    const SDK_VERSION = "PHP-2.2.1";
}

