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
use MCSDK\Discovery\DiscoveryResponse;

/**
 * Interface to the discovery response cache.
 */
interface ICache
{

    /**
     * Add a value to the cache with the specified key.
     *
     * @param CacheKey|null $key The key. (Required).
     * @param CacheValue|null $value The value to be cached. (Required).
     */
    //public function add(CacheKey $key, CacheValue $value);
    public function add($mcc, $mnc, $value);

    //public function addKey($key, $value);
    /**
     * Return a cached value as determined by the key.
     *
     * @param CacheKey $key The key of the data to return. (Required).
     * @return CacheValue|null The cached value if present, null otherwise.
     */
    public function get($mcc, $mnc);

    /**
     * Remove any entry from the cache that matches the key.
     *
     * @param CacheKey $key The data to be removed. (Required).
     */
    public function remove($mcc, $mnc);

    /**
     * Empty the cache.
     */
    public function clear();
}
