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

namespace MCSDK\Utils;

use MCSDK\discovery\DiscoveryResponse;
use MCSDK\oidc\ParsedIdToken;
use MCSDK\oidc\RequestTokenResponse;
use MCSDK\oidc\RequestTokenResponseData;
use MCSDK\helpers\OperatorUrls;
use MCSDK\Web\MobileConnectWebResponse;

/**
 * Class to hold Json utility functions.
 */
class JsonUtils
{
    public static function toJson(MobileConnectWebResponse $response) {
        $temp = array(
            "status" => $response->getStatus(),
            "action" => $response->getAction(),
            "applicationShortName" => $response->getApplicationShortName(),
            "url" => $response->getUrl(),
            "sdkSession" => $response->getSdkSession(),
            "state" => $response->getState(),
            "nonce" => $response->getNonce(),
            "subscriberId" => $response->getSubscriberId(),
            "token" => $response->getToken(),
            "identity" => $response->getIdentity(),
            "error" => $response->getError(),
            "description" => $response->getDescription()
        );
        return json_encode($temp);
    }

    /**
     * Extract the requested URL from the Operator Not Identified Discovery response.
     *
     * @param string $jsonDoc The json object to check.
     * @param string $relToFind The URL to find.
     * @return string The requested URL if present, null otherwise.
     */
    public static function extractUrl($jsonDoc, $relToFind)
    {
        if (is_null($jsonDoc)) {
            throw new \InvalidArgumentException("Missing argument jsonDoc");
        }

        if (is_null($relToFind)) {
            throw new \InvalidArgumentException("Missing argument relToFind");
        }

        if (property_exists($jsonDoc, Constants::LINKS_FIELD_NAME)) {
            $linksNode = $jsonDoc->{Constants::LINKS_FIELD_NAME};
            if (is_null($linksNode)) {
                return null;
            }
        } else {
            return null;
        }

        foreach ($linksNode as $key => $node) {
            $rel = static::getOptionalStringValue($node, Constants::REL_FIELD_NAME);
            if ($relToFind == $rel) {
                return static::getOptionalStringValue($node, Constants::HREF_FIELD_NAME);
            }
        }

        return null;
    }

    /**
     * Extract an error response from the discovery response if any.
     *
     * A discovery response has an error if the error field is present.
     *
     * @param \stdClass $jsonDoc The discovery response to examine.
     * @return ErrorResponse The error response if present, null otherwise.
     */
    public static function getErrorResponse($jsonDoc)
    {
        if (is_null($jsonDoc)) {
            throw new \InvalidArgumentException("Missing argument jsonDoc");
        }

        $error = static::getOptionalStringValue($jsonDoc, Constants::ERROR_NAME);
        $errorDescription = static::getOptionalStringValue($jsonDoc, Constants::ERROR_DESCRIPTION_NAME);
        // Sometimes "description" rather than "error_description" is seen
        $altErrorDescription = static::getOptionalStringValue($jsonDoc, Constants::ERROR_DESCRIPTION_ALT_NAME);
        $errorUri = static::getOptionalStringValue($jsonDoc, Constants::ERROR_URI_NAME);

        if (is_null($error)) {
            return null;
        }

        if (!is_null($altErrorDescription)) {
            if (is_null($errorDescription)) {
                $errorDescription = $altErrorDescription;
            } else {
                $errorDescription .= ' ' . $altErrorDescription;
            }
        }

        $errorResponse = new ErrorResponse();
        $errorResponse->set_error($error);
        $errorResponse->set_error_description($errorDescription);
        $errorResponse->set_error_uri($errorUri);

        return $errorResponse;
    }

    /**
     * Parse the string into a standard class
     *
     * Returns null if invalid json object
     *
     * @param string $jsonStr The Json string to parse.
     * @param bool associativeArray Convert object into associative array
     * @return \stdClass The Jackson Json Tree
     */
    public static function parseJson($jsonStr, $associativeArray = false)
    {
        return json_decode($jsonStr, $associativeArray);
    }

