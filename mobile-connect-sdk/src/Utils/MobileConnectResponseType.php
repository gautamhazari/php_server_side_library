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
 * Enum of possible response types for MobileConnectStatus
 */
class MobileConnectResponseType
{
    /**
     * ResponseType indicating Error was encountered
     */
    const Error = 0;

    /**
     * ResponseType indicating the next step should be OperatorSelection
     */
    const OperatorSelection = 1;

    /**
     * ResponseType indicating the next step should be to restart Discovery
     */
    const StartDiscovery = 2;

    /**
     * ResponseType indicating the next step should be StartAuthentication
     */
    const StartAuthentication = 3;

    /**
     * ResponseType indicating the next step should be Authentication
     */
    const Authentication = 4;

    /**
     * ResponseType indicating completion of the MobileConnectProcess
     */
    const Complete = 5;

    /**
     * ResponseType indicating userInfo has been received
     */
    const UserInfo = 6;

    /**
     * ResponseType indicating identity has been received
     */
    const Identity = 7;

    /**
     * ResponseType indicating token has been successfully revoked
     */
    const TokenRevoked = 8;
}
