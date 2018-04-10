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

use MCSDK\Discovery\SupportedVersions;

class SupportedVersionsTest extends PHPUnit_Framework_TestCase {

    public function testGetSupportedVersionShouldReturnFirstVersionForScope() {
        $versions = new SupportedVersions([["test" => "mc_v1.2"], ["test" => "mc_v1.1"]]);
        $actual = $versions->GetSupportedVersion("test");
        $expected = "mc_v1.2";
        $this->assertEquals($expected, $actual);
    }

    public function testGetSupportedVersionShouldReturnVersionForScope()
    {
        $versions = new SupportedVersions([["openid" => "1"], ["test" => "2"]]);
        $expected = "2";

        $actual = $versions->GetSupportedVersion("test");

        $this->assertEquals($expected, $actual);
    }

    public function testGetSupportedVersionShouldReturnVersionForOpenidIfScopeNotFound()
    {
        $versions = new SupportedVersions([["openid" => "1"], ["test2" => "2"]]);
        $expected = "1";

        $actual = $versions->GetSupportedVersion("test");

        $this->assertEquals($expected, $actual);
    }

    public function testGetSupportedVersionShouldReturnVersionFromDefaultVersionsIfOpenidScopeNotFound()
    {
        $versions = new SupportedVersions(null);
        $expected = "mc_v1.2";

        $actual = $versions->GetSupportedVersion("openid mc_authz");
        $this->assertEquals($expected, $actual);
    }
}
