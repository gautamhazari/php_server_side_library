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

require_once(dirname(__FILE__) . '/../MockRestClient.php');

use MCSDK\Constants;
use MCSDK\Utils\ValidationUtils;
use MCSDK\Constants\LoginHintPrefixes;
use MCSDK\Authentication\LoginHint;
use MCSDK\Discovery\SupportedVersions;

class LoginHintTest extends PHPUnit_Framework_TestCase {
    public function testIsSupportedMSISDNShouldReturnTrueIfMSISDNIncluded()
    {
        $metadata = $this->GetMetadataWithSupportedLoginHint(LoginHintPrefixes::MSISDN);

        $actual = LoginHint::IsSupportedForMSISDN($metadata);

        $this->assertTrue($actual);
    }
    public function testIsSupportedMSISDNShouldReturnFalseIfMSISDNNotIncluded() {
        $metadata = $this->GetMetadataWithSupportedLoginHint(LoginHintPrefixes::PCR);
        $actual = LoginHint::IsSupportedForMSISDN($metadata);

        $this->assertFalse($actual);
    }

    public function testIsSupportedEncryptedMSISDNShouldReturnTrueIfEncryptedMSISDNIncluded() {
        $metadata = $this->GetMetadataWithSupportedLoginHint(LoginHintPrefixes::EncryptedMSISDN);
        $actual = LoginHint::IsSupportedForEncryptedMSISDN($metadata);

        $this->assertTrue($actual);
    }

    public function testIsSupportedEncryptedMSISDNShouldReturnFalseIfEncryptedMSISDNNotIncluded() {
        $metadata = $this->GetMetadataWithSupportedLoginHint(LoginHintPrefixes::PCR);
        $actual = LoginHint::IsSupportedForEncryptedMSISDN($metadata);

        $this->assertFalse($actual);
    }

    public function testIsSupportedPCRShouldReturnTrueIfPCRIncluded() {
        $metadata = $this->GetMetadataWithSupportedLoginHint(LoginHintPrefixes::PCR);
        $actual = LoginHint::IsSupportedForPCR($metadata);

        $this->assertTrue($actual);
    }

    public function testIsSupportedPCRShouldReturnFalseIfPCRNotIncluded() {
        $metadata = $this->GetMetadataWithSupportedLoginHint(LoginHintPrefixes::MSISDN);

        $actual = LoginHint::IsSupportedForPCR($metadata);

        $this->assertFalse($actual);
    }

    public function testIsSupportedMSISDNShouldReturnTrueIfMissingMetadata() {
        $actual = LoginHint::IsSupportedForMSISDN(null);

        $this->assertTrue($actual);
    }

    public function testIsSupportedEncryptedMSISDNShouldReturnTrueIfMissingMetadata()
    {
        $actual = LoginHint::IsSupportedForEncryptedMSISDN(null);

        $this->assertTrue($actual);
    }

    public function testIsSupportedPCRShouldReturnFalseIfMissingMetadata()
    {
        $actual = LoginHint::IsSupportedForPCR(null);

        $this->assertFalse($actual);
    }

    public function testIsSupportedPCRShouldReturnTrueIfSupportedVersionIs1_2()
    {
        $metadata = array ();
        $metadata['mobile_connect_version_supported'] = new SupportedVersions(array ("openid" => "mc_v1.2"));
        $metadata['login_hint_methods_supported'] = array ();

        $actual = LoginHint::IsSupportedForMSISDN($metadata);

        $this->assertTrue($actual);
    }

    public function testIsSupportedForShouldReturnFalseIfUnrecognisedPrefixAndMissingMetadata()
    {
        $actual = LoginHint::IsSupportedFor(null, "testprefix");

        $this->assertFalse($actual);
    }

    public function testIsSupportedForShouldBeCaseInsensitive()
    {
        $metadata = $this->GetMetadataWithSupportedLoginHint("MSISDN");

        $actual = LoginHint::IsSupportedFor($metadata, "MsIsDn");

        $this->assertTrue($actual);
    }

    public function testGenerateForMSISDNShouldGenerateCorrectFormat()
    {
        $actual = LoginHint::GenerateForMSISDN("+447700900250");

        $this->assertEquals("MSISDN:447700900250", $actual);
    }

    public function testGenerateForEncryptedMSISDNShouldGenerateCorrectFormat()
    {
        $actual = LoginHint::GenerateForEncryptedMSISDN("zmalqpwoeirutyfhdjskaslxzmxncbv");

        $this->assertEquals("ENCR_MSISDN:zmalqpwoeirutyfhdjskaslxzmxncbv", $actual);
    }

    public function testGenerateForPCRShouldGenerateCorrectFormat()
    {
        $actual = LoginHint::GenerateForPCR("zmalqpwoeirutyfhdjskaslxzmxncbv");

        $this->assertEquals("PCR:zmalqpwoeirutyfhdjskaslxzmxncbv", $actual);

    }

    public function testGenerateForShouldReturnNullWhenValueNull()
    {
        $this->assertNull(LoginHint::GenerateFor("PCR", null));
    }

    public function testGenerateForShouldReturnNullWhenValueEmpty()
    {
        $this->assertNull(LoginHint::GenerateFor("PCR", null));
    }

    public function testGenerateForShouldReturnNullWhenPrefixNull()
    {
        $this->assertNull(LoginHint::GenerateFor(null, "testvalue"));
    }

    public function testGenerateForShouldReturnNullWhenPrefixEmpty()
    {
        $this->assertNull(LoginHint::GenerateFor("", "testvalue"));
    }

    private function GetMetadataWithSupportedLoginHint($supported)
    {
        $providerMetadata = array ();
        $providerMetadata['login_hint_methods_supported'] = array ($supported);
        return $providerMetadata;
    }
}
