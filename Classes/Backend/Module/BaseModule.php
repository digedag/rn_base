<?php

namespace Sys25\RnBase\Backend\Module;

use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Backend\Template\ModuleParts;
use Sys25\RnBase\Backend\Template\ModuleTemplate;
use Sys25\RnBase\Backend\Utility\BackendUtility;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Configuration\Processor;
use Sys25\RnBase\Frontend\Marker\BaseMarker;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Utility\Arrays;
use Sys25\RnBase\Utility\Files;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;

/***************************************************************
*  Copyright notice
*
*  (c) 2009-2021 Rene Nitzsche (rene@system25.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Fertige Implementierung eines BE-Moduls. Das Modul ist dabei nur eine Hülle für die einzelnen Modulfunktionen.
 * Die Klasse stellt also lediglich eine Auswahlbox mit den verfügbaren Funktionen bereit. Neue Funktionen können
 * dynamisch über die ext_tables.php angemeldet werden:
 *  tx_rnbase_util_Extensions::insertModuleFunction('user_txmkmailerM1', 'tx_mkmailer_mod1_FuncOverview',
 *    tx_rnbase_util_Extensions::extPath($_EXTKEY).'mod1/class.tx_mkmailer_mod1_FuncOverview.php',
 *    'LLL:EXT:mkmailer/mod1/locallang_mod.xml:func_overview'
 *  );
 * Die Funktionsklassen sollten das Interface tx_rnbase_mod_IModFunc implementieren. Eine Basisklasse mit nützlichen
 * Methoden steht natürlich auch bereit: tx_rnbase_mod_BaseModFunc.
 */
abstract class BaseModule extends BaseScriptClass implements IModule
{
    public $doc;

    /** @var ConfigurationInterface */
    private $configurations;

    /** @var ToolBox */
    private $formTool;

    /** @var ModuleTemplate */
    private $moduleTemplate;

    /**
     * @var array
     */
    protected $tabs;

    /**
     * @var array
     */
    protected $subselector;

    /**
     * Initializes the backend module by setting internal variables, initializing the menu.
     */
    public function init()
    {
        parent::init();

        if (0 === $this->id) {
            $this->id = $this->getConfigurations()->getInt('_cfg.fallbackPid');
        }
    }

    /**
     * For the new TYPO3 request handlers.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response = null
     *
     * @return bool|\Psr\Http\Message\ResponseInterface TRUE, if the request request could be dispatched
     */
    public function __invoke(
        $request = null,
        $response = null
    ) {
        $GLOBALS['MCONF']['script'] = '_DISPATCH';
        $this->init();
        $this->main();

        if (!TYPO3::isTYPO90OrHigher()) {
            $this->printContent();
            $response = true;
        } else {
            // response is null if
            // $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['simplifiedControllerActionDispatching'] is true
            if (null == $response) {
                $response = new \TYPO3\CMS\Core\Http\HtmlResponse($this->printContent(true));
            } else {
                $this->printContent();
            }
        }

        return $response;
    }

    /**
     * Main function of the module. Write the content to $this->content
     * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree.
     */
    public function main()
    {
        // Einbindung der Modul-Funktionen
        $this->checkExtObj();

        $this->moduleTemplate = tx_rnbase::makeInstance(ModuleTemplate::class, $this, [
            'form' => $this->getFormTag(),
            'docstyles' => $this->getDocStyles(),
            'template' => $this->getModuleTemplate(),
        ]);

        // Die Variable muss gesetzt sein.
        $this->doc = $this->getModTemplate()->getDoc();

        /* @var $parts ModuleParts */
        $parts = tx_rnbase::makeInstance(ModuleParts::class);
        $this->prepareModuleParts($parts);

        $this->content = $this->getModTemplate()->renderContent($parts);
    }

    protected function prepareModuleParts($parts)
    {
        // Access check. The page will show only if there is a valid page
        // and if this page may be viewed by the user
        $pageinfo = BackendUtility::readPageAccess($this->getPid(), $this->perms_clause);

        $parts->setContent($this->moduleContent());
        $parts->setButtons($this->getButtons());
        $parts->setTitle($GLOBALS['LANG']->getLL('title'));
        $parts->setFuncMenu($this->getFuncMenu());
        // if we got no array the user got no permissions for the
        // selected page or no page is selected
        $parts->setPageInfo(is_array($pageinfo) ? $pageinfo : []);
        $parts->setSubMenu($this->tabs);
        $parts->setSelector($this->selector ?? $this->subselector);
    }

