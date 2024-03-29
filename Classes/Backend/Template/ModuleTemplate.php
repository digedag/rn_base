<?php

namespace Sys25\RnBase\Backend\Template;

use Exception;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Backend\Template\Override\DocumentTemplate;
use Sys25\RnBase\Backend\Utility\BackendUtility;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;

/* *******************************************************
 *  Copyright notice
 *
 *  (c) 2017-2023 René Nitzsche <rene@system25.de>
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

/**
 * Die Klasse wrapped die Template-Klassen für BE-Module in TYPO3. Diese sind recht starken
 * Änderungen in der API unterworfen. Bis zur 6.2 gab es recht unterschiedliche Templateklassen,
 * wobei in erster Linie TYPO3\CMS\Backend\Template\DocumentTemplate genutzt wurde. Seit der
 * 7.6 gibt es zusätzlich die Klasse TYPO3\CMS\Backend\Template\ModulTemplate. Diese bietet eine
 * abgespeckte API und sollte ab 7.6 verwendet werden. Leider ändert sich die API in der 8.7 nochmals.
 *
 * Diese Klasse hier soll eine einheitliche API über alle LTS-Versionen bieten. Intern werden die
 * jeweils passenden TYPO3-Klassen genutzt, nach außen sollte das aber für die Module keine Rolle spielen.
 */
class ModuleTemplate
{
    private $template;

    private $doc;

    /** @var IModule */
    private $module;

    private $options;

    public function __construct(IModule $module, $options = [])
    {
        $this->module = $module;
        $this->options = $this->prepareOptions($options);
    }

    /**
     * @return string complete module html code
     */
    public function renderContent(ModuleParts $parts)
    {
        if (TYPO3::isTYPO121OrHigher()) {
            return $this->renderContent12($parts);
        } else {
            return $this->renderContent76($parts);
        }
    }

    public function getPageRenderer()
    {
        return $this->getDoc()->getPageRenderer();
    }

    /**
     * der Weg ab TYPO3 7.6
     * TODO: fertig implementieren.
     */
    protected function renderContent12(ModuleParts $parts)
    {
        /** @var ModuleTemplateFactory $factory */
        $factory = tx_rnbase::makeInstance(ModuleTemplateFactory::class);
        $view = $factory->create($this->options['request']);
        $content = '';
        //        $moduleTemplate->getPageRenderer()->loadJquery();
        $view->getDocHeaderComponent()->setMetaInformation($parts->getPageInfo());
        $this->registerMenu($view, $parts);

        $content .= $this->options['form'] ?? $this->module->buildFormTag();

        $view->makeDocHeaderModuleMenu(['id' => $this->module->getPid()]);

        if (is_string($parts->getFuncMenu())) {
            // Fallback für Module, die das FuncMenu selbst als String generieren
            $content .= $parts->getFuncMenu();
        }

        $content .= $parts->getSelector().'<div style="clear:both;"></div>';
        $content .= $parts->getSubMenu();
        $content .= $parts->getContent();
        $content .= '</form>';

        // Es ist sinnvoll, die Buttons nach der Generierung des Content zu generieren
        $this->generateButtons($view, $parts);

        // Workaround: jumpUrl wieder einfügen
        // @TODO Weg finden dass ohne das DocumentTemplate zu machen
        $content .= '<!--###POSTJSMARKER###-->';
        $content = $this->getDoc()->insertStylesAndJS($content);
        // @TODO haupttemplate eines BE moduls enthält evtl. JS/CSS etc.
        // das wurde bisher über das DocumentTemplate eingefügt, was jetzt
        // nicht mehr geht. Dafür muss ein Weg gefunden werden.
        $view->setContent($content);

        return $view->renderContent();
    }

