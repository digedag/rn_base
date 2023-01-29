<?php

namespace Sys25\RnBase\Testing;

use Exception;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Exception\ExceptionHandlerInterface;

class TestErrorHandler implements ExceptionHandlerInterface
{
    public function handleException($actionName, Exception $e, ConfigurationInterface $configurations)
    {
        return sprintf('TestErrorHandler with %s and %s', $actionName, $e->getMessage());
    }
}
