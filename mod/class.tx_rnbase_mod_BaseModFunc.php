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
		if(!$templateCode) return $conf->getLL('msg_template_not_found');
		$subpart = '###'.strtoupper($this->getFuncId()).'###';
		$template = $conf->getCObj()->getSubpart($templateCode, $subpart);
		if(!$template) return $conf->getLL('msg_subpart_not_found'). ': ' . $subpart;

		$out .= $this->getContent($template, $conf, $conf->getFormatter());
		return $out;
	}
	/**
	 * Kindklassen implementieren diese Methode um den Modulinhalt zu erzeugen
	 * @return string
	 */
	abstract protected function getContent($template, &$configurations, &$formatter);
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

?>