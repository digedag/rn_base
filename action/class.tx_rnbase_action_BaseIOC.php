<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (rene@system25.de)
*  All rights reserved
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

require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');

tx_div::load('tx_rnbase_util_Misc');

/**
 * Abstract base class for an action. This action is build to implement the
 * pattern Inversion of Control (IOC). If you implement a child class you have 
 * to implement
 * handleRequest() - do whatever your action has to do
 * getTemplateName() - What is the default name of your html-Template
 * getViewClassName() - which class should render the result
 * All other tasks are done here.
 * 
 * This class works with PHP5 only!
 */
abstract class tx_rnbase_action_BaseIOC {
	private static $callCount = 0;
	private static function countCall() { return self::$callCount++; }
	function execute(&$parameters,&$configurations){
		$debug = $configurations->get($this->getConfId().'_debugview');
		if($debug) {
			$time = microtime(true);
			$memStart = memory_get_usage();
		}
 
		$viewData =& $configurations->getViewData();
		$GLOBALS['TT']->push(get_class($this), 'handleRequest');
		$errOut = $this->handleRequest($parameters,$configurations, $viewData);
		$GLOBALS['TT']->pull();
		if($errOut) return $errOut;

		// View
		$view = tx_div::makeInstance($this->getViewClassName());
		$view->setTemplatePath($configurations->getTemplatePath());
		// Das Template wird komplett angegeben
		$tmplName = $this->getTemplateName();
		if(!$tmplName || !strlen($tmplName))
			tx_rnbase_util_Misc::mayday('No template name defined!');

		$view->setTemplateFile($configurations->get($tmplName.'Template'));
		$GLOBALS['TT']->push(get_class($this), 'render');
		$out = $view->render($tmplName, $configurations);
		$GLOBALS['TT']->pull();
		if($debug) {
			$memEnd = memory_get_usage();
			t3lib_div::debug(array(
				'Execution Time'=>(microtime(true)-$time),
				'Memory Start'=>$memStart,
				'Memory End'=>$memEnd,
				'Memory Consumed'=>($memEnd-$memStart),
			), 'View statistics for: '.$this->getConfId());
		}
		return $out;
	}

	/**
	 * Liefert die ConfId für den View
	 * @return string
	 */
	protected function getConfId() {
		return $this->getTemplateName().'.';
	}
	/**
	 * Liefert den Default-Namen des Templates. Über diesen Namen
	 * wird per Konvention auch auf ein per TS konfiguriertes HTML-Template
	 * geprüft. Dessen Key wird aus dem Name und dem String "Template" 
	 * gebildet: [tmpname]Template
	 * @return string
	 */
	protected abstract function getTemplateName();

	/**
	 * Liefert den Namen der View-Klasse
	 * @param tx_rnbase_configurations $configurations
	 * @return string
	 */
	protected abstract function getViewClassName();
	/**
	 * Kindklassen führen ihr die eigentliche Arbeit durch. Zugriff auf das 
	 * Backend und befüllen der viewdata 
	 *
	 * @param tx_lib_parameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewdata
	 * @return string Errorstring or null
	 */
	protected abstract function handleRequest(&$parameters,&$configurations, &$viewdata);

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_BaseIOC.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_BaseIOC.php']);
}
?>