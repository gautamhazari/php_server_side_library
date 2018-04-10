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

use MCSDK\Authentication\TokenValidation;

use Jose\Factory\JWKFactory;
use Jose\Loader;
use MCSDK\Authentication\TokenValidationResult;

class TokenValidationTest extends PHPUnit_Framework_TestCase {

    private static $nonce = "1234567890";
    private static $clientId = "x-clientid-x";
    private static $issuer = "http://mobileconnect.io";
    private static $maxAge = 36000;
    private static $_allValidationSupportedVersion = "1.2";
    private static $_validationUnsupportedVersion = "1.1";

    public function testValidateIdTokenShouldValidateWhenAllValid() {
        $jwksJson = "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\"}]}";
        $idToken = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXgiLCJhenAiOiJ4LWNsaWVudGlkLXgiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3LCJpYXQiOjE0NzEwMDczMjd9.U9c5iuybG4GIvrbQH5BT9AgllRbPL6SuIzL4Y3MW7VlCVIQOc_HFfkiLa0LNvqZiP-kFlADmnkzuuQxPq7IyaOILVYct20mrcOb_U_zMli4jg-t9P3BxHaq3ds9JlLBjz0oewd01ZQtWHgRnrGymfKAIojzHlde-aePuL1M26Eld5zoKQvCLcKAynZsjKsWF_6YdLk-uhlC5ofMOaOoPirPSPAxYvbj91z3o9XIgSHoU-umN7AJ6UQ4H-ulfftlRGK8hz0Yzpf2MHOy9OHg1u3ayfCaaf8g5zKGngcz0LgK9VAw2B31xJw-RHkPPh0Hz82FgBc4588oEFC1c22GGTw";

        $jwks = json_decode($jwksJson, true);


        $loader = new Loader();

        $actual = TokenValidation::ValidateIdToken($idToken, self::$clientId, self::$issuer, self::$nonce, self::$maxAge, $jwks, self::$_allValidationSupportedVersion);
        $this->assertEquals(TokenValidationResult::Valid, $actual);
    }

    public function testValidateIdTokenShouldNotValidateWhenNoIdToken()
    {
        $jwksJson = "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\"}]}";
        $idToken = "";
        $jwks = json_decode($jwksJson, true);

        $actual = TokenValidation::ValidateIdToken($idToken, self::$clientId, self::$issuer, self::$nonce, self::$maxAge, $jwks, self::$_allValidationSupportedVersion);
        $this->assertEquals(TokenValidationResult::IdTokenMissing, $actual);
    }

    public function testValidateIdTokenShouldNotValidateWhenClaimInvalid()
    {
        $jwksJson = "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\"}]}";
        $idToken = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXkiLCJhenAiOiJ4LWNsaWVudGlkLXkiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3LCJpYXQiOjE0NzEwMDc2NzR9.byu8aDef11sJPpPD9WM_j5uv92CsQEJLJ23SVCwrmf-btdyViTe5q1Q0X1hjVzv6FcCQLlrdJj1ib4sky6It1kVEEDk_E7w8KHH1CmmApghWh2lozJRlg8LQTQXgvfnUPeSLsoGBDYWI502aUhyy9V_zm9M0F3Vi0GWmDVZeXIvUlqdGd1YdzO0cmEfc9nyQSchimVmc-0etCGJn8qehvCZa_x96_u-qJeUiOb_7NypECoVDv8UzAZ48P5Dq-iDCYP6jCmOjdZ36b4JO6co1OnYp4cGONqZTQadVDewAfskKtGkspm6XUdil0WDct1DMuPnDuH1eweQtYopxtHRsjw";
        $jwks = json_decode($jwksJson, true);

        $actual = TokenValidation::ValidateIdToken($idToken, self::$clientId, self::$issuer, self::$nonce, self::$maxAge, $jwks, self::$_allValidationSupportedVersion);

        $this->assertEquals(TokenValidationResult::InvalidAudAndAzp, $actual);
    }

