<?php
/**
 * Created by PhpStorm.
 * User: asushkov
 * Date: 9.1.19
 * Time: 15.30
 */

namespace MCSDK\Exceptions;


class InvalidScopeException extends \Exception
{
    const MESSAGE = "Failed to process the scope: '%s'. The scope doesn't support (scope isn't correct or doesn't match with version (%s))";

    public function __construct($scope, $version=null) {
        $message = sprintf(self::MESSAGE, $scope, $version);
        parent::__construct($message);
    }
}