    /**
     * Returns the module ident name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->MCONF['name'];
    }

    /**
     * Generates the module content.
     * Normaly we would call $this->extObjContent(); But this method writes the output to $this->content. We need
     * the output directly so this is reimplementation of extObjContent().
     *
     * @return string
     */
    protected function moduleContent()
    {
        // Dummy-Button für automatisch Submit
        $content = '<p style="position:absolute; top:-5000px; left:-5000px;">'
            .'<input type="submit" />'
            .'</p>';
        if ($this->extObj) {
            $this->extObj->pObj = &$this; // Wozu diese Zuweisung? Die Submodule können getModule() verwenden...
            if (is_callable([$this->extObj, 'main'])) {
                $content .= $this->extObj->main();
            } else {
                $content .= 'Module '.get_class($this->extObj).' has no method main.';
            }
        }

        return $content;
    }

    /**
     * (non-PHPdoc).
     */
    public function checkExtObj()
    {
        if (isset($this->extClassConf['name'])) {
            $this->extObj = tx_rnbase::makeInstance($this->extClassConf['name']);
            $this->extObj->init($this, $this->extClassConf);
            // Re-write:
            $this->MOD_SETTINGS = BackendUtility::getModuleData(
                $this->MOD_MENU,
                \Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('SET'),
                $this->getName(),
                $this->modMenu_type,
                $this->modMenu_dontValidateList,
                $this->modMenu_setDefaultList
            );
        }
    }

    /**
     * @see IModule::getFormTool()
     *
     * @return ToolBox
     */
    public function getFormTool()
    {
        if (!$this->formTool) {
            $this->formTool = tx_rnbase::makeInstance(ToolBox::class);
            $this->formTool->init($this->getDoc(), $this);
        }

        return $this->formTool;
    }

    /**
     * Liefert eine Instanz von ConfigurationInterface. Da wir uns im BE bewegen, wird diese mit einem
     * Config-Array aus der TSConfig gefüttert. Dabei wird die Konfiguration unterhalb von mod.extkey. genommen.
     * Für "extkey" wird der Wert der Methode getExtensionKey() verwendet.
     * Zusätzlich wird auch die Konfiguration von "lib." bereitgestellt.
     * Wenn Daten für BE-Nutzer oder Gruppen überschrieben werden sollen, dann darauf achten, daß die
     * Konfiguration mit "page." beginnen muss. Also bspw. "page.lib.test = 42".
     *
     * Ein eigenes TS-Template für das BE wird in der ext_localconf.php mit dieser Anweisung eingebunden:
     * tx_rnbase_util_Extensions::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:myext/mod1/pageTSconfig.txt">');
     *
     * @return ConfigurationInterface
     */
    public function getConfigurations()
    {
        if (!$this->configurations) {
            Misc::prepareTSFE(); // Ist bei Aufruf aus BE notwendig!
            $cObj = TYPO3::getContentObject();

            $pageTSconfigFull = BackendUtility::getPagesTSconfig($this->getPid());
            $pageTSconfig = $pageTSconfigFull['mod.'][$this->getExtensionKey().'.'] ?? [];
            $pageTSconfig['lib.'] = $pageTSconfigFull['lib.'] ?? [];

            $userTSconfig = $GLOBALS['BE_USER']->getTSConfig('mod.'.$this->getExtensionKey().'.');
            if (!empty($userTSconfig['properties'])) {
                $pageTSconfig = Arrays::mergeRecursiveWithOverrule($pageTSconfig, $userTSconfig['properties']);
            }

            $qualifier = $pageTSconfig['qualifier'] ?? $this->getExtensionKey();
            $this->configurations = tx_rnbase::makeInstance(Processor::class);
            $this->configurations->init($pageTSconfig, $cObj, $this->getExtensionKey(), $qualifier);

            // init the parameters object
            $this->configurations->setParameters(
                tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class)
            );
            $this->configurations->getParameters()->init('SET');
        }

