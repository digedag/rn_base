<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2009-2017 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_mod_IModule');
tx_rnbase::load('tx_rnbase_mod_IModFunc');
tx_rnbase::load('Tx_Rnbase_Backend_Utility');
tx_rnbase::load('tx_rnbase_util_Typo3Classes');
tx_rnbase::load('Tx_Rnbase_Backend_Utility_Icons');
tx_rnbase::load('Tx_Rnbase_Backend_Module_Base');

/**
 * Fertige Implementierung eines BE-Moduls. Das Modul ist dabei nur eine Hülle für die einzelnen Modulfunktionen.
 * Die Klasse stellt also lediglich eine Auswahlbox mit den verfügbaren Funktionen bereit. Neue Funktionen können
 * dynamisch über die ext_tables.php angemeldet werden:
 *  tx_rnbase_util_Extensions::insertModuleFunction('user_txmkmailerM1', 'tx_mkmailer_mod1_FuncOverview',
 *    tx_rnbase_util_Extensions::extPath($_EXTKEY).'mod1/class.tx_mkmailer_mod1_FuncOverview.php',
 *    'LLL:EXT:mkmailer/mod1/locallang_mod.xml:func_overview'
 *  );
 * Die Funktionsklassen sollten das Interface tx_rnbase_mod_IModFunc implementieren. Eine Basisklasse mit nützlichen
 * Methoden steht natürlich auch bereit: tx_rnbase_mod_BaseModFunc
 */
abstract class tx_rnbase_mod_BaseModule extends Tx_Rnbase_Backend_Module_Base implements tx_rnbase_mod_IModule
{
    public $doc;
    /** @var Tx_Rnbase_Configuration_ProcessorInterface */
    private $configurations;
    private $formTool;
    /** @var Tx_Rnbase_Backend_Template_ModuleTemplate */
    private $moduleTemplate;

    /**
     * Initializes the backend module by setting internal variables, initializing the menu.
     *
     * @return void
     */
    public function init()
    {
        $GLOBALS['LANG']->includeLLFile('EXT:rn_base/mod/locallang.xml');

        $this->initModConf();

        parent::init();

        if ($this->id === 0) {
            $this->id = $this->getConfigurations()->getInt('_cfg.fallbackPid');
        }
    }

    /**
     * Initializes the mconf of this module
     *
     * @return void
     */
    protected function initModConf()
    {
        // Name might be set from outside
        if (!$this->MCONF['name']) {
            $this->MCONF = $GLOBALS['MCONF'];
        }
        // check dispatch mode calls without rnbase module runner and fetch the config.
        if (!$this->MCONF['name']) {
            /* @var $runner Tx_Rnbase_Backend_ModuleRunner */
            $runner = tx_rnbase::makeInstance('Tx_Rnbase_Backend_ModuleRunner');
            $runner->initTargetConf($this);
        }
    }

    /**
     * For the new TYPO3 request handlers
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response = null
     *
     * @return bool TRUE, if the request request could be dispatched
     */
    public function __invoke(
        /* Psr\Http\Message\ServerRequestInterface */ $request = null,
        /* Psr\Http\Message\ResponseInterface */ $response = null
    ) {
        $GLOBALS['MCONF']['script'] = '_DISPATCH';

        $this->init();
        $this->main();
        $this->printContent();

        return true;
    }

