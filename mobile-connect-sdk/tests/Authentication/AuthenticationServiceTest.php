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
//require_once(dirname(__FILE__) . '/../bootstrap.php');
require_once(dirname(__FILE__) . '/../MockRestClient.php');

//use MCSDKTEST\MockRestClient;
use MCSDK\Utils\RestResponse;
use MCSDK\Authentication\AuthenticationService;
use MCSDK\Authentication\IAuthenticationService;
use MCSDK\MobileConnectConfig;
use MCSDK\Discovery\SupportedVersions;
use MCSDK\Authentication\AuthenticationOptions;
use MCSDK\Utils\HttpUtils;
use MCSDK\Authentication\RequestTokenResponse;
use MCSDK\Authentication\TokenValidationResult;
use MCSDK\Exceptions\MobileConnectEndpointHttpException;

class AuthenticationServiceTest extends PHPUnit_Framework_TestCase {
    const REDIRECT_URL = "http://localhost:8080/";
    const AUTHORIZE_URL = "http://localhost:8080/authorize";
    const TOKEN_URL = "http://localhost:8080/token";

    private $_responses = array();
    private $_authentication;
    private $_restClient;
    private $_config;
    private $_defaultVersions;

    public function __construct() {
        $this->_responses["token"] = new RestResponse(200, "{\"access_token\":\"966ad150-16c5-11e6-944f-43079d13e2f3\",\"token_type\":\"Bearer\",\"expires_in\":3600,\"id_token\":\"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJub25jZSI6Ijc3YzE2M2VmZDkzYzQ4ZDFhNWY2NzdmNGNmNTUzOGE4Iiwic3ViIjoiY2M3OGEwMmNjM2ViNjBjOWVjNTJiYjljZDNhMTg5MTAiLCJhbXIiOlsiU0lNX1BJTiJdLCJhdXRoX3RpbWUiOjE0NjI4OTQ4NTcsImFjciI6IjIiLCJhenAiOiI2Njc0MmE4NS0yMjgyLTQ3NDctODgxZC1lZDViN2JkNzRkMmQiLCJpYXQiOjE0NjI4OTQ4NTYsImV4cCI6MTQ2Mjg5ODQ1NiwiYXVkIjpbIjY2NzQyYTg1LTIyODItNDc0Ny04ODFkLWVkNWI3YmQ3NGQyZCJdLCJpc3MiOiJodHRwOi8vb3BlcmF0b3JfYS5zYW5kYm94Mi5tb2JpbGVjb25uZWN0LmlvL29pZGMvYWNjZXNzdG9rZW4ifQ.lwXhpEp2WUTi0brKBosM8Uygnrdq6FnLqkZ0Bm53gXA\"}");
        $this->_responses["invalid-code"] = new RestResponse(400, "{\"error\":\"invalid_grant\",\"error_description\":\"Authorization code doesn't exist or is invalid for the client\"}");
        $this->_responses["token_revoked"] = new RestResponse(200, "");
        $this->_responses["refresh_token"] = new RestResponse(400, "{\"error\":\"this is an error\",\"error_description\":\"this is an error description\"}");

        $this->_restClient = new MockRestClient();
        $this->_authentication = new AuthenticationService($this->_restClient);
        $this->_config = new MobileConnectConfig();
        $this->_config->setClientId("1234567890");
        $this->_config->setClientSecret("1234567890");
        $this->_config->setDiscoveryUrl("http://localhost:8080/v2/discovery/");
        $this->_defaultVersions = new SupportedVersions([["openid" => "mc_v1.2"]]);
    }

    public function testStartAuthenticationReturnsUrlWhenArgumentsValid()
    {
        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, "state", "nonce", null, null, null);

