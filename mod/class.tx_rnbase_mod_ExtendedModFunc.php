<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_mod_IModule');
tx_rnbase::load('tx_rnbase_mod_IModFunc');
tx_rnbase::load('tx_rnbase_util_BaseMarker');
tx_rnbase::load('tx_rnbase_util_Templates');


/**
 * ModFunc mit SubSelector und SubMenu
 */
abstract class tx_rnbase_mod_ExtendedModFunc implements tx_rnbase_mod_IModFunc {
	public function init(tx_rnbase_mod_IModule $module, $conf) {
		$this->mod = $module;
	}
	/**
	 * Returns the base module
	 *
	 * @return tx_rnbase_mod_IModule
	 */
	public function getModule() {
		return $this->mod;
	}
	public function main() {
		$out = '';
		$conf = $this->getModule()->getConfigurations();

		$file = t3lib_div::getFileAbsFileName($conf->get($this->getConfId().'template'));
		$templateCode = t3lib_div::getURL($file);
		if(!$templateCode) return $conf->getLL('msg_template_not_found').'<br />File: \'' . $file . '\'<br />ConfId: \'' . $this->getConfId().'template\'';
		$subpart = '###'.strtoupper($this->getFuncId()).'###';
		$template = tx_rnbase_util_Templates::getSubpart($templateCode, $subpart);
		if(!$template) return $conf->getLL('msg_subpart_not_found'). ': ' . $subpart;

		$start = microtime(true);
		$memStart = memory_get_usage();
		$out .= $this->createModuleContent();
		if(tx_rnbase_util_BaseMarker::containsMarker($out, 'MOD_')) {
			$markerArr = array();
			$memEnd = memory_get_usage();
			$markerArr['###MOD_PARSETIME###'] = (microtime(true) - $start);
			$markerArr['###MOD_MEMUSED###'] = ($memEnd - $memStart);
			$markerArr['###MOD_MEMSTART###'] = $memStart;
			$markerArr['###MOD_MEMEND###'] = $memEnd;
			$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($out, $markerArr);
		}
		return $out;
	}
	private function createModuleContent() {
		$out = '';
		// TabMenu initialisieren
		$menuItems = array();
		$menu = $this->initSubMenu($menuItems);
		// SubSelectors
		$selectorStr = '';
		$args = $this->makeSubSelectors($selectorStr);
		if(is_array($args) && count($args) == 0) {
			// Abbruch, da kein Wert gewählt
			return $this->handleNoSubSelectorValues();
		}

		$args = is_array($args) ? $args : array();

		$out .= $this->getContent($template, $conf, $conf->getFormatter(), $this->getModule()->getFormTool());

		$handler = $menuItems[$menu['value']];
		if(is_object($handler)) {
			//
			$args[] = $this->getModule();
			$out .= call_user_func_array(array($handler,'showScreen'),$args);
		}

		$content .= $formTool->getTCEForm()->printNeededJSFunctions_top();
		$content .= $modContent;
		// Den JS-Code für Validierung einbinden
		$content .= $formTool->getTCEForm()->printNeededJSFunctions();
		return $content;

		return $out;
	}
	/**
	 * Kindklassen implementieren diese Methode um den Modulinhalt zu erzeugen
	 * @param string $template
	 * @param tx_rnbase_configurations $configurations
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param tx_rnbase_util_FormTool $formTool
	 * @return string
	 */
	abstract protected function getContent($template, &$configurations, &$formatter, $formTool);
	/**
	 * Liefert die ConfId für diese ModFunc
	 *
	 * @return string
	 */
	public function getConfId() {
		return $this->getFuncId().'.';
	}
	/**
	 * Jede Modulfunktion sollte über einen eigenen Schlüssel innerhalb des Moduls verfügen. Dieser
	 * wird später für die Konfigruration verwendet
	 *
	 */
	abstract protected function getFuncId();
	protected function initSubMenu($formTool, &$menuItems) {
		$items = $this->getSubMenuItems();
		if(!is_array($items)) return;

		foreach($items As $idx => $tabItem) {
			$menuItems[$idx] = $tabItem->getTabLabel();
			$tabItem->handleRequest($this->getModule());
		}
		return $formTool->showTabMenu($this->getModule()->getPid(), 'mn_'.$this->getFuncId(), $this->getModule()->getName(),
						$menuItems);
	}
	/**
	 * It is possible to overwrite this method and return an array of tab functions
	 * @return array
	 */
	abstract protected function getSubMenuItems();

	/**
	 * Liefert false, wenn es keine SubSelectors gibt. sonst ein Array mit den ausgewählten Werten.
	 * @param string $selectorStr
	 * @return array or false if not needed. Return empty array if no item found
	 */
	abstract protected function makeSubSelectors(&$selectorStr);

	protected function handleNoSubSelectorValues() {
		return '###LABEL_NO_SUBSELECTORITEMS_FOUND###';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_ExtendedModFunc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_ExtendedModFunc.php']);
}

?>