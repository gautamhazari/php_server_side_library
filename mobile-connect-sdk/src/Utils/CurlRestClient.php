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

use MCSDK\Exceptions\OperationCancellationException;

/**
 * Rest client based on curl library
 */
class CurlRestClient {
    private $_client;

    public function __construct() {
    }

    private function followUrl($url) {
        $this->_client = curl_init();
        curl_setopt_array($this->_client, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 50,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_NOBODY => true,
        ));

        curl_exec($this->_client);
        $err = curl_error($this->_client);

        if(!empty($err)) {
            curl_close($this->_client);
            throw new \RuntimeException("Runtime exception occured " . $err);
        }

        $location = curl_getinfo($this->_client, CURLINFO_REDIRECT_URL);

        curl_close($this->_client);
        return $location;
    }

    /**
     * Follows redirect from start to final url
     * @param $url Url to start from
     * @param $finalUrl Url where function stops
     * @return Returns final redirect
     */
    public function followRedirects($url, $finalUrl) {
        $nextUrl = $url;
        $maxRedirects = 5;
        $count = 0;
        do {
            if (isset($GLOBALS["CancelHeadlessAuthentication"])) {
                $GLOBALS["CancelHeadlessAuthentication"] = false;
                throw new OperationCancellationException("Operation cancelled");
            }
            $nextUrl = $this->followUrl($nextUrl);
            sleep(5);
            $count += 1;
            if ($count == $maxRedirects) {
                break;
            }
        } while (strpos($nextUrl, $finalUrl) === false);
        if ($count >= $maxRedirects) {
            throw new \RuntimeException("Runtime exception occured: too many redirects");
        }
        return $nextUrl;
    }
}
