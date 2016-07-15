<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2012 Rene Nitzsche
 *  Contact: rene@system25.de
 *
 *  Original version:
 *  (c) 2006 Elmar Hinz
 *  Contact: elmar.hinz@team-red.net
 *
 *  All rights reserved
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

/***************************************************************
 *
 * This is a refactored version of class tx_lib_configurations.
 * What has been changed?
 * - there is no parent class anymore to reduce complexity
 * - all data is stored at instance variable _dataStore
 * - this class is the dataholder of any cObj in the plugin
 * - provides an instance of formatter class
 * - provides a dataholder named $_viewdata used to transfer any
 *   data from controller to view
 * Configuration is the meant to be a central point for all parts
 * of MVC Pattern.
 * The API of this class has not been changed. So it can be used,
 * as always.
 ***************************************************************/

/**
 * Object to load, hold, transport and deliver all or some TypoScript-Setup of an extension.
 *
 * It is very usefull to store all configuration of an extension into this object and to
 * access it easily in any place you need it all over your extension. Usefull functions are already provided.
 * More will be added by time. You also may provide your own functions in inherited classes.
 *
 * Configuration can be loaded into this object by 3 different functions, typically in the following order:
 *
 * 1.) loadTypoScript():          loads TS down from a given node of the TS tree into the object.
 * 2.) loadConfiguration():       loads an already redered TS array, typically from local configuration
 * 3.) loadFlexForm():            loads flexform data (xml or array) into the object
 *
 * Details:
 *
 * 1.) TS directly from a setup path: example 'plugin.tx_myextension.configuration.'. Please mind that there is
 *     a DOT at the end of the path, because it has subelements. You may set the setupPath in a class variable
 *     of a derived class or alternatively provide it as parameter.
 *
 * 2.) An configuration array of rendered TypoScript like it is handeled to the main function of plugins by the
 *     outer framework: tx_myextension_controller->main($out, $configurationArray). This should be called after 1.)
 *     to give the possibility to overwrite global settings.
 *
 * 3.) The last load typically comes from the flexform to give the enduser the possibilty for finegrained selections.
 *
 * @author Elmar Hinz <elmar.hinz@team-red.net>
 * @package TYPO3
 * @subpackage tx_rnbase
 *
 */
tx_rnbase::load('tx_rnbase_util_Network');
tx_rnbase::load('tx_rnbase_util_Typo3Classes');
tx_rnbase::load('tx_rnbase_util_Arrays');

class tx_rnbase_configurations {
	/**
	 * Here, all configuration data is stored.
	 *
	 * @var ArrayObject
	 */
	private $_dataStore = null;

	/**
	 * This is a container to transfer data to the view.
	 *
	 * @var ArrayObject
	 */
	private $_viewData = null;

	/**
	 * @var tx_rnbase_IParameters
	 */
	private $_parameters = null;

	/**
	 * This is a container for variables necessary in links.
	 *
	 * @var ArrayObject
	 */
	private $_keepVars = null;

	/**
	 * @var string
	 */
	private $_qualifier = '';

	/**
	 * die UID des Plugins (also des Content-Objekts)
	 *
	 * @var int
	 */
	private $pluginUid = 0;

	/**
	 * das originale cObj des Plugins
	 *
	 * @var tslib_cObj|\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 * @internal DO NOT USE THIS DIRECTLY! Use method getCObj() instead.
	 */
	public $cObj = null;

	/**
	 * Container für alternative cObjs innerhalb des Plugins
	 * @var tslib_cObj[]|\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer[]
	 */
	private $cObjs = array();

	/**
	 * @todo Unused?
	 *
	 * language to use
	 *
	 * @var string
	 */
	public $LLkey = 'default';

	/**
	 * @todo Unused?
	 *
	 * alternative language to use
	 *
	 * @var string
	 */
	public $altLLkey='';

	/**
	 * @var string
	 */
	private $_extensionKey = '';

	/**
	 * @var int[]
	 */
	private static $libIds = array();

	/**
	 * Set this in the derived class or give the setupPath to the loadTypoScript method.
	 *
	 * @var string
	 */
	protected $setupPath = '';

