<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 20.11.18
 * Time: 12.33
 */

namespace App\Http\Config;


use App\Http\ConfigUtils;
use App\Http\Constants\Constants;
use MCSDK\Constants\Parameters;
use MCSDK\MobileConnectConfig;

abstract class BaseConfig
{
    protected $client_id;
    protected $clientSecret;
    protected $discoveryURL;
    protected $redirectURL;
    protected $xRedirect;
    protected $includeRequestIP;
    protected $loginHintTokenPreference;
    protected $apiVersion;
    protected $clientName;
    protected $scopes;
    protected $context;
    protected $binding_message;

    //KYC params
    protected $name;
    protected $givenName;
    protected $familyName;
    protected $address;
    protected $housenoOrHousename;
    protected $postalCode;
    protected $town;
    protected $country;
    protected $birthdate;
    protected $nameHashed;
    protected $givenNameHashed;
    protected $familyNameHashed;
    protected $addressHashed;
    protected $housenoOrHousenameHashed;
    protected $postalCodeHashed;
    protected $townHashed;
    protected $countryHashed;
    protected $birthdateHashed;

    public function getCommonValuesFromJson($json) {
        $this->client_id =  $json[Parameters::CLIENT_ID];
        $this->clientSecret = $json[Parameters::CLIENT_SECRET];
        $this->redirectURL = $json[Constants::REDIRECT_URL];
        $this->apiVersion = isset($json[Parameters::API_VERS])?$json[Parameters::API_VERS]: null;
        $this->clientName = isset($json[Parameters::CLIENT_NAME])?$json[Parameters::CLIENT_NAME]: null;
        $this->scopes = $json[Parameters::SCOPE];
        $this->context = isset($json[Parameters::CONTEXT])?$json[Parameters::CONTEXT]: null;
        $this->binding_message = isset($json[Parameters::BINDING_MESSAGE])?$json[Parameters::BINDING_MESSAGE]: null;
    }

    public function getKycValuesFromFile(String $fileName) {
        $json = ConfigUtils::getJsonFromFile($fileName);
        $this->name = isset($json[Parameters::NAME])?$json[Parameters::NAME]: null;
        $this->givenName = isset($json[Parameters::GIVEN_NAME])?$json[Parameters::GIVEN_NAME]: null;
        $this->familyName = isset($json[Parameters::FAMILY_NAME])?$json[Parameters::FAMILY_NAME]: null;
        $this->address = isset($json[Parameters::ADDRESS])?$json[Parameters::ADDRESS]: null;
        $this->housenoOrHousename = isset($json[Parameters::HOUSENO_OR_HOUSENAME])?$json[Parameters::HOUSENO_OR_HOUSENAME]: null;
        $this->postalCode = isset($json[Parameters::POSTAL_CODE])?$json[Parameters::POSTAL_CODE]: null;
        $this->town = isset($json[Parameters::TOWN])?$json[Parameters::TOWN]: null;
        $this->country = isset($json[Parameters::COUNTRY])?$json[Parameters::COUNTRY]: null;
        $this->birthdate = isset($json[Parameters::BIRTHDATE])?$json[Parameters::BIRTHDATE]: null;
        $this->nameHashed = isset($json[Parameters::NAME_HASHED])?$json[Parameters::NAME_HASHED]: null;
        $this->givenNameHashed = isset($json[Parameters::GIVEN_NAME_HASHED])?$json[Parameters::GIVEN_NAME_HASHED]: null;
        $this->familyNameHashed = isset($json[Parameters::FAMILY_NAME_HASHED])?$json[Parameters::FAMILY_NAME_HASHED]: null;
        $this->addressHashed = isset($json[Parameters::ADDRESS_HASHED])?$json[Parameters::ADDRESS_HASHED]: null;
        $this->housenoOrHousenameHashed = isset($json[Parameters::HOUSENO_OR_HOUSENAME_HASHED])?$json[Parameters::HOUSENO_OR_HOUSENAME_HASHED]: null;
        $this->postalCodeHashed = isset($json[Parameters::POSTAL_CODE_HASHED])?$json[Parameters::POSTAL_CODE_HASHED]: null;
        $this->townHashed = isset($json[Parameters::TOWN_HASHED])?$json[Parameters::TOWN_HASHED]: null;
        $this->countryHashed = isset($json[Parameters::COUNTRY_HASHED])?$json[Parameters::COUNTRY_HASHED]: null;
        $this->birthdateHashed = isset($json[Parameters::BIRTHDATE_HASHED])?$json[Parameters::BIRTHDATE_HASHED]: null;
    }

    public function getMcConfig() {
        $config = new MobileConnectConfig();
        $config->setClientId($this->client_id);
        $config->setClientSecret($this->clientSecret);
        $config->setDiscoveryUrl($this->discoveryURL);
        $config->setRedirectUrl($this->redirectURL);

        return $config;
    }

    public function getClientId() {
        return $this->client_id;
    }

    public function getClientSecret() {
        return $this->clientSecret;
    }

    public function getRedirectUrl() {
        return $this->redirectURL;
    }

    public function isXredirect() {
        return $this->xRedirect == "True";
    }

    public function isIncludeRequestIP() {
        return $this->includeRequestIP == "True";
    }

    public function isLoginHintTokenPreference () {
        return $this->loginHintTokenPreference == "True";
    }

    public function getApiVersion() {
        return $this->apiVersion;
    }

    public function getClientName() {
        return $this->clientName;
    }

    public function getScopes() {
        return $this->scopes;
    }

    public function getContext() {
        return $this->context;
    }

    public function getBindingMessage() {
        return $this->binding_message;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * @return mixed
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return mixed
     */
    public function getHousenoOrHousename()
    {
        return $this->housenoOrHousename;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @return mixed
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return mixed
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * @return mixed
     */
    public function getNameHashed()
    {
        return $this->nameHashed;
    }

    /**
     * @return mixed
     */
    public function getGivenNameHashed()
    {
        return $this->givenNameHashed;
    }

    /**
     * @return mixed
     */
    public function getFamilyNameHashed()
    {
        return $this->familyNameHashed;
    }

    /**
     * @return mixed
     */
    public function getAddressHashed()
    {
        return $this->addressHashed;
    }

    /**
     * @return mixed
     */
    public function getHousenoOrHousenameHashed()
    {
        return $this->housenoOrHousenameHashed;
    }

    /**
     * @return mixed
     */
    public function getPostalCodeHashed()
    {
        return $this->postalCodeHashed;
    }

    /**
     * @return mixed
     */
    public function getTownHashed()
    {
        return $this->townHashed;
    }

    /**
     * @return mixed
     */
    public function getCountryHashed()
    {
        return $this->countryHashed;
    }

    /**
     * @return mixed
     */
    public function getBirthdateHashed()
    {
        return $this->birthdateHashed;
    }
}