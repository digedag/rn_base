<?php

/**
 * This class is a wrapper around tslib_cObj::typoLink
 *
 * PHP versions 4 and 5
 *
 *  (c) 2008 Rene Nitzsche
 *  Contact: rene@system25.de
 *
 *  Original version:
 * Copyright (c) 2006-2007 Elmar Hinz
 *
 * LICENSE:
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 */

/**
 * This class is a wrapper around tslib_cObj::typoLink
 *
 * It is not a full implementation of typolink functionality
 * but targeted to the day-to-day requirements. The idea is to provide
 * an simple to use object orientated interface as an alternative to the
 * typolink functions of pi_base.
 *
 * Depends on: the TS link function
 *
 * @author     Elmar Hinz <elmar.hinz@team-red.net>
 * @package    TYPO3
 * @subpackage lib
 */
class tx_rnbase_util_Link {

	var $tagAttributes = array();       // setting attributes for the tag in general
	var $classString = '';              // tags class attribute
	var $idString = '';                 // tags id attribute
	var $cObject;                       // instance of tslib_cObj
	var $destination = '';              // page id, alias, external link, etc.
	var $labelString = '';              // tags label
	var $labelHasAlreadyHtmlSpecialChars = false; // is the label already HSC?
	var $noCacheBoolean = false;        // don't make a cHash
	var $noHashBoolean = false;         // add a no_cache=1 parameter
	var $overruledParameters = array(); // parameters overruled by $parameters
	var $parameters = array();		      // parameters of the link
	var $designatorString = '';         // parameter array name (prefixId) as controller namespace
	var $anchorString = '';             // section anchor as url target
	var $targetString = '';             // tags target attribute
	var $externalTargetString = '_blank'; // external target defaults to new window
	var $titleString = '';              // tags title attribute
	var $titleHasAlreadyHtmlSpecialChars = false; //is title attribute already HSC?

	// -------------------------------------------------------------------------------------
	// Constructor
	// -------------------------------------------------------------------------------------

	/**
	 * Construct a link object
	 *
	 * By default this object wraps tslib_cObj::typolink();
	 * The $cObjectClass parameter can be used to provide a mock object
	 * for unit tests.
	 *
	 * @param	object		mock object for testing purpuses
	 * @return	void
	 */
	function tx_rnbase_util_Link($cObjectClass = 'tslib_cObj') {
		$this->cObject = t3lib_div::makeInstance($cObjectClass);
	}

	// -------------------------------------------------------------------------------------
	// Setters
	// -------------------------------------------------------------------------------------

	/**
	 * Set the section anchor of the url
	 *
	 * Anchor of page as url target.
	 *
	 * @param	string		the anchor
	 * @return	object		self
	 */
	function anchor($anchorString) {
		$this->anchorString = $anchorString;
		return $this;
	}

	/**
	 * Set the designator (parameter array name) as controler namespace
	 *
	 * Put the parameters into this array.
	 * <samp>Example: &tx_example[parameterName]=parameterValue</samp>
	 * tx_example is the designator, parameterName is the key,
	 * pararmeterValue is the value of one array element.
	 *
	 * @param	string		parameter array name
	 * @return	object		self
	 */
	function designator($designatorString) {
		$this->designatorString = $designatorString;
		return $this;
	}

	/**
	 * Set the id attribute of the tag
	 *
	 * @param	string		id attribute
	 * @return	object		self
	 */
	function idAttribute($idString) {
		$this->idString = $idString;
		return $this;
	}

	/**
	 * Set the class attribute of the tag
	 *
	 * @param	string		class name
	 * @return	object		self
	 */
	function classAttribute($classString) {
		$this->classString = $classString;
		return $this;
	}

	/**
	 * Set the links destination
	 *
	 * @param	mixed		pageId, page alias, external url, etc.
	 * @param	boolean		if true don't parse through htmlspecialchars()
	 * @return	object		self
	 * @see		TSref => typolink => parameter
	 * @see		tslib_cObj::typoLink()
	 */
	function destination($destination) {
		$this->destination = $destination;
		return $this;
	}

	/**
	 * Add no_cache=1 and disable the cHash parameter
	 *
	 * @param	boolean		if true don't make a cHash, set no_cache=1
	 * @return	object		self
	 */
	function noCache() {
		$this->noCacheBoolean = true;
		return $this;
	}

	/**
	 * Disable the cHash parameter
	 *
	 * @param	boolean		if true don't make a cHash
	 * @return	object		self
	 */
	function noHash() {
		$this->noHashBoolean = true;
		return $this;
	}

