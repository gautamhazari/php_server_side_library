<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 26.11.18
 * Time: 17.51
 */

namespace App\Http;


use App\Http\Constants\Constants;

class ConfigUtils
{
    public static function getStringFromFile(String $fileName)
    {
        $string = file_get_contents(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.Constants::CONFIG_DIR_NAME.DIRECTORY_SEPARATOR.$fileName);
        return $string;
    }

    public static function getJsonFromFile(String $fileName)
    {
        $string = ConfigUtils::getStringFromFile($fileName);
        return json_decode($string, true);
    }


}