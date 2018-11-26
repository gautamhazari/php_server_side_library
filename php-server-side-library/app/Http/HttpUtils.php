<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 20.11.18
 * Time: 11.49
 */

namespace App\Http;


use App\Http\Config\MainConfig;
use MCSDK\MobileConnectStatus;
use MCSDK\Utils\JsonUtils;
use MCSDK\Web\ResponseConverter;

class HttpUtils
{
    public static function CreateResponse(MobileConnectStatus $status)    {
        if ($status->getState() !== null) return $status;
        else {
            $json = json_decode(JsonUtils::toJson(ResponseConverter::Convert($status)));
            $clear_json = (object)array_filter((array)$json);
            return response()->json($clear_json);
        }
    }
}

