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

use MCSDK\Utils;

namespace MCSDK\Utils;

class JsonWebToken {
    /// <summary>
    /// Check if token is in valid JWT format
    /// </summary>
    /// <param name="token">Token to check</param>
    /// <returns>True if token contains 3 parts split by '.' the last part may be empty</returns>
    public static function IsValidFormat($token) {
        //var split = token.Split(new char[] { '.' }, StringSplitOptions.None);
        $split = preg_split("/[.]/", $token);
        return count($split) == 3;
    }

    /// <summary>
    /// Decodes the specified token part
    /// </summary>
    /// <param name="token">JSON Web Token to decode the part content</param>
    /// <param name="part">Part to decode, if signature then the part will be returned directly and no decode will be completed</param>
    /// <returns>JSON string decoded from part</returns>
    public static function DecodePart($token, $part)
    {
        $split = preg_split("/[.]/", $token);
        $stringPart = $split[$part];
        return static::urlsafeB64Decode($stringPart);
    }

    private static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}

/// <summary>
/// Enum for specifying part of a Json Web Token
/// </summary>
abstract class JWTPart
{
    /// <summary>
    /// First part of the JSON Web Token containing information about the Algorithm and token type
    /// </summary>
    const Header = 0;
    /// <summary>
    /// Second part of the JSON Web Token containing data and required claims
    /// </summary>
    const Payload = 1;
    /// <summary>
    /// Third part of the JSON Web Token used to verify the token authenticity
    /// </summary>
    const Signature = 2;
}

