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
    public const DISCOVERY_URL = "discoveryURL";
    public const REDIRECT_URL = "redirectURL";
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

