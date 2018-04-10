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
use MCSDK\Identity\IdentityResponse;

class IdentityResponseTest extends PHPUnit_Framework_TestCase {

    public function testConstructorShouldSetResponseJson()
    {
        $responseJson = "{\"sub\":\"411421B0-38D6-6568-A53A-DF99691B7EB6\",\"email\":\"test2@example.com\",\"email_verified\":true}";
        $response = new RestResponse(202, $responseJson);
        $actual = new IdentityResponse($response);

        $this->assertEquals($responseJson, $actual->getResponseJson());
    }

    public function testConstructorShouldSetResponseWithDecodedJWTPayload()
    {
        $responseJWT = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI0MTE0MjFCMC0zOEQ2LTY1NjgtQTUzQS1ERjk5NjkxQjdFQjYiLCJlbWFpbCI6InRlc3QyQGV4YW1wbGUuY29tIiwiZW1haWxfdmVyaWZpZWQiOnRydWV9.AcpILNH2Uvok99MQWwxP6X7x3OwtVmTOw0t9Hq00gmQ";
        $response = new RestResponse(202, $responseJWT);

        $actual = new IdentityResponse($response);
        $json = json_decode($actual->getResponseJson(), true);

        $this->assertNotNull($actual->getResponseJson());
        $this->assertEquals("411421B0-38D6-6568-A53A-DF99691B7EB6", $json["sub"]);
        $this->assertEquals("test2@example.com", $json["email"]);
        $this->assertEquals(true, $json["email_verified"]);
    }

    public function testConstructorShouldSetResponseWithNullContent()
    {
        $response = new RestResponse(202, null);
        $actual = new IdentityResponse($response);

        $this->assertNull($actual->getResponseJson());
    }

    public function testConstructorShouldSetErrorForInvalidFormatResponseData()
    {
        $responseJson = "<html>not valid</html>";
        $response = new RestResponse(202, $responseJson);
        $actual = new IdentityResponse($response);

        $this->assertNotNull($actual->getErrorResponse());
        $this->assertEquals("invalid_format", $actual->getErrorResponse()["error"]);
    }
/*
    public function ConstructorShouldSetErrorForAuthenticationError()
    {
        $response = new RestResponse(System.Net.HttpStatusCode.Unauthorized, "");
        response.Headers = new List<BasicKeyValuePair> { new BasicKeyValuePair("WWW-Authenticate", "Bearer error = \"invalid_request\", error_description = \"No Access Token\"") };

        $actual = new IdentityResponse(response);

        $this->assertNotNull($actual->getErrorResponse());
        $this->assertEquals("invalid_request", actual.ErrorResponse.Error);
        $this->assertEquals("No Access Token", actual.ErrorResponse.ErrorDescription);
    }
*/
    public function testResponseDataAsShouldDeserializeToUserInfoData()
    {
        $responseJson = "{\"sub\":\"411421B0-38D6-6568-A53A-DF99691B7EB6\",\"email\":\"test2@example.com\",\"email_verified\":true,\"phone_number\":\"+447700200200\",\r\n\"phone_number_verified\":true,\"birthdate\":\"1990-04-11\",\"updated_at\":\"1460779506\",\"address\":{\"formatted\":\"123 Fake Street Manchester\",\"postal_code\":\"M1 1AB\"}}";
        $response = new RestResponse(202, ($responseJson));

        $userInfoResponse = new IdentityResponse($response);
        $actual = $userInfoResponse->getResponseData();

        $this->assertNotNull($actual);
        $this->assertEquals("411421B0-38D6-6568-A53A-DF99691B7EB6", $actual["sub"]);
        $this->assertEquals("test2@example.com", $actual["email"]);
        $this->assertEquals(true, $actual["email_verified"]);
        $this->assertEquals("+447700200200", $actual["phone_number"]);
        $this->assertEquals(true, $actual["phone_number_verified"]);
        $this->assertNotNull($actual["address"]);
        $this->assertEquals("123 Fake Street Manchester", $actual["address"]["formatted"]);
        $this->assertEquals("M1 1AB", $actual["address"]["postal_code"]);
        $this->assertEquals(new DateTime('1990-4-11'), new DateTime($actual["birthdate"]));
        //$this->assertEquals(new DateTime(2016, 4, 16, 4, 5, 6), actual.UpdatedAt);*/
    }

    public function testResponseDataAsShouldReuseConvertedResponse()
    {
        $responseJson = "{\"sub\":\"411421B0-38D6-6568-A53A-DF99691B7EB6\",\"email\":\"test2@example.com\",\"email_verified\":true,\"phone_number\":\"+447700200200\",\"phone_number_verified\":true,\"birthdate\":\"1990-04-11\",\"updated_at\":\"1460779506\",\"address\":{\"formatted\":\"123 Fake Street \r\n Manchester\",\"postal_code\":\"M1 1AB\"}}";
        $response = new RestResponse(202, $responseJson);

        $userInfoResponse = new IdentityResponse($response);
        $first = $userInfoResponse->getResponseData();
        $second = $userInfoResponse->getResponseData();

        $this->assertEquals($first, $second);
    }

    public function testResponseDataAsShouldReturnDefaultIfResponseJsonNull()
    {
        $response = new RestResponse(202, null);

        $userInfoResponse = new IdentityResponse($response);
        $actual = $userInfoResponse->getResponseData();

        $this->assertNull($actual);
    }
}
