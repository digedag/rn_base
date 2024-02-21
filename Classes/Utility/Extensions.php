<?php

namespace Sys25\RnBase\Utility;

use InvalidArgumentException;
use tx_rnbase;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015-2021 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Wrapperclass for TYPO3 Extension Manager.
 *
 * @author René Nitzsche
 *
 * @method static \TYPO3\CMS\Core\Cache\CacheManager getCacheManager()
 * @method static \TYPO3\CMS\Extbase\SignalSlot\Dispatcher getSignalSlotDispatcher()
 * PATHS and other evaluation
 * @method static bool isLoaded(string $key, bool $exitOnError = false)
 * @method static string extPath(string $key, string $script = '')
 * @method static string extRelPath(string $key)
 * @method static string siteRelPath(string $key)
 * @method static string getExtensionVersion(string $key)
 * Adding BACKEND features
 * @method static void addTCAcolumns(string $table, array $columnArray, bool $addTofeInterface = false)
 * @method static void addToAllTCAtypes(string $table, string $newFieldsString, string $typeList = '', string $position = '')
 * @method static void addFieldsToAllPalettesOfField(string $table, string $field, string $addFields, string $insertionPosition = '')
 * @method static void addFieldsToPalette(string $table, string $palette, string $addFields, string $insertionPosition = '')
 * @method static void addTcaSelectItem(string $table, string $field, array $item, string $relativeToField = '', string $relativePosition = '')
 * @method static array getFileFieldTCAConfig(string $fieldName, array $customSettingOverride = [], string $allowedFileExtensions = '', string $disallowedFileExtensions = '')
 * @method static void addFieldsToUserSettings(string $addFields, string $insertionPosition = '')
 * @method static void allowTableOnStandardPages(string $table)
 * @method static void addExtJSModule(string $extensionName, string $mainModuleName, string $subModuleName = '', string $position = '', array $moduleConfiguration = [])
 * @method static array configureModule(string $moduleSignature, string $modulePath)
 * @method static void addModule(string $main, string $sub = '', string $position = '', string $path = '', string $moduleConfiguration = [])
 * @method static void registerExtDirectComponent(string $endpointName, string $callbackClass, string $moduleName = null, string $accessLevel = null)
 * @method static void registerAjaxHandler(string $ajaxId, string $callbackMethod, bool $csrfTokenCheck = true)
 * @method static void insertModuleFunction(string $modname, string $className, string $classPath = null, string $title, string $MM_key = 'function', string $WS = '')
 * @method static void appendToTypoConfVars(string $group, string $key, string $content)
 * @method static void addPageTSConfig(string $content)
 * @method static void addUserTSConfig(string $content)
 * Adding SERVICES features
 * @method static void addService(string $extKey, string $serviceType, string $serviceKey, array $info)
 * @method static array|false findService(string $serviceType, string $serviceSubType = '', array $excludeServiceKeys = [])
 * @method static array findServiceByKey(string $serviceKey)
 * @method static bool isServiceAvailable(string $serviceType, string $serviceKey, array $serviceDetails)
 * @method static void deactivateService(string $serviceType, string $serviceKey)
 * Adding FRONTEND features
 * @method static void addPlugin(array $itemArray, string $type = 'list_type', string $extensionKey = null)
 * @method static void addPiFlexFormValue(string $piKeyToMatch, string $value, string $CTypeToMatch = 'list')
 * @method static void addToInsertRecords(string $table, string $content_table = 'tt_content', string $content_field = 'records')
 * @method static void addStaticFile(string $extKey, string $path, string $title)
 * @method static void registerPageTSConfigFile(string $extKey, string $filePath, string $title)
 * @method static void addTypoScriptSetup(string $content)
 * @method static void addTypoScriptConstants(string $content)
 * @method static void addLLrefForTCAdescr(string $tca_descr_key, string $file_ref)
 * @method static array getLoadedExtensionListArray()
 *
 * @see TYPO3\CMS\Core\Utility\ExtensionManagementUtility
 */
class Extensions
{
    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, array $arguments)
    {
        return call_user_func_array([static::getExtensionManagementUtilityClass(), $method], $arguments);
    }

    /**
     * @return string
     */
    protected static function getExtensionManagementUtilityClass()
    {
        return 'TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility';
    }

    /**
     * Registers an Extbase module (main or sub) to the backend interface.
     * FOR USE IN ext_tables.php FILES.
     *
     * @param string $extensionName
     * @param string $mainModuleName
     * @param string $subModuleName
     * @param string $position
     * @param array  $controllerActions
     * @param array  $moduleConfiguration
     *
     * @throws InvalidArgumentException
     */
    public static function registerModule(
        $extensionName,
        $mainModuleName = '',
        $subModuleName = '',
        $position = '',
        array $controllerActions = [],
        array $moduleConfiguration = []
    ) {
        if (TYPO3::isTYPO121OrHigher()) {
            // since T3 12 it is not possible to register be module programmatically anymore
            return;
        }
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            $extensionName,
            $mainModuleName,
            $subModuleName,
            $position,
            $controllerActions,
            $moduleConfiguration
        );
        static::fixModuleRouteTarget($extensionName, $mainModuleName, $subModuleName, $moduleConfiguration);
    }

    /**
     * Correct the route target.
     *
     * Since TYPO3 9.5 the route target is hardcoded to \TYPO3\CMS\Extbase\Core\Bootstrap::handleBackendRequest
     * We want the route target that was initially configured to the rnbase based module.
     *
     * @param $extensionName
     * @param string $mainModuleName
     * @param string $subModuleName
     * @param array $moduleConfiguration
     */
    private static function fixModuleRouteTarget(
        $extensionName,
        $mainModuleName = '',
        $subModuleName = '',
        array $moduleConfiguration = []
    ) {
        if (TYPO3::isTYPO90OrHigher() && $moduleConfiguration['routeTarget']) {
            $moduleName = static::buildModuleSignature($extensionName, $mainModuleName, $subModuleName);
            tx_rnbase::makeInstance(\TYPO3\CMS\Backend\Routing\Router::class)->getRoutes()[$moduleName]->setOption(
                'target',
                $moduleConfiguration['routeTarget']
            );
        }
    }

    /**
     * Creates the Backend Module signature by  extension, module and submodule.
     *
     * Since TYPO3 10 ['TBE_MODULES']['_configuration'] are not sorted by adding order in every case.
     * So we has parts taken from \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule
     * and build the signature to fix the routeTarget.
     *
     * @param $extensionName
     * @param string $mainModuleName
     * @param string $subModuleName
     *
     * @return string
     */
    private static function buildModuleSignature(
        $extensionName,
        $mainModuleName = '',
        $subModuleName = ''
    ) {
        if (false !== $delimiterPosition = strrpos($extensionName, '.')) {
            $extensionName = substr($extensionName, $delimiterPosition + 1);
        }
        $extensionName = str_replace(' ', '', ucwords(str_replace('_', ' ', $extensionName)));

        if ('' !== $mainModuleName && !array_key_exists($mainModuleName, $GLOBALS['TBE_MODULES'])) {
            $mainModuleName = $extensionName.Strings::underscoredToUpperCamelCase($mainModuleName);
        } else {
            $mainModuleName = '' !== $mainModuleName ? $mainModuleName : 'web';
        }

        $moduleSignature = $mainModuleName;
        if ('' !== $subModuleName) {
            $subModuleName = $extensionName.Strings::underscoredToUpperCamelCase($subModuleName);
            $moduleSignature .= '_'.$subModuleName;
        }

        return $moduleSignature;
    }
}
