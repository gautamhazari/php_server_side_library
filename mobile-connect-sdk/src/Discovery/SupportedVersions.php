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

namespace MCSDK\Discovery;
use MCSDK\MobileConnectConstants;
use MCSDK\Utils\MobileConnectVersions;

/**
 * Storage for supported mobile connect versions in ProviderMetadata.MobileConnectVersionSupported
 */
class SupportedVersions
{
    private $_recognizedScopes;
    private $_maxSupportedVersion;
    private static $_r1Version;

    public function __construct($versionSupport = null) {
        $this->_initialValuesDict = empty($versionSupport) ? array () : $versionSupport;
        $this->_recognizedScopes = array (
            MobileConnectConstants::MOBILECONNECT,
            MobileConnectConstants::MOBILECONNECTAUTHENTICATION,
            MobileConnectConstants::MOBILECONNECTAUTHORIZATION,
            MobileConnectConstants::MOBILECONNECTIDENTITYNATIONALID,
            MobileConnectConstants::MOBILECONNECTIDENTITYPHONE,
            MobileConnectConstants::MOBILECONNECTIDENTITYSIGNUP,
            MobileConnectConstants::MOBILECONNECTIDENTITYSIGNUPPLUS
        );
        $this->_maxSupportedVersion = $this->IdentifyMaxSupportedVersion($this->_initialValuesDict);
    }

    public function getMaxSupportedVersion() {
        return $this->_maxSupportedVersion;
    }

    public static function getR1Version() {
        return static::$_r1Version;
    }

    public static function setR1Version() {
        static::$_r1Version = "1.1";
    }

    public function InitialValues() {
        return $this->_initialValuesDict;
    }

    public function getInitialValues() {
        return $this->_initialValuesDict;
    }
    /**
     * Gets the available mobile connect version for the specified scope value.
     * If versions aren't available then configured default versions will be used.
     * @param string $scope Scope value to retrieve supported version for
     */
    public function GetSupportedVersion($scope) {
        $version = $this->getValue($scope);
        if (empty($version)) {
            $version = $this->getValue(MobileConnectConstants::MOBILECONNECT);
        }
        return MobileConnectVersions::CoerceVersion($version, $scope);
    }

    private static function IdentifyMaxSupportedVersion($versionSupport) {
        $max = static::GetAsVersion(MobileConnectVersions::CoerceVersion(null, MobileConnectConstants::MOBILECONNECT));
        foreach($versionSupport as $key => $value) {
            if (is_array($value)) {
                $v = current($value);
                $version = static::GetAsVersion($v);
            } else {
                $version = static::GetAsVersion($value);
            }
            if ($version > $max) {
                $max = $version;
            }
        }
        return $max;
    }

    private function getValue($scope) {
        $result = null;
        for ($i = 0; $i < count($this->_initialValuesDict); $i++) {
            foreach($this->_initialValuesDict[$i] as $key => $value) {
                if ($key === $scope) {
                    return $value;
                }
            }
        }
        return $result;
    }

    public function IsVersionSupported($version) {
        if (empty($version)) {
            return false;
        }
        $trueVersion = $this->getAsVersion($version);
        return $this->_maxSupportedVersion >= $trueVersion;
    }

    public static function GetAsVersion($version) {
        if (preg_match('/\d+(?:\.\d+)+/', $version, $matches)) {
           return $matches[0];
        }
        return null;
    }
}

SupportedVersions::setR1Version();
