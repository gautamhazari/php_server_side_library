<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 20.11.18
 * Time: 11.49
 */

namespace App\Http;


use App\Http\Config\MainConfig;
use App\Http\Constants\Constants;
use MCSDK\MobileConnectStatus;
use MCSDK\Utils\JsonUtils;
use MCSDK\Web\ResponseConverter;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Component\HttpFoundation\Response;

class HttpUtils
{
    public static function createResponse(MobileConnectStatus $status)    {
        if ($status->getState() !== null) return $status;
        else {
            $json = json_decode(JsonUtils::toJson(ResponseConverter::Convert($status)));
            $clear_json = (object)array_filter((array)$json);
            return response()->json($clear_json, Response::HTTP_FOUND);
        }
    }

    public static function redirectToView(MobileConnectStatus $status, String $operationStatus) {
        $modelMap = array(Constants::OPERATION_KEY => $operationStatus);
        if (!empty($status->getErrorCode())) {
            $modelMap[Constants::STATUS_KEY] = $status;
            return view(Constants::FAIL_KEY, $modelMap);
        }
        return view(Constants::SUCCESS_KEY, $modelMap);
    }

    public static function convertToListBySpace($initString) {
        return preg_split("/[\s,]+/", $initString);
    }
}