        $this->assertNotNull($result);
        $this->assertNotEmpty($result->getUrl());
        $this->assertTrue(strpos($result->getUrl(), self::AUTHORIZE_URL) !== false);
    }

    public function testStartAuthenticationWith1_1VersionShouldStripAuthnArgumentFromScope()
    {
        $initialScope = "openid mc_authn";
        $expectedScope = "openid";
        $versions = new SupportedVersions([["openid" => "mc_v1.1"]]);
        $authOptions = new AuthenticationOptions();
        $authOptions->setScope($initialScope);

        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, "state", "nonce", null, $versions, $authOptions);
        $actualScope = HttpUtils::ExtractQueryValue($result->getUrl(), "scope");

        $this->assertEquals($expectedScope, $actualScope);
    }

    public function testStartAuthenticationWith1_2VersionShouldLeaveAuthnArgumentInScope()
    {
        $initialScope = "openid mc_authn";
        $expectedScope = "openid mc_authn";
        $versions = new SupportedVersions([["openid" => "mc_v1.2"]]);

        $authOptions = new AuthenticationOptions();
        $authOptions->setScope($initialScope);

        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, "state", "nonce", null, $versions, $authOptions);

        $actualScope = HttpUtils::ExtractQueryValue($result->getUrl(), "scope");

        $this->assertEquals($expectedScope, $actualScope);
    }

    public function testStartAuthenticationWithout1_2VersionShouldAddAuthnArgumentToScope()
    {
        $initialScope = "openid";
        $expectedScope = "openid mc_authn";
        $versions = new SupportedVersions([["openid" => "mc_v1.2"]]);

        $authOptions = new AuthenticationOptions();
        $authOptions->setScope($initialScope);

        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, "state", "nonce", null, $versions, $authOptions);
        $actualScope = HttpUtils::ExtractQueryValue($result->getUrl(), "scope");

        $this->assertEquals($expectedScope, $actualScope);
    }

    public function testStartAuthenticationWithMc_AuthzScopeShouldAddAuthorizationArguments()
    {
        $options = new AuthenticationOptions();
        $options->setScope("openid mc_authz");
        $options->setClientName("test");
        $options->setContext("context");
        $options->setBindingMessage("binding");

        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, "state", "nonce", null, $this->_defaultVersions, $options);
        $query = HttpUtils::ParseQueryString($result->getUrl());

        $this->assertEquals($options->getContext(), $query["context"]);
        $this->assertEquals($options->getClientName(), $query["client_name"]);
        $this->assertEquals($options->getBindingMessage(), $query["binding_message"]);
    }

    public function testStartAuthenticationWithContextShouldUseAuthorizationScope()
    {
        $initialScope = "openid";
        $expectedScope = "openid mc_authz";
        $options = new AuthenticationOptions();
        $options->setScope($initialScope);
        $options->setClientName("clientName");
        $options->setContext("context");

        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, "state", "nonce", null, $this->_defaultVersions, $options);
        $actualScope = HttpUtils::ExtractQueryValue($result->getUrl(), "scope");

        $this->assertEquals($expectedScope, $actualScope);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStartAuthenticationWithMobileConnectProductScopeShouldUseAuthorization()
    {
        $initialScope = "openid mc_authn mc_identity_phone";
        $options = new AuthenticationOptions();
        $options->setScope($initialScope);
        $response = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, "state", "nonce", null, $this->_defaultVersions, $options);
    }

    public function testRequestTokenShouldHandleTokenResponse() {
        $response = $this->_responses["token"];
        $this->_restClient->queueResponse($response);
        $result = $this->_authentication->RequestToken($this->_config->getClientId(),
            $this->_config->getClientSecret(), self::TOKEN_URL, self::REDIRECT_URL, "code");

        $this->assertNotNull($result);
        $this->assertEquals(200, $result->getResponseCode());
        $this->assertNotNull($result->getResponseData());
        $this->assertNotNull($result->getResponseData()["access_token"]);
    }

    public function testRequestTokenShouldHandleInvalidCodeResponse()
    {
        $response = $this->_responses["invalid-code"];
        $this->_restClient->queueResponse($response);

        $result = $this->_authentication->RequestToken($this->_config->getClientId(),
            $this->_config->getClientSecret(), self::TOKEN_URL, self::REDIRECT_URL, "code");

        $this->assertNotNull($result);
        $this->assertEquals(400, $result->getResponseCode());
        $this->assertNotNull($result->getErrorResponse());
        $this->assertNotNull($result->getErrorResponse()["error_description"]);
    }

    /**
     * @expectedException MCSDK\Exceptions\MobileConnectEndpointHttpException
     */
    public function testRequestTokenShouldHandleRuntimeException()
    {
        $response = $this->_responses["token"];
        $this->_restClient->queueException(new Zend\Http\Exception\RuntimeException("this is the message"));

        $result = $this->_authentication->RequestToken($this->_config->getClientId(), $this->_config->getClientSecret(), self::TOKEN_URL, self::REDIRECT_URL, "code");
    }

    /**
     * @expectedException MCSDK\Exceptions\MobileConnectEndpointHttpException
     */
    public function testRequestTokenShouldHandleAnotherRuntimeException()
    {
        $response = $this->_responses["token"];
        $this->_restClient->queueException(new Zend\Http\Client\Exception\RuntimeException("this is the message"));

        $result = $this->_authentication->RequestToken($this->_config->getClientId(), $this->_config->getClientSecret(), self::TOKEN_URL, self::REDIRECT_URL, "code");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStartAuthenticationShouldThrowWhenClientIdIsNull()
    {
        $result = $this->_authentication->StartAuthentication(null, self::AUTHORIZE_URL, self::REDIRECT_URL, "state", "nonce", null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStartAuthenticationShouldThrowWhenAuthorizeUrlIsNull()
    {
        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), null, self::REDIRECT_URL, "state", "nonce", null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStartAuthenticationShouldThrowWhenRedirectUrlIsNull()
    {
        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, null, "state", "nonce", null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStartAuthenticationShouldThrowWhenStateIsNull()
    {
        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, null, "nonce", null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStartAuthenticationShouldThrowWhenNonceIsNull()
    {
        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, "state", null, null, null, null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStartAuthenticationShouldThrowWhenClientNameIsNullAndShouldUseAuthorization()
    {
        $options = new AuthenticationOptions();
        $options->setContext("context");
        $options->setBindingMessage("bind");
        $options->setClientName(null);
        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, "state", null, null, null, $options);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStartAuthenticationShouldThrowWhenContextIsNullAndShouldUseAuthorization()
    {
        $options = new AuthenticationOptions();
        $options->setContext(null);
        $options->setBindingMessage("bind");
        $options->setClientName("client");
        $result = $this->_authentication->StartAuthentication($this->_config->getClientId(), self::AUTHORIZE_URL, self::REDIRECT_URL, "state", null, null, null, $options);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequestTokenShouldThrowWhenClientIdIsNull()
    {
        $result = $this->_authentication->RequestToken(null, $this->_config->getClientSecret(), self::TOKEN_URL, self::REDIRECT_URL, "code");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequestTokenShouldThrowWhenClientSecretIsNull()
    {
        $result = $this->_authentication->RequestToken($this->_config->getClientId(), null, self::TOKEN_URL, self::REDIRECT_URL, "code");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequestTokenShouldThrowWhenTokenUrlIsNull()
    {
        $result = $this->_authentication->RequestToken($this->_config->getClientId(), $this->_config->getClientSecret(), null, self::REDIRECT_URL, "code");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequestTokenShouldThrowWhenRedirectUrlIsNull()
    {
        $result = $this->_authentication->RequestToken($this->_config->getClientId(), $this->_config->getClientSecret(), self::TOKEN_URL, null, "code");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequestTokenShouldThrowWhenCodeIsNull()
    {
        $result = $this->_authentication->RequestToken($this->_config->getClientId(), $this->_config->getClientSecret(), self::TOKEN_URL, self::REDIRECT_URL, null);
    }

    public function testValidateTokenResponseShouldValidateIfAccessAndIdTokenAreValid()
    {
        $jwksJson = "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\"}]}";
        $rawResponse = new RestResponse(202, "{\"access_token\":\"966ad150-16c5-11e6-944f-43079d13e2f3\",\"token_type\":\"Bearer\",\"expires_in\":3600,\"id_token\":\"eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXgiLCJhenAiOiJ4LWNsaWVudGlkLXgiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3LCJpYXQiOjE0NzEwMDczMjd9.U9c5iuybG4GIvrbQH5BT9AgllRbPL6SuIzL4Y3MW7VlCVIQOc_HFfkiLa0LNvqZiP-kFlADmnkzuuQxPq7IyaOILVYct20mrcOb_U_zMli4jg-t9P3BxHaq3ds9JlLBjz0oewd01ZQtWHgRnrGymfKAIojzHlde-aePuL1M26Eld5zoKQvCLcKAynZsjKsWF_6YdLk-uhlC5ofMOaOoPirPSPAxYvbj91z3o9XIgSHoU-umN7AJ6UQ4H-ulfftlRGK8hz0Yzpf2MHOy9OHg1u3ayfCaaf8g5zKGngcz0LgK9VAw2B31xJw-RHkPPh0Hz82FgBc4588oEFC1c22GGTw\"}");
        $tokenResponse = new RequestTokenResponse($rawResponse);
        $jwks = json_decode($jwksJson, true);
        $nonce = "1234567890";
        $clientId = "x-clientid-x";
        $issuer = "http://mobileconnect.io";
        $maxAge = 36000;

        $actual = $this->_authentication->ValidateTokenResponse($tokenResponse, $clientId, $issuer, $nonce, $jwks, $maxAge);

        $this->assertEquals(TokenValidationResult::Valid, $actual);
    }

    public function testValidateTokenResponseShouldNotValidateIfResponseIsIncomplete()
    {
        $jwksJson = "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\"}]}";
        $rawResponse = new RestResponse(202, "");
        $tokenResponse = new RequestTokenResponse($rawResponse);
        $jwks = json_decode($jwksJson, true);
        $nonce = "1234567890";
        $clientId = "x-clientid-x";
        $issuer = "http://mobileconnect.io";
        $maxAge = 36000;

        $actual = $this->_authentication->ValidateTokenResponse($tokenResponse, $clientId, $issuer, $nonce, $jwks, $maxAge);

        $this->assertEquals(TokenValidationResult::IncompleteTokenResponse, $actual);
    }

    public function testValidateTokenResponseShouldNotValidateIfAccessTokenIsInvalid()
    {
        $jwksJson = "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\"}]}";
        $rawResponse = new RestResponse(202, "{\"token_type\":\"Bearer\",\"expires_in\":3600,\"id_token\":\"eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXgiLCJhenAiOiJ4LWNsaWVudGlkLXgiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3LCJpYXQiOjE0NzEwMDczMjd9.U9c5iuybG4GIvrbQH5BT9AgllRbPL6SuIzL4Y3MW7VlCVIQOc_HFfkiLa0LNvqZiP-kFlADmnkzuuQxPq7IyaOILVYct20mrcOb_U_zMli4jg-t9P3BxHaq3ds9JlLBjz0oewd01ZQtWHgRnrGymfKAIojzHlde-aePuL1M26Eld5zoKQvCLcKAynZsjKsWF_6YdLk-uhlC5ofMOaOoPirPSPAxYvbj91z3o9XIgSHoU-umN7AJ6UQ4H-ulfftlRGK8hz0Yzpf2MHOy9OHg1u3ayfCaaf8g5zKGngcz0LgK9VAw2B31xJw-RHkPPh0Hz82FgBc4588oEFC1c22GGTw\"}");
        $tokenResponse = new RequestTokenResponse($rawResponse);
        $jwks = json_decode($jwksJson, true);
        $nonce = "1234567890";
        $clientId = "x-clientid-x";
        $issuer = "http://mobileconnect.io";
        $maxAge = 36000;

        $actual = $this->_authentication->ValidateTokenResponse($tokenResponse, $clientId, $issuer, $nonce, $jwks, $maxAge);

        $this->assertEquals(TokenValidationResult::AccessTokenMissing, $actual);
    }

    public function testValidateTokenResponseShouldValidateIfIdTokenIsInvalid()
    {
        $jwksJson = "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\"}]}";
        $rawResponse = new RestResponse(202, "{\"access_token\":\"966ad150-16c5-11e6-944f-43079d13e2f3\",\"token_type\":\"Bearer\",\"expires_in\":3600,\"id_token\":\"eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXgiLCJhenAiOiJ4LWNsaWVudGlkLXgiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3LCJpYXQiOjE0NzEwMDczMjd9.U9c5iuybG4GIvrbQH5BT9AgllRbPL6SuIzL4Y3MW7VlCVIQOc_HFfkiLa0LNvqZiP-kFlADmnkzuuQxPq7IyaOILVYct20mrcOb_U_zMli4jg-t9P3BxHaq3ds9JlLBjz0oewd01ZQtWHgRnrGymfKAIojzHlde-aePuL1M26Eld5zoKQvCLcKAynZsjKsWF_6YdLk-uhlC5ofMOaOoPirPSPAxYvbj91z3o9XIgSHoU-umN7AJ6UQ4H-ulfftlRGK8hz0Yzpf2MHOy9OHg1u3ayfCaaf8g5zKGngcz0LgK9VAw2B31xJw-RHkPPh0Hz82FgBc4588oEFC1c22GGTw\"}");
        $tokenResponse = new RequestTokenResponse($rawResponse);
        $jwks = json_decode($jwksJson, true);
        $nonce = "1234567890";
        $clientId = "x-clientid-x";
        $maxAge = 36000;


        $actual = $this->_authentication->ValidateTokenResponse($tokenResponse, $clientId, "notissuer", $nonce, $jwks, $maxAge);

        $this->assertEquals(TokenValidationResult::InvalidIssuer, $actual);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRefreshTokenShouldThrowWhenClientIdIsNull() {
        $response = $this->_authentication->RefreshToken(null, $this->_config->getClientSecret(), self::TOKEN_URL, "token");
    }

    /**
    * @expectedException InvalidArgumentException
    */
    public function testRefreshTokenShouldThrowWhenClientSecretIsNull() {
        $response = $this->_authentication->RefreshToken($this->_config->getClientId(), null, self::TOKEN_URL, "token");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRefreshTokenShouldThrowWhenRefreshUrlIsNull() {
        $response = $this->_authentication->RefreshToken($this->_config->getClientId(), $this->_config->getClientId(), null, "token");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRefreshTokenShouldThrowWhenRefreshTokenIsNull() {
        $response = $this->_authentication->RefreshToken($this->_config->getClientId(), $this->_config->getClientId(), self::TOKEN_URL, null);
    }

    public function testRefreshTokenShouldHandleTokenResponse() {
        $response = $this->_responses["token"];
        $this->_restClient->queueResponse($response);
        $result = $this->_authentication->RequestToken($this->_config->getClientId(),
            $this->_config->getClientSecret(), self::TOKEN_URL, self::REDIRECT_URL, "token");

        $this->assertNotNull($result);
        $this->assertEquals(200, $result->getResponseCode());
        $this->assertNotNull($result->getResponseData());
        $this->assertNotNull($result->getResponseData()["access_token"]);
    }

    /**
     * @expectedException MCSDK\Exceptions\MobileConnectEndpointHttpException
     */
    public function testRefreshTokenShouldHandleHttpRequestException() {
        $response = $this->_responses["token"];
        $this->_restClient->queueException(new Zend\Http\Client\Exception\RuntimeException("this is the message"));
        $result = $this->_authentication->RequestToken($this->_config->getClientId(),
            $this->_config->getClientSecret(), self::TOKEN_URL, self::REDIRECT_URL, "token");
    }

    public function testRefreshTokenShouldReturnErrorDetails() {
        $response = $this->_responses["refresh_token"];
        $this->_restClient->queueResponse($response);
        $result = $this->_authentication->RequestToken($this->_config->getClientId(),
            $this->_config->getClientSecret(), self::TOKEN_URL, self::REDIRECT_URL, "token");
        $this->assertNotNull($result);
        $this->assertNotNull($result->getErrorResponse());
        $this->assertEquals("this is an error", $result->getErrorResponse()["error"]);
        $this->assertEquals("this is an error description", $result->getErrorResponse()["error_description"]);
    }

    public function testRevokeTokenShouldMarkSuccessIfNoError() {
        $response = $this->_responses["token"];
        $this->_restClient->queueResponse($response);
        $result = $this->_authentication->RevokeToken($this->_config->getClientId(),
            $this->_config->getClientSecret(), "http://revoke", "token", "refresh_token");

        $this->assertNotNull($result);
        $this->assertTrue($result->getSuccess());
        $this->assertNull($result->getErrorResponse());
    }

    public function testRevokeTokenShouldReturnErrorIfError() {
        $response = $this->_responses["invalid-code"];
        $this->_restClient->queueResponse($response);
        $result = $this->_authentication->RevokeToken($this->_config->getClientId(),
            $this->_config->getClientSecret(), "http://revoke", "token", "refresh_token");

        $this->assertNotNull($result);
        $this->assertFalse($result->getSuccess());
        $this->assertNotNull($result->getErrorResponse());
        $this->assertEquals("invalid_grant", $result->getErrorResponse()["error"]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRevokeTokenShouldThrowWhenClientIdIsNull() {
        $result = $this->_authentication->RevokeToken(null,
            $this->_config->getClientSecret(), "http://revoke", "token", "token hint");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRevokeTokenShouldThrowWhenClientSecretIsNull() {
        $result = $this->_authentication->RevokeToken($this->_config->getClientId(),
            null, "http://revoke", "token", "token hint");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRevokeTokenShouldThrowWhenRevokeUrlIsNull() {
        $result = $this->_authentication->RevokeToken($this->_config->getClientId(),
            $this->_config->getClientSecret(), null, "token", "token hint");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRevokeTokenShouldThrowWhenTokenIsNull() {
        $result = $this->_authentication->RevokeToken($this->_config->getClientId(),
            $this->_config->getClientSecret(), "revoke url", null, "token hint");
    }
}
