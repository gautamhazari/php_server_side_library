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
 * Static Helper Class containing various methods and extensions required for Http Requests
 */
class HttpUtils {
    /**
     * Returns true if status code is an error type (400s and 500s)
     * @param $statusCode Status code to check
     * @return True if error code is an error
     */
    public static function IsHttpErrorCode($statusCode)
    {
        $codeType = substr((string)$statusCode, 0, 1);
        return $codeType == '4' || $codeType == '5';
    }

    public static function GenerateAuthenticationError($wwwauthenticate) {
        if (!isset($wwwauthenticate)) {
            return null;
        }
        $matches = [];
        preg_match('/error="([^"]*)"[\n\s]*error_description="([^"]*)"/', $wwwauthenticate->getFieldValue(), $matches);
        return array (
            "error" => $matches[1],
            "error_description" => $matches[2]
        );
    }

    /**
     * Extracts url parameter
     * @param $url Given url
     * @param $value Parameter to extract
     * @return parameter key or null if not found
     */
    public static function ExtractQueryValue($url, $value) {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        if (empty($query)) {
            return null;
        }

        return isset($query[$value]) ? $query[$value] : null;
    }

    /**
     * Returns url components
     * @param $url Url to parse
     * @return array of url components
     */
    public static function ParseQueryString($url) {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        return $query;
    }

}
