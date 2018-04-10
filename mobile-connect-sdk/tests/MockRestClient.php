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

use MCSDK\Utils\RestClient;
use MCSDK\Utils\RestResponse;
use MCSDK\Exceptions\MobileConnectEndpointHttpException;

class MockRestClient extends RestClient {
    private $_nextExpectedResponse;
    private $_nextExpectedException;
    private $_responseQueue;

    public function __construct() {
        $this->_responseQueue = new SplQueue();
    }

    public function get($uri, $authentication = null, $sourceIp = null, $params = null, array $cookies = null) {
        return $this->createResponse();
    }

    public function post($uri, $authentication = null, $formData, $sourceIp, $cookies = null) {
        return $this->createResponse();
    }

    private function createResponse() {
        if (count($this->_responseQueue) == 0) {
            throw RuntimeException("No responses queued");
        }
        $response = $this->_responseQueue->dequeue();
        if ($response instanceof Zend\Http\Exception\RuntimeException) {
            throw new MCSDK\Exceptions\MobileConnectEndpointHttpException($response->getMessage());
        }
        if ($response instanceof Zend\Http\Client\Exception\RuntimeException) {
            throw new MCSDK\Exceptions\MobileConnectEndpointHttpException($response->getMessage());
        }
        if ($response instanceof Exception) {
            throw new Exception($response->getMessage());
        }
        return $response;
    }

    public function queueResponse($response) {
        $this->_responseQueue->enqueue($response);
    }

    public function queueException($exception) {
        $this->_responseQueue->enqueue($exception);
    }
}
