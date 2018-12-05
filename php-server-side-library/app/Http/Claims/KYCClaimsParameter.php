<?php

namespace App\Http\Claims;

use MCSDK\Constants\LinkRels;
use MCSDK\Constants\Parameters;

/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 29.11.18
 * Time: 11.50
 */

class KYCClaimsParameter {
    private $name;
    private $givenName;
    private $familyName;
    private $address;
    private $housenoOrHousename;
    private $postalCode;
    private $town;
    private $country;
    private $birthdate;

    private $nameHashed;
    private $givenNameHashed;
    private $familyNameHashed;
    private $addressHashed;
    private $housenoOrHousenameHashed;
    private $postalCodeHashed;
    private $townHashed;
    private $countryHashed;
    private $birthdateHashed;

    public function setName($val)
    {
        $this->name = $val;
        return $this;
    }

    public function setGivenName($val)
    {
        $this->givenName = $val;
        return $this;
    }

    public function setFamilyName($val)
    {
        $this->familyName = $val;
        return $this;
    }

    public function setAddress($val)
    {
        $this->address = $val;
        return $this;
    }

    public function setHousenoOrHousename($val)
    {
        $this->housenoOrHousename = $val;
        return $this;
    }

    public function setPostalCode($val)
    {
        $this->postalCode = $val;
        return $this;
    }

    public function setTown($val)
    {
        $this->town = $val;
        return $this;
    }

    public function setCountry($val)
    {
        $this->country = $val;
        return $this;
    }

    public function setBirthdate($val)
    {
        $this->birthdate = $val;
        return $this;
    }

    public function setNameHashed($val)
    {
        $this->nameHashed = $val;
        return $this;
    }

    public function setGivenNameHashed($val)
    {
        $this->givenNameHashed = $val;
        return $this;
    }

    public function setFamilyNameHashed($val)
    {
        $this->familyNameHashed = $val;
        return $this;
    }

    public function setAddressHashed($val)
    {
        $this->addressHashed = $val;
        return $this;
    }

    public function setHousenoOrHousenameHashed($val)
    {
        $this->housenoOrHousenameHashed = $val;
        return $this;
    }

    public function setPostalCodeHashed($val)
    {
        $this->postalCodeHashed = $val;
        return $this;
    }

    public function setTownHashed($val)
    {
        $this->townHashed = $val;
        return $this;
    }

    public function setCountryHashed($val)
    {
        $this->countryHashed = $val;
        return $this;
    }

    public function setBirthdateHashed($val)
    {
        $this->birthdateHashed = $val;
        return $this;
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

    public function toJson()
    {
        $claimsJson = new ClaimsToJson();
        $claimsJson->setMainClaim(LinkRels::PREMIUMINFO)
            ->addKeyValue(Parameters::NAME, $this->name)
            ->addKeyValue(Parameters::GIVEN_NAME, $this->givenName)
            ->addKeyValue(Parameters::FAMILY_NAME, $this->familyName)
            ->addKeyValue(Parameters::ADDRESS, $this->address)
            ->addKeyValue(Parameters::HOUSENO_OR_HOUSENAME, $this->housenoOrHousename)
            ->addKeyValue(Parameters::POSTAL_CODE, $this->postalCode)
            ->addKeyValue(Parameters::TOWN, $this->town)
            ->addKeyValue(Parameters::COUNTRY, $this->country)
            ->addKeyValue(Parameters::BIRTHDATE, $this->birthdate)

            ->addKeyValue(Parameters::NAME_HASHED, $this->nameHashed)
            ->addKeyValue(Parameters::GIVEN_NAME_HASHED, $this->givenNameHashed)
            ->addKeyValue(Parameters::FAMILY_NAME_HASHED, $this->familyNameHashed)
            ->addKeyValue(Parameters::ADDRESS_HASHED, $this->addressHashed)
            ->addKeyValue(Parameters::HOUSENO_OR_HOUSENAME_HASHED, $this->housenoOrHousenameHashed)
            ->addKeyValue(Parameters::POSTAL_CODE_HASHED, $this->postalCodeHashed)
            ->addKeyValue(Parameters::TOWN_HASHED, $this->townHashed)
            ->addKeyValue(Parameters::COUNTRY_HASHED, $this->countryHashed)
            ->addKeyValue(Parameters::BIRTHDATE_HASHED, $this->birthdateHashed);

        return $claimsJson->build();
    }
}


