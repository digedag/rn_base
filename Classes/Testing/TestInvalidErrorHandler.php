<?php

namespace Sys25\RnBase\Testing;

use Exception;
use Sys25\RnBase\Configuration\ConfigurationInterface;

/**
 * @author Hannes Bochmann
 */
class TestInvalidErrorHandler
{
    public function handleException($actionName, Exception $e, ConfigurationInterface $configurations)
    {
        return 'should not be used';
    }
}
