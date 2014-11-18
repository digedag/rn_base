<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2011 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_rnbase_util_Debug');
tx_rnbase::load('tx_rnbase_util_Templates');

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
	private $configurations = NULL;

	/**
	 * @param tx_rnbase_parameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 *
	 * @return string
	 */
	function execute(&$parameters, &$configurations){
		$this->setConfigurations($configurations);
		$debugKey = $configurations->get($this->getConfId().'_debugview');
		$debug = ($debugKey && ($debugKey==='1' ||
				($_GET['debug'] && array_key_exists($debugKey, array_flip(t3lib_div::trimExplode(',', $_GET['debug'])))) ||
				($_POST['debug'] && array_key_exists($debugKey, array_flip(t3lib_div::trimExplode(',', $_POST['debug']))))
				)
		);
		if($debug) {
			$time = microtime(TRUE);
			$memStart = memory_get_usage();
		}
		if ($configurations->getBool($this->getConfId() . 'toUserInt')) {
			if ($debug) {
				tx_rnbase_util_Debug::debug(
					'Converting to USER_INT!',
					'View statistics for: ' . $this->getConfId(). ' Key: ' . $debugKey
				);
			}
			$configurations->convertToUserInt();
		}

		$cacheHandler = $this->getCacheHandler($configurations, $this->getConfId().'_caching.');
		$out = $cacheHandler ? $cacheHandler->getOutput($this, $configurations, $this->getConfId()) : '';
		$cached = TRUE;
		if(!$out) {
			$cached = FALSE;
			$viewData =& $configurations->getViewData();
			tx_rnbase_util_Misc::pushTT(get_class($this), 'handleRequest');
			$out = $this->handleRequest($parameters, $configurations, $viewData);
			tx_rnbase_util_Misc::pullTT();
			if(!$out) {
				// View
				// It is possible to set another view via typoscript
				$viewClassName = $configurations->get($this->getConfId() . 'viewClassName');
				$viewClassName = strlen($viewClassName) > 0 ? $viewClassName : $this->getViewClassName();
				// TODO: error handling...
				$view = tx_rnbase::makeInstance($viewClassName);
				$view->setTemplatePath($configurations->getTemplatePath());
				if(method_exists($view, 'setController'))
					$view->setController($this);
				// Das Template wird komplett angegeben
				$tmplName = $this->getTemplateName();
				if(!$tmplName || !strlen($tmplName))
					tx_rnbase_util_Misc::mayday('No template name defined!');

				$view->setTemplateFile($configurations->get($tmplName.'Template', TRUE));
				tx_rnbase_util_Misc::pushTT(get_class($this), 'render');
				$out = $view->render($tmplName, $configurations);
				tx_rnbase_util_Misc::pullTT();
			}
			if($cacheHandler)
				$cacheHandler->setOutput($out, $configurations, $this->getConfId());
		}
		if($debug) {
			$memEnd = memory_get_usage();
			tx_rnbase_util_Debug::debug(array(
				'Execution Time'=>(microtime(TRUE)-$time),
				'Memory Start'=>$memStart,
				'Memory End'=>$memEnd,
				'Memory Consumed'=>($memEnd-$memStart),
				'Cached?' => $cached ? 'yes' : 'no',
				'CacheHandler' => is_object($cacheHandler) ? get_class($cacheHandler) : '',
				'SubstCacheEnabled?' => tx_rnbase_util_Templates::isSubstCacheEnabled() ? 'yes' : 'no',
			), 'View statistics for: '.$this->getConfId(). ' Key: ' . $debugKey);
		}
		// reset the substCache after each view!
		tx_rnbase_util_Templates::resetSubstCache();
		return $out;
	}

	/**
	 * Returns configurations object
	 * @return tx_rnbase_configurations
	 */
	public function getConfigurations() {
		return $this->configurations;
	}
	/**
	 * Returns configurations object
	 * @return tx_rnbase_configurations
	 */
	public function setConfigurations(tx_rnbase_configurations $configurations) {
		$this->configurations = $configurations;
	}

	/**
	 * Returns request parameters
	 *
	 * @return tx_rnbase_IParameters
	 */
	public function getParameters() {
		return $this->getConfigurations()->getParameters();
	}

	/**
	 * Returns view data
	 *
	 * @return ArrayObject
	 */
	public function getViewData() {
		return $this->getConfigurations()->getViewData();
	}

	/**
	 * Find a configured cache handler.
	 *
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId
	 * @return tx_rnbase_action_ICacheHandler
	 */
	protected function getCacheHandler($configurations, $confId) {
		$clazz = $configurations->get($confId.'class');
		if(!$clazz) return FALSE;
		$handler = tx_rnbase::makeInstance($clazz, $configurations, $confId);
		return $handler;
	}

	/**
	 * Liefert die ConfId für den View
	 * @return string
	 */
	public function getConfId() {
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
	 * @param tx_rnbase_IParameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array $viewdata
	 * @return string Errorstring or NULL
	 */
	protected abstract function handleRequest(&$parameters, &$configurations, &$viewdata);

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_BaseIOC.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/action/class.tx_rnbase_action_BaseIOC.php']);
}
