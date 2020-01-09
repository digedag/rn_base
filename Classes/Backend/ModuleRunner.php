<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Module Runner.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *        GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Backend_ModuleRunner
{
    /**
     * This method forwards the call like the TYPO3 CMS 7.x request handler.
     *
     * @param string $moduleSignature
     *
     * @return bool TRUE, if the request request could be dispatched
     */
    public function callModule($moduleSignature)
    {
        try {
            $moduleConfiguration = $this->getModuleConfiguration($moduleSignature);
            if (empty($moduleConfiguration['routeTarget'])) {
                throw new \RuntimeException('Module target'.$moduleSignature.' is not configured.', 1289918327);
            }
        } catch (RuntimeException $e) {
            return false;
        }

        $targetIdentifier = $moduleConfiguration['routeTarget'];
        $target = $this->getCallableFromTarget($targetIdentifier);

        self::initTargetConf($target, $moduleSignature);

        // TYPO3\CMS\Core\Http\Response since 7.x
        $response = null;
        // Psr\Http\Message\ServerRequestInterface since 7.x
        $request = null;

        return call_user_func_array(
            $target,
            [$request, $response]
        );
    }

    /**
     * Initializes the modconf.
     *
     * @param string $target
     * @param string $moduleSignature
     */
    public function initTargetConf($target, $moduleSignature = '')
    {
        $moduleConfiguration = $this->getModuleConfiguration($moduleSignature);

        // set the MCONF
        if (is_object($target)) {
            $target->MCONF = $moduleConfiguration;
        }

        // set the global SOBE for backward compatibility
        $GLOBALS['SOBE'] = $target;

        // set dispatch mode for module
        $GLOBALS['MCONF']['script'] = '_DISPATCH';
    }

    /**
     * Returns the module configuration which is provided during module registration.
     *
     * @param string $moduleName
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    protected function getModuleConfiguration($moduleName = '')
    {
        if ('' === $moduleName) {
            if (\tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
                $moduleName = \tx_rnbase::makeInstance(\TYPO3\CMS\Backend\Routing\Router::class)->match(
                    \Sys25\RnBase\Frontend\Request\Parameters::getGetParameters('route')
                )->getOption('moduleName');
            } else {
                $moduleName = Sys25\RnBase\Frontend\Request\Parameters::getGetParameters('M');
            }
        }

        if (!isset($GLOBALS['TBE_MODULES']['_configuration'][$moduleName])) {
            throw new \RuntimeException('Module '.$moduleName.' is not configured.', 1289918326);
        }

        return $GLOBALS['TBE_MODULES']['_configuration'][$moduleName];
    }

    /**
     * Creates a callable out of the given parameter, which can be a string, a callable / closure or an array
     * which can be handed to call_user_func_array().
     *
     * This method is taken from TYPO3\CMS\Core\Http\Dispatcher (TYPO3-7.x)
     *
     * @param array|string|callable $target the target which is being resolved
     *
     * @throws \InvalidArgumentException
     *
     * @return callable
     */
    protected function getCallableFromTarget($target)
    {
        if (is_array($target)) {
            return $target;
        }

        if (is_object($target) && $target instanceof \Closure) {
            return $target;
        }

        // Only a class name is given
        if (is_string($target) && false === strpos($target, ':')) {
            $targetObject = tx_rnbase::makeInstance($target);
            if (!method_exists($targetObject, '__invoke')) {
                throw new InvalidArgumentException('Object "'.$target.'" doesn\'t implement an __invoke() method and cannot be used as target.', 1442431631);
            }

            return $targetObject;
        }

        // Check if the target is a concatenated string of "className::actionMethod"
        if (is_string($target) && false !== strpos($target, '::')) {
            list($className, $methodName) = explode('::', $target, 2);
            $targetObject = Tx_Rnbase_Utility_T3General::makeInstance($className);

            return [$targetObject, $methodName];
        }

        // This needs to be checked at last as a string with object::method is recognize as callable
        if (is_callable($target)) {
            return $target;
        }

        throw new InvalidArgumentException('Invalid target for "'.$target.'", as it is not callable.', 1425381442);
    }
}