    public function testValidateIdTokenSignatureShouldValidateUsingMatchingAlgKey()
    {
        $jwksJson = "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\"}]}";
        $idToken = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhenAiOiJNekZsWmpreFpHSXRPV1UyTlMwMFpURm1MVGt3TXpjdE5UUXpOamRrTURCa016Y3pPbTl3WlhKaGRHOXlMV0U9IiwiYXV0aF90aW1lIjoxNDcwMzI2ODIwLCJhdWQiOlsiTXpGbFpqa3haR0l0T1dVMk5TMDBaVEZtTFRrd016Y3ROVFF6Tmpka01EQmtNemN6T205d1pYSmhkRzl5TFdFPSJdLCJhbXIiOlsiU0lNX1BJTiJdLCJub25jZSI6ImFkNjVmOGUwNzA3MTRlYTU5Yzc2NDRlZjE1OGM1MjM3IiwiaWF0IjoxNDcwMzI2ODIwLCJpc3MiOiJpbnRlZ3JhdGlvbjIuc2FuZGJveC5tb2JpbGVjb25uZWN0LmlvIiwiYWNyIjoiMiIsImV4cCI6MTQ3MDMzMDQyMCwic3ViIjoiYzIzMjQ2N2MtNDliMi0xMWU2LTlhYTgtMDI0MmFjMTEwMDAzIn0.QOdjTBG5xzX9ROIYmEyJ5ozamcd1O8R6Zna0GpO14n2lFu2oG2FP7HWws3VvgDqMkgwhyt-l7wFs-SDxYWsXj6a3wCGOHQSkOwdFWx5QZwHf4abOCVbcD0HMcFRWAAhBU8K0k9gBlNOdblArEusXWUtNOb3zA9kE5X8aX8v3anh_utrxaKYSvndjHIe7d50XybsOip4QOsqMEUbeBdos4hqSc_KW9qQvZcqBoZs3J7n-n8nPX5TcXu7OZd62pT48GvpL1Y1O6xvBA-gvLEpba3KffucBkgSXtLYsfw8n109A335z9TWIM_9D6OrRkWQrYBLm3B6GfcGOUDJIISegTA";
        $jwks = json_decode($jwksJson, true);

        $actual = TokenValidation::ValidateIdTokenSignature($idToken, $jwks);
        $this->assertEquals(TokenValidationResult::Valid, $actual);
    }

    public function testValidateIdTokenSignatureShouldNotValidateWhenKeysetNull()
    {
        $token = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6InRlc3QifQ.eyJhenAiOiJNekZsWmpreFpHSXRPV1UyTlMwMFpURm1MVGt3TXpjdE5UUXpOamRrTURCa016Y3pPbTl3WlhKaGRHOXlMV0U9IiwiYXV0aF90aW1lIjoxNDcwMzI2ODIwLCJhdWQiOlsiTXpGbFpqa3haR0l0T1dVMk5TMDBaVEZtTFRrd016Y3ROVFF6Tmpka01EQmtNemN6T205d1pYSmhkRzl5TFdFPSJdLCJhbXIiOlsiU0lNX1BJTiJdLCJub25jZSI6ImFkNjVmOGUwNzA3MTRlYTU5Yzc2NDRlZjE1OGM1MjM3IiwiaWF0IjoxNDcwMzI2ODIwLCJpc3MiOiJpbnRlZ3JhdGlvbjIuc2FuZGJveC5tb2JpbGVjb25uZWN0LmlvIiwiYWNyIjoiMiIsImV4cCI6MTQ3MDMzMDQyMCwic3ViIjoiYzIzMjQ2N2MtNDliMi0xMWU2LTlhYTgtMDI0MmFjMTEwMDAzIn0.PKN_cBANpXLegnmu6My4yhqcdbZaRVRLlseQJ4y1gMyFzLfRfYFHhbQC4xrIaN6ryxIsgJvFZ-047WfMwyptIhcP87exuYt6253k9gddndmjJtLuT9d5DB9bjiKkK49IdVsu91xyT1bXBHiWnZ-alFgnC4NfsCN3ec9TAynlivhzlBwghfdc6T8V27ewHWKg1ds0ZZbLQYZ0PtuLd0PW_SEOAnajVICBN7xm0rgxf9CTgOs5mBnKVCgPu1sJ-6bdcfA2VpLGLleuDHb9J9t6kbMytEMUjs4eDjdgxlogIUBOvY4MWfuu4l85GPZPMJ29aGmvAbns9e5Pufm8nO9DEA";

        $actual = TokenValidation::ValidateIdTokenSignature($token, null);

        $this->assertEquals(TokenValidationResult::JWKSError, $actual);
    }