    public static function parseOperatorUrls($jsonDoc)
    {

        if (is_null($jsonDoc)) {
            throw new \InvalidArgumentException("Missing parameter jsonDoc");
        }
        try {
            $operatorUrls = new OperatorUrls();
            $responseNode = static::getExpectedNode($jsonDoc, Constants::RESPONSE_FIELD_NAME);

            $linkNode = $responseNode->{Constants::APIS_FIELD_NAME}->{Constants::OPERATORID_FIELD_NAME}->{Constants::LINK_FIELD_NAME};
            if (count($linkNode) == 0) {
                return null;
            }

            foreach ($linkNode as $key => $node) {
                $rel = static::getExpectedStringValue($node, Constants::REL_FIELD_NAME);
                if (Constants::AUTHORIZATION_REL == $rel) {
                    $operatorUrls->setAuthorization(static::getExpectedStringValue($node, Constants::HREF_FIELD_NAME));
                } else if (Constants::TOKEN_REL == $rel) {
                    $operatorUrls->setToken(static::getExpectedStringValue($node, Constants::HREF_FIELD_NAME));
                } else if (Constants::USER_INFO_REL == $rel) {
                    $operatorUrls->setUserInfo(static::getExpectedStringValue($node, Constants::HREF_FIELD_NAME));
                } else if (Constants::PREMIUM_INFO_REL == $rel) {
                    $operatorUrls->setPremiumInfo(static::getExpectedStringValue($node, Constants::HREF_FIELD_NAME));
                } else if (Constants::OPENID_CONFIGURATION_REL == $rel) {
                    $operatorUrls->setOpenidConfiguration(static::getExpectedStringValue($node, Constants::HREF_FIELD_NAME));
                }
            }

            return $operatorUrls;
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Get client id from DiscoveryResponse json
     * @param DiscoveryResponse json
     * @return client id
     */
    public static function getClientId($jsonDoc)
    {
        if (is_null($jsonDoc)) {
            throw new \InvalidArgumentException("Missing parameter jsonDoc");
        }
        $responseNode = static::getExpectedNode($jsonDoc, Constants::RESPONSE_FIELD_NAME);
        return static::getExpectedStringValue($responseNode, Constants::CLIENT_ID_FIELD_NAME);
    }

    /**
     * Get client secret from DiscoveryResponse json
     * @param DiscoveryResponse json
     * @return client secret
     */
    public static function getClientSecret($jsonDoc)
    {
        if (is_null($jsonDoc)) {
            throw new \InvalidArgumentException("Missing parameter jsonDoc");
        }
        $responseNode = static::getExpectedNode($jsonDoc, Constants::RESPONSE_FIELD_NAME);
        return static::getExpectedStringValue($responseNode, Constants::CLIENT_SECRET_FIELD_NAME);
    }

    /**
     * Parse the response from a request token call.
     *
     * The json is expected to be either an error or a successful request token response.
     *
     * @param \DateTime $timeReceived The time the response was received, used to timestamp the returned object.
     * @param string $jsonStr The Json string to examine.
     * @return RequestTokenResponse The parsed response.
     * @throws \Exception
     */
    public static function parseRequestTokenResponse($timeReceived, $jsonStr)
    {
        $requestTokenResponse = new RequestTokenResponse();

        $jsonDoc = json_decode($jsonStr);

        $errorResponse = static::getErrorResponse($jsonDoc);
        if (!is_null($errorResponse)) {
            $requestTokenResponse->setErrorResponse(static::getErrorResponse($jsonDoc));

            return $requestTokenResponse;
        }

        $responseData = new RequestTokenResponseData();
        $requestTokenResponse->setResponseData($responseData);

        $responseData->setTimeReceived($timeReceived);
        $responseData->setOriginalResponse($jsonStr);

        $responseData->set_access_token(static::getOptionalStringValue($jsonDoc, Constants::ACCESS_TOKEN_FIELD_NAME));
        $responseData->set_token_type(static::getOptionalStringValue($jsonDoc, Constants::TOKEN_TYPE_FIELD_NAME));
        $responseData->set_refresh_token(static::getOptionalStringValue($jsonDoc, Constants::REFRESH_TOKEN_FIELD_NAME));
        $expiresIn = static::getOptionalIntegerValue($jsonDoc, Constants::EXPIRES_IN_FIELD_NAME);
        $responseData->set_expires_in($expiresIn);
        $idTokenStr = static::getOptionalStringValue($jsonDoc, Constants::ID_TOKEN_FIELD_NAME);
        if (!is_null($idTokenStr)) {
            $parsedIdToken = static::createParsedIdToken($idTokenStr);
            $responseData->setParsedIdToken($parsedIdToken);
        }

        return $requestTokenResponse;
    }

    /**
     * Parse the passed string as an id token.
     *
     * @param string $idTokenStr The string to parse.
     * @return ParsedIdToken A ParsedIdToken.
     * @throws \Exception
     */
    public static function createParsedIdToken($idTokenStr)
    {
        $idToken = static::parseIdToken($idTokenStr);

        $parsedIdToken = new ParsedIdToken();
        $parsedIdToken->set_id_token($idTokenStr);
        $parsedIdToken->set_pcr($idToken->getPayload()->get_sub());
        $parsedIdToken->set_nonce($idToken->getPayload()->get_nonce());
        $parsedIdToken->set_id_token_claims($idToken->getPayload()->getClaims());
        $parsedIdToken->set_id_token_verified(false);

        return $parsedIdToken;
    }

    /**
     * Parse the string as an id_token.
     *
     * @param string $idToken The string to parse.
     * @return IdToken An IdToken.
     * @throws \Exception
     */
    private static function parseIdToken($idToken)
    {
        $parts = explode(".", $idToken);

        if (count($parts) != 3) {
            throw new \InvalidArgumentException("Not an id_token");
        }

        $header = utf8_encode(base64_decode($parts[0]));
        $payload = utf8_encode(base64_decode($parts[1]));

        $parsedIdToken = new IdToken();
        $parsedIdToken->setHeader(static::createJwtHeader($header));
        $parsedIdToken->setPayload(static::createJwtPayload($payload));
        $parsedIdToken->setSignature($parts[2]);

        return $parsedIdToken;
    }

    /**
     * Parse the string as a header.
     *
     * @param string $header The string to parse.
     * @return JwtHeader A parsed header.
     * @throws \Exception
     */
    static private function createJwtHeader($header)
    {
        $jsonDoc = json_decode($header);

        $parsedHeader = new JwtHeader();
        $parsedHeader->set_typ(static::getOptionalStringValue($jsonDoc, Constants::TYP_FIELD_NAME));
        $parsedHeader->set_alg(static::getOptionalStringValue($jsonDoc, Constants::ALG_FIELD_NAME));

        return $parsedHeader;
    }

    /**
     * Parse the string as a payload.
     *
     * @param string $payload The string to parse.
     * @return JwtPayload A Payload.
     * @throws \Exception
     */
    static private function createJwtPayload($payload)
    {
        $jsonDoc = json_decode($payload);

        $parsedJwtPayload = new JwtPayload();
        $parsedJwtPayload->setClaims($jsonDoc);

        return $parsedJwtPayload;
    }

    /**
     * Query the parent node for the named child node.
     *
     * @param \stdClass $parentNode Node to check.
     * @param string $name Name of child node.
     * @return \stdClass The child node if found.
     * @throws NoFieldException Thrown if field not found.
     */
    static private function getExpectedNode($parentNode, $name)
    {
        $childNode = $parentNode->$name;
        if (is_null($childNode)) {
            throw new \InvalidArgumentException($name);
        }

        return $childNode;
    }

    /**
     * Query the parent node for the named child node and return the text value of the child node
     *
     * @param \stdClass $parentNode Node to check.
     * @param string $name Name of the child node.
     * @return string The text value of the child node.
     * @throws NoFieldException Thrown if field not found.
     */
    static private function getExpectedStringValue($parentNode, $name)
    {

        return (string)static::getExpectedNode($parentNode, $name);
    }

    /**
     * Return the string value of an optional child node.
     *
     * Check the parent node for the named child, if found return the string contents of the child node, return null otherwise.
     *
     * @param \stdClass $parentNode The node to check.
     * @param string $name Name of the optional child node.
     * @return string value of child node, if found, null otherwise.
     */
    static function getOptionalStringValue($parentNode, $name)
    {
        if (!property_exists($parentNode, $name)) {
            return null;
        }
        $childNode = $parentNode->$name;
        if (is_null($childNode)) {
            return null;
        }

        return strval($childNode);
    }

    /**
     * Return the integer value of an optional child node.
     *
     * Check the parent node for the named child, if found return the integer contents of the child node, return null otherwise.
     *
     * @param \stdClass $parentNode The node to check
     * @param string $name The name of the optional child.
     * @return int Value of the child node if present, null otherwise.
     */
    static private function getOptionalIntegerValue($parentNode, $name)
    {

        return intval(static::getOptionalStringValue($parentNode, $name));
    }

    /**
     * Return the long value of an optional child node.
     * <p>
     * Check the parent node for the named child, if found return the long contents of the child node, return null otherwise.
     *
     * @param \stdClass $parentNode The node to check
     * @param string $name The name of the optional child.
     * @return int Value of the child node if present, null otherwise.
     */
    static private function getOptionalLongValue($parentNode, $name)
    {

        return static::getOptionalIntegerValue($parentNode, $name);
    }

    /**
     * Return the ttl field in the Json object.
     *
     * @param \stdClass $node The Json object to examine.
     * @return int The ttl value if present, null otherwise.
     */
    static public function getDiscoveryResponseTtl($node)
    {
        return static::getOptionalLongValue($node, Constants::TTL_FIELD_NAME);
    }

}
