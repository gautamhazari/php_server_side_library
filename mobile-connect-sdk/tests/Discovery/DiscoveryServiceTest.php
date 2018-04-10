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
use MCSDK\Discovery\DiscoveryService;
use MCSDK\Discovery\IDiscoveryService;
use MCSDK\Discovery\DiscoveryOptions;
use MCSDK\MobileConnectConfig;
use MCSDK\Cache\ICache;
use MCSDK\Cache\Cache;
use MCSDK\Exceptions\MobileConnectEndpointHttpException;
use Zend\Cache\Storage\ClearByNamespaceInterface;

class DiscoveryServiceTest extends PHPUnit_Framework_TestCase {
    const REDIRECT_URL = "http://localhost:8080/";
    private static $_responses = array();

    private static $_discovery;
    private static $_cache;
    private static $_restClient;
    private static $_config;

    public static function setUpBeforeClass() {
        self::$_responses["operator-selection"] = new RestResponse(202, "{\"links\":[{\"rel\":\"operatorSelection\",\"href\":\"http://discovery.sandbox2.mobileconnect.io/v2/discovery/users/operator-selection?session_id=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJyZWRpcmVjdFVybCI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMS8iLCJhcHBsaWNhdGlvbiI6eyJleHRlcm5hbF9pZCI6IjExMzgiLCJuYW1lIjoiY3NoYXJwLXNkayIsImtleXMiOnsic2FuZGJveCI6eyJrZXkiOiI2Njc0MmE4NS0yMjgyLTQ3NDctODgxZC1lZDViN2JkNzRkMmQiLCJzZWNyZXQiOiJmMTUxOTlmNC1iNjU4LTRlNTgtOGJiMy1lNDA5OTg4NzMzOTIifX0sInJlZGlyZWN0X3VyaSI6Imh0dHBzOi8vbG9jYWxob3N0OjgwMDEvIiwiZGV2ZWxvcGVyIjp7InBvcnRhbF91c2VyX2lkIjoiMTEzOCIsIm5hbWUiOiJOaWNob2xhcyBEb25vaG9lIiwiZW1haWwiOiJuaWNob2xhcy5kb25vaG9lQGJqc3MuY29tIiwicHJvZmlsZSI6Imh0dHBzOi8vZGV2ZWxvcGVyLm1vYmlsZWNvbm5lY3QuaW8vYXBpL3YxL3VzZXI_ZW1haWw9bmljaG9sYXMuZG9ub2hvZSU0MGJqc3MuY29tIiwidXBkYXRlZCI6IjIwMTYtMDQtMjBUMDk6MzQ6MThaIiwibXNpc2RucyI6WyI5NDE0ZTI1MmMzYjE1ZWUzMGIyN2NmYmQxNjkzN2UwNWJlMGQ1NWYwZGZjZGQ0MjM2OTg3NTU1MjQ3ZjU0YzUyIiwiZjYwZjFkZDU1YzUxMjE3ZTAwMTc4YWE3ZGIxM2Q5Njc4OGUxZmM0MzRkMGU2ZGZiZmI2NjVlYjU5NzU3MGIwZiJdLCJtc2lzZG5TaG9ydCI6WyI3NTc1IiwiMzMzMyJdLCJzbXNBdXRoIjp0cnVlLCJtY2MiOiI5MDEiLCJtbmMiOiIwMSIsImNvbnNlbnQiOmZhbHNlfX0sInVzZXIiOnsibmFtZSI6IjY2NzQyYTg1LTIyODItNDc0Ny04ODFkLWVkNWI3YmQ3NGQyZCIsInBhc3MiOiJmMTUxOTlmNC1iNjU4LTRlNTgtOGJiMy1lNDA5OTg4NzMzOTIifSwiaWF0IjoxNDYxMTY5MzA5fQ.2Lp0Xt9JXVZxNbnNq_RH-5KJPQ06qw6ttR4ZK3fwcQU\"}]}");
        self::$_responses["authentication"] = new RestResponse(200, "{\"ttl\":1466082848000,\"response\":{\"client_id\":\"x-ZWRhNjU3OWI3MGIwYTRh\",\"client_secret\":\"x-NjQzZTBhZWM0YmQ4ZDQ5\",\"serving_operator\":\"demo_unitedkingdom\",\"country\":\"UnitedKingdom\",\"currency\":\"GBP\",\"apis\":{\"operatorid\":{\"link\":[{\"rel\":\"authorization\",\"href\":\"https://reference.mobileconnect.io/mobileconnect/index.php/auth\"},{\"rel\":\"token\",\"href\":\"https://reference.mobileconnect.io/mobileconnect/index.php/token\"},{\"rel\":\"userinfo\",\"href\":\"https://reference.mobileconnect.io/mobileconnect/index.php/userinfo\"},{\"rel\":\"jwks\",\"href\":\"https://reference.mobileconnect.io/mobileconnect/cert.jwk\"},{\"rel\":\"applicationShortName\",\"href\":\"test1\"},{\"rel\":\"openid-configuration\",\"href\":\"https://reference.mobileconnect.io/mobileconnect/discovery.php/openid-configuration\"}]}}},\"subscriber_id\":\"6c483ef529a86e5aa808f9cfdcb78ac3ec9f24aba27ea1a003476b0693751d89c3feacd3d2ff00c0e1e1cb683ff7de9ea87bdd775d4e79b7da5a4fbec509d918c1f804fdaf1fcaa9d1aae572bd19a12de7de2d695d004a3b2828be9b79e5f13a5c70a35adebedef138ab11440f8573fff53e59c8348caaf458716dbb53b4162d27737f290a8a759a4eab409af27685b3667659ce1f5b2194ab68953c0381126fc941eb0043c17647021d1e47a07cfde2e5e18c9e29ca01af1a8d2b3558d9853ffeed1cd9c8545e0d4c609db4ca318c02d10cddaf83bab927f81c4ca8bbb04da4dba273a4f76d3962e5a31a59f806067393823ae6702850726281352849209fe4\"}");
        self::$_responses["error"] = new RestResponse(200, "{\"error\":\"Not_Found_Entity\",\"description\":\"Operator Not Found\"}");
        self::$_responses["provider-metadata"] = new RestResponse(200, "{\"version\":\"3.0\",\"issuer\":\"https://reference.mobileconnect.io/mobileconnect\",\"authorization_endpoint\":\"https://reference.mobileconnect.io/mobileconnect/index.php/auth\",\"token_endpoint\":\"https://reference.mobileconnect.io/mobileconnect/index.php/token\",\"userinfo_endpoint\":\"https://reference.mobileconnect.io/mobileconnect/index.php/userinfo\",\"check_session_iframe\":\"https://reference.mobileconnect.io/mobileconnect/opframe.php\",\"end_session_endpoint\":\"https://reference.mobileconnect.io/mobileconnect/index.php/endsession\",\"jwks_uri\":\"https://reference.mobileconnect.io/mobileconnect/op.jwk\",\"scopes_supported\":[\"openid\",\"mc_authn\",\"mc_authz\",\"profile\",\"email\",\"address\"],\"response_types_supported\":[\"code\",\"code token\",\"code id_token\",\"token\",\"token id_token\",\"code token id_token\",\"id_token\"],\"grant_types_supported\":[\"authorization_code\"],\"acr_values_supported\":[\"2\",\"3\"],\"subject_types_supported\":[\"public\",\"pairwise\"],\"userinfo_signing_alg_values_supported\":[\"HS256\",\"HS384\",\"HS512\",\"RS256\",\"RS384\",\"RS512\"],\"userinfo_encryption_alg_values_supported\":[\"RSA1_5\",\"RSA-OAEP\"],\"userinfo_encryption_enc_values_supported\":[\"A128CBC-HS256\",\"A256CBC-HS512\",\"A128GCM\",\"A256GCM\"],\"id_token_signing_alg_values_supported\":[\"HS256\",\"HS384\",\"HS512\",\"RS256\",\"RS384\",\"RS512\"],\"id_token_encryption_alg_values_supported\":[\"RSA1_5\",\"RSA-OAEP\"],\"id_token_encryption_enc_values_supported\":[\"A128CBC-HS256\",\"A256CBC-HS512\",\"A128GCM\",\"A256GCM\"],\"request_object_signing_alg_values_supported\":[\"HS256\",\"HS384\",\"HS512\",\"RS256\",\"RS384\",\"RS512\"],\"request_object_encryption_alg_values_supported\":[\"RSA1_5\",\"RSA-OAEP\"],\"request_object_encryption_enc_values_supported\":[\"A128CBC-HS256\",\"A256CBC-HS512\",\"A128GCM\",\"A256GCM\"],\"token_endpoint_auth_methods_supported\":[\"client_secret_post\",\"client_secret_basic\",\"client_secret_jwt\",\"private_key_jwt\"],\"token_endpoint_auth_signing_alg_values_supported\":[\"HS256\",\"HS384\",\"HS512\",\"RS256\",\"RS384\",\"RS512\"],\"display_values_supported\":[\"page\"],\"claim_types_supported\":[\"normal\"],\"claims_supported\":[\"name\",\"given_name\",\"family_name\",\"middle_name\",\"nickname\",\"preferred_username\",\"profile\",\"picture\",\"website\",\"email\",\"email_verified\",\"gender\",\"birthdate\",\"zoneinfo\",\"locale\",\"phone_number\",\"phone_number_verified\",\"address\",\"updated_at\"],\"service_documentation\":\"https://reference.mobileconnect.io/mobileconnect/index.php/servicedocs\",\"claims_locales_supported\":[\"en-US\"],\"ui_locales_supported\":[\"en-US\"],\"require_request_uri_registration\":false,\"op_policy_uri\":\"https://reference.mobileconnect.io/mobileconnect/index.php/op_policy\",\"op_tos_uri\":\"https://reference.mobileconnect.io/mobileconnect/index.php/op_tos\",\"claims_parameter_supported\":true,\"request_parameter_supported\":true,\"request_uri_parameter_supported\":true,\"mobile_connect_version_supported\":[{\"openid\":\"mc_v1.1\"},{\"openid mc_authn\":\"mc_v1.2\"},{\"openid mc_authz\":\"mc_v1.2\"}],\"login_hint_methods_supported\":[\"MSISDN\",\"ENCR_MSISDN\",\"PCR\"]} ");
        self::setEnvironment(null);
    }