    public function testValidateIdTokenSignatureShouldNotValidateWhenNoMatchingKey()
    {
        $jwksJson = "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\",\"kid\":\"test1\"},{\"e\":\"AQAB\",\"n\":\"sj_E_-OM6We6kM3Zl8LFQCbp4J1GA_RArvFo8Y0jLXR1xK20nJ0UIhCR1u4a3WD9dSwDRmcDa-3nT_1g5mzMOjBBO1I0VFDG61LyTkrbHhaz-VtRKjMcZMaVPHGC-nRogg92984s-ahO-Q4hkE05tiO96u3xj4S8_A3bsMIQCLQRYKS9_ovl_HxEJne3NFRkSZiiTym5g0H_nOrl2RlBYfV7GPst8Vzq45Mn1uDtCHocSeM3zunLG8TNOz0t6U_s0hAd0gKukoxgaGc1JDSsRWNs8r9SPniRMclKkcMWpdZQbLdT9ARsEB7i6w4x4C1p9i75PloXhwE-EOZ9kCeOtw\",\"kty\":\"RSA\",\"kid\":\"nottest\"}]}";

        $token = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6InRlc3QifQ.eyJhenAiOiJNekZsWmpreFpHSXRPV1UyTlMwMFpURm1MVGt3TXpjdE5UUXpOamRrTURCa016Y3pPbTl3WlhKaGRHOXlMV0U9IiwiYXV0aF90aW1lIjoxNDcwMzI2ODIwLCJhdWQiOlsiTXpGbFpqa3haR0l0T1dVMk5TMDBaVEZtTFRrd016Y3ROVFF6Tmpka01EQmtNemN6T205d1pYSmhkRzl5TFdFPSJdLCJhbXIiOlsiU0lNX1BJTiJdLCJub25jZSI6ImFkNjVmOGUwNzA3MTRlYTU5Yzc2NDRlZjE1OGM1MjM3IiwiaWF0IjoxNDcwMzI2ODIwLCJpc3MiOiJpbnRlZ3JhdGlvbjIuc2FuZGJveC5tb2JpbGVjb25uZWN0LmlvIiwiYWNyIjoiMiIsImV4cCI6MTQ3MDMzMDQyMCwic3ViIjoiYzIzMjQ2N2MtNDliMi0xMWU2LTlhYTgtMDI0MmFjMTEwMDAzIn0.PKN_cBANpXLegnmu6My4yhqcdbZaRVRLlseQJ4y1gMyFzLfRfYFHhbQC4xrIaN6ryxIsgJvFZ-047WfMwyptIhcP87exuYt6253k9gddndmjJtLuT9d5DB9bjiKkK49IdVsu91xyT1bXBHiWnZ-alFgnC4NfsCN3ec9TAynlivhzlBwghfdc6T8V27ewHWKg1ds0ZZbLQYZ0PtuLd0PW_SEOAnajVICBN7xm0rgxf9CTgOs5mBnKVCgPu1sJ-6bdcfA2VpLGLleuDHb9J9t6kbMytEMUjs4eDjdgxlogIUBOvY4MWfuu4l85GPZPMJ29aGmvAbns9e5Pufm8nO9DEA";
        $jwks = json_decode($jwksJson, true);

        $actual = TokenValidation::ValidateIdTokenSignature($token, $jwks);

        $this->assertEquals(TokenValidationResult::NoMatchingKey, $actual);
    }

