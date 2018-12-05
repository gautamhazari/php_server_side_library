<?php

namespace App\Http\Claims;
use MCSDK\Constants\Parameters;

/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 30.11.18
 * Time: 12.18
 */

class ClaimsToJson
{
    private $_mainClaim;
    private $valuesArr;

    public function __construct()
    {
        $this->valuesArr = array();
    }

    public function setMainClaim($mainClaim){
        $this->_mainClaim = $mainClaim;
        return $this;
    }

    public function addKeyValue($key, $value){
        $this->addValToArr($this->valuesArr, $key, $value);
        return $this;
    }

    public function build(){
        $claimsArr = array($this->_mainClaim => $this->valuesArr);
        return json_encode($claimsArr, JSON_FORCE_OBJECT);
    }

    public function addValToArr($array, $key, $value){
        if (!empty($value)) {
            $this->valuesArr = array_add($array, $key, array(Parameters::VALUE => $value));
        }
    }

}
