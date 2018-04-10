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

namespace MCSDK\Identity;

use MCSDK\Constants\Header;
use MCSDK\Utils\JWTPart;
use MCSDK\Utils\RestResponse;
use MCSDK\Utils\JsonWebToken;
use MCSDK\Utils\HttpUtils;
use Zend\Http\Header\WWWAuthenticate;

/**
 * Class to hold response from UserInfo service
 */
class IdentityResponse {
    private $_convertedResponseData;
    private $_responseCode;
    private $_errorResponse;
    private $_responseJson;

    public function __construct(RestResponse $rawResponse = null) {
        if (!empty($rawResponse)) {
            $this->_responseCode = (int)$rawResponse->getStatusCode();
            if ($this->_responseCode < 400) {
                $this->_responseJson = $this->extractJson($rawResponse->getContent());
                $this->parseResponseData($this->_responseJson);
                return;
            }
        }

        if(!empty($rawResponse->getHeaders())&&!empty($rawResponse->getHeaders()->get(Header::WWW_AUTHENTICATE))&&!empty($authenticationError = $rawResponse->getHeaders()->get(Header::WWW_AUTHENTICATE)[0])) {
            $authenticationError = $rawResponse->getHeaders()->get(Header::WWW_AUTHENTICATE)[0];
            $this->_errorResponse = HttpUtils::GenerateAuthenticationError($authenticationError);
        }
    }

    private function extractJson($data) {
        if (!isset($data)) {
            return $data;
        }
        if (json_decode($data, true) !== null) {
            return $data;
        }

        if (JsonWebToken::IsValidFormat($data)) {
            return JsonWebToken::DecodePart($data, JWTPart::Payload);
        }
        return "{\"error\":\"invalid_format\",\"error_description\":\"Recieved UserInfo response that is not JSON or JWT format\"}";
    }

    public function getResponseData() {
        return json_decode($this->_responseJson, true);
    }

    private function parseResponseData($responseJson) {
        if (!isset($responseJson)) {
            return;
        }
        $response = json_decode($responseJson, true);
        if (isset($response["error"])) {
            $this->_errorResponse = array (
                "error" => $response["error"],
                "error-description" => $response["error_description"]
            );
        }
    }

    public function getConvertedResponseData(){
        return $this->_convertedResponseData;
    }

    public function setConvertedResponseData($_convertedResponseData){
        $this->_convertedResponseData = $_convertedResponseData;
    }

    public function getResponseCode(){
        return $this->_responseCode;
    }

    public function setResponseCode($_responseCode){
        $this->_responseCode = $_responseCode;
    }

    public function getErrorResponse(){
        return $this->_errorResponse;
    }

    public function setErrorResponse($_errorResponse){
        $this->_errorResponse = $_errorResponse;
    }

    public function getResponseJson(){
        return $this->_responseJson;
    }

    public function setResponseJson($_responseJson){
        $this->_responseJson = $_responseJson;
    }
}