    public function testValidateIdTokenSignatureShouldNotValidateWhenSignatureMissing()
    {
        $jwksJson = "{\"keys\":[{\"alg\":\"RS256\",\"e\":\"AQAB\",\"n\":\"hzr2li5ABVbbQ4BvdDskl6hejaVw0tIDYO-C0GBr5lRA-AXtmCO7bh0CEC9-R6mqctkzUhVnU22Vrj-B1J0JtJoaya9VTC3DdhzI_-7kxtIc5vrHq-ss5wo8-tK7UqtKLSRf9DcyZA0H9FEABbO5Qfvh-cfK4EI_ytA5UBZgO322RVYgQ9Do0D_-jf90dcuUgoxz_JTAOpVNc0u_m9LxGnGL3GhMbxLaX3eUublD40aK0nS2k37dOYOpQHxuAS8BZxLvS6900qqaZ6z0kwZ2WFq-hhk3Imd6fweS724fzqVslY7rHpM5n7z5m7s1ArurU1dBC1Dxw1Hzn6ZeJkEaZQ\",\"kty\":\"RSA\",\"use\":\"sig\"}]}";
        $token = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhenAiOiJNekZsWmpreFpHSXRPV1UyTlMwMFpURm1MVGt3TXpjdE5UUXpOamRrTURCa016Y3pPbTl3WlhKaGRHOXlMV0U9IiwiYXV0aF90aW1lIjoxNDcwMzI2ODIwLCJhdWQiOlsiTXpGbFpqa3haR0l0T1dVMk5TMDBaVEZtTFRrd016Y3ROVFF6Tmpka01EQmtNemN6T205d1pYSmhkRzl5TFdFPSJdLCJhbXIiOlsiU0lNX1BJTiJdLCJub25jZSI6ImFkNjVmOGUwNzA3MTRlYTU5Yzc2NDRlZjE1OGM1MjM3IiwiaWF0IjoxNDcwMzI2ODIwLCJpc3MiOiJpbnRlZ3JhdGlvbjIuc2FuZGJveC5tb2JpbGVjb25uZWN0LmlvIiwiYWNyIjoiMiIsImV4cCI6MTQ3MDMzMDQyMCwic3ViIjoiYzIzMjQ2N2MtNDliMi0xMWU2LTlhYTgtMDI0MmFjMTEwMDAzIn0.";
        $jwks = json_decode($jwksJson, true);

        $actual = TokenValidation::ValidateIdTokenSignature($token, $jwks);

        $this->assertEquals(TokenValidationResult::InvalidSignature, $actual);
    }

    public function testValidateIdTokenClaimsShouldReturnValidWhenAllClaimsValidWithAudArray()
    {
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOlsieC1jbGllbnRpZC14Il0sImF6cCI6IngtY2xpZW50aWQteCIsImlzcyI6Imh0dHA6Ly9tb2JpbGVjb25uZWN0LmlvIiwiZXhwIjoyMTQ3NDgzNjQ3LCJhdXRoX3RpbWUiOjIxNDc0ODM2NDd9.sPMj1GIchXKcVTXXRDb5tJeUFds7JkuREYYIuoBvpCM";

        $result = TokenValidation::ValidateIdTokenClaims($token, self::$clientId, self::$issuer, self::$nonce, self::$maxAge);

        $this->assertEquals(TokenValidationResult::Valid, $result);
    }

    public function testValidateIdTokenClaimsShouldReturnValidWhenAllClaimsValidWithAudString()
    {
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXgiLCJhenAiOiJ4LWNsaWVudGlkLXgiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3fQ.8M6GM8GlMxSH_T8mYiQXZyEx0h6h4OYm0QN0H07ixwI";

        $result = TokenValidation::ValidateIdTokenClaims($token, self::$clientId, self::$issuer, self::$nonce, self::$maxAge);

        $this->assertEquals(TokenValidationResult::Valid, $result);
    }

    public function testValidateIdTokenClaimsShouldReturnInvalidNonceWhenNonceNotMatching()
    {
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXgiLCJhenAiOiJ4LWNsaWVudGlkLXgiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3fQ.8M6GM8GlMxSH_T8mYiQXZyEx0h6h4OYm0QN0H07ixwI";

        $result = TokenValidation::ValidateIdTokenClaims($token, self::$clientId, self::$issuer, "notnonce", self::$maxAge);

        $this->assertEquals(TokenValidationResult::InvalidNonce, $result);
    }

    public function testValidateIdTokenClaimsShouldReturnInvalidAudWhenClientIdNotMatching()
    {
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXgiLCJhenAiOiJ4LWNsaWVudGlkLXgiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3fQ.8M6GM8GlMxSH_T8mYiQXZyEx0h6h4OYm0QN0H07ixwI";

        $result = TokenValidation::ValidateIdTokenClaims($token, "notclientid", self::$issuer, self::$nonce, self::$maxAge);

        $this->assertEquals(TokenValidationResult::InvalidAudAndAzp, $result);
    }

