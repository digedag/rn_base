<?php

namespace Sys25\RnBase\Backend\Utility;

use Sys25\RnBase\Utility\TYPO3;

/***************************************************************
 * Copyright notice
 *
 * (c) 2015-2020 René Nitzsche <rene@system25.de>
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
 * Wrapper for \TYPO3\CMS\Backend\Utility\BackendUtility.
 *
 * @author Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *        GNU Lesser General Public License, version 3 or later
 *
 * @method static string getModuleUrl(string $moduleName, array $urlParameters)
 * @method static array|bool readPageAccess(int $id, string $perms_clause)
 * @method static array|null getRecord(string $table, int $uid, string $fields = null, string $where = '', bool $useDeleteClause = false)
 * @method static string getFuncMenu(mixed $mainParams, string $elementName, string $currentValue, array $menuItems, string $script = '', string $addParams = '')
 * @method static array getModuleData(array $MOD_MENU_MOD_MENU, array $CHANGED_SETTINGS, string $modName, string $type = '', string $dontValidateList = '', string $setDefaultList = '')
 */
class BackendUtility
{
    /**
     * Magic method to forward the call to the right be util.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, array $arguments)
    {
        return call_user_func_array(
            [
                static::getBackendUtilityClass(),
                $method,
            ],
            $arguments
        );
    }

    /**
     * Returns the be util class depending on TYPO3 version.
     *
     * @return string
     */
    protected static function getBackendUtilityClass()
    {
        return \TYPO3\CMS\Backend\Utility\BackendUtility::class;
    }

    /**
     * Generates a token and returns a parameter for the URL.
     *
     * @param string $formName
     * @param string $tokenName
     *
     * @throws \InvalidArgumentException
     *
     * @return string A URL GET variable including ampersand
     */
    public static function getUrlToken($formName = 'securityToken', $tokenName = 'formToken')
    {
        $formProtection = \TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get();

        return '&'.$tokenName.'='.$formProtection->generateToken($formName);
    }

    /**
     * Checks if we are in dispatch mode.
     *
     * @return bool
     */
    public static function isDispatchMode()
    {
        return '_DISPATCH' === $GLOBALS['MCONF']['script'];
    }

    /* *** ************************************ *** *
     * *** Methods with Parameters as Reference *** *
     * *** ************************************ *** */

    /**
     * Find page-tree PID for versionized record.
     *
     * @param string $table                Table name
     * @param array  $rr                   Record array passed by reference. As minimum, "pid" and "uid" fields must exist! "t3ver_oid" and "t3ver_wsid" is nice and will save you a DB query.
     * @param bool   $ignoreWorkspaceMatch Ignore workspace match
     */
    public static function fixVersioningPid($table, &$rr, $ignoreWorkspaceMatch = false)
    {
        $util = static::getBackendUtilityClass();
        $util::fixVersioningPid($table, $rr, $ignoreWorkspaceMatch);
    }

    /**
     * Workspace Preview Overlay.
     *
     * @param string $table             Table name
     * @param array  $row               Record array passed by reference. As minimum, the "uid" and  "pid" fields must exist! Fake fields cannot exist since the fields in the array is used as field names in the SQL look up. It would be nice to have fields like "t3ver_state" and "t3ver_mode_id" as well to avoid a new lookup inside movePlhOL().
     * @param int    $wsid              Workspace ID, if not specified will use static::getBackendUserAuthentication()->workspace
     * @param bool   $unsetMovePointers If TRUE the function does not return a "pointer" row for moved records in a workspace
     *
     * @see fixVersioningPid()
     */
    public static function workspaceOL($table, &$row, $wsid = -99, $unsetMovePointers = false)
    {
        $util = static::getBackendUtilityClass();
        $util::workspaceOL($table, $row, $wsid, $unsetMovePointers);
    }

    /**
     * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getLinkToDataHandlerAction
     * @see \TYPO3\CMS\Backend\Template\DocumentTemplate::issueCommand
     *
     * @param string $getParameters
     * @param string $redirectUrl
     *
     * @return string
     */
    public static function issueCommand($getParameters, $redirectUrl = '')
    {
        $link = \TYPO3\CMS\Backend\Utility\BackendUtility::getLinkToDataHandlerAction($getParameters, $redirectUrl);

        return $link;
    }

    /**
     * Returns the URL to a given module.
     *
     * @param string $moduleName    Name of the module
     * @param array  $urlParameters URL parameters that should be added as key value pairs
     *
     * @return string Calculated URL
     */
    public static function getModuleUrl($moduleName, $urlParameters = [])
    {
        /* @var $uriBuilder \TYPO3\CMS\Backend\Routing\UriBuilder */
        $uriBuilder = \tx_rnbase::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);

        try {
            $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);
        } catch (\TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException $e) {
            if (TYPO3::isTYPO95OrHigher()) {
                $uri = $uriBuilder->buildUriFromRoutePath($moduleName, $urlParameters);
            } else {
                // no route registered, use the fallback logic to check for a module
                $uri = $uriBuilder->buildUriFromModule($moduleName, $urlParameters);
            }
        }

        return (string) $uri;
    }
}
