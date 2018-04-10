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

namespace MCSDK\Authentication;

/**
 * Enum for available token validation results
 */
class TokenValidationResult {

    /**
     * No validation has occured
     */
    const None = 0;
    /**
     * Token when signed does not match signature
     */
    const InvalidSignature = 1;
    /**
     * Token passed all validation steps
     */
    const Valid = 2;
    /**
     * Key was not retrieved from the jwks url or a jwks url was not present
     */
    const JWKSError = 4;
    /**
     * The alg claim in the id token header does not match the alg requested or the default alg of RS256
     */
    const IncorrectAlgorithm = 8;
    /**
     * Neither the azp nor the aud claim in the id token match the client id used to make the auth request
     */
    const InvalidAudAndAzp = 16;
    /**
     * The iss claim in the id token does not match the expected issuer
     */
    const InvalidIssuer = 32;
    /**
     * The IdToken has expired
     */
    const IdTokenExpired = 64;
    /**
     * No key matching the requested key id was found
     */
    const NoMatchingKey = 128;
    /**
     * Key does not contain the required information to validate against the requested algorithm
     */
    const KeyMisformed = 256;
    /**
     * Algorithm is unsupported for validation
     */
    const UnsupportedAlgorithm = 512;
    /**
     * The access token has expired
     */
    const AccessTokenExpired = 1024;
    /**
     * The access token is null or empty in the token response
     */
    const AccessTokenMissing = 2048;
    /**
     * The id token is null or empty in the token response
     */
    const IdTokenMissing = 4096;
    /**
     * The id token is older than the max age specified in the auth stage
     */
    const MaxAgePassed = 8192;
    /**
     * A longer time than the configured limit has passed since the token was issued
     */
    const TokenIssueTimeLimitPassed = 16384;
    /**
     * The nonce in the id token claims does not match the nonce specified in the auth stage
     */
    const InvalidNonce = 32768;
    /**
     * The token response is null or missing required data
     */
    const IncompleteTokenResponse = 65536;
    /**
     * Token validation skipped (valid only for mc_v1.1)
     */
    const IdTokenValidationSkipped = 131072;
}