    public static function setEnvironment(ICache $cache = null) {
        self::$_cache = $cache;
        self::$_restClient = new MockRestClient();
        self::$_discovery = new DiscoveryService(self::$_restClient, self::$_cache);
        self::$_config = new MobileConnectConfig();
        self::$_config->setClientId("1234567890");
        self::$_config->setClientSecret("1234567890");
        self::$_config->setDiscoveryUrl("http://localhost:8080/v2/discovery/");
    }

    public function testAutomatedOperatorDiscoveryShouldHandleOperatorSelectionResponse()
    {
        $response = self::$_responses["operator-selection"];
        $options = new DiscoveryOptions();
        self::$_restClient->queueResponse($response);

        $result = self::$_discovery->StartAutomatedOperatorDiscoveryByPreferences(self::$_config, self::REDIRECT_URL, $options, null);

        $this->assertNotNull($result);
        $this->assertEquals(202, $result->getResponseCode());
        $this->assertNotNull($result->getResponseData());
        $this->assertNotNull($result->getResponseData()["links"]);
        $this->assertFalse($result->isCached());
    }

    public function testAutomatedOperatorDiscoveryShouldHandleAuthenticationResponse()
    {
        $response = self::$_responses["authentication"];
        $options = new DiscoveryOptions();
        self::$_restClient->queueResponse($response);
        self::$_restClient->queueResponse(self::$_responses["provider-metadata"]);

        $result = self::$_discovery->StartAutomatedOperatorDiscoveryByPreferences(self::$_config, self::REDIRECT_URL, $options, null);

        $this->assertNotNull($result);
        $this->assertEquals(200, $result->getResponseCode());
        $this->assertNotNull($result->getResponseData());
        $this->assertNotNull($result->getResponseData()["response"]);
        $this->assertNotNull($result->getResponseData()["ttl"]);
        $this->assertNotNull($result->getProviderMetadata());
        $this->assertFalse($result->isCached());
    }

