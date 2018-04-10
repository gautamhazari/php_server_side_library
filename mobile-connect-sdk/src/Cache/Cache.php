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

namespace MCSDK\Cache;

use MCSDK\Utils\Constants;
use Zend\Cache\Storage\Adapter\Memory;
use Zend\Cache\Storage\Plugin\ExceptionHandler;
use MCSDK\Discovery\DiscoveryResponse;
use Zend\Http\Headers;

/**
 * Implementation of ICache that uses a Zend Cache instance as a
 * backing store.
 */
class Cache implements ICache
{
    private $_cache;

    /**
     * CacheImpl constructor.
     */
    public function __construct()
    {
        $this->_cache = new Memory();
        $this->_cache->getOptions()->setNamespace('/');
        $this->_cache->getOptions()->setTtl(3600000);

        $plugin = new ExceptionHandler();
        $plugin->getOptions()->setThrowExceptions(true);
        $this->_cache->addPlugin($plugin);
    }

    public function add($mcc, $mnc, $value) {
        $this->addKey($this->concatKey($mcc, $mnc), $value);
    }

    public function addKey($key, $value) {
        $value->setExpirationUTCTimestamp();
        $this->_cache->setItem($key, serialize($value));
    }

    public function getCache()
    {
        return $this->_cache;
    }

    public function getKey($key, $removeIfExpired = false) {
        if (empty($key)) {
            return;
        }
        $response = unserialize($this->_cache->getItem($key));
        if ($response == false) {
            return null;
        }

        if ($removeIfExpired && $this->IsExpired($response)) {
            $this->removeKey($key);
        }
        return $response;
    }

    private function IsExpired($object) {
        $now = new \DateTime("now");
        return $now->GetTimestamp() > $object->getExpirationUTCTimestamp();
    }

    public function get($mcc, $mnc) {
        if (empty($mcc) || empty($mnc)) {
            return;
        }
        return $this->getKey($this->concatKey($mcc, $mnc));
    }

    private function concatKey($mcc, $mnc) {
        return $mcc . '_' . $mnc;
    }

    /**
     * Remove cache value by key
     *
     * @param CacheKey $key
     */
    public function remove($mcc, $mnc)
    {
        $key = $this->concatKey($mcc, $mnc);
        $this->validateKey($key);
        $this->_cache->removeItem($key);
    }

    public function removeKey($key)
    {
        $this->validateKey($key);
        $this->_cache->removeItem($key);
    }

    /**
     * Clear the whole cache
     */
    public function clear()
    {
        $this->_cache->clearByNamespace('/');
    }

    /**
     * Determine is a value is a valid stdClass
     *
     * @param mixed $value param to be tested
     */
    private function validateValue($value)
    {
        if (is_null($value)) {
            throw new \InvalidArgumentException("Value cannot be null");
        }
    }

    private function validateKey($key)
    {
        if (is_null($key)) {
            throw new \InvalidArgumentException("Key cannot be null");
        }
    }
}