    public function testValidateIdTokenClaimsShouldReturnInvalidAudWhenClientIdNotInAudArray()
    {
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOlsibm90Y2xpZW50aWQiXSwiYXpwIjoieC1jbGllbnRpZC14IiwiaXNzIjoiaHR0cDovL21vYmlsZWNvbm5lY3QuaW8iLCJleHAiOjIxNDc0ODM2NDcsImF1dGhfdGltZSI6MjE0NzQ4MzY0N30.Is8A9klSQZYEs0MAScdyq_EqcpCy6r_56yzizktclNQ";

        $result = TokenValidation::ValidateIdTokenClaims($token, self::$clientId, self::$issuer, self::$nonce, self::$maxAge);

        $this->assertEquals(TokenValidationResult::InvalidAudAndAzp, $result);
    }

    public function testValidateIdTokenClaimsShouldReturnInvalidIssuerWhenIssuerNotMatching()
    {
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXgiLCJhenAiOiJ4LWNsaWVudGlkLXgiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3fQ.8M6GM8GlMxSH_T8mYiQXZyEx0h6h4OYm0QN0H07ixwI";

        $result = TokenValidation::ValidateIdTokenClaims($token, self::$clientId, "notissuer", self::$nonce, self::$maxAge);

        $this->assertEquals(TokenValidationResult::InvalidIssuer, $result);
    }

    public function testValidateIdTokenClaimsShouldReturnIdTokenExpiredWhenExpValueHasPassed()
    {
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXgiLCJhenAiOiJ4LWNsaWVudGlkLXgiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MTQ1MTY1MTY2MiwiYXV0aF90aW1lIjoyMTQ3NDgzNjQ3fQ.4MhPMtGMKBbzGrpT3TC4DUzR__sBsz2J6UqXdPksJLw";

        $result = TokenValidation::ValidateIdTokenClaims($token, self::$clientId, self::$issuer, self::$nonce, self::$maxAge);

        $this->assertEquals(TokenValidationResult::IdTokenExpired, $result);
    }

    public function testValidateIdTokenClaimsShouldReturnMaxAgePassedWhenMaxAgePassed()
    {
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6IjEyMzQ1Njc4OTAiLCJhdWQiOiJ4LWNsaWVudGlkLXgiLCJhenAiOiJ4LWNsaWVudGlkLXgiLCJpc3MiOiJodHRwOi8vbW9iaWxlY29ubmVjdC5pbyIsImV4cCI6MjE0NzQ4MzY0NywiYXV0aF90aW1lIjoxNDUxNjUxNjYyfQ.novjze9SAX5QF-EKhdelob4UAhB_ZNEC-VzrcDRqXCk";

        $result = TokenValidation::ValidateIdTokenClaims($token, self::$clientId, self::$issuer, self::$nonce, 2600);

        $this->assertEquals(TokenValidationResult::MaxAgePassed, $result);
    }

    public function testValidateAccessTokenShouldReturnValidIfExistsAndNotExpired()
    {
        $json = "{\"access_token\":\"zmxncbvlaksjdhfgeteywteuiroqp\",\"expires_in\":36000}";
        $tokenResponse = json_decode($json, true);

        $result = TokenValidation::ValidateAccessToken($tokenResponse);

        $this->assertEquals(TokenValidationResult::Valid, $result);
    }

    public function testValidateAccessTokenShouldReturnExpiredIfExistsAndExpired()
    {
        $json = "{\"access_token\":\"zmxncbvlaksjdhfgeteywteuiroqp\",\"time_received\":\"2016-01-01T12:34:22\",\"expires_in\":3600}";
        $tokenResponse = json_decode($json, true);

        $result = TokenValidation::ValidateAccessToken($tokenResponse);

        $this->assertEquals(TokenValidationResult::AccessTokenExpired, $result);
    }

    public function testValidateAccessTokenShouldReturnMissingIfNotExists()
    {
        // This test will stop working at 01/19/2038 @ 3:14am (UTC) (but so will all unix timestamps)
        $json = "{\"access_token\":\"\",\"expires_in\":2147483647}";
        $tokenResponse = json_decode($json, true);

        $result = TokenValidation::ValidateAccessToken($tokenResponse);

        $this->assertEquals(TokenValidationResult::AccessTokenMissing, $result);
    }
}
