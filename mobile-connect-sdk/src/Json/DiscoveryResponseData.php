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
class DiscoveryResponseData
{
    /**
     * Parsed from JSON response
     */
    private $_ttl;
    /**
     * Parsed from JSON response
     */
    private $_subscriber_id;
    /**
     * Parsed from JSON response
     */
    private $_error;
    /**
     * Parsed from JSON response
     */
    private $_description;
    /**
     * Parsed from JSON response
     */
    public $_links;
    /**
     * Parsed from JSON response
     */
    public $_response;

    public function get_ttl() {
        return $this->_ttl;
    }

    public function set_ttl($_ttl){
        $this->_ttl = $_ttl;
    }

    public function get_subscriber_id() {
        return $this->_subscriber_id;
    }

    public function set_subscriber_id($_subscriber_id){
        $this->_subscriber_id = $_subscriber_id;
    }

    public function get_error() {
        return $this->_error;
    }

    public function set_error($_error){
        $this->_error = $_error;
    }

    public function get_description() {
        return $this->_description;
    }

    public function set_description($_description){
        $this->_description = $_description;
    }

}