	/**
	 * Util used to load and retrieve local lang labels
	 *
	 * @var tx_rnbase_util_Lang
	 */
	private $localLangUtil = null;

	/**
	 * @var tx_rnbase_util_FormatUtil
	 */
	private $_formatter = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->_dataStore = new ArrayObject();
		$this->_viewData = new ArrayObject();
		$this->_keepVars = new ArrayObject();
		$this->cObjs = array();
		$this->localLangUtil = tx_rnbase::makeInstance('tx_rnbase_util_Lang');
	}
	/**
	 * Initialize this instance with Configuration Array and cObj-Data
	 *
	 * A note to extensionKey and qualifier: both values should be set by plugins Typoscript-setup:
	 *
	 * plugin.tx_cfcleaguefe_competition {
	 *   qualifier      = t3sports
	 *   extensionKey   = cfc_league_fe
	 * }
	 * extensionKey is not used right now. The qualifier is used as prefix for plugin parameters
	 * like this: t3sports[param1]=value
	 *
	 * @param array $configurationArray the typoscript configuration array given from TYPO3
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj
	 * @param string $extensionKey the extension key for TYPO3
	 * @param string $qualifier the prefix string for plugin parameters.
	 */
	public function init(&$configurationArray, $cObj, $extensionKey, $qualifier) {
		// keep the cObj
		if(is_object($cObj)) {
			$this->cObj = $cObj;
			$this->cObjs[0] = $this->cObj;
			$this->pluginUid = is_object($cObj) ? $this->createPluginId() : 0;

			// make the data of the cObj available
			$this->_setCObjectData($cObj->data);
		}

		// If configurationArray['setupPath'] is provided it will be used by tx_rnbase_configurations or subclass.
		// if configurationArray['setupPath'] is empty the subclass will use it's internally defined setupPath.
		$this->_setTypoScript($configurationArray['setupPath']);

		// Add the local configuration, overwriting TS setup
		$this->_setConfiguration($configurationArray);

		if(is_object($cObj)) {
			// Flexformvalues have the maximal precedence
			$this->_setFlexForm($cObj->data['pi_flexform']);
		}

		// A qualifier and extkey from TS are preferred
		$this->_extensionKey = $this->get('extensionKey') ? $this->get('extensionKey') : $extensionKey;
		$this->_qualifier = $this->get('qualifier') ? $this->get('qualifier') : $qualifier;

		$this->_formatter = tx_rnbase::makeInstance('tx_rnbase_util_FormatUtil', $this);

		$this->loadLL();
	}

	// -------------------------------------------------------------------------------------
	// Getters
	// -------------------------------------------------------------------------------------

	/**
	 * Returns the UID of the current content object in tt_content.
	 *
	 * @return int
	 */
	public function getPluginId() {
		return $this->pluginUid;
	}

	/**
	 * Converts an USER to USER_INT, so the cache can be disabled in the action!
	 *
	 * If this method is called, the Skip Exception can be thrown.
	 * The controller is called twice,
	 * whenn a USER Object is convertet to USER_INTERNAL.
	 * The SkipAction avoids this!
	 *
	 * @param bool $convert
	 *
	 * @return bool|void
	 *
	 * @throws tx_rnbase_exception_Skip
	 */
	public function convertToUserInt($convert = TRUE)
	{
		// set this only, if we are not an USER_INTERNAL
		if ($convert && $this->isPluginUserInt()) {
			return;
		}
		$this->getCObj()->doConvertToUserIntObject = $convert;
		if ($convert) {
			throw tx_rnbase::makeInstance('tx_rnbase_exception_Skip');
		}
		return TRUE;
	}

	/**
	 * Whether or not the current plugin is executed as USER_INT
	 *
	 * @return bool
	 */
	public function isPluginUserInt() {
		$contentObjectRendererClass = tx_rnbase_util_Typo3Classes::getContentObjectRendererClass();
		return $this->getCObj()->getUserObjectType() == $contentObjectRendererClass::OBJECTTYPE_USER_INT;
	}

	/**
	 * Whether or not the plugins uses its own parameters. This will add the plugin id to all
	 * parameters of the given plugin.
	 *
	 * @return bool
	 */
	public function isUniqueParameters() {
		return $this->getBool('uniqueParameters') == TRUE;
	}
	/**
	 * Returns a unique ID for this plugin.
	 *
	 * @return int
	 */
	private function createPluginId() {
		$id = 0;
		if (is_array($this->cObj->data)) {
			$id = $this->cObj->data['uid'];
			if(array_key_exists('doktype', $this->cObj->data)) {
				// Es handelt sich um ein Plugin, daß per TS eingebunden wurde. In data steht der
				// Record der Seite.
				$base = array_key_exists($id, self::$libIds) ? (intval(self::$libIds[$id])+1) : 1;
				self::$libIds[$id] = $base;
				$id = (100000 + $id) * $base;
			}
		}
		return $id;
	}

  /**
   * Create your individuell instance of cObj. For each id only one instance is created.
   * If id == 0 the will get the plugins original cOBj.
   *
   * @param int $id any
   * @param string|null $cObjClass String Optional cObj-classname
   *
   * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer|tslib_cObj
   */
	public function &getCObj($id = 0, $cObjClass = NULL) {
		$cObjClass = $cObjClass === NULL ? tx_rnbase_util_Typo3Classes::getContentObjectRendererClass() : $cObjClass;
		if(strcmp($id, '0') == 0) {
			if(!is_object($this->cObj)) {
				$this->cObj = tx_rnbase::makeInstance($cObjClass);
				$this->cObjs[0] = $this->cObj;
			}
			return $this->cObj;
		}

		$cObj = $this->cObjs[$id];

		if(!is_object($cObj)) {
			$this->cObjs[$id] = tx_rnbase::makeInstance($cObjClass);
		}

		return $this->cObjs[$id];
	}

	/**
	 * Returns the formatter connected to this configuration object.
	 *
	 * @return tx_rnbase_util_FormatUtil
	 */
	public function &getFormatter() {
		return $this->_formatter;
	}

  /**
   * Return the data container for view by reference. This container should be filled
   * by Controller-Action
   *
   * @return ArrayObject
   */
  public function &getViewData() {
    return $this->_viewData;
  }

  /**
   * Returns the defined path to template directory. This is by default
   * 'EXT:your_extension/templates/'. You can change this by TS setting templatePath
   *
   * @return string
   */
  public function getTemplatePath() {
    $path = $this->get('templatePath');
    return $path ? $path : 'EXT:' . $this->getExtensionKey() . '/views/templates/';
  }

	/**
	 * Factory-Method for links. The new link is initialized with qualifier and optional
	 * with keepVars set.
	 *
	 * @param bool $addKeepVars whether or not keepVars should be set
	 *
	 * @return tx_rnbase_util_Link
	 */
	public function &createLink($addKeepVars = TRUE) {
		/* @var $link tx_rnbase_util_Link */
		$link = tx_rnbase::makeInstance('tx_rnbase_util_Link', $this->getCObj());
		$link->designatorString = $this->getQualifier();
		// Die KeepVars setzen
		if($addKeepVars)
			$link->overruled($this->getKeepVars());
		if($this->isUniqueParameters())
			$link->setUniqueParameterId($this->getPluginId());
		return $link;
	}

	/**
	 * @param tx_rnbase_IParameters $parameters
	 *
	 * @return void
	 */
	public function setParameters($parameters) {
		$this->_parameters = $parameters;
		// Make sure to keep all parameters
		$this->setKeepVars($parameters);
	}
	/**
	 * Returns request parameters
	 *
	 * @return tx_rnbase_IParameters
	 */
	public function getParameters() {
		return $this->_parameters;
	}

	/**
	 * Returns the KeepVars-Array
	 *
	 * @return ArrayObject
	 */
	public function getKeepVars() {
		return $this->_keepVars;
	}

	/**
	 * Set an ArrayObject with variables to keep between requests
	 *
	 * @param tx_rnbase_IParameters $keepVars
	 *
	 * @return void
	 */
	public function setKeepVars($keepVars) {
		$arr = $keepVars->getArrayCopy();

		foreach( $arr As $key => $value) {
			if(strpos($key, 'NK_') === FALSE)
				$this->_keepVars->offsetSet($key, $value);
		}
	}

	/**
	 * Add a value that must be kept by parameters
	 *
	 * @param mixed $name
	 * @param mixed $value
	 *
	 * @return void
	 */
  public function addKeepVar($name, $value) {
    $this->_keepVars->offsetSet($name, $value);
  }

	/**
	 * Removes a value that must be kept by parameters.
	 *
	 * @param mixed $name
	 *
	 * @return void
	 */
  public function removeKeepVar($name) {
    $this->_keepVars->offsetUnset($name);
  }

	/**
	 * @param string $name
	 *
	 * @return string
	 */
  public function createParamName($name) {
    return $this->getQualifier().'[' . $name . ']';
  }

  /**
   * Returns the extension key.
   *
   * @return string
   */
	public function getExtensionKey() {
    return $this->_extensionKey;
  }
  /**
   * Returns the qualifier for plugin links: qualifier[param]=value.
   *
   * @return string
   */
	public function getQualifier() {
    return $this->_qualifier;
  }

  /**
   * Returns the flexform data of this plugin as array
   *
   * @return array by reference
   */
	public function &getFlexFormArray() {
    static $flex;
    if (!is_array($flex)) {
      $flex = tx_rnbase_util_Network::getURL(tx_rnbase_util_Extensions::extPath($this->getExtensionKey()) . $this->get('flexform'));
      $flex = tx_rnbase_util_Arrays::xml2array($flex);
    }
    return $flex;
  }

	/**
	 * Returns the localized label of the LOCAL_LANG key.
	 * This is a reimplementation from tslib_pibase::pi_getLL().
	 *
	 * @param string $key
	 * @param string $alt
	 * @param bool $hsc
	 *
	 * @return string
	 */
	public function getLL($key, $alt='', $hsc=FALSE) {
		return $this->localLangUtil->getLL(
			$key, $alt, $hsc,
			tx_rnbase_util_Debug::isLabelDebugEnabled($this)
		);
	}


  /**
   * Returns a value from extension configuration.
   * Can be called static
   *
   * @param string $extKey
   * @param string $cfgKey
   *
   * @return mixed
   */
  public static function getExtensionCfgValue($extKey, $cfgKey) {
    $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
    return (is_array($extConfig) && array_key_exists($cfgKey, $extConfig)) ? $extConfig[$cfgKey] : FALSE;
  }

	/**
	 * Get a value or an array by providing a relative pathKey
	 *
	 * The provided pathKey is relative to the part of the TS-Setup you have loaded. Examples:
	 *
	 * Absolute Setup:          'plugin.tx_myextension.configuration.parts.10.id = 33'
	 * Loaded Path:             'plugin.tx_myextension.configuration.'
	 * Resulting relative pathKey of a value:                       'parts.10.id'
	 * Resulting relative pathKey of an array:                      'parts.'
	 * Resulting relative pathKey of an array:                      'parts.10.'
	 *
	 * Mind: To query an array end with a DOT. To to query a single value end without DOT.
	 *
	 * @param string             $pathKey relative setupPath
	 * @param bool               $deep
	 *
	 * @return array|string|null
	 */
	public function get($pathKey, $deep=FALSE) {
		if(!$deep)
			return $this->_queryArrayByPath($this->_dataStore->getArrayCopy(), $pathKey);

		// Wenn am Ende kein Punkt steht, ist das Ergebnis ein String
		// deep ist nur dann sinnvoll, wenn ohne Punkt am Ende gefragt wird.
		$ret = $this->_queryArrayByPath($this->_dataStore->getArrayCopy(), $pathKey);
		$noEndingDot = substr($pathKey, strlen($pathKey)-1, 1) != '.';
		if (!is_array($ret) && $noEndingDot) {
			$arr = $this->_queryArrayByPath($this->_dataStore->getArrayCopy(), $pathKey.'.');
			if (is_array($arr)){
				$ret = array('key' => $ret, 'key.' => $arr);
			}
		}
		if (is_array($ret)) {
			$ret = $this->renderTS($ret, $this->getCObj());
			$ret = $noEndingDot ? $ret['key'] : $ret;
		}
		return $ret;
	}
	/**
	 * Returns a boolean config value. The return value is FALSE if the value is empty or 0 or 'FALSE'
	 * @param string $pathKey
	 * @param bool $deep
	 * @param bool $notDefined value to return if no value configured or empty
	 *
	 * @return bool
	 */
	public function getBool($pathKey, $deep=FALSE, $notDefined=FALSE) {
		$value = $this->get($pathKey, $deep);
		if (is_array($value)) {
			return TRUE;
		}
		if ($value == '') {
			return $notDefined;
		}

		return (!$value || strtolower($value) == 'false') ? FALSE : TRUE;
	}
	/**
	 * Returns a int config value.
	 *
	 * @param string $pathKey
	 * @param bool $deep
	 *
	 * @return int
	 */
	public function getInt($pathKey, $deep=FALSE) {
		return intval( $this->get($pathKey, $deep));
	}

	/**
	 * Returns the complete TS config array
	 *
	 * @return array
	 */
	public function getConfigArray() {
		return $this->_dataStore->getArrayCopy();
	}

	/**
	 * Finds a value either from config or in language markers. Please note, that all points are
	 * replaced by underscores for language strings. This is, because TYPO3 doesn't like point
	 * notations for language keys.
	 *
	 * @param string $pathKey
	 *
	 * @return mixed but should be a string
	 */
	public function getCfgOrLL($pathKey) {
		$ret = $this->_queryArrayByPath($this->_dataStore->getArrayCopy(), $pathKey);
		if(!$ret) {
			$pathKey = strtr($pathKey, '.', '_');
			$ret = $this->getLL($pathKey);
		}

		return $ret;
	}


	/**
	 * Get a exploded value
	 *
	 * @param string $pathKey
	 * @param string $delim
	 * @param bool $deep
	 *
	 * @return array
	 */
	public function getExploded($pathKey, $delim = ',', $deep = FALSE) {
		$value = $this->get($pathKey, $deep);
		if (is_array($value)) {
			return $value;
		}
		if (empty($value)) {
			return array();
		}

		return tx_rnbase_util_Strings::trimExplode($delim, $value, TRUE);
	}

	/**
	 * Query a uniform hash from a dataset like setup
	 *
	 * persons.10.id = 103
	 * persons.10.firstName = Peter
	 * persons.10.surName = Posters
	 * persons.10.yearOfBirth = 1973
	 * persons.20.id = 104
	 * persons.20.firstName = Susan
	 * persons.20.surName = Sunny
	 * persons.20.yearOfBirth = 1965
	 * persons.30.id = 105
	 * persons.30.firstName = Mary
	 * persons.30.surName = Martins
	 * persons.30.yearOfBirth = 1989
	 *
	 * usage: $configurations->queryHash('persons.', 'firstName', 'yearOfBirth');
	 * result: array('Peter' => '1973', 'Susan' => '1965', 'Mary' => '1989');
	 *
	 * @param string $pathKey relative pathKey
	 * @param string $keyName key of of the wanted key
	 * @param string $valueName key of of the wanted value
	 *
	 * @return array  wanted Hash (key-value-pairs)
	 */
	public function queryHash($pathKey, $keyName, $valueName) {
		$selection = $this->_dataStore->get($pathKey);
		$array = array();
		foreach($selection as $set) {
			$array[$set[$keyName]] = $set[$valueName];
		}
		return $array;
	}

	/**
	 * Query a single dataset from a list of datasets by a key entry
	 *
	 * persons.10.id = 103
	 * persons.10.firstName = Peter
	 * persons.10.surName = Posters
	 * persons.10.yearOfBirth = 1973
	 * persons.20.id = 104
	 * persons.20.firstName = Susan
	 * persons.20.surName = Sunny
	 * persons.20.yearOfBirth = 1965
	 * persons.30.id = 105
	 * persons.30.firstName = Mary
	 * persons.30.surName = Martins
	 * persons.30.yearOfBirth = 1989
	 *
	 * usage: $configurations->queryDataSet('persons.', 'id', 104);
	 * result: array('id' => '104', 'firstName' => 'Susan', 'surName' => 'Sunny', 'yearOfBirth' => '1965')
	 *
	 * @param string  relative pathKey
	 * @param string  key of key
	 * @param string  value of key
	 * @return array  wanted dataset
	 */
	function queryDataSet($path, $key, $value) {
		$selection = $this->_dataStore->get($path);
		foreach($selection as $set) {
			if ($set[$key] == $value) {
				return $set;
			}
		}
	}

	/**
	 * Query a single data from a list of datasets by a combination of key entries
	 *
	 * persons.10.id = 103
	 * persons.10.firstName = Peter
	 * persons.10.surName = Posters
	 * persons.10.yearOfBirth = 1973
	 * persons.20.id = 104
	 * persons.20.firstName = Susan
	 * persons.20.surName = Sunny
	 * persons.20.yearOfBirth = 1965
	 * persons.30.id = 105
	 * persons.30.firstName = Mary
	 * persons.30.surName = Martins
	 * persons.30.yearOfBirth = 1989
	 *
	 * usage: $configurations->queryData('persons.', 'id', 104, 'yearOfBirth');
	 * result: '1965'
	 *
	 * @param string  relative pathKey
	 * @param string  key of key
	 * @param string  value of key
	 * @param string  key of the wanted result value
	 * @return string  wanted value
	 */
	function queryData($path, $key, $value, $wanted) {
		$selection = $this->_dataStore->get($path);
		foreach($selection as $set) {
			if ($set[$key] == $value) {
				return $offsetSet[$wanted];
			}
		}
	}

	/**
	 * Returns all keynames below a config branch. Any trailing points will be removed.
	 *
	 * @param string $confId
	 * @return array of strings or empty array
	 */
	public function getKeyNames($confId)
	{
		$dynaMarkers = $this->get($confId);
		if (!is_array($dynaMarkers)) {
			return array();
		}

		return $this->getUniqueKeysNames($dynaMarkers);
	}

	/**
	 * Returns all keynames below a config branch. Any trailing points will be removed.
	 *
	 * @param array $conf configuration array
	 * @return array
	 */
	public function getUniqueKeysNames(array $conf)
	{
		$keys = array();

		$dynaMarkers = array_keys($conf);
		if (empty($dynaMarkers)) {
			return $keys;
		}

		// Jetzt evt. vorhandene Punkt am Ende entfernen
		foreach ($dynaMarkers as $dynaMarker) {
			$keys[] = preg_replace('/\./', '', $dynaMarker);
		}

		return array_values(array_unique($keys));
	}

	// -------------------------------------------------------------------------------------
	// Private functions
	// -------------------------------------------------------------------------------------

  /**
   *
   */
  function _setCObjectData($data) {
    $this->_dataStore->offsetSet('tt_content.', $data);
  }

	/**
	 * Load a (local) configuration array into the object
	 *
	 * An configuration array of rendered TypoScript like it is handeled to the main function
	 * of plugins by the outer framework: tx_myextension_controller->main($out, $configurationArray).
	 *
	 * @param array  direct setup input in form of a renderd TS array
	 * @return void
	 */
	function _setConfiguration($configuration) {
		foreach((array)$configuration as $key => $value) {
			$this->_dataStore->offsetSet($key, $value);
		}
	}

	/**
	 * Load TypoScript Setup for an extension
	 *
	 * Loads TS form the TS tree down from a node that you define by the $setupPath.
	 * If no parameter is provided the setupPath is taken from a class variable of the same name.
	 *
	 * @param string  setup path from TS, example: 'plugin.tx_myextension.configuration.'
	 * @return void
	 */
	function _setTypoScript($setupPath = '') {
		$setupPath = $setupPath ? $setupPath : $this->setupPath;
		if($setupPath){
			$array = $this->_queryArrayByPath($GLOBALS['TSFE']->tmpl->setup, $setupPath);
			foreach((array)$array as $key => $value) {
				$this->_dataStore->offsetSet($key, $value);
			}
		}
	}

	function insertIntoDataArray($dataArr, $pathArray, $newValue) {
		// Cancel Recursion on value level
		if(count($pathArray) == 1) {
			$dataArr[$pathArray[0]] = $newValue;
			return $dataArr;
		}
		$ret = array();
		if(!$dataArr)
			$dataArr = array($pathArray[0] . '.' => '');
		if(!array_key_exists($pathArray[0] . '.', $dataArr))
			$dataArr[$pathArray[0] . '.'] = '';
		foreach($dataArr As $key => $value) {
			if($key == $pathArray[0] . '.') {
				// Go deeper
				$ret[$key] = $this->insertIntoDataArray($value, array_slice($pathArray, 1), $newValue);
			}
			else {
				$ret[$key] = $value;
			}
		}
		return $ret;
	}
	/**
	 * Load flexformdata into the object
	 *
	 * Takes a xml string or an already rendered array.
	 * Typically it would come from the field tt_content.pi_flexform
	 *
	 * This configuration assumes unique key names for the fields.
	 * The names of the sheets are of no relevance.
	 * If you need a more sophisticated solution simply write a your
	 * own loader function in an inherited class.
	 *
	 * @param mixed  xml or rendered flexform array
	 * @return void
	 */
	function _setFlexForm($xmlOrArray) {
		$languagePointer = 'lDEF'; // we don't support languages here for now
		$valuePointer = 'vDEF';
		// also hardcoded here
		if (!$xmlOrArray) {
			return FALSE;
		}
		// Converting flexform data into array if neccessary
		if (is_array($xmlOrArray)) {
			$array = $xmlOrArray;
		} else {
			$array = tx_rnbase_util_Arrays::xml2array($xmlOrArray);
		}
		$data = $array['data'];
		// Looking for the special sheet s_tssetup
		$flexTs = FALSE;
		if(isset($data['s_tssetup'])) {
			$flexTs = $data['s_tssetup']['lDEF']['flexformTS']['vDEF'];
			unset($data['s_tssetup']);
		}
		foreach((array) $data as $sheet => $languages) {
			foreach((array) $languages[$languagePointer] as $key => $def) {
				// Wir nehmen Flexformwerte nur, wenn sie sinnvolle Daten enthalten
				// Sonst werden evt. vorhandenen Daten überschrieben
				if(!(strlen($def[$valuePointer]) == 0 )) { // || $def[$valuePointer] == '0')
					$pathArray = explode('.', trim($key));
					if(count($pathArray) > 1) {
						// Die Angabe im Flexform ist in Punktnotation
						// Wir holen das Array im höchsten Knoten
						$dataArr = $this->_dataStore->offsetGet($pathArray[0] . '.');
						$newValue = $def[$valuePointer];
						$newArr = $this->insertIntoDataArray($dataArr, array_slice($pathArray, 1), $newValue);
						$this->_dataStore->offsetSet($pathArray[0] . '.', $newArr);
					}
					else {
						$this->_dataStore->offsetSet($key, $def[$valuePointer]);
					}
				}
			}
		}
		if($flexTs) {
			// This handles ts setup from flexform
			$tsParser = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getTypoScriptParserClass());
			$tsParser->setup = $this->_dataStore->getArrayCopy();
			$tsParser->parse($flexTs);
			$flexTsData = $tsParser->setup;
			$this->_dataStore->exchangeArray($flexTsData);
		}
	}

	private function mergeTSReference($value, $conf) {
		if (substr($value, 0, 1) != '<')	{
			return $conf;
		}

		// das < abschneiden, um den pfad zum link zu erhalten
		$key = trim(substr($value, 1));

		$tsParser = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getTypoScriptParserClass());

		// $name and $conf is loaded with the referenced values.
		list($linkValue, $linkConf) = $tsParser->getVal($key, $GLOBALS['TSFE']->tmpl->setup);

		// Konfigurationen mergen
		if (is_array($conf) && count($conf))	{
			$linkConf = self::joinTSarrays($linkConf, $conf);
		}

		// auf rekursion hin prüfen
		$linkConf = $this->mergeTSReference($linkValue, $linkConf);

		return $linkConf;
	}

	/**
	 * Merges two TypoScript propery array, overlaing the $old_conf onto the $conf array
	 *
	 * @param	array		TypoScript property array, the "base"
	 * @param	array		TypoScript property array, the "overlay"
	 * @return	array		The resulting array
	 * @see mergeTSRef(), tx_tstemplatestyler_modfunc1::joinTSarrays()
	 */
	static function joinTSarrays($conf, $old_conf)	{
		if (is_array($old_conf))	{
			reset($old_conf);
			while(list($key, $val)=each($old_conf))	{
				if (is_array($val))	{
					$conf[$key] = self::joinTSarrays($conf[$key], $val);
				} else {
					$conf[$key] = $val;
				}
			}
		}
		return $conf;
	}

	/**
	 * @param array $array
	 * @param string $path
	 *
	 * @return array
     */
	function _queryArrayByPath($array, $path) {
		$pathArray = explode('.', trim($path));
		for($i = 0, $cnt = count($pathArray); $i < $cnt; $i++) {
			if ($i < ($cnt -1 )) {
				// Noch nicht beendet. Auf Reference prüfen
				$array = $this->mergeTSReference(
					$array[$pathArray[$i]],
					$array[$pathArray[$i] . '.']
				);
			} elseif(empty($pathArray[$i])) {
				// It ends with a dot. We return the rest of the array
				return $array;
			} else {
				// It endes without a dot. We return the value.
				return $array[$pathArray[$i]];
			}
		}
	}

	/**
	 * Loads local language file for frontend rendering if defined in configuration.
	 * Also locallang values from TypoScript property "_LOCAL_LANG" are merged onto the
	 * values. This is a reimplementation from tslib_pibase::pi_loadLL()
	 */
	private function loadLL() {
		$this->localLangUtil->loadLLFile($this->get('locallangFilename'));

		// Overlaying labels from additional locallangs are minor prior
		// we support comma separated lists and arrays
		$locallangOverlays = (array) $this->get('locallangFilename.');
		if(array_key_exists('_cfg.', $locallangOverlays)) {
			unset($locallangOverlays['_cfg.']);
			if($this->getBool('locallangFilename._cfg.naturalOrder')) {
				ksort($locallangOverlays);
			}
		}

		if(!empty($locallangOverlays)){
			foreach ($locallangOverlays as $locallangOverlayFilename) {
				$this->localLangUtil->loadLLFile($locallangOverlayFilename);
			}
		}
		// Overlaying labels from TypoScript are higher prior (including fictitious language keys for non-system languages!):
		$this->localLangUtil->loadLLTs($this->get('_LOCAL_LANG.'));
	}

	/**
	 * (Try to) Render Typoscript recursively
	 *
	 * \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::cObjGetSingle() renders a TS array
	 * only if the passed array structure is directly
	 * defined renderable Typoscript - it does however
	 * not care for deep array structures.
	 * This method heals this lack by traversing the
	 * given TS array recursively and calling
	 * \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::cObjGetSingle() on each sub-array
	 * which looks like being renderable.
	 *
	 * @param array            $data    Deep data array parsed from Typoscript text
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer or tslib_cObj $cObj
	 * @return array                Data array with Typoscript rendered
	 * @author Lars Heber
	 */
	private function renderTS($data, $cObj) {
		foreach ($data as $key=>$value) {
			// Array key with trailing '.'?
			if (substr($key, strlen($key)-1, 1) == '.') {
				// Remove last character
				$key_1 = substr($key, 0, strlen($key)-1);
				// Same key WITHOUT '.' exists as well? Treat as renderable Typoscript!
				if (isset($data[$key_1])) {
					$data[$key_1] = $cObj->cObjGetSingle($data[$key_1], $data[$key]);
					unset($data[$key]);
				}
				// Traverse recursively
				else $data[$key] = $this->renderTS($data[$key], $cObj);
			}
		}
		return $data;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/class.tx_rnbase_configurations.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/class.tx_rnbase_configurations.php']);
}
