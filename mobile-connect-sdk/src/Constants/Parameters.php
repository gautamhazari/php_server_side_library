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

class Parameters
{
    //Required param for discovery
    const REDIRECT_URL = "Redirect_URL";

    //Optional params for discovery
    const MANUALLY_SELECT = "Manually-Select";
    const IDENTIFIED_MCC = "Identified-MCC";
    const IDENTIFIED_MNC = "Identified-MNC";
    const SELECTED_MCC = "Selected-MCC";
    const SELECTED_MNC = "Selected-MNC";
    const USING_MOBILE_DATA = "Using-Mobile-Data";
    const LOCAL_CLIENT_IP = "Local-Client-IP";
    const MSISDN = "MSISDN";

    const MCC_MNC = "mcc_mnc";
    const SUBSCRIBER_ID = "subscriber_id";

    //Required params for authentication
    const CLIENT_ID = "client_id";
    const RESPONSE_TYPE = "response_type";
    const AUTHENTICATION_REDIRECT_URI = "redirect_uri";
    const SCOPE = "scope";
    const ACR_VALUES = "acr_values";
    const STATE = "state";
    const NONCE = "nonce";
    const VERSION = "version";
    const LOGIN_TOKEN_HINT="login_token_hint";

    //Optional params for authentication
    const DISPLAY = "display";
    const PROMPT = "prompt";
    const MAX_AGE = "max_age";
    const UI_LOCALES = "ui_locales";
    const CLAIMS_LOCALES = "claims_locales";
    const ID_TOKEN_HINT = "id_token_hint";
    const LOGIN_HINT = "login_hint";
    const DTBS = "dtbs";
    const CLAIMS = "claims";

    //Required params for authorization
    const CLIENT_NAME = "client_name";
    const CONTEXT = "context";
    const BINDING_MESSAGE = "binding_message";

    //Params for AuthorizationResponse
    const ERROR = "error";
    const ERROR_DESCRIPTION = "error_description";
    const ERROR_URI = "error_uri";
    const CODE = "code";

    //Params for Token
    const GRANT_TYPE = "grant_type";

    const REFRESH_TOKEN = "refresh_token";
    const TOKEN = "token";
    const TOKEN_TYPE_HINT = "token_type_hint";

    const ACCESS_TOKEN_HINT = "access_token";
    const REFRESH_TOKEN_HINT = "refresh_token";
}

