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
 */
abstract class tx_rnbase_mod_BaseModFunc implements tx_rnbase_mod_IModFunc {
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

		$start = microtime(TRUE);
		$memStart = memory_get_usage();
		$out .= $this->getContent($template, $conf, $conf->getFormatter(), $this->getModule()->getFormTool());
		if(tx_rnbase_util_BaseMarker::containsMarker($out, 'MOD_')) {
			$markerArr = array();
			$memEnd = memory_get_usage();
			$markerArr['###MOD_PARSETIME###'] = (microtime(TRUE) - $start);
			$markerArr['###MOD_MEMUSED###'] = ($memEnd - $memStart);
			$markerArr['###MOD_MEMSTART###'] = $memStart;
			$markerArr['###MOD_MEMEND###'] = $memEnd;
			$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($out, $markerArr);
		}
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_IModFunc.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_IModFunc.php']);
}