    /**
     * Main function of the module. Write the content to $this->content
     * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
     *
     * @return  [type]      ...
     */
    public function main()
    {
        // Einbindung der Modul-Funktionen
        $this->checkExtObj();

        $this->moduleTemplate = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Template_ModuleTemplate', $this, [
            'form' => $this->getFormTag(),
            'docstyles' => $this->getDocStyles(),
            'template' => $this->getModuleTemplate(),
        ]);

        // Die Variable muss gesetzt sein.
        $this->doc = $this->getModTemplate()->getDoc();

        /* @var $parts Tx_Rnbase_Backend_Template_ModuleParts */
        $parts = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Template_ModuleParts');
        $this->prepareModuleParts($parts);

        $this->content = $this->getModTemplate()->renderContent($parts);
    }
    protected function prepareModuleParts($parts)
    {
        // Access check. The page will show only if there is a valid page
        // and if this page may be viewed by the user
        $pageinfo = Tx_Rnbase_Backend_Utility::readPageAccess($this->getPid(), $this->perms_clause);

        $parts->setContent($this->moduleContent());
        $parts->setButtons($this->getButtons());
        $parts->setTitle($GLOBALS['LANG']->getLL('title'));
        $parts->setFuncMenu($this->getFuncMenu());
        $parts->setPageInfo($pageinfo);
        $parts->setSubMenu($this->tabs);
        $parts->setSelector($this->selector ? $this->selector : $this->subselector);
    }

    /**
     * Returns the module ident name
     * @return string
     */
    public function getName()
    {
        return $this->MCONF['name'];
    }
    /**
     * Generates the module content.
     * Normaly we would call $this->extObjContent(); But this method writes the output to $this->content. We need
     * the output directly so this is reimplementation of extObjContent()
     *
     * @return string
     */
    protected function moduleContent()
    {
        // Dummy-Button für automatisch Submit
        $content = '<p style="position:absolute; top:-5000px; left:-5000px;">'
            . '<input type="submit" />'
            . '</p>';
        $this->extObj->pObj = &$this; // Wozu diese Zuweisung? Die Submodule können getModule() verwenden...

        if (is_callable(array($this->extObj, 'main'))) {
            $content .= $this->extObj->main();
        } else {
            $content .= 'Module '.get_class($this->extObj).' has no method main.';
        }

        return $content;
    }

    /**
     * (non-PHPdoc)
     * @see t3lib_SCbase::checkExtObj()
     */
    public function checkExtObj()
    {
        if (is_array($this->extClassConf) && $this->extClassConf['name']) {
            $this->extObj = tx_rnbase::makeInstance($this->extClassConf['name']);
            $this->extObj->init($this, $this->extClassConf);
                // Re-write:
            tx_rnbase::load('tx_rnbase_parameters');
            $this->MOD_SETTINGS = Tx_Rnbase_Backend_Utility::getModuleData(
                $this->MOD_MENU,
                tx_rnbase_parameters::getPostOrGetParameter('SET'),
                $this->getName(),
                $this->modMenu_type,
                $this->modMenu_dontValidateList,
                $this->modMenu_setDefaultList
            );
        }
    }

    /**
     * @see tx_rnbase_mod_IModule::getFormTool()
     * @return tx_rnbase_util_FormTool
     */
    public function getFormTool()
    {
        if (!$this->formTool) {
            $this->formTool = tx_rnbase::makeInstance(
                Tx_Rnbase_Backend_Utility::isDispatchMode() ? 'Tx_Rnbase_Backend_Form_ToolBox' : 'tx_rnbase_util_FormTool'
            );
            $this->formTool->init($this->getDoc(), $this);
        }

        return $this->formTool;
    }

    /**
     * Liefert eine Instanz von Tx_Rnbase_Configuration_ProcessorInterface. Da wir uns im BE bewegen, wird diese mit einem
     * Config-Array aus der TSConfig gefüttert. Dabei wird die Konfiguration unterhalb von mod.extkey. genommen.
     * Für "extkey" wird der Wert der Methode getExtensionKey() verwendet.
     * Zusätzlich wird auch die Konfiguration von "lib." bereitgestellt.
     * Wenn Daten für BE-Nutzer oder Gruppen überschrieben werden sollen, dann darauf achten, daß die
     * Konfiguration mit "page." beginnen muss. Also bspw. "page.lib.test = 42".
     *
     * Ein eigenes TS-Template für das BE wird in der ext_localconf.php mit dieser Anweisung eingebunden:
     * tx_rnbase_util_Extensions::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:myext/mod1/pageTSconfig.txt">');
     *
     * @return Tx_Rnbase_Configuration_ProcessorInterface
     */
    public function getConfigurations()
    {
        if (!$this->configurations) {
            tx_rnbase::load('tx_rnbase_util_Misc');
            tx_rnbase::load('tx_rnbase_util_Typo3Classes');

            tx_rnbase_util_Misc::prepareTSFE(); // Ist bei Aufruf aus BE notwendig!
            $cObj = tx_rnbase_util_TYPO3::getContentObject();

            $pageTSconfigFull = Tx_Rnbase_Backend_Utility::getPagesTSconfig($this->getPid());
            $pageTSconfig = $pageTSconfigFull['mod.'][$this->getExtensionKey().'.'];
            $pageTSconfig['lib.'] = $pageTSconfigFull['lib.'];

            $userTSconfig = $GLOBALS['BE_USER']->getTSConfig('mod.' . $this->getExtensionKey().'.');
            if (!empty($userTSconfig['properties'])) {
                tx_rnbase::load('tx_rnbase_util_Arrays');
                $pageTSconfig = tx_rnbase_util_Arrays::mergeRecursiveWithOverrule($pageTSconfig, $userTSconfig['properties']);
            }

            $qualifier = $pageTSconfig['qualifier'] ? $pageTSconfig['qualifier'] : $this->getExtensionKey();
            $this->configurations = tx_rnbase::makeInstance('Tx_Rnbase_Configuration_Processor');
            $this->configurations->init($pageTSconfig, $cObj, $this->getExtensionKey(), $qualifier);

            // init the parameters object
            $this->configurations->setParameters(
                tx_rnbase::makeInstance('tx_rnbase_parameters')
            );
            $this->configurations->getParameters()->init('SET');
        }

        return $this->configurations;
    }
    /**
     * Liefert bei Web-Modulen die aktuelle Pid
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
     * Selector String for the marker ###SELECTOR###
     * @param $selectorString
     */
    public function setSelector($selectorString)
    {
        $this->selector = $selectorString;
    }

    /**
     * Prints out the module HTML
     *
     * @return  void
     */
    public function printContent()
    {
        $this->content .= $this->getDoc()->endPage();

        $params = $markerArray = $subpartArray = $wrappedSubpartArray = array();
        tx_rnbase::load('tx_rnbase_util_BaseMarker');
        tx_rnbase::load('tx_rnbase_util_Templates');
        tx_rnbase_util_BaseMarker::callModules($this->content, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $this->getConfigurations()->getFormatter());
        $content = tx_rnbase_util_Templates::substituteMarkerArrayCached($this->content, $markerArray, $subpartArray, $wrappedSubpartArray);

        echo $content;
    }

    /**
     * Returns a template instance
     * Liefert die Instanzvariable doc. Die muss immer Public bleiben, weil auch einige TYPO3-Funktionen
     * direkt darauf zugreifen.
     * @return \TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public function getDoc()
    {
        return $this->doc;
    }
    /**
     * Returns the ModuleTemplate of rn_base used to render the module output.
     * @return Tx_Rnbase_Backend_Template_ModuleTemplate
     */
    public function getModTemplate()
    {
        return $this->moduleTemplate;
    }

    /**
     * Erstellt das Menu mit den Submodulen. Die ist als Auswahlbox oder per Tabs möglich und kann per TS eingestellt werden:
     * mod.mymod._cfg.funcmenu.useTabs
     */
    protected function getFuncMenu()
    {
        $items = $this->getFuncMenuItems($this->MOD_MENU['function']);
        $useTabs = intval($this->getConfigurations()->get('_cfg.funcmenu.useTabs')) > 0;
        if ($useTabs) {
            $menu = $this->getFormTool()->showTabMenu($this->getPid(), 'function', $this->getName(), $items);
        } else {
            $menu = $this->getFormTool()->showMenu($this->getPid(), 'function', $this->getName(), $items, $this->getModuleScript());
        }

        return $menu['menu'];
    }
    /**
     * index.php or empty
     * @return string
     */
    protected function getModuleScript()
    {
        return '';
    }
    /**
     * Find out all visible sub modules for the current user.
     * mod.mymod._cfg.funcmenu.deny = className of submodules
     * mod.mymod._cfg.funcmenu.allow = className of submodules
     * @param array $items
     * @return array
     */
    protected function getFuncMenuItems($items)
    {
        $visibleItems = $items;
        if ($denyItems = $this->getConfigurations()->get('_cfg.funcmenu.deny')) {
            $denyItems = tx_rnbase_util_Strings::trimExplode(',', $denyItems);
            foreach ($denyItems as $item) {
                unset($visibleItems[$item]);
            }
        }
        if ($allowItems = $this->getConfigurations()->get('_cfg.funcmenu.allow')) {
            $visibleItems = array();
            $allowItems = tx_rnbase_util_Strings::trimExplode(',', $allowItems);
            foreach ($allowItems as $item) {
                $visibleItems[$item] = $items[$item];
            }
        }

        return $visibleItems;
    }

    /**
     * Builds the form open tag
     *
     * @TODO: per TS einstellbar machen
     *
     * @return string
     */
    protected function getFormTag()
    {
        $modUrl = Tx_Rnbase_Backend_Utility::getModuleUrl(
            $this->getName(),
            array(
                'id' => $this->getPid()
            ),
            ''
        );

        return '<form action="' . $modUrl . '" method="post" enctype="multipart/form-data">';
    }
    /**
     * Returns the filename for module HTML template. This can be overwritten.
     * The first place to search for template is EXT:[your_ext_key]/mod1/template.html. If this file
     * not exists the default from rn_base is used. Overwrite this method to set your own location.
     * @return string
     */
    protected function getModuleTemplate()
    {
        $filename = $this->getConfigurations()->get('template');
        if (file_exists(tx_rnbase_util_Files::getFileAbsFileName($filename, true, true))) {
            return $filename;
        }
        $filename = 'EXT:'.$this->getExtensionKey() .  '/mod1/template.html';
        if (file_exists(tx_rnbase_util_Files::getFileAbsFileName($filename, true, true))) {
            return $filename;
        }

        return 'EXT:rn_base/mod/template.html';
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
            <script language="javascript" type="text/javascript">
                script_ended = 0;
                function jumpToUrl(URL)	{
                    document.location = URL;
                }
            </script>
            ';

        // TODO: Die Zeile könnte problematisch sein...
        $doc->postCode = '
            <script language="javascript" type="text/javascript">
                script_ended = 1;
                if (top.fsMod) top.fsMod.recentIds["web"] = ' . $this->id . ';</script>';
    }

    /**
     * Liefert den Extension-Key des Moduls
     * @return string
     */
    abstract public function getExtensionKey();

    protected function getDocStyles()
    {
        $css .= '
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
     * @return multitype:multitype:string  multitype:multitype:string
     */
    public function getTableLayout()
    {
        return array(
                    'table' => array('<table class="typo3-dblist" width="100%" cellspacing="0" cellpadding="0" border="0">', '</table><br/>'),
                    '0' => array( // Format für 1. Zeile
                        'tr'        => array('<tr class="t3-row-header c-headLineTable">', '</tr>'),
                        // Format für jede Spalte in der 1. Zeile
                        'defCol' => array('<td>', '</td>')
                    ),
                    'defRow' => array( // Formate für alle Zeilen
                        'tr'       => array('<tr class="db_list_normal">', '</tr>'),
                        'defCol' => array('<td>', '</td>') // Format für jede Spalte in jeder Zeile
                    ),
                    'defRowEven' => array( // Formate für alle geraden Zeilen
                        'tr'       => array('<tr class="db_list_alt">', '</tr>'),
                        // Format für jede Spalte in jeder Zeile
                        'defCol' => array('<td>', '</td>')
                    )
                );
    }


    /**
     * Create the panel of buttons for submitting the form or otherwise perform operations.
     *
     * @return  array   all available buttons as an assoc. array
     */
    public function getButtons()
    {
        global $BE_USER;

        $buttons = array(
            'csh' => '',
            'view' => '',
            'record_list' => '',
            'shortcut' => '',
        );
            // TODO: CSH
        $buttons['csh'] = Tx_Rnbase_Backend_Utility::cshItem(
            '_MOD_' . $this->getName(),
            '',
            $GLOBALS['BACK_PATH'],
            '',
            true
        );

        if ($this->id && is_array($this->pageinfo)) {
            // Shortcut
            if ($BE_USER->mayMakeShortcut()) {
                $buttons['shortcut'] = $this->getDoc()->makeShortcutIcon(
                    'id, edit_record, pointer, new_unique_uid, search_field, search_levels, showLimit',
                    implode(',', array_keys($this->MOD_MENU)),
                    $this->getName()
                );
            }
        }

        return $buttons;
    }
    /**
     * (Non PHP-doc)
     * @deprecated use tx_rnbase_util_Misc::addFlashMessage instead
     */
    public function addMessage($message, $title = '', $severity = 0, $storeInSession = false)
    {
        tx_rnbase_util_Misc::addFlashMessage($message, $title, $severity, $storeInSession);
    }

    /**
     * @see TYPO3\CMS\Backend\Utility\BackendUtility::getLinkToDataHandlerAction
     * @see TYPO3\CMS\Backend\Template\DocumentTemplate::issueCommand
     * @see template::issueCommand
     *
     * @param string $getParameters
     * @param string $redirectUrl
     * @return string
     */
    public function issueCommand($getParameters, $redirectUrl = '')
    {
        if (tx_rnbase_util_TYPO3::isTYPO76OrHigher()) {
            $link = TYPO3\CMS\Backend\Utility\BackendUtility::getLinkToDataHandlerAction($getParameters, $redirectUrl);
        } else {
            $link = $this->getDoc()->issueCommand($getParameters, $redirectUrl);
        }

        return $link;
    }
}

