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

use Zend\Http\Headers;

/**
 * Class to hold the response from making a Rest call.
 */
class RestResponse
{
    private $_statusCode;
    private $_headers;
    private $_content;

    public function __construct($code = null, $content = null, $headers=null) {
        $this->_statusCode = $code;
        $this->_content = $content;
        $this->_headers = $headers;
    }

    /**
     * Return the status code of the response
     *
     * @return int The status code of the response
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    public function setStatusCode($code)
    {
        $this->_statusCode = $code;
    }

    /**
     * Return the response Http headers
     *
     * @return Headers The response Http headers
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    public function setHeaders(array $headers) {
        $this->_headers = $headers;
    }

    public function getContent() {
        return $this->_content;
    }

    public function setContent($content) {
        $this->_content = $content;
    }
}
