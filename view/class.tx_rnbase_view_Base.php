<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2006-2015 Rene Nitzsche
 * Contact: rene@system25.de
 * All rights reserved
 *
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
tx_rnbase::load('tx_rnbase_action_BaseIOC');
tx_rnbase::load('tx_rnbase_util_BaseMarker');
tx_rnbase::load('tx_rnbase_util_Templates');
tx_rnbase::load('tx_rnbase_util_Files');

/**
 * Depends on: none
 *
 * Base class for all views.
 * TODO: This class should have a default template path and an optional user defined path. So
 * templates can be searched in both.
 *
 * @author René Nitzsche <rene@system25.de>
 * @package TYPO3
 * @subpackage rn_base
 */
class tx_rnbase_view_Base{
	private $pathToTemplates;
	private $_pathToFile;
	private $controller;

	/**
	 * Enter description here...
	 *
	 * @param string $view default name of view
	 * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
	 * @return string
	 */
	function render($view, &$configurations){
		$this->_init($configurations);
		$templateCode = tx_rnbase_util_Files::getFileResource($this->getTemplate($view, '.html'));
		if(!strlen($templateCode)) {
			tx_rnbase::load('tx_rnbase_util_Misc');
			tx_rnbase_util_Misc::mayday('TEMPLATE NOT FOUND: ' . $this->getTemplate($view, '.html'));
		}

		// Die ViewData bereitstellen
		$viewData =& $configurations->getViewData();
		// Optional kann schon ein Subpart angegeben werden
		$subpart = $this->getMainSubpart($viewData);
		if(!empty($subpart)) {
			$templateCode = tx_rnbase_util_Templates::getSubpart($templateCode, $subpart);
			if(!strlen($templateCode)) {
				tx_rnbase::load('tx_rnbase_util_Misc');
				tx_rnbase_util_Misc::mayday('SUBPART NOT FOUND: ' . $subpart);
			}
		}

		$controller = $this->getController();
		if($controller) {
			// disable substitution marker cache
			if($configurations->getBool($controller->getConfId().'_caching.disableSubstCache')) {
				tx_rnbase_util_Templates::disableSubstCache();
			}
		}

		$out = $this->createOutput($templateCode, $viewData, $configurations, $configurations->getFormatter());
		$out = $this->renderPluginData($out, $configurations);

		if($controller) {
			$params = array();
			$params['confid'] = $controller->getConfId();
			$params['item'] = $controller->getViewData()->offsetGet('item');
			$params['items'] = $controller->getViewData()->offsetGet('items');
			$markerArray = $subpartArray = $wrappedSubpartArray = array();
			tx_rnbase_util_BaseMarker::callModules(
				$out,
				$markerArray,
				$subpartArray,
				$wrappedSubpartArray,
				$params,
				$configurations->getFormatter()
			);
			$out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached(
				$out,
				$markerArray,
				$subpartArray,
				$wrappedSubpartArray
			);
		}

		return $out;
	}

	/**
	 * render plugin data and additional flexdata
	 *
	 * @param string $templateCode
	 * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
	 * @return string
	 */
	protected function renderPluginData(
		$templateCode,
		Tx_Rnbase_Configuration_ProcessorInterface $configurations
	) {
		// render only, if there is an controller
		if (!$this->getController()) {
			return $templateCode;
		}

		// check, if there are plugin markers to render
		if(!tx_rnbase_util_BaseMarker::containsMarker($templateCode, 'PLUGIN_')) {
			return $templateCode;
		}

		$confId = $this->getController()->getConfId();

		// build the data to render
		$pluginData = array_merge(
			// use the current data (tt_conten) to render
			(array) $configurations->getCObj()->data,
			// add some aditional columns, for example from the flexform od typoscript directly
			$configurations->getExploded(
				$confId . 'plugin.flexdata.'
			)
		);
		// check for unused columns
		$ignoreColumns = tx_rnbase_util_BaseMarker::findUnusedCols(
			$pluginData,
			$templateCode,
			'PLUGIN'
		);
		// create the marker array with the parsed columns
		$markerArray = $configurations->getFormatter()->getItemMarkerArrayWrapped(
			$pluginData,
			$confId . 'plugin.',
			$ignoreColumns,
			'PLUGIN_'
		);

		return tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($templateCode, $markerArray);
	}

	/**
	 * Entry point for child classes
	 *
	 * @param string $template
	 * @param array_object $viewData
	 * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
	 * @param tx_rnbase_util_FormatUtil $formatter
	 */
	function createOutput($template, &$viewData, &$configurations, &$formatter) {
		return $template;
	}

	/**
	 * Kindklassen können hier einen Subpart-Marker angeben, der initial als Template
	 * verwendet wird.
	 * Es wird dann in createOutput nicht mehr das gesamte
	 * Template übergeben, sondern nur noch dieser Abschnitt. Außerdem wird sichergestellt,
	 * daß dieser Subpart im Template vorhanden ist.
	 *
	 * @return string like ###MY_MAIN_SUBPART### or FALSE
	 */
	function getMainSubpart(&$viewData) {
		return FALSE;
	}

	/**
	 * This method is called first.
	 *
	 * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
	 */
	function _init(&$configurations){

	}

	/**
	 * Set the path of the template directory
	 *
	 * You can make use the syntax EXT:myextension/somepath.
	 * It will be evaluated to the absolute path by tx_rnbase_util_Files::getFileAbsFileName()
	 *
	 * @param string path to the directory containing the php templates
	 * @return void
	 * @see intro text of this class above
	 */
	function setTemplatePath($pathToTemplates) {
		$this->pathToTemplates = $pathToTemplates;
	}

	/**
	 * Set the used controller
	 *
	 * @param tx_rnbase_action_BaseIOC $controller
	 */
	public function setController(tx_rnbase_action_BaseIOC $controller) {
		$this->controller = $controller;
	}

	/**
	 * Returns the used controller
	 *
	 * @return tx_rnbase_action_BaseIOC
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * Set the path of the template file.
	 *
	 * You can make use the syntax EXT:myextension/template.php
	 *
	 * @param string path to the file used as templates
	 * @return void
	 */
	function setTemplateFile($pathToFile) {
		$this->_pathToFile = $pathToFile;
	}

	/**
	 * Returns the template to use.
	 * If TemplateFile is set, it is preferred. Otherwise
	 * the filename is build from pathToTemplates, the templateName and $extension.
	 *
	 * @param string name of template
	 * @param string file extension to use
	 * @return complete filename of template
	 */
	function getTemplate($templateName, $extension = '.php', $forceAbsPath = 0) {
		if (strlen($this->_pathToFile) > 0) {
			return ($forceAbsPath) ? tx_rnbase_util_Files::getFileAbsFileName($this->_pathToFile) : $this->_pathToFile;
		}
		$path = $this->pathToTemplates;
		$path .= substr($path, -1, 1) == '/' ? $templateName : '/' . $templateName;
		$extLen = strlen($extension);
		$path .= substr($path, ($extLen * -1), $extLen) == $extension ? '' : $extension;
		return $path;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/view/class.tx_rnbase_view_Base.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/view/class.tx_rnbase_view_Base.php']);
}
