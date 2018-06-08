<?php

/* *******************************************************
 *  Copyright notice
 *
 *  (c) 2017 René Nitzsche <rene@system25.de>
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
 */

tx_rnbase::load('tx_rnbase_mod_IModule');
/**
 * Die Klasse wrapped die Template-Klassen für BE-Module in TYPO3. Diese sind recht starken
 * Änderungen in der API unterworfen. Bis zur 6.2 gab es recht unterschiedliche Templateklassen,
 * wobei in erster Linie TYPO3\CMS\Backend\Template\DocumentTemplate genutzt wurde. Seit der
 * 7.6 gibt es zusätzlich die Klasse TYPO3\CMS\Backend\Template\ModulTemplate. Diese bietet eine
 * abgespeckte API und sollte ab 7.6 verwendet werden. Leider ändert sich die API in der 8.7 nochmals.
 *
 * Diese Klasse hier soll eine einheitliche API über alle LTS-Versionen bieten. Intern werden die
 * jeweils passenden TYPO3-Klasse genutzt, nach außen sollte das aber für die Module keine Rolle spielen.
 *
 *
 */
class Tx_Rnbase_Backend_Template_ModuleTemplate
{
    private $template;
    private $doc;
    /** @var tx_rnbase_mod_IModule */
    private $module;
    private $options;

    public function __construct(tx_rnbase_mod_IModule $module, $options = [])
    {
        $this->module = $module;
        $this->options = $this->prepareOptions($options);
    }

    /**
     * @return string complete module html code
     */
    public function renderContent(Tx_Rnbase_Backend_Template_ModuleParts $parts)
    {
        $method = $this->module->useModuleTemplate() ? 'renderContent76' : 'renderContent62';

        return $this->$method($parts);
    }

    public function getPageRenderer()
    {
        return $this->getDoc()->getPageRenderer();
    }

    /**
     * @return string complete module html code
     */
    protected function renderContent62(Tx_Rnbase_Backend_Template_ModuleParts $parts)
    {
        $markers = array();
        $content .= $parts->getContent(); // Muss vor der Erstellung des Headers geladen werden
        $content .= $this->getDoc()->sectionEnd();  // Zur Sicherheit eine offene Section schließen

        // Setting up the buttons and markers for docheader
        $docHeaderButtons = $parts->getButtons();
        $markers['CSH'] = $docHeaderButtons['csh'];
        $markers['HEADER'] = $this->getDoc()->header($parts->getTitle());
        $markers['SELECTOR'] = $parts->getSelector();
        // Das FUNC_MENU enthält die Modul-Funktionen, die per ext_tables.php registriert werden
        $markers['FUNC_MENU'] = $parts->getFuncMenu();

        // SUBMENU sind zusätzliche Tabs die eine Modul-Funktion bei Bedarf einblenden kann.
        $markers['SUBMENU'] = $parts->getSubMenu();
        $markers['TABS'] = $markers['SUBMENU']; // Deprecated use ###SUBMENU###
        $markers['CONTENT'] = $content;

        $content = $this->getDoc()->startPage($parts->getTitle());
        $content .= $this->getDoc()->moduleBody($parts->getPageInfo(), $docHeaderButtons, $markers);

        return $this->getDoc()->insertStylesAndJS($content);
    }

    /**
     * der Weg ab TYPO3 7.6
     * TODO: fertig implementieren
     * @return void
     */
    protected function renderContent76(Tx_Rnbase_Backend_Template_ModuleParts $parts)
    {
        /* @var $moduleTemplate TYPO3\CMS\Backend\Template\ModuleTemplate */
        $moduleTemplate = tx_rnbase::makeInstance('TYPO3\\CMS\\Backend\\Template\\ModuleTemplate');
//        $moduleTemplate->getPageRenderer()->loadJquery();
        $moduleTemplate->getDocHeaderComponent()->setMetaInformation($parts->getPageInfo());
        $this->registerMenu($moduleTemplate, $parts);

        $content = $moduleTemplate->header($parts->getTitle());
        $content .= $this->module->buildFormTag();
        if (is_string($parts->getFuncMenu())) {
            // Fallback für Module, die das FuncMenu selbst als String generieren
            $content .= $parts->getFuncMenu();
        }

        $content .= $parts->getSelector() .'<div style="clear:both;"></div>';
        $content .= $parts->getSubMenu();
        $content .= $parts->getContent();
        $content .= '</form>';

        // Es ist sinnvoll, die Buttons nach der Generierung des Content zu generieren
        $this->generateButtons($moduleTemplate, $parts);

        // Workaround: jumpUrl wieder einfügen
        // @TODO Weg finden dass ohne das DocumentTemplate zu machen
        $content .= '<!--###POSTJSMARKER###-->';
        $content = $this->getDoc()->insertStylesAndJS($content);
        // @TODO haupttemplate eines BE moduls enthält evtl. JS/CSS etc.
        // das wurde bisher über das DocumentTemplate eingefügt, was jetzt
        // nicht mehr geht. Dafür muss ein Weg gefunden werden.
        $moduleTemplate->setContent($content);
        return $moduleTemplate->renderContent();
    }

