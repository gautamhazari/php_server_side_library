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

namespace MCSDK\Authentication;

use MCSDK\Constants;
use MCSDK\Utils\ValidationUtils;
use MCSDK\Constants\LoginHintPrefixes;
use MCSDK\Discovery\SupportedVersions;

/**
 * Utility methods for working with login hints for the auth login hint parameter
 */
class LoginHint {
    private static $_recognizedHints = array (LoginHintPrefixes::EncryptedMSISDN, LoginHintPrefixes::MSISDN, LoginHintPrefixes::PCR);
    public static $_defaultVersions;

    public static function IsSupportedForMSISDN($metadata) {
        return static::IsSupportedFor($metadata, LoginHintPrefixes::MSISDN);
    }

    public static function IsSupportedForEncryptedMSISDN($metadata) {
        return static::IsSupportedFor($metadata, LoginHintPrefixes::EncryptedMSISDN);
    }

    public static function IsSupportedForPCR($metadata) {
        return static::IsSupportedFor($metadata, LoginHintPrefixes::PCR);
    }

    public static function IsSupportedFor($metadata, $prefix) {
        static::$_defaultVersions = new SupportedVersions(null);
        if (empty($metadata) || !isset($metadata['login_hint_methods_supported']) || count($metadata['login_hint_methods_supported']) == 0) {

            $supportedVersions = static::$_defaultVersions;
            if (!empty($metadata) && isset($metadata['mobile_connect_version_supported'])) {
                $supportedVersions = $metadata['mobile_connect_version_supported'];
            }
            if (array_search(strtolower($prefix), array_map('strtolower', static::$_recognizedHints)) === false) {
                return false;
            }

            if ($supportedVersions->IsVersionSupported("1.2")) {
                return true;
            }

            if ($prefix != LoginHintPrefixes::EncryptedMSISDN && $prefix != LoginHintPrefixes::MSISDN) {
                return false;
            }
            return true;
        }
        $result = array_search(strtolower($prefix), array_map('strtolower', $metadata['login_hint_methods_supported']));
        return $result !== false;
    }

    public static function GenerateForMSISDN($msisdn) {
        ValidationUtils::validateParameter($msisdn, "msisdn");
        return static::GenerateFor(LoginHintPrefixes::MSISDN, ltrim($msisdn, '+'));
    }

    public static function GenerateForEncryptedMSISDN($encryptedMSISDN) {
        ValidationUtils::validateParameter($encryptedMSISDN, "encryptedMSISDN");
        return static::GenerateFor(LoginHintPrefixes::EncryptedMSISDN, ltrim($encryptedMSISDN, '+'));
    }

    public static function GenerateForPCR($pcr) {
        ValidationUtils::validateParameter($pcr, "pcr");
        return static::GenerateFor(LoginHintPrefixes::PCR, $pcr);
    }

    public static function GenerateFor($prefix, $value) {
        if (empty($prefix) || empty($value)) {
            return null;
        }
        return $prefix .":" . $value;
    }
}

LoginHint::$_defaultVersions = new SupportedVersions(null);