    /**
     * der Weg ab TYPO3 7.6
     * TODO: fertig implementieren.
     */
    protected function renderContent76(ModuleParts $parts)
    {
        /* @var $moduleTemplate \TYPO3\CMS\Backend\Template\ModuleTemplate */
        $moduleTemplate = null;
        if (TYPO3::isTYPO121OrHigher()) {
            /** @var ModuleTemplateFactory $factory */
            $factory = tx_rnbase::makeInstance(ModuleTemplateFactory::class);
            $moduleTemplate = $factory->create($this->options['request']);
        } else {
            $moduleTemplate = tx_rnbase::makeInstance('TYPO3\\CMS\\Backend\\Template\\ModuleTemplate');
        }

        $content = '';
        //        $moduleTemplate->getPageRenderer()->loadJquery();
        $moduleTemplate->getDocHeaderComponent()->setMetaInformation($parts->getPageInfo());
        $this->registerMenu($moduleTemplate, $parts);

        $content .= $this->options['form'] ?? $this->module->buildFormTag();
        if (is_string($parts->getFuncMenu())) {
            // Fallback für Module, die das FuncMenu selbst als String generieren
            $content .= $parts->getFuncMenu();
        }

        $content .= $parts->getSelector().'<div style="clear:both;"></div>';
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
    protected function generateButtons($moduleTemplate, ModuleParts $parts)
    {
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        // CSH
        $docHeaderButtons = $parts->getButtons();
        if (isset($docHeaderButtons['csh']) && $docHeaderButtons['csh']) {
            $cshButton = $buttonBar->makeHelpButton();
            if (TYPO3::isTYPO121OrHigher()) {
                // TODO
            } else {
                $cshButton->setModuleName($this->module->getName())
                    ->setFieldName('');
            }
            $buttonBar->addButton($cshButton);
        }
        if ($this->module->getPid()) {
            // Shortcut
            $shortcutButton = $buttonBar->makeShortcutButton();
            if (TYPO3::isTYPO121OrHigher()) {
                $shortcutButton->setRouteIdentifier($this->module->getRouteIdentifier())
                ->setDisplayName($this->module->getTitle())
                ->setArguments(['id' => $this->module->getPid()]);
            } else {
                $shortcutButton->setModuleName($this->module->getName());
                $shortcutButton->setGetVariables(['id', 'edit_record', 'pointer', 'new_unique_uid', 'search_field', 'search_levels', 'showLimit'])
                    ->setSetVariables(array_keys($this->module->MOD_MENU));
            }

            $buttonBar->addButton($shortcutButton);
        }
    }

    /**
     * @param \TYPO3\CMS\Backend\Template\ModuleTemplate $moduleTemplate
     * @param ModuleParts     $parts
     */
    protected function registerMenu($moduleTemplate, ModuleParts $parts)
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
     * @return DocumentTemplate|\TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public function getDoc()
    {
        if (!$this->doc) {
            $this->doc = tx_rnbase::makeInstance(DocumentTemplate::class);
            $this->initDoc($this->doc);
        }

        return $this->doc;
    }

    /**
     * @param DocumentTemplate $doc
     */
    protected function initDoc($doc)
    {
        $doc->backPath = $GLOBALS['BACK_PATH'] ?? '';
        $doc->form = $this->options['form'];
        $doc->docType = 'xhtml_trans';
        $doc->inDocStyles = $this->options['docstyles'];
        $doc->inDocStylesArray[] = $doc->inDocStyles;
        //        $doc->tableLayout = $this->getTableLayout();
        $doc->setModuleTemplate($this->options['template']);
    }

    private function prepareOptions($options)
    {
        if (!isset($options['modname'])) {
            $options['modname'] = $this->module->getName();
        }
        if (!isset($options['pid'])) {
            $options['pid'] = $this->module->getPid();
        }
        if (!isset($options['template'])) {
            throw new Exception('No template for module found.');
        }
        if (!isset($options['form'])) {
            $modUrl = BackendUtility::getModuleUrl(
                $options['modname'],
                [
                    'id' => $options['pid'],
                ],
                ''
            );
            $options['form'] = '<form action="'.$modUrl.'" method="post" enctype="multipart/form-data">';
        }

        return $options;
    }
}
