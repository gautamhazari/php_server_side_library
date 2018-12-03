<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 14.11.18
 * Time: 16.13
 */

namespace App\Http\Constants;

use MCSDK\Constants\Scope;

class Constants
{
    public const MSISDN = "msisdn";
    public const MCC = "mcc";
    public const MNC = "mnc";
    public const SOURCE_IP = "sourceIp";
    public const SUB_ID = "subscriber_id";
    public const MCC_MNC = "mcc_mnc";

    public const ERROR = "error";
    public const ERROR_DESCR = "error_description";

    public const CODE = "code";
    public const STATE = "state";
    public const ACCESS_TOKEN = "access_token";

    public const CLIENT_ID = "clientID";
    public const CLIENT_SECRET = "clientSecret";
    public const DISCOVERY_URL = "discoveryURL";
    public const REDIRECT_URL = "redirectURL";
    public const X_REDIRECT = "xRedirect";
    public const INCLUDE_REQ_IP = "includeRequestIP";
    public const API_VERS = "apiVersion";
    public const CLIENT_NAME = "clientName";
    public const SCOPES = "scopes";
    public const CONTEXT = "context";
    public const BIND_MSG = "binding_message";
    public const AUTH_URL = "authURL";
    public const TOKEN_URL = "tokenURL";
    public const USERINFO_URL = "userInfoURl";
    public const PREMIUMINFO_URL = "premiumInfoURl";
    public const METADATA_URL = "metadataURl";

    public const REDIRECT_STATUS = "REDIRECT_STATUS";
    public const CONFIG_DIR_NAME = "data";

    public const SPACE = " ";
    public const OR = " or ";



    public const SECTOR_IDENTIFIER_PATH = "sector_identifier_uri.json";
    public const KYC_CLAIMS_PATH = "kycClaims.json";
    public const DATA_PATH = "data.json";
    public const WD_DATA_PATH = "withoutDiscoveryData.json";

    public const IDENTITY_SCOPES = array(Scope::IDENTITY_PHONE, Scope::IDENTITY_SIGNUP,
            Scope::IDENTITY_NATIONALID, Scope::IDENTITY_SIGNUPPLUS, Scope::KYC_HASHED, Scope::KYC_PLAIN);
    public const USERINFO_SCOPES = array(Scope::PROFILE, Scope::EMAIL, Scope::ADDRESS,
            Scope::PHONE, Scope::OFFLINE_ACCESS);

}

