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

use Zend\Http\Client;
use Zend\Http\Response;
use Zend\Http\Headers;
use Zend\Http\Request;
use MCSDK\Utils\UriBuilder;
use MCSDK\Constants\Header;
use MCSDK\Utils\RestAuthentication;
use MCSDK\Constants\DefaultOptions;

/**
 * Wrapper for Http requests, returning a simple normalised response object
 */
class RestClient {
    private $_client;
    private $_headers;

    public function __construct() {
        $this->_client = new Client();
        $this->_headers = new Headers();
        $this->_headers->addHeaderLine('Accept: application/json');
    }

    /**
     * Executes a HTTP GET to the supplied uri with optional basic auth, cookies and query params
     * @param $uri Base uri of GET request
     * @param $auth Authentication value to be used (if auth required)
     * @param $sourceIp Source request IP (if identified)
     * @param $params Query params to be added to the base url (if required)
     * @param $cookies Cookies to be added to the request (if required)
     * @return RestResponse containing status code, headers and content
     */
    public function get($uri, $auth = null, $sourceIp = null, $params = null, $xRedirect = null, $version = null, array $cookies = null) {
        $builder = new UriBuilder($uri);
        if (!empty($params)) {
            $builder->addQueryParams($params);
        }

        $this->createRequest($auth, Request::METHOD_GET, $builder->getUri(), $sourceIp, $xRedirect, $cookies);
        $response = $this->_client->send();

        return $this->createRestResponse($response);
    }

    /**
     * Executes a HTTP POST to the supplied uri with x-www-form-urlencoded content and optional cookies
     * @param $uri Base uri of the POST
     * @param $auth Authentication value to be used (if auth required)
     * @param $formData Form data to be added as POST content
     * @param $sourceIp Source request IP (if identified)
     * @param $cookies Cookies to be added to the request (if required)
     * @return RestResponse containing status code, headers and content
     */
    public function post($uri, $auth, $formData, $sourceIp, $xRedirect = null, $version = null, $cookies = null) {
        $this->createRequest($auth, Request::METHOD_POST, $uri, $sourceIp, $xRedirect, $cookies);
        $this->_client->setParameterPost($formData);
        $response = $this->_client->send();
        return $this->createRestResponse($response);
    }

    private function createRequest($auth, $method, $uri, $sourceIp, $xRedirect = null, $version = null, array $cookies = null) {
        $this->_client->setMethod($method);
        $this->_client->setUri($uri);
        if ($sourceIp !== null) {
            $this->_headers->addHeaderLine(Header::X_SOURCE_IP, $sourceIp);
        }
        if ($xRedirect !== null) {
            $this->_headers->addHeaderLine(Header::X_REDIRECT, $xRedirect);
        }
        if ($version !== null) {
            $this->_headers->addHeaderLine(Header::SDK_VERSION, DefaultOptions::SDK_VERSION);
        }
        if (!empty($auth)) {
            $this->_headers->addHeaderLine(sprintf('Authorization: %s %s', $auth->getScheme(), $auth->getParameter()));
        }
        $this->_client->setHeaders($this->_headers);
    }

    private function createRestResponse($response) {
        $headers = $response->getHeaders();
        $restResponse = new RestResponse($response->getStatusCode(), $headers, $headers);
        $restResponse->setContent($response->getBody());
        return $restResponse;
    }
}
