<?php

/**
 *                          SOFTWARE USE PERMISSION
 *
 *  By downloading and accessing this software and associated documentation
 *  files ("Software") you are granted the unrestricted right to deal in the
 *  Software, $including, $without limitation the right to use, $copy, $modify,
 *  publish, $sublicense and grant such rights to third parties, $subject to the
 *  following conditions:
 *
 *  The following copyright notice and this permission notice shall be included
 *  in all copies, $modifications or substantial portions of this Software:
 *  Copyright © 2016 GSM Association.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS," WITHOUT WARRANTY OF ANY KIND, $INCLUDING
 *  BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, $FITNESS FOR A
 *  PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, $DAMAGES OR OTHER LIABILITY,
 *  WHETHER IN AN ACTION OF CONTRACT, $TORT OR OTHERWISE, $ARISING FROM, $OUT OF OR
 *  IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE. YOU AGREE TO INDEMNIFY AND HOLD HARMLESS THE AUTHORS AND COPYRIGHT
 *  HOLDERS FROM AND AGAINST ANY SUCH LIABILITY.
 */

use MCSDK\MobileConnectRequestOptions;
use MCSDK\Discovery\DiscoveryOptions;

class MobileConnectRequestOptionsTest extends PHPUnit_Framework_TestCase
{
    public function testShouldFillDiscoveryOptions()
    {
        $isUsingMobileData = true;
        $localClientIp = "111.11.11.11";
        $clientIp = "222.22.22.22";

        $actual = new MobileConnectRequestOptions();
        $actual->setIsUsingMobileData($isUsingMobileData);
        $actual->setLocalClientIP($localClientIp);
        $actual->setClientIP($clientIp);

        $this->assertEquals($isUsingMobileData, $actual->getIsUsingMobileData());
        $this->assertEquals($localClientIp, $actual->getLocalClientIP());
        $this->assertEquals($clientIp, $actual->getClientIP());
    }

    public function testShouldFillAuthenticationOptions()
    {
        $display = "display type";
        $prompt = "prompt";
        $uiLocales = "ui locales";
        $claimsLocales = "claims locales";
        $idTokenHint = "id token";
        $loginHint = "login hint";
        $dtbs = "data to be sent";
        $scope = "scope value";
        $acr = "acr value";
        $maxAge = 1200;
        $claimsJson = "claims json";
        $claims = array ();

        $actual = new MobileConnectRequestOptions();
        $actual->setDisplay($display);
        $actual->setPrompt($prompt);
        $actual->setUiLocales($uiLocales);
        $actual->setClaimsLocales($claimsLocales);
        $actual->setIdTokenHint($idTokenHint);
        $actual->setLoginHint($loginHint);
        $actual->setDtbs($dtbs);
        $actual->setScope($scope);
        $actual->setAcrValues($acr);
        $actual->setMaxAge($maxAge);
        $actual->setClaimsJson($claimsJson);
        $actual->setClaims($claims);

        $this->assertEquals($display, $actual->getDisplay());
        $this->assertEquals($prompt, $actual->getPrompt());
        $this->assertEquals($uiLocales, $actual->getUiLocales());
        $this->assertEquals($claimsLocales, $actual->getClaimsLocales());
        $this->assertEquals($idTokenHint, $actual->getIdTokenHint());
        $this->assertEquals($loginHint, $actual->getLoginHint());
        $this->assertEquals($dtbs, $actual->getDtbs());
        $this->assertEquals($scope, $actual->getScope());
        $this->assertEquals($acr, $actual->getAcrValues());
        $this->assertEquals($maxAge, $actual->getMaxAge());
        $this->assertEquals($claimsJson, $actual->getClaimsJson());
        $this->assertEquals($claims, $actual->getClaims());
    }
}

