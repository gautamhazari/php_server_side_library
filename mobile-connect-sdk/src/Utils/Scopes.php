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

use MCSDK\Constants;

/**
 * Helper methods for dealing with scope and scope values
 */
class Scopes
{
    /**
     * Returns a scope that is ensured to contain the defaultScope and has any duplication of values removed
     * @param $scope Scope to coerce
     * @param $defaultScope Required default scope
     * @return Scope containing default scope values and no duplicated values
     */
    public static function CoerceOpenIdScope($scope, $defaultScope = Scope::OPENID)
    {
        if (is_array($scope)) {
            return static::CoerceOpenIdScopeByArray($scope, $defaultScope);
        }

        $split = explode(" ", $scope);
        $scopeValues = static::CoerceOpenIdScope($split, $defaultScope);

        return static::CreateScope($scopeValues);
    }

    /**
     * Returns a list of scope values that is ensured to contain the defaultScope values and has any duplication of values removed.
     * This can be used when multiple modifications of scope are required to be chained
     * @param $scopeValues Scope to coerce
     * @param $defaultScope Required default scope
     * @return List of scope values containing default scope values and no duplicated values
     */
    private static function CoerceOpenIdScopeByArray($scopeValues, $defaultScope = Scope::OPENID)
    {
        $splitDefault = explode(" ", $defaultScope);
        for ($i = 0; $i < count($splitDefault); $i++)
        {
            if (empty(array_search($scopeValues, $splitDefault))) {
                array_push($scopeValues, $splitDefault[$i]);
            }
        }
        return array_unique($scopeValues);
    }

    public static function CreateScope($scopeValues)
    {
        return trim(implode(" ", $scopeValues));
    }
}
