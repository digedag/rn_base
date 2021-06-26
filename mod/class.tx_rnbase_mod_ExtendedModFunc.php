<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2009-2011 Rene Nitzsche (rene@system25.de)
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
tx_rnbase::load('tx_rnbase_util_Network');
tx_rnbase::load('tx_rnbase_mod_IModule');
tx_rnbase::load('tx_rnbase_mod_IModFunc');
tx_rnbase::load('tx_rnbase_util_BaseMarker');
tx_rnbase::load('tx_rnbase_util_Templates');

/**
 * ModFunc mit SubSelector und SubMenu.
 */
abstract class tx_rnbase_mod_ExtendedModFunc implements tx_rnbase_mod_IModFunc
{
    public function init(tx_rnbase_mod_IModule $module, $conf)
    {
        $this->mod = $module;
        $configurations = $this->getModule()->getConfigurations();
        if ($file = $configurations->get($this->getConfId().'locallang')) {
            $GLOBALS['LANG']->includeLLFile($file);
        }
    }

    /**
     * Returns the base module.
     *
     * @return tx_rnbase_mod_IModule
     */
    public function getModule()
    {
        return $this->mod;
    }

    public function main()
    {
        $out = '';
        $conf = $this->getModule()->getConfigurations();

        $file = tx_rnbase_util_Files::getFileAbsFileName($conf->get($this->getConfId().'template'));
        $templateCode = tx_rnbase_util_Network::getUrl($file);
        if (!$templateCode) {
            return $conf->getLL('msg_template_not_found').'<br />File: \''.$file.'\'<br />ConfId: \''.$this->getConfId().'template\'';
        }
        $subpart = '###'.strtoupper($this->getFuncId()).'###';
        $template = tx_rnbase_util_Templates::getSubpart($templateCode, $subpart);
        if (!$template) {
            return $conf->getLL('msg_subpart_not_found').': '.$subpart;
        }

        $start = microtime(true);
        $memStart = memory_get_usage();
        $out .= $this->createContent($template, $conf);
        if (tx_rnbase_util_BaseMarker::containsMarker($out, 'MOD_')) {
            $markerArr = [];
            $memEnd = memory_get_usage();
            $markerArr['###MOD_PARSETIME###'] = (microtime(true) - $start);
            $markerArr['###MOD_MEMUSED###'] = ($memEnd - $memStart);
            $markerArr['###MOD_MEMSTART###'] = $memStart;
            $markerArr['###MOD_MEMEND###'] = $memEnd;
            $out = tx_rnbase_util_Templates::substituteMarkerArrayCached($out, $markerArr);
        }

        return $out;
    }

    private function createContent($template, $conf)
    {
        $formTool = $this->getModule()->getFormTool();

        // TabMenu initialisieren
        $menuItems = [];
        $menu = $this->initSubMenu($menuItems, $this->getModule()->getFormTool());

        $this->getModule()->setSubMenu($menu['menu']);

        // SubSelectors
        $selectorStr = '';
        $subSels = $this->makeSubSelectors($selectorStr);
        $this->getModule()->setSelector($selectorStr);
        if (is_array($subSels) && 0 == count($subSels)) {
            // Abbruch, da kein Wert gewählt
            return $this->handleNoSubSelectorValues();
        } elseif (is_string($subSels)) {
            // Ein String als Ergebnis bedeutet ebenfalls Abbruch.
            return $subSels;
        }

        $args = [];

        //$out .= $this->getContent($template, $conf, $conf->getFormatter(), $formTool);

        $handler = $menuItems[$menu['value']];
        if (is_object($handler)) {
            $subpart = '###'.strtoupper($handler->getSubID()).'###';
            $templateSub = tx_rnbase_util_Templates::getSubpart($template, $subpart);

            $args[] = $templateSub;
            $args[] = $this->getModule();
            $args[] = ['subSels' => $subSels];
            // Der Handler sollte nicht das gesamte Template bekommen, sondern nur seinen Subpart...
            $subOut = call_user_func_array([$handler, 'showScreen'], $args);
        } else {
            $subOut = '';
        }

        // wrap the content into a tab pane
        if ($this->getModule()->useModuleTemplate()) {
            $subOut = '<div class="tab-content"><div role="tabpanel" class="tab-pane active"><div class="form-section">'.
                $subOut.
            '</div></div></div>';
        }

        // Jetzt noch die COMMON-PARTS
        $content = '';
        $content .= $formTool->getTCEForm()->printNeededJSFunctions_top();
        $content .= tx_rnbase_util_Templates::getSubpart($template, '###COMMON_START###');
        $content .= $subOut;
        $content .= tx_rnbase_util_Templates::getSubpart($template, '###COMMON_END###');
        // Den JS-Code für Validierung einbinden
        $content .= $formTool->getTCEForm()->printNeededJSFunctions();

        return $content;
    }

    /**
     * Liefert die ConfId für diese ModFunc.
     *
     * @return string
     */
    public function getConfId()
    {
        return $this->getFuncId().'.';
    }

    /**
     * Jede Modulfunktion sollte über einen eigenen Schlüssel innerhalb des Moduls verfügen. Dieser
     * wird später für die Konfiguration verwendet.
     */
    abstract protected function getFuncId();

    /**
     * @param array                   $menuObjs
     * @param tx_rnbase_util_FormTool $formTool
     *
     * @return array
     */
    protected function initSubMenu(&$menuObjs, $formTool)
    {
        $items = $this->getSubMenuItems();
        if (!is_array($items)) {
            return;
        }

        $menuItems = [];
        foreach ($items as $idx => $tabItem) {
            $menuItems[$idx] = $tabItem->getSubLabel();
            $menuObjs[$idx] = $tabItem;
            $out = $tabItem->handleRequest($this->getModule());
            if ($out) {
                $this->showMessage($out, $tabItem);
            }
        }

        $menu = $formTool->showTabMenu($this->getModule()->getPid(), 'mn_'.$this->getFuncId(), $this->getModule()->getName(), $menuItems);

        return $menu;
    }

    protected function showMessage($message, tx_rnbase_mod_IModHandler $handler)
    {
        $flashMessageClass = tx_rnbase_util_Typo3Classes::getFlashMessageClass();
        $severity = $flashMessageClass::OK;
        $store = false;
        if (is_array($message)) {
            $msg = $message['message'];
            $title = $message['title'];
            $severity = $message['severity'];
            $store = boolean($message['storeinsession']);
        } else {
            $msg = $message;
            $title = $handler->getSubLabel();
        }
        $this->getModule()->addMessage($msg, $title, $severity, $store);
    }

    /**
     * It is possible to overwrite this method and return an array of tab functions.
     *
     * @return array
     */
    abstract protected function getSubMenuItems();

    /**
     * Liefert false, wenn es keine SubSelectors gibt. sonst ein Array mit den ausgewählten Werten.
     *
     * @param string $selectorStr
     *
     * @return array or false if not needed. Return empty array if no item found
     */
    abstract protected function makeSubSelectors(&$selectorStr);

    protected function handleNoSubSelectorValues()
    {
        return '###LABEL_NO_SUBSELECTORITEMS_FOUND###';
    }
}