    public function testAutomatedOperatorDiscoveryShouldHandleErrorResponse()
    {
        $response = self::$_responses["error"];
        $options = new DiscoveryOptions();
        self::$_restClient->queueResponse($response);

        $result = self::$_discovery->StartAutomatedOperatorDiscoveryByPreferences(self::$_config, self::REDIRECT_URL, $options, null);

        $this->assertNotNull($result);
        $this->assertEquals(200, $result->getResponseCode());
        $this->assertNotNull($result->getResponseData());
        $this->assertNotNull($result->getErrorResponse());
        $this->assertFalse($result->isCached());
    }

    /**
     * @expectedException MCSDK\Exceptions\MobileConnectEndpointHttpException
     */
    public function testAutomatedOperatorDiscoveryShouldHandleHttpRequestException()
    {
        $options = new DiscoveryOptions();
        self::$_restClient->queueException(new Zend\Http\Exception\RuntimeException("This is the message"));
        $result = self::$_discovery->StartAutomatedOperatorDiscoveryByPreferences(self::$_config, self::REDIRECT_URL, $options, null);
    }

    public function testGetOperatorSelectionURLShouldHandleOperatorSelectionResponse()
    {
        $response = self::$_responses["operator-selection"];
        self::$_restClient->queueResponse($response);

        $result = self::$_discovery->GetOperatorSelectionURLByPreferences(self::REDIRECT_URL, self::$_config);

        $this->assertNotNull($result);
        $this->assertEquals(202, $result->getResponseCode());
        $this->assertNotNull($result->getResponseData());
        $this->assertNotNull($result->getResponseData()["links"]);
        $this->assertFalse($result->isCached());
    }

