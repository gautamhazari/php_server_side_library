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
    const MESSAGE = "Failed to process the scope: '%s'. The scope doesn't support (scope isn't correct or doesn't match with version)";

    public function __construct($scope) {
        $message = sprintf(self::MESSAGE, $scope);
        parent::__construct($message);
    }
}