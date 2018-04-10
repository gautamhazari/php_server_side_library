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

use MCSDK\Utils\RestResponse;
use MCSDK\Identity\IdentityService;

class IdentityServiceTest extends PHPUnit_Framework_TestCase {
    private $_unauthorizedResponse;
    private $_responses;
    private $_restClient;
    private $_identityService;

    public function __construct() {
        $this->_unauthorizedResponse = new RestResponse(401, "");
        $this->_unauthorizedResponse->setHeaders(array (
            "WWW-Authenticate-Bearer error=\"invalid_request\", error_description=\"No Access Token\""
        ));
        $this->_responses['user-info'] = new RestResponse(200, "{\"sub\":\"411421B0-38D6-6568-A53A-DF99691B7EB6\",\"email\":\"test2@example.com\",\"email_verified\":true}");
        $this->_responses['unauthorized'] = $this->_unauthorizedResponse;

        $this->_restClient = new MockRestClient();
        $this->_identityService = new IdentityService($this->_restClient);
    }

    public function testRequestUserInfoShouldHandleUserInfoResponse()
    {
        $response = $this->_responses["user-info"];
        $this->_restClient->queueResponse($response);

        $result = $this->_identityService->RequestUserInfo("user info url", "zmalqpxnskwocbdjeivbfhru");

        $this->assertNotNull($result);
        $this->assertEquals(200, $result->getResponseCode());
        $this->assertNotNull($result->getResponseJson());
    }
/*
    public function RequestUserInfoShouldHandleHttpRequestException()
    {
        _restClient.NextException = new System.Net.Http.HttpRequestException("This is the message");

        Assert.ThrowsAsync<MobileConnectEndpointHttpException>(() => _identityService.RequestUserInfo("user info url", "zmalqpxnskwocbdjeivbfhru"));
    }

    public function RequestUserInfoShouldHandleWebRequestException()
    {
        _restClient.NextException = new System.Net.WebException("This is the message");

        Assert.ThrowsAsync<MobileConnectEndpointHttpException>(() => _identityService.RequestUserInfo("user info url", "zmalqpxnskwocbdjeivbfhru"));
    }

    #region Argument Validation

    public function RequestUserInfoShouldThrowWhenUserInfoUrlNull()
    {
        Assert.ThrowsAsync<MobileConnectInvalidArgumentException>(() => _identityService.RequestUserInfo(null, "zmalqpxnskwocndjeivbfhru"));
    }

    public function RequestUserInfoShouldThrowWhenAccessTokenNull()
    {
        Assert.ThrowsAsync<MobileConnectInvalidArgumentException>(() => _identityService.RequestUserInfo("user info url", null));
    }

    #endregion*/
}

