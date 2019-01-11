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

class Scope
{
    const OPENID = "openid";
    const AUTHN = "mc_authn";
    const AUTHZ = "mc_authz";
    const KYC = "mc_kyc";

    const PROFILE = "profile";
    const EMAIL = "email";
    const ADDRESS = "address";
    const PHONE = "phone";
    const OFFLINE_ACCESS = "offline_access";

    const IDENTITY_PHONE = "mc_identity_phonenumber";
    const IDENTITY_SIGNUP = "mc_identity_signup";
    const IDENTITY_SIGNUPPLUS = "mc_identity_signupplus";
    const IDENTITY_NATIONALID = "mc_identity_nationalid";

    const KYC_PLAIN = "mc_kyc_plain";
    const KYC_HASHED = "mc_kyc_hashed";
    const ATTR_VM_MATCH = "mc_attr_vm_match";
    const ATTR_VM_MATCH_HASH = "mc_attr_vm_match_hash";

    const MC_INDIA_TC = "mc_india_tc";
    const MC_MNV_VALIDATE = "mc_mnv_validate";
    const MC_MNV_VALIDATE_PLUS = "mc_mnv_validate_plus";
    const MC_ATTR_VM_SHARE = "mc_attr_vm_share";
    const MC_ATTR_VM_SHARE_HASH = "mc_attr_vm_share_hash";

    const MCPREFIX = "mc_";
}


