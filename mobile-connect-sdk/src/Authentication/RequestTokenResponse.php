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

namespace MCSDK\Authentication;
use MCSDK\Utils\RestResponse;
use MCSDK\Utils\HttpUtils;
use MCSDK\Authentication\RequestTokenResponseData;

/**
 * Class to hold the response of IAuthenticationService.RequestToken()
 * Will contain either an error response or request data
 */
class RequestTokenResponse {
    private $_responseCode;
    private $_headers;
    private $_responseData;
    private $_decodedIdTokenPayload;
    private $_errorResponse;
    private $_validationResult;

    public function __construct(RestResponse $rawResponse)
    {
        $this->_responseCode = $rawResponse->getStatusCode();
        $this->_headers = $rawResponse->getHeaders();

        if (HttpUtils::IsHttpErrorCode($this->_responseCode)) {
            $this->_errorResponse = json_decode($rawResponse->getContent(), true);
        } else {
            $object = new RequestTokenResponseData(json_decode($rawResponse->getContent(), true));
            $this->_responseData = $object->getData();
        }
    }

    public function getResponseCode(){
        return $this->_responseCode;
    }

    public function setResponseCode($_responseCode){
        $this->_responseCode = $_responseCode;
    }

    public function getHeaders(){
        return $this->_headers;
    }

    public function setHeaders($_headers){
        $this->_headers = $_headers;
    }

    public function getResponseData(){
        return $this->_responseData;
    }

    public function setResponseData($_responseData){
        $this->_responseData = $_responseData;
    }

    public function getDecodedIdTokenPayload(){
        return $this->_decodedIdTokenPayload;
    }

    public function setDecodedIdTokenPayload($_decodedIdTokenPayload){
        $this->_decodedIdTokenPayload = $_decodedIdTokenPayload;
    }

    public function getErrorResponse(){
        return $this->_errorResponse;
    }

    public function setErrorResponse($_errorResponse){
        $this->_errorResponse = $_errorResponse;
    }

    public function setValidationResult($result) {
        $this->_validationResult = $result;
    }

    public function getValidationResult() {
        return $this->_validationResult;
    }
}
