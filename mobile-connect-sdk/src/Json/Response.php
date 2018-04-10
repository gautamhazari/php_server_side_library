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

namespace MCSDK\Json;

/**
 * Object for deserialization of Discovery Response content
 */
class Response
{
    /**
     * Parsed from JSON response
     */
    private $_serving_operator;
    /**
     * Parsed from JSON response
     */
    private $_country;
    /**
     * Parsed from JSON response
     */
    private $_currency;
    /**
     * Parsed from JSON response
     */
    public $_apis;
    /**
     * Parsed from JSON response
     */
    private $_client_id;
    /**
     * Parsed from JSON response
     */
    private $_client_secret;
    /**
     * Parsed from JSON response
     */
    private $_subscriber_id;

    public function get_serving_operator() {
        return $this->_serving_operator;
    }

    public function set_serving_operator($_serving_operator){
        $this->_serving_operator = $_serving_operator;
    }

    public function get_country() {
        return $this->_country;
    }

    public function set_country($_country){
        $this->_country = $_country;
    }

    public function get_currency() {
        return $this->_currency;
    }

    public function set_currency($_currency){
        $this->_currency = $_currency;
    }

    public function get_client_id() {
        return $this->_client_id;
    }

    public function set_client_id($_client_id){
        $this->_client_id = $_client_id;
    }

    public function get_client_secret() {
        return $this->_client_secret;
    }

    public function set_client_secret($_client_secret){
        $this->_client_secret = $_client_secret;
    }

    public function get_subscriber_id() {
        return $this->_subscriber_id;
    }

    public function set_subscriber_id($_subscriber_id){
        $this->_subscriber_id = $_subscriber_id;
    }

}
