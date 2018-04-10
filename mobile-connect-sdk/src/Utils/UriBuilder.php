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
 * Class to manipulate url parameters
 */
class UriBuilder
{

    private $uri;
    private $params;

    /**
     * URIBuilder constructor.
     *
     * @param string $uri the uri to be used by the builder to derive parts
     */
    public function __construct($uri)
    {
        $this->uri = $uri;
        $this->params = array();
    }

    /**
     * Add a parameter to the GET query list
     *
     * @param $key
     * @param $value
     */
    public function addParameter($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * @return string the complete url for GET http requests
     */
    public function build()
    {
        return $this->uri . $this->buildHTTPQuery();
    }

    /**
     * @return string build the get query list and url encode
     */
    private function buildHTTPQuery()
    {
        $query = array();
        if (count($this->params) > 0) {
            foreach ($this->params as $paramKey => $paramValue) {
                $query[] = $paramKey . '=' . $paramValue;
            }
        }
        $httpQuery = '?' . implode('&', $query);

        return urlencode($httpQuery);
    }

    public function addQueryParams(array $params) {
        $this->uri = $this->uri . '?' . http_build_query($params);
    }

    public function getUri() {
        return $this->uri;
    }
}