    /**
     * @expectedException MCSDK\Exceptions\MobileConnectEndpointHttpException
     */
    public function testGetOperatorSelectionURLShouldHandleHttpRequestException()
    {
        self::$_restClient->queueException(new Zend\Http\Exception\RuntimeException("This is the message"));
        self::$_discovery->GetOperatorSelectionURLByPreferences(self::REDIRECT_URL, self::$_config);
    }

    public function testCompleteSelectedOperatorDiscoveryShouldHandleOperatorSelectionResponse()
    {
        $response = self::$_responses["operator-selection"];
        self::$_restClient->queueResponse($response);

        $result = self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "901", "01", self::$_config);

        $this->assertNotNull($result);
        $this->assertEquals(202, $result->getResponseCode());
        $this->assertNotNull($result->getResponseData());
        $this->assertNotNull($result->getResponseData()["links"]);
        $this->assertFalse($result->isCached());
    }

    public function testCompleteSelectedOperatorDiscoveryShouldHandleAuthenticationResponse()
    {
        $response = self::$_responses["authentication"];
        self::$_restClient->queueResponse($response);

        self::$_restClient->queueResponse(self::$_responses["provider-metadata"]);
        $result = self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "901", "01", self::$_config);

        $this->assertNotNull($result);
        $this->assertEquals(200, $result->getResponseCode());
        $this->assertNotNull($result->getResponseData());
        $this->assertNotNull($result->getResponseData()["response"]);
        $this->assertNotNull($result->getResponseData()["ttl"]);
        $this->assertNotNull($result->getProviderMetadata());
        $this->assertFalse($result->isCached());
    }

    public function testCompleteSelectedOperatorDiscoveryShouldHandleErrorResponse()
    {
        $response = self::$_responses["error"];
        self::$_restClient->queueResponse($response);

        $result = self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "901", "01", self::$_config);

        $this->assertNotNull($result);
        $this->assertEquals(200, $result->getResponseCode());
        $this->assertNotNull($result->getResponseData());
        $this->assertNotNull($result->getErrorResponse());
        $this->assertFalse($result->isCached());
    }

    public function testCompleteSelectedOperatorDiscoveryShouldUseCachedResponsesIfCacheSupplied()
    {
        $this->SetupWithCache();
        $response = self::$_responses["authentication"];
        $options = new DiscoveryOptions();
        self::$_restClient->queueResponse($response);
        self::$_restClient->queueResponse(self::$_responses["provider-metadata"]);
        self::$_restClient->queueResponse(new Exception("this should not be hit as the process should pull the cached value"));
        $initialResult = self::$_discovery->completeSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "901", "01", self::$_config);

        $cachedResult = self::$_discovery->completeSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "901", "01", self::$_config);
        //var_dump($cachedResult);
        $this->assertNotNull($cachedResult);
        //$this->assertEquals($initialResult->getResponseData()['links'], $cachedResult->getResponseData()['links']);
        $this->assertNotNull($cachedResult->getProviderMetadata());
    }

    private function SetupWithCache() {
        self::setEnvironment(new Cache());
    }

    /**
     * @expectedException MCSDK\Exceptions\MobileConnectEndpointHttpException
     */
    public function CompleteSelectedSelectedOperatorDiscoveryShouldHandleHttpRequestException() {
        self::$_restClient->queueException(new Zend\Http\Exception\RuntimeException("This is the message"));
        $response = self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "901", "01", self::$_config);
    }

    /**
     * @expectedException MCSDK\Exceptions\MobileConnectEndpointHttpException
     */
    public function CompleteSelectedSelectedOperatorDiscoveryShouldHandleWebException()
    {
        self::$_restClient->queueException(new Zend\Http\Exception\RuntimeException("This is the message"));
        $response = self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "901", "01", self::$_config);
    }

    public function testGetCachedDiscoveryResultShouldReturnCachedResult()
    {
        $this->SetupWithCache();
        $response = self::$_responses["authentication"];
        $options = new DiscoveryOptions();
        self::$_restClient->queueResponse($response);
        self::$_restClient->queueResponse(self::$_responses["provider-metadata"]);
        $initialResult = self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "901", "01", self::$_config);

        $cachedResult = self::$_discovery->GetCachedDiscoveryResult("901", "01");

        $this->assertNotNull($cachedResult);
        //$this->assertEquals($initialResult->getResponseData.links, cachedResult.ResponseData.links);
        $this->assertNotNull($cachedResult->getProviderMetadata());
    }

    public function testClearDiscoveryCacheShouldEmptyCacheWithEmptyArguments()
    {
        $this->SetupWithCache();
        $response = self::$_responses["authentication"];
        $options = new DiscoveryOptions();

        for ($i = 0; $i < 5; $i++)
        {
            self::$_restClient->queueResponse($response);
            self::$_restClient->queueResponse(self::$_responses["provider-metadata"]);
            $result = self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "90" . $i, "01", self::$_config);
        }

        self::$_discovery->ClearDiscoveryCache(null, null);

        $cachedResult = self::$_discovery->GetCachedDiscoveryResult("900", "01");

        //Assert.IsTrue(_cache.IsEmpty);
        $this->assertNull($cachedResult);
    }

    public function testClearDiscoveryCacheShouldClearSingleEntryIfSuppliedValidMCCMNC()
    {
        $this->SetupWithCache();
        $response = self::$_responses["authentication"];
        $options = new DiscoveryOptions();

        for ($i = 0; $i < 5; $i++)
        {
            self::$_restClient->queueResponse($response);
            self::$_restClient->queueResponse(self::$_responses["provider-metadata"]);
            $result = self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "90" . $i, "01", self::$_config);
        }

        self::$_discovery->ClearDiscoveryCache("903", "01");
        $otherCachedResult = self::$_discovery->GetCachedDiscoveryResult("902", "01");

        $this->assertNotNull($otherCachedResult);
    }

    public function testDiscoveryResponseShouldContainProviderMetadataWhenAvailable()
    {
        $response = self::$_responses["authentication"];
        self::$_restClient->queueResponse($response);
        self::$_restClient->queueResponse(self::$_responses["provider-metadata"]);
        $result = self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "901", "01", self::$_config);

        $this->assertNotNull($result->getProviderMetadata());
        $this->assertNotNull($result->getProviderMetadata()['version']);
    }