	/**
	 * Set the links label
	 *
	 * By default the label will be parsed through htmlspecialchars().
	 *
	 * @param	string		the label
	 * @param	boolean		if true don't parse through htmlspecialchars()
	 * @return	object		self
	 */
	function label($labelString, $hasAlreadyHtmlSpecialChars = false) {
		$this->labelString = $labelString;
		$this->labelHasAlreadyHtmlSpecialChars = $hasAlreadyHtmlSpecialChars;
		return $this;
	}

	/**
	 * Set array of parameters to be overruled by parameters
	 *
	 * The parameters will create a common array with the name $this->designatorString.
	 * <samp>Example: &tx_example[parameterName]=parameterValue</samp>
	 * tx_example is the designator, parameterName is the key,
	 * pararmeterValue is the value of one array element.
	 *
	 * Usually you set the incomming piVars here you wan't to forward.
	 * Like in tslib_pibase::pi_linkTP_keepPIvars the element DATA is unset during processing.
	 *
	 * @param	mixed		parameters
	 * @return	object		self
	 */
	function overruled($overruledParameters = array()) {
		if(is_object($overruledParameters)) {
			$overruledParameters = $overruledParameters->getArrayCopy();
		}
		$this->overruledParameters = $overruledParameters;
		return $this;
	}

	/**
	 * Set array of new parameters to add to the link url
	 *
	 * The parameters will create a common array with the name $this->designatorString.
	 * <samp>Example: &tx_example[parameterName]=parameterValue</samp>
	 * tx_example is the designator, parameterName is the key,
	 * pararmeterValue is the value of one array element.
	 *
	 * This parameters overrule parameters in $this->baseParameters.
	 *
	 * @param	mixed		parameters
	 * @return	object		self
	 */
	function parameters($parameters = array()) {
		if(is_object($parameters)) {
			$parameters = $parameters->getArrayCopy();
		}
		$this->parameters = $parameters;
		return $this;
	}

	/**
	 * Set the attributes of the tag
	 *
	 * This is a general approach to set tag attributes by an array hash.
	 *
	 * @see	classAttribute()
	 * @see	titleAttribute()
	 * @see	targetAttribute()
	 *
	 * @param	array		key value pairs
	 * @return	object		self
	 */
	function attributes($tagAttributes = array()) {
		$this->tagAttributes = $tagAttributes;
		return $this;
	}

	/**
	 * Set target attribute of the tag
	 * A shortcut for the targetAttribute() function.
	 *
	 * @see	targetAttribute()
	 *
	 * @param	string		target attribute
	 * @return	object		self
	 */
	function target($targetString) {
		$this->targetAttribute($targetString);
		return $this;
	}

	/**
	 * Set target attribute of the tag
	 *
	 * @param	string		target attribute
	 * @return	object		self
	 */
	function targetAttribute($targetString) {
		$this->targetString = $targetString;
		return $this;
	}

	/**
	 * Set external target attribute of the tag
	 * Defaults to _blank
	 *
	 * @param	string		external target attribute
	 * @return	object		self
	 */
	function externalTargetAttribute($targetString) {
		$this->externalTargetString = $targetString;
		return $this;
	}

	/**
	 * Set title attribute of the tag
	 * A shortcut for the titleAttribute() function.
	 *
	 * @see	titleAttribute()
	 *
	 * @param	string		title attribute
	 * @param	boolean		if true don't apply htmlspecialchars() again
	 * @return	object		self
	 */
	function title($titleString, $hasAlreadyHtmlSpecialChars = false) {
		$this->titleAttribute($titleString, $hasAlreadyHtmlSpecialChars);
		return $this;
	}

	/**
	 * Set title attribute of the tag
	 *
	 * @param	string		title attribute
	 * @param	boolean		if true don't apply htmlspecialchars() again
	 * @return	object		self
	 */
	function titleAttribute($titleString, $hasAlreadyHtmlSpecialChars = false) {
		$this->titleString = $titleString;
		$this->titleHasAlreadyHtmlSpecialChars = $hasAlreadyHtmlSpecialChars;
		return $this;
	}

	// -------------------------------------------------------------------------------------
	// Getters
	// -------------------------------------------------------------------------------------

	/**
	 * Return the link as tag
	 *
	 * @return	string		the link tag
	 */
	function makeTag() {
		return $this->cObject->typolink(
			$this->_makeLabel(),
			$this->_makeConfig('tag')
		);
	}

