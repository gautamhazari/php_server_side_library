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

/**
 * Helper class for holding authentication values for calling rest endpoints using RestClient
 */
class RestAuthentication {
    /**
     * The scheme of authentication e.g. Basic
     */
    private $_scheme;

    /**
     * The authentication parameter such as a token or encoded value
     */
    private $_parameter;

    public function getScheme() {
        return $this->_scheme;
    }

    public function getParameter() {
        return $this->_parameter;
    }

    /**
     * Create a new instance of the RestAuthentication class with the specified scheme and parameter
     * @param $scheme The scheme to be used
     * @param $parameter The authentication parameter value
     */
    public function __construct($scheme, $parameter) {
        $this->_scheme = $scheme;
        $this->_parameter = $parameter;
    }

    /**
     * Creates a new instance of the RestAuthentication class for Basic authentication
     * @param @key Key/User value
     * @param @secret Secret/Password value
     * @return A new instance of RestAuthentication configured for Basic auth
     */
    public static function Basic($key, $secret) {
        return new RestAuthentication("Basic", static::encode($key, $secret));
    }

    /**
     * Creates a new instance of the RestAuthentication class for Bearer authentication
     * @param @token Bearer token
     * @return A new instance of RestAuthentication configured for Bearer auth
     */
    public static function Bearer($token) {
        return new RestAuthentication("Bearer", $token);
    }

    private static function encode($key, $secret) {
        $tmp = sprintf("%s:%s", $key, $secret);
        return base64_encode($tmp);
    }
}