    /**
     * Create the panel of buttons for submitting the form or otherwise perform operations.
     *
     * @param \TYPO3\CMS\Backend\Template\ModuleTemplate $moduleTemplate
     */
    protected function generateButtons($moduleTemplate, Tx_Rnbase_Backend_Template_ModuleParts $parts)
    {
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        // CSH
        $docHeaderButtons = $parts->getButtons();
        if(isset($docHeaderButtons['csh']) && $docHeaderButtons['csh']) {
            $cshButton = $buttonBar->makeHelpButton()
                ->setModuleName($this->module->getName())
                ->setFieldName('');
            $buttonBar->addButton($cshButton);
        }
        if($this->module->getPid()) {
            // Shortcut
            $shortcutButton = $buttonBar->makeShortcutButton()
                ->setModuleName($this->module->getName())
                ->setGetVariables(['id', 'edit_record', 'pointer', 'new_unique_uid', 'search_field', 'search_levels', 'showLimit'])
                ->setSetVariables(array_keys($this->module->MOD_MENU));
            $buttonBar->addButton($shortcutButton);

        }
    }

    /**
     *
     * @param \TYPO3\CMS\Backend\Template\ModuleTemplate $moduleTemplate
     * @param Tx_Rnbase_Backend_Template_ModuleParts $parts
     */
    protected function registerMenu($moduleTemplate, Tx_Rnbase_Backend_Template_ModuleParts $parts)
    {
        // So funktioniert das nicht. Warum auch immer
        // $moduleTemplate->registerModuleMenu($this->options['modname']);
        // Das Menu wird im Module generiert und hier nur registriert
        if (is_object($parts->getFuncMenu())) {
            // Das ist der empfohlene Weg
            $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($parts->getFuncMenu());
        }
    }

    /**
     * Returns a template instance. Liefert die Instanzvariable doc.
     * Die Instanz wird bis einschließlich T3 8.7 erstellt.
     *
     * @return template|TYPO3\CMS\Backend\Template\DocumentTemplate|Tx_Rnbase_Backend_Template_Override_DocumentTemplate
     */
    public function getDoc()
    {
        if (!$this->doc) {
            $this->doc = tx_rnbase::makeInstance(
                'Tx_Rnbase_Backend_Template_Override_DocumentTemplate'
            );
            $this->initDoc($this->doc);
        }

        return $this->doc;
    }

    /**
     *
     * @param TYPO3\CMS\Backend\Template\DocumentTemplate $doc
     */
    protected function initDoc($doc)
    {
        $doc->backPath = $GLOBALS['BACK_PATH'];
        $doc->form = $this->options['form'];
        $doc->docType = 'xhtml_trans';
        $doc->inDocStyles = $this->options['docstyles'];
        $doc->inDocStylesArray[] = $doc->inDocStyles;
//        $doc->tableLayout = $this->getTableLayout();
        $doc->setModuleTemplate($this->options['template']);
        if(!tx_rnbase_util_TYPO3::isTYPO76OrHigher()) {
            $doc->loadJavascriptLib('contrib/prototype/prototype.js');
        }
        else {
            $doc->getPageRenderer()->loadJquery();
        }
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
                if (top.fsMod) top.fsMod.recentIds["web"] = ' . $this->options['pid'] . ';</script>';
    }

    private function prepareOptions($options)
    {
        if(!isset($options['modname'])) {
            $options['modname'] = $this->module->getName();
        }
        if(!isset($options['pid'])) {
            $options['pid'] = $this->module->getPid();
        }
        if(!isset($options['template'])) {
            throw new Exception('No template for module found.');
        }
        if(!isset($options['form'])) {
            $modUrl = Tx_Rnbase_Backend_Utility::getModuleUrl(
                $options['modname'],
                array(
                    'id' => $options['pid']
                ),
                ''
            );
            $options['form'] = '<form action="' . $modUrl . '" method="post" enctype="multipart/form-data">';
        }

        return $options;
    }
}