/*
    public function testDiscoveryResponseShouldUseCachedProviderMetadataWhenAvailable()
    {
        $this->SetupWithCache(new CacheImpl());
        $response = self::$_responses["authentication"];
        $options = new DiscoveryOptions();
        $options->setMSISDN("+441122334455");

        self::$_restClient->queueResponse($response);
        self::$_restClient->queueResponse(self::$_responses["provider-metadata"]);
        $discoveryResponse = self::$_discovery->StartAutomatedOperatorDiscoveryByPreferences(self::$_config, self::REDIRECT_URL, $options, null);
        self::$_restClient->queueResponse($response);

        $responseWithCachedMetadata = self::$_discovery->StartAutomatedOperatorDiscoveryByPreferences(self::$_config, self::REDIRECT_URL, $options, null);

        $this->assertNotNull($responseWithCachedMetadata->getProviderMetadata());
    }*/

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAutomatedOperatorDiscoveryShouldThrowWhenPreferencesNull()
    {
        $result = self::$_discovery->StartAutomatedOperatorDiscovery(null, self::REDIRECT_URL, new DiscoveryOptions(), null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAutomatedOperatorDiscoveryShouldThrowWhenClientIdNull()
    {
        $result = self::$_discovery->StartAutomatedOperatorDiscovery(null, "secret", "discoveryUrl", "redirecturl", new DiscoveryOptions(), null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAutomatedOperatorDiscoveryShouldThrowWhenClientSecretNull()
    {
        $result = self::$_discovery->StartAutomatedOperatorDiscovery("id", null, "discoveryUrl", "redirecturl", new DiscoveryOptions(), null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAutomatedOperatorDiscoveryShouldThrowWhenDiscoveryUrlNull()
    {
        $result = self::$_discovery->StartAutomatedOperatorDiscovery("id", "secret", null, "redirecturl", new DiscoveryOptions(), null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAutomatedOperatorDiscoveryShouldThrowWhenRedirectUrlNull()
    {
        $result = self::$_discovery->StartAutomatedOperatorDiscovery("id", "secret", "discoveryUrl", null, new DiscoveryOptions(), null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetOperatorSelectionUrlShouldThrowWhenPreferencesNull()
    {
        $result = self::$_discovery->GetOperatorSelectionURLByPreferences(self::REDIRECT_URL, null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetOperatorSelectionUrlShouldThrowWhenClientIdNull()
    {
        $result = self::$_discovery->GetOperatorSelectionURL(null, "secret", "discoveryUrl", "redirecturl");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetOperatorSelectionUrlShouldThrowWhenClientSecretNull()
    {
        $result = self::$_discovery->GetOperatorSelectionURL("id", null, "discoveryUrl", "redirecturl");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetOperatorSelectionUrlShouldThrowWhenDiscoveryUrlNull()
    {
        $result = self::$_discovery->GetOperatorSelectionURL("id", "secret", null, "redirecturl");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetOperatorSelectionUrlShouldThrowWhenRedirectUrlNull()
    {
        $result = self::$_discovery->GetOperatorSelectionURL("id", "secret", "discoveryUrl", null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCompleteSelectedOperatorDiscoveryShouldThrowWhenPreferencesNull()
    {
        $result = self::$_discovery->CompleteSelectedOperatorDiscoveryByPreferences(self::REDIRECT_URL, "901", "01", null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCompleteSelectedOperatorDiscoveryShouldThrowWhenClientIdNull()
    {
        $result = self::$_discovery->CompleteSelectedOperatorDiscovery(null, "secret", "discoveryUrl", "redirecturl", "selectedMcc", "selectedMnc");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCompleteSelectedOperatorDiscoveryShouldThrowWhenClientSecretNull()
    {
        $result = self::$_discovery->CompleteSelectedOperatorDiscovery("id", null, "discoveryUrl", "redirecturl", "selectedMcc", "selectedMnc");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCompleteSelectedOperatorDiscoveryThrowWhenDiscoveryUrlNull()
    {
        $result = self::$_discovery->CompleteSelectedOperatorDiscovery("id", "secret", null, "redirecturl", "selectedMcc", "selectedMnc");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCompleteSelectedOperatorDiscoveryShouldThrowWhenRedirectUrlNull()
    {
        $result = self::$_discovery->CompleteSelectedOperatorDiscovery("id", "secret", "discoveryUrl", null, "selectedMcc", "selectedMnc");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCompleteSelectedOperatorDiscoveryShouldThrowWhenSelectedMCCNull()
    {
        $result = self::$_discovery->CompleteSelectedOperatorDiscovery("id", "secret", "discoveryUrl", "redirectUrl", null, "selectedMnc");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCompleteSelectedOperatorDiscoveryShouldThrowWhenSelectedMNCNull()
    {
        $result = self::$_discovery->CompleteSelectedOperatorDiscovery("id", "secret", "discoveryUrl", "redirectUrl", "selectedMcc", null);
    }
}
