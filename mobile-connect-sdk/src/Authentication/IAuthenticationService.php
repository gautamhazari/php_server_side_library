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
use MCSDK\Discovery\SupportedVersions;

/**
 * Interface for the Mobile Connect Requests
 */
interface IAuthenticationService
{
    /**
     * Generates an authorisation url based on the supplied options and previous discovery response
     * @param string clientId The registered application ClientId (Required)
     * @param string authorizeUrl The authorization url returned by the discovery process (Required)
     * @param string redirectUrl On completion or error where the result information is sent using a HTTP 302 redirect (Required)
     * @param string state Application specified unique scope value
     * @param string nonce Application specified nonce value. (Required)
     * @param string encryptedMSISDN Encrypted MSISDN for user if returned from discovery service
     * @param SupportedVersions versions SupportedVersions from ProviderMetadata if null default supported versions will be used to generate the auth url
     * @param AuthenticationOptions options Optional parameters
     */
    public function StartAuthentication($clientId, $authorizeUrl, $redirectUrl, $state, $nonce,
        $encryptedMSISDN, $versions = null, AuthenticationOptions $options = null);

    /**
     * Allows an application to use the authorization code obtained from authentication/authorization to obtain an access token
     * and related information from the authorization server. This function requires a valid token url from the discovery process
     * and a valid code from the initial authorization call
     * @param string clientId The registered application ClientId (Required)
     * @param string clientSecret he registered application ClientSecret (Required)
     * @param string requestTokenUrl The url for token requests recieved from the discovery process (Required)
     * @param string redirectUrl Confirms the redirectURI that the application used when the authorization request (Required)
     * @param string code The authorization code provided to the application via the call to the authentication/authorization API (Required)
     */
    public function RequestToken($clientId, $clientSecret, $requestTokenUrl, $redirectUrl, $code);

    /**
     * Initiates headless authentication, if authentication is successful a token will be returned.
     * @param string clientId The application ClientId returned by the discovery process (Required)
     * @param string clientSecret The ClientSecret returned by the discovery response (Required)
     * @param string authorizeUrl The authorization url returned by the discovery process (Required)
     * @param string tokenUrl The token url returned by the discovery process (Required)
     * @param string redirectUrl On completion or error where the result information is sent using a HTTP 302 redirect (Required)
     * @param string state Application specified unique state value (Required)
     * @param string nonce Application specified nonce value. (Required)
     * @param string encryptedMSISDN Encrypted MSISDN for user if returned from discovery service
     * @param SupportedVersions versions if null default supported versions will be used to generate the auth url
     * @param AuthenticationOptions options Optional parameters
     * @return Token if headless authentication is successful
     */
    public function RequestHeadlessAuthentication($clientId, $clientSecret, $authorizeUrl, $tokenUrl, $redirectUrl,
        $state, $nonce, $encryptedMSISDN, SupportedVersions $versions = null, AuthenticationOptions $options = null);

    /**
     * Allows an application to use the refresh token obtained from request token response and request for a token refresh.
     * This function requires a valid refresh token
     * @param string clientId The application clientId returned by the discovery process
     * @param string clientSecret The application clientSecret returned by the discovery process
     * @param string refreshTokenUrl The url for token refresh received from the discovery process
     * @param string refreshToken Refresh token returned from RequestToken request
     */
    public function RefreshToken($clientId, $clientSecret, $refreshTokenUrl, $refreshToken);

    /**
     * Allows an application to use the access token or the refresh token obtained from request token response and request for a token revocation
     * This function requires either a valid access token or a refresh token to be provided
     * @param string clientId The application ClientId returned by the discovery process
     * @param string clientSecret The application clientSecret returned by the discovery process
     * @param string revokeTokenUrl The url for token refresh received from the discovery process
     * @param string token Access/Refresh token returned from RequestToken request
     * @param string tokenTypeHint Hint to indicate the type of token being passed in
     */
    public function RevokeToken($clientId, $clientSecret, $revokeTokenUrl, $token, $tokenTypeHint);
}