	/**
	 * Return the link as url
	 *
	 * @param	boolean		set to true to run htmlspecialchars() on generated url
	 * @return	string		the link url
	 */
	function makeUrl($applyHtmlspecialchars = TRUE) {
		$url = $this->cObject->typolink(null, $this->_makeConfig('url'));
		return $applyHtmlspecialchars ? htmlspecialchars($url) : $url;
	}

	/**
	 * Redirect the page to the url
	 *
	 * @return	void
	 */
	function redirect() {
		session_write_close();
		header('Location: ' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') 
			. $this->cObject->typolink(null, $this->_makeConfig('url')));
		exit();
	}

	// -------------------------------------------------------------------------------------
	// Private functions
	// -------------------------------------------------------------------------------------

	/**
	 * Make the full configuration for the typolink function
	 *
	 * @param	string		$type: tag oder url
	 * @return	array		the configuration
	 * @access	private
	 */
	function _makeConfig($type) {
		$conf = Array();
		$this->parameters = is_array($this->parameters) ?
			$this->parameters : array();
		$this->overruledParameters = is_array($this->overruledParameters) ?
			$this->overruledParameters : array();
		unset($this->overruledParameters['DATA']);
		$parameters
			= t3lib_div::array_merge_recursive_overrule($this->overruledParameters,
					$this->parameters);
		foreach((array) $parameters as $key => $value) {
			$conf['additionalParams'] .= $this->makeUrlParam($key, $value);
		}
		if($this->noHashBoolean ) {
			$conf['useCacheHash'] = 0;
		} else {
			$conf['useCacheHash'] = 1;
		}
		if($this->noCacheBoolean) {
			$conf['no_cache'] = 1;
			$conf['useCacheHash'] = 0;
		} else {
			$conf['no_cache'] = 0;
		}
		if($this->destination !== '')
			$conf['parameter'] = $this->destination;
		if($type == 'url') {
			$conf['returnLast'] = 'url';
		}
		if($this->anchorString) {
			$conf['section'] = $this->anchorString;
		}
		if($this->targetString) {
			$conf['target'] = $this->targetString;
		}
		if($this->externalTargetString) {
			$conf['extTarget'] = $this->externalTargetString;
		}
		if($this->classString) {
			$conf['ATagParams'] .= 'class="' . $this->classString . '" ';
		}
		if($this->idString) {
			$conf['ATagParams'] .= 'id="' . $this->idString . '" ';
		}
		if($this->titleString) {
			$title = ($this->titleHasAlreadyHtmlSpecialChars) ? $this->titleString
				: htmlspecialchars($this->titleString);
			$conf['ATagParams'] .= 'title="' . $title . '" ';
		}
		if(is_array($this->tagAttributes)
				&& (count($this->tagAttributes) > 0)) {
			foreach($this->tagAttributes as $key => $value) {
				$conf['ATagParams'] .= ' ' .  $key . '="' . htmlspecialchars($value) . '" ';
			}
		}
		return $conf;
	}

	/**
	 * Generates an additional parameter.
	 * Examples:
	 * $key='param'; $value='123' => &qualifier[param]=123
	 * $key='ttnews::param'; $value='123' => &ttnews[param]=123
	 * $key='::param'; $value='123' => &param=123
	 *  
	 * @param string $key
	 * @param string $value
	 * @return string
	 */
	private function makeUrlParam($key, $value) {
		$ret = '';
		$qualifier = $this->designatorString;
		if(!is_array($value)) {
			$paramName = $key;
			if(strstr($key, '::')) {
				$arr = t3lib_div::trimExplode('::', $key);
				$qualifier = $arr[0];
				$paramName = $arr[1];
			}

			if($qualifier) {
				$ret .= '&' . rawurlencode( $qualifier . '[' . $paramName . ']') . '=' . rawurlencode($value);
			} else {
				$ret .= '&' . rawurlencode($paramName) . '=' . rawurlencode($value);
			}
		}
		else {
			if($qualifier) {
				foreach($value As $arKey => $aValue) {
					$ret .= '&' . rawurlencode( $qualifier . '[' . $key . ']['.$arKey.']') . '=' . rawurlencode($aValue);
				}
			} else {
				foreach($value As $arKey => $aValue) {
					$ret .= '&' . rawurlencode($key) . '[]=' . rawurlencode($aValue);
				}
			}
		}
		return $ret;
	}

	/**
	 * Make the label for the link
	 *
	 * @return	string		the label
	 * @access	private
	 */
	function _makeLabel() {
		return ($this->labelHasAlreadyHtmlSpecialChars) ? $this->labelString
			: htmlspecialchars($this->labelString);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Link.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Link.php']);
}

?>
