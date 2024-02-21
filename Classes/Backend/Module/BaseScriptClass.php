<?php

namespace Sys25\RnBase\Backend\Module;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Takeover from TYPO3 accourding to
 * https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Deprecation-86225-ClassesBaseScriptClassAndAbstractFunctionModule.html.
 *
 * Parent class for 'ScriptClasses' in backend modules.
 *
 * EXAMPLE PROTOTYPE
 *
 * As for examples there are lots of them if you search for classes which extends \TYPO3\CMS\Backend\Module\BaseScriptClass
 * However you can see a prototype example of how a module might use this class in an index.php file typically hosting a backend module.
 *
 * NOTICE: This example only outlines the basic structure of how this class is used.
 * You should consult the documentation and other real-world examples for some actual things to do when building modules.
 *
 * TYPICAL SETUP OF A BACKEND MODULE:
 *
 * PrototypeController EXTENDS THE CLASS \TYPO3\CMS\Backend\Module\BaseScriptClass with a main() function:
 *
 * namespace Vendor\Prototype\Controller;
 *
 * class PrototypeController extends \TYPO3\CMS\Backend\Module\BaseScriptClass {
 * 	public function __construct() {
 * 		$this->getLanguageService()->includeLLFile('EXT:prototype/Resources/Private/Language/locallang.xlf');
 * 		$this->getBackendUser()->modAccess($GLOBALS['MCONF']);
 * 	}
 * }
 *
 * MAIN FUNCTION - HERE YOU CREATE THE MODULE CONTENT IN $this->content
 * public function main() {
 * 	TYPICALLY THE INTERNAL VAR, $this->doc is instantiated like this:
 * 	$this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\DocumentTemplate::class);
 * 	... AND OF COURSE A LOT OF OTHER THINGS GOES ON - LIKE PUTTING CONTENT INTO $this->content
 * 	$this->content='';
 * }
 *
 * MAKE INSTANCE OF THE SCRIPT CLASS AND CALL init()
 * $GLOBALS['SOBE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Vendor\Prototype\Controller\PrototypeController::class);
 * $GLOBALS['SOBE']->init();
 *
 *
 * THEN WE WILL CHECK IF THERE IS A 'SUBMODULE' REGISTERED TO BE INITIALIZED AS WELL:
 * $GLOBALS['SOBE']->checkExtObj();
 *
 * THEN WE CALL THE main() METHOD AND THIS SHOULD SPARK THE CREATION OF THE MODULE OUTPUT.
 * $GLOBALS['SOBE']->main();
 */
abstract class BaseScriptClass
{
    /**
     * Loaded with the global array $MCONF which holds some module configuration from the conf.php file of backend modules.
     *
     * @see init()
     *
     * @var array
     */
    public $MCONF = [];

    /**
     * The integer value of the GET/POST var, 'id'. Used for submodules to the 'Web' module (page id).
     *
     * @see init()
     *
     * @var int
     */
    public $id;

    /**
     * The value of GET/POST var, 'CMD'.
     *
     * @see init()
     *
     * @var mixed
     */
    public $CMD;

    /**
     * A WHERE clause for selection records from the pages table based on read-permissions of the current backend user.
     *
     * @see init()
     *
     * @var string
     */
    public $perms_clause;

    /**
     * The module menu items array. Each key represents a key for which values can range between the items in the array of that key.
     *
     * @see init()
     *
     * @var array
     */
    public $MOD_MENU = [
        'function' => [],
    ];

    /**
     * Current settings for the keys of the MOD_MENU array.
     *
     * @see $MOD_MENU
     *
     * @var array
     */
    public $MOD_SETTINGS = [];

    /**
     * Module TSconfig based on PAGE TSconfig / USER TSconfig.
     *
     * @see menuConfig()
     *
     * @var array
     */
    public $modTSconfig;

    /**
     * If type is 'ses' then the data is stored as session-lasting data. This means that it'll be wiped out the next time the user logs in.
     * Can be set from extension classes of this class before the init() function is called.
     *
     * @see menuConfig(), \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleData()
     *
     * @var string
     */
    public $modMenu_type = '';

    /**
     * dontValidateList can be used to list variables that should not be checked if their value is found in the MOD_MENU array. Used for dynamically generated menus.
     * Can be set from extension classes of this class before the init() function is called.
     *
     * @see menuConfig(), \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleData()
     *
     * @var string
     */
    public $modMenu_dontValidateList = '';

    /**
     * List of default values from $MOD_MENU to set in the output array (only if the values from MOD_MENU are not arrays)
     * Can be set from extension classes of this class before the init() function is called.
     *
     * @see menuConfig(), \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleData()
     *
     * @var string
     */
    public $modMenu_setDefaultList = '';

    /**
     * Contains module configuration parts from TBE_MODULES_EXT if found.
     *
     * @see handleExternalFunctionValue()
     *
     * @var array
     */
    public $extClassConf;

    /**
     * Generally used for accumulating the output content of backend modules.
     *
     * @var string
     */
    public $content = '';

    /**
     * @var \TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public $doc;

    /**
     * May contain an instance of a 'Function menu module' which connects to this backend module.
     *
     * @see checkExtObj()
     *
     * @var AbstractFunctionModule
     */
    public $extObj;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    protected $moduleName;

    /**
     * Constructor deprecates the class.
     */
    public function __construct()
    {
    }

    /**
     * Initializes the backend module by setting internal variables, initializing the menu.
     *
     * @see menuConfig()
     */
    public function init()
    {
        $GLOBALS['LANG']->includeLLFile('EXT:rn_base/Resources/Private/Language/locallang.xlf');

        // Name might be set from outside
        if (!isset($this->MCONF['name'])) {
            $this->MCONF = $GLOBALS['MCONF'] ?? [];
        }

        $this->id = (int) GeneralUtility::_GP('id');
        $this->CMD = GeneralUtility::_GP('CMD');
        $this->moduleName = GeneralUtility::_GP('M');
        // Das sollte zur Initialisierung des Modules ausreichend sein.
        // Das access Level sollte nicht vorgegeben werden.
        if (!isset($this->MCONF['name']) && $this->moduleName) {
            $this->MCONF = array_merge((array) $GLOBALS['MCONF'], [
                'name' => $this->moduleName,
                // 'access' => 'user,group',
        ]);
        }

        $this->perms_clause = $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW);
        $this->menuConfig();
        $this->handleExternalFunctionValue();
    }

    /**
     * Initializes the internal MOD_MENU array setting and unsetting items based on various conditions. It also merges in external menu items from the global array TBE_MODULES_EXT (see mergeExternalItems())
     * Then MOD_SETTINGS array is cleaned up (see \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleData()) so it contains only valid values. It's also updated with any SET[] values submitted.
     * Also loads the modTSconfig internal variable.
     *
     * @see init(), $MOD_MENU, $MOD_SETTINGS, \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleData(), mergeExternalItems()
     */
    public function menuConfig()
    {
        // Page / user TSconfig settings and blinding of menu-items
        $this->modTSconfig['properties'] = BackendUtility::getPagesTSconfig($this->id)['mod.'][$this->MCONF['name'].'.'] ?? [];
        $this->MOD_MENU['function'] = $this->mergeExternalItems($this->MCONF['name'], 'function', $this->MOD_MENU['function']);
        $blindActions = $this->modTSconfig['properties']['menu.']['function.'] ?? [];
        foreach ($blindActions as $key => $value) {
            if (!$value && array_key_exists($key, $this->MOD_MENU['function'])) {
                unset($this->MOD_MENU['function'][$key]);
            }
        }
        if (empty($this->MOD_MENU)) {
            exit('Backend module is not initialized. No module functions found in MOD_MENU.');
        }

        $this->MOD_SETTINGS = BackendUtility::getModuleData($this->MOD_MENU, GeneralUtility::_GP('SET'), $this->MCONF['name'], $this->modMenu_type, $this->modMenu_dontValidateList, $this->modMenu_setDefaultList);
    }

    /**
     * Merges menu items from global array $TBE_MODULES_EXT.
     *
     * @param string $modName Module name for which to find value
     * @param string $menuKey Menu key, eg. 'function' for the function menu.
     * @param array  $menuArr the part of a MOD_MENU array to work on
     *
     * @return array modified array part
     *
     * @internal
     *
     * @see \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(), menuConfig()
     */
    public function mergeExternalItems($modName, $menuKey, $menuArr)
    {
        $mergeArray = $GLOBALS['TBE_MODULES_EXT'][$modName]['MOD_MENU'][$menuKey] ?? null;
        if (is_array($mergeArray)) {
            foreach ($mergeArray as $k => $v) {
                if (('' === (string) $v['ws'] || 0 === $this->getBackendUser()->workspace && GeneralUtility::inList($v['ws'], 'online')) || -1 === $this->getBackendUser()->workspace && GeneralUtility::inList($v['ws'], 'offline') || $this->getBackendUser()->workspace > 0 && GeneralUtility::inList($v['ws'], 'custom')) {
                    $menuArr[$k] = $this->getLanguageService()->sL($v['title']);
                }
            }
        }

        return $menuArr;
    }

    /**
     * Loads $this->extClassConf with the configuration for the CURRENT function of the menu.
     *
     * @param string $MM_key   The key to MOD_MENU for which to fetch configuration. 'function' is default since it is first and foremost used to get information per "extension object" (I think that is what its called)
     * @param string $MS_value The value-key to fetch from the config array. If NULL (default) MOD_SETTINGS[$MM_key] will be used. This is useful if you want to force another function than the one defined in MOD_SETTINGS[function]. Call this in init() function of your Script Class: handleExternalFunctionValue('function', $forcedSubModKey)
     *
     * @see getExternalItemConfig(), init()
     */
    public function handleExternalFunctionValue($MM_key = 'function', $MS_value = null)
    {
        if (null === $MS_value) {
            $MS_value = $this->MOD_SETTINGS[$MM_key];
        }
        $this->extClassConf = $this->getExternalItemConfig($this->MCONF['name'], $MM_key, $MS_value);
    }

    /**
     * Returns configuration values from the global variable $TBE_MODULES_EXT for the module given.
     * For example if the module is named "web_info" and the "function" key ($menuKey) of MOD_SETTINGS is "stat" ($value) then you will have the values of $TBE_MODULES_EXT['webinfo']['MOD_MENU']['function']['stat'] returned.
     *
     * @param string $modName Module name
     * @param string $menuKey Menu key, eg. "function" for the function menu. See $this->MOD_MENU
     * @param string $value   Optionally the value-key to fetch from the array that would otherwise have been returned if this value was not set. Look source...
     *
     * @return mixed the value from the TBE_MODULES_EXT array
     *
     * @see handleExternalFunctionValue()
     */
    public function getExternalItemConfig($modName, $menuKey, $value = '')
    {
        if (isset($GLOBALS['TBE_MODULES_EXT'][$modName])) {
            return '' !== (string) $value ? $GLOBALS['TBE_MODULES_EXT'][$modName]['MOD_MENU'][$menuKey][$value] : $GLOBALS['TBE_MODULES_EXT'][$modName]['MOD_MENU'][$menuKey];
        }

        return null;
    }

    /**
     * Creates an instance of the class found in $this->extClassConf['name'] in $this->extObj if any (this should hold three keys, "name", "path" and "title" if a "Function menu module" tries to connect...)
     * This value in extClassConf might be set by an extension (in an ext_tables/ext_localconf file) which thus "connects" to a module.
     * The array $this->extClassConf is set in handleExternalFunctionValue() based on the value of MOD_SETTINGS[function]
     * If an instance is created it is initiated with $this passed as value and $this->extClassConf as second argument. Further the $this->MOD_SETTING is cleaned up again after calling the init function.
     *
     * @see handleExternalFunctionValue(), \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(), $extObj
     */
    public function checkExtObj()
    {
        if (is_array($this->extClassConf) && $this->extClassConf['name']) {
            $this->extObj = GeneralUtility::makeInstance($this->extClassConf['name']);
            $this->extObj->init($this, $this->extClassConf);
            // Re-write:
            $this->MOD_SETTINGS = BackendUtility::getModuleData($this->MOD_MENU, GeneralUtility::_GP('SET'), $this->MCONF['name'], $this->modMenu_type, $this->modMenu_dontValidateList, $this->modMenu_setDefaultList);
        }
    }

    /**
     * Calls the checkExtObj function in sub module if present.
     */
    public function checkSubExtObj()
    {
        if (is_object($this->extObj)) {
            $this->extObj->checkExtObj();
        }
    }

    /**
     * Calls the 'header' function inside the "Function menu module" if present.
     * A header function might be needed to add JavaScript or other stuff in the head. This can't be done in the main function because the head is already written.
     */
    public function extObjHeader()
    {
        if (is_callable([$this->extObj, 'head'])) {
            $this->extObj->head();
        }
    }

    /**
     * Calls the 'main' function inside the "Function menu module" if present.
     */
    public function extObjContent()
    {
        if (null === $this->extObj) {
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->getLanguageService()->sL('LLL:EXT:backend/Resources/Private/Language/locallang.xlf:no_modules_registered'),
                $this->getLanguageService()->getLL('title'),
                FlashMessage::ERROR
            );
            /** @var FlashMessageService $flashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue $defaultFlashMessageQueue */
            $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $defaultFlashMessageQueue->enqueue($flashMessage);
        } else {
            $this->extObj->pObj = $this;
            if (is_callable([$this->extObj, 'main'])) {
                $this->content .= $this->extObj->main();
            }
        }
    }

    /**
     * Return the content of the 'main' function inside the "Function menu module" if present.
     *
     * @return string
     */
    public function getExtObjContent()
    {
        $savedContent = $this->content;
        $this->content = '';
        $this->extObjContent();
        $newContent = $this->content;
        $this->content = $savedContent;

        return $newContent;
    }

    /**
     * Returns the Language Service.
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Returns the Backend User.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return PageRenderer
     */
    protected function getPageRenderer()
    {
        if (null === $this->pageRenderer) {
            $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        }

        return $this->pageRenderer;
    }
}