        return $this->configurations;
    }

    /**
     * Liefert bei Web-Modulen die aktuelle Pid.
     *
     * @return int
     */
    public function getPid()
    {
        return $this->id;
    }

    public function setSubMenu($menuString)
    {
        $this->tabs = $menuString;
    }

    /**
     * Selector String for the marker ###SELECTOR###.
     *
     * @param $selectorString
     */
    public function setSelector($selectorString)
    {
        $this->selector = $selectorString;
    }

    /**
     * Prints out the module HTML.
     *
     * @param bool $returnContent
     *
     * @return string|null
     */
    public function printContent($returnContent = false)
    {
        $this->content .= $this->getDoc()->endPage();

        $params = $markerArray = $subpartArray = $wrappedSubpartArray = [];
        BaseMarker::callModules($this->content, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $this->getConfigurations()->getFormatter());
        $content = Templates::substituteMarkerArrayCached($this->content, $markerArray, $subpartArray, $wrappedSubpartArray);

        if ($returnContent) {
            return $content;
        } else {
            echo $content;

            return null;
        }
    }

    /**
     * Returns a template instance
     * Liefert die Instanzvariable doc. Die muss immer Public bleiben, weil auch einige TYPO3-Funktionen
     * direkt darauf zugreifen.
     *
     * @return \TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * Returns the ModuleTemplate of rn_base used to render the module output.
     *
     * @return ModuleTemplate
     */
    public function getModTemplate()
    {
        return $this->moduleTemplate;
    }

    /**
     * Zukünftig ab T3 7.6 das ModuleTemplate verwenden.
     *
     * @return bool
     */
    public function useModuleTemplate()
    {
        return true;
    }

    /**
     * Erstellt das Menu mit den Submodulen. Die ist als Auswahlbox oder per Tabs möglich und kann per TS eingestellt werden:
     * mod.mymod._cfg.funcmenu.useTabs.
     */
    protected function getFuncMenu()
    {
        if ($this->useModuleTemplate()) {
            $menuRegistry = tx_rnbase::makeInstance(\TYPO3\CMS\Backend\Template\Components\MenuRegistry::class);
            $menu = $menuRegistry->makeMenu();
            $modMenu = $this->MOD_MENU;
            $modSettings = $this->MOD_SETTINGS;
            $menu->setIdentifier('WebT3sportsJumpMenu'); // FIXME!!

            foreach ($modMenu['function'] as $controller => $title) {
                $item = $menu
                    ->makeMenuItem()
                    ->setHref(
                        BackendUtility::getModuleUrl(
                            $this->getName(),
                            [
                                'id' => $this->getPid(),
                                'SET' => [
                                    'function' => $controller,
                                ],
                            ]
                        )
                    )
                    ->setTitle($title);
                if ($controller === $modSettings['function']) {
                    $item->setActive(true);
                }
                $menu->addMenuItem($item);
            }

            return $menu;
        } else {
            $items = $this->getFuncMenuItems($this->MOD_MENU['function']);
            $useTabs = intval($this->getConfigurations()->get('_cfg.funcmenu.useTabs')) > 0;
            if ($useTabs) {
                $menu = $this->getFormTool()->showTabMenu($this->getPid(), 'function', $this->getName(), $items);
            } else {
                $menu = $this->getFormTool()->showMenu($this->getPid(), 'function', $this->getName(), $items, $this->getModuleScript());
            }

            return $menu['menu'];
        }
    }

    /**
     * index.php or empty.
     *
     * @return string
     */
    protected function getModuleScript()
    {
        return '';
    }

    /**
     * Find out all visible sub modules for the current user.
     * mod.mymod._cfg.funcmenu.deny = className of submodules
     * mod.mymod._cfg.funcmenu.allow = className of submodules.
     *
     * @param array $items
     *
     * @return array
     */
    protected function getFuncMenuItems($items)
    {
        $visibleItems = $items;
        if ($denyItems = $this->getConfigurations()->get('_cfg.funcmenu.deny')) {
            $denyItems = Strings::trimExplode(',', $denyItems);
            foreach ($denyItems as $item) {
                unset($visibleItems[$item]);
            }
        }
        if ($allowItems = $this->getConfigurations()->get('_cfg.funcmenu.allow')) {
            $visibleItems = [];
            $allowItems = Strings::trimExplode(',', $allowItems);
            foreach ($allowItems as $item) {
                $visibleItems[$item] = $items[$item];
            }
        }

        return $visibleItems;
    }

    /**
     * Builds the form open tag.
     *
     * @TODO: per TS einstellbar machen
     *
     * @return string
     */
    protected function getFormTag()
    {
        $modUrl = BackendUtility::getModuleUrl($this->getName());

        return '<form action="'.$modUrl.'" method="post" name="editform" enctype="multipart/form-data"><input type="hidden" name="id" value="'.htmlspecialchars($this->id).'" />';
    }

    /**
     * @return string
     */
    public function buildFormTag()
    {
        return $this->getFormTag();
    }

    /**
     * Returns the filename for module HTML template. This can be overwritten.
     * The first place to search for template is EXT:[your_ext_key]/mod1/template.html. If this file
     * not exists the default from rn_base is used. Overwrite this method to set your own location.
     *
     * @return string
     */
    protected function getModuleTemplate()
    {
        $filename = $this->getConfigurations()->get('template');
        if (file_exists(Files::getFileAbsFileName($filename, true, true))) {
            return $filename;
        }
        $filename = 'EXT:'.$this->getExtensionKey().'/mod1/template.html';
        if (file_exists(Files::getFileAbsFileName($filename, true, true))) {
            return $filename;
        }

        return 'EXT:rn_base/Resources/Private/Templates/template2.html';
    }

    /**
     * @deprecated remove
     */
    protected function initDoc($doc)
    {
        $doc->backPath = $GLOBALS['BACK_PATH'];
        $doc->form = $this->getFormTag();
        $doc->docType = 'xhtml_trans';
        $doc->inDocStyles = $this->getDocStyles();
        $doc->inDocStylesArray[] = $doc->inDocStyles;
        $doc->tableLayout = $this->getTableLayout();
        $doc->setModuleTemplate($this->getModuleTemplate());
        $doc->loadJavascriptLib('contrib/prototype/prototype.js');
        // JavaScript
        $doc->JScode .= '
            <script>
                script_ended = 0;
                function jumpToUrl(URL)	{
                    document.location = URL;
                }
            </script>
            ';

        if (!TYPO3::isTYPO115OrHigher()) {
            // TODO: Die Zeile könnte problematisch sein...
            $doc->postCode = '
            <script>
                script_ended = 1;
                if (top.fsMod) top.fsMod.recentIds["web"] = '.$this->id.';</script>';
        }
    }

    /**
     * Liefert den Extension-Key des Moduls.
     *
     * @return string
     */
    abstract public function getExtensionKey();

    protected function getDocStyles()
    {
        $css = '
    .rnbase_selector div {
        float:left;
        margin: 0 5px 10px 0;
    }
    .rnbase_content div {
        float:left;
        margin: 5px 5px 10px 0;
    }
    .cleardiv {clear:both;}
    .rnbase_content .c-headLineTable td {
        font-weight:bold;
        color:#FFF!important;
    }';

        return $css;
    }

    /**
     * @deprecated use mod_Tables
     *
     * @return array
     */
    public function getTableLayout()
    {
        return [
                    'table' => ['<table class="typo3-dblist" width="100%" cellspacing="0" cellpadding="0" border="0">', '</table><br/>'],
                    '0' => [ // Format für 1. Zeile
                        'tr' => ['<tr class="t3-row-header c-headLineTable">', '</tr>'],
                        // Format für jede Spalte in der 1. Zeile
                        'defCol' => ['<td>', '</td>'],
                    ],
                    'defRow' => [ // Formate für alle Zeilen
                        'tr' => ['<tr class="db_list_normal">', '</tr>'],
                        'defCol' => ['<td>', '</td>'], // Format für jede Spalte in jeder Zeile
                    ],
                    'defRowEven' => [ // Formate für alle geraden Zeilen
                        'tr' => ['<tr class="db_list_alt">', '</tr>'],
                        // Format für jede Spalte in jeder Zeile
                        'defCol' => ['<td>', '</td>'],
                    ],
                ];
    }

    /**
     * Create the panel of buttons for submitting the form or otherwise perform operations.
     *
     * @return array all available buttons as an assoc. array
     */
    public function getButtons()
    {
        global $BE_USER;

        $buttons = [
            'csh' => '',
            'view' => '',
            'record_list' => '',
            'shortcut' => '',
        ];
        // TODO: CSH
        $buttons['csh'] = BackendUtility::cshItem(
            '_MOD_'.$this->getName(),
            '',
            $GLOBALS['BACK_PATH'] ?? '',
            '',
            true
        );

        return $buttons;
    }

    /**
     * (Non PHP-doc).
     *
     * @deprecated use tx_rnbase_util_Misc::addFlashMessage instead
     */
    public function addMessage($message, $title = '', $severity = 0, $storeInSession = false)
    {
        Misc::addFlashMessage($message, $title, $severity, $storeInSession);
    }

    /**
     * @deprecated use Tx_Rnbase_Backend_Utility::issueCommand instead
     */
    public function issueCommand($getParameters, $redirectUrl = '')
    {
        return BackendUtility::issueCommand($getParameters, $redirectUrl);
    }

    public function getTitle()
    {
        return '';
    }

    public function getRouteIdentifier()
    {
        return '';
    }

    public function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
