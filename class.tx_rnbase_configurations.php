<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2010 Rene Nitzsche
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


require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

class tx_rnbase_configurations {
  // We store all Data to an internal ArrayObject
  var $_dataStore;
  var $_viewData;
  var $_parameters;
  var $_keepVars;
  var $_qualifier;
  var $pluginUid; // Die UID des Plugins (also des Content-Objekts)
  var $cObj; // Das originale cObj des Plugins
  var $_cObjs; // Container für alternative cObjs innerhalb des Plugins
  var $LLkey='default'; // language to use
  var $altLLkey=''; // alternative language to use

  var $LOCAL_LANG = Array(); // Local language content
  var $LOCAL_LANG_charset = Array(); // Local language content charset for overwritten labels
  var $LOCAL_LANG_loaded = 0; // Local language flag

  var $_extensionKey;

  var $setupPath; // set this in the derived class or give the setupPath to loadTypoScript function

  // -------------------------------------------------------------------------------------
  // Constructor
  // -------------------------------------------------------------------------------------
  function __construct() {
    // This is there all configuration data is stored
    //$this->_dataStore = tx_rnbase::makeInstance('tx_lib_spl_arrayObject');
  	$this->_dataStore = new ArrayObject();
    // This is a container to transfer data to view
    $this->_viewData = new ArrayObject();
    // This is a container for variables necessary in links
    $this->_keepVars = new ArrayObject();
    $this->_cObjs = array(); // Wir verzichten mal auf das ArrayObject
  }
  /**
   * Initialize this instance with Configuration Array and cObj-Data
   */
  function init(&$configurationArray, &$cObj, $extensionKey, $qualifier) {
    // keep the cObj
    $this->cObj = $cObj;
    $this->cObjs[0] = $this->cObj;
    $this->pluginUid = $this->cObj->data['uid'];

    // make the data of the cObj available
    $this->_setCObjectData($cObj->data);

    // If configurationArray['setupPath'] is provided it will be used by tx_rnbase_configurations or subclass.
    // if configurationArray['setupPath'] is empty the subclass will use it's internally defined setupPath.
    $this->_setTypoScript($configurationArray['setupPath']);

    // Add the local configuration, overwriting TS setup
    $this->_setConfiguration($configurationArray);
    
    // Flexformvalues have the maximal precedence
    $this->_setFlexForm($cObj->data['pi_flexform']);
    
    // A qualifier and extkey from TS are preferred
    $this->_extensionKey = $this->get('extensionKey') ? $this->get('extensionKey') : $extensionKey;
    $this->_qualifier = $this->get('qualifier') ? $this->get('qualifier') : $qualifier;

    // The formatter
    $this->_formatter = tx_rnbase::makeInstance('tx_rnbase_util_FormatUtil', $this);

    // load local language strings
    if(tx_rnbase_util_TYPO3::isTYPO46OrHigher())
	    $this->_loadLL46($this->get('locallangFilename')? $this->get('locallangFilename') : 0);
    else
	    $this->_loadLL($this->get('locallangFilename')? $this->get('locallangFilename') : 0);
  }

  // -------------------------------------------------------------------------------------
  // Getters
  // -------------------------------------------------------------------------------------

  /**
   * Returns the uid of current content object in tt_content
   *
   * @return int
   */
  function getPluginId() {
  	return $this->pluginUid;
  }
  /**
   * Create your individuell instance of cObj. For each id only one instance is created.
   * If id == 0 the will get the plugins original cOBj.
   * @param $id any
   * @param $cObjClass String Optional cObj-classname
   */
  function &getCObj($id = 0, $cObjClass = 'tslib_cObj') {
    if(strcmp($id,'0') == 0) {
      return $this->cObj;
    }

    $cObj = $this->_cObjs[$id];

    if(!is_object($cObj)) {
      $this->cObjs[$id] = t3lib_div::makeInstance($cObjClass);
//    $this->cObj->data = $this->configurations->get('tt_content.');
    }
    return $this->cObjs[$id];
  }

  /**
   * Returns the formatter connected to this configuration object
   * @return tx_rnbase_util_FormatUtil
   */
  function &getFormatter() {
    return $this->_formatter;
  }

  /**
   * Return the data container for view by reference. This container should be filled
   * by Controller-Action
   */
  function &getViewData() {
    return $this->_viewData;
  }

  /**
   * Returns the defined path to template directory. This is by default
   * 'EXT:your_extension/templates/'. You can change this by TS setting templatePath
   */
  function getTemplatePath() {
    $path = $this->get('templatePath');
    return $path ? $path : 'EXT:' . $this->getExtensionKey() . '/views/templates/';
  }

	/**
	 * Factory-Method for links. The new link is initialized with qualifier and optional
	 * with keepVars set.
	 * @param boolean $addKeepVars whether or not keepVars should be set
	 * @return tx_rnbase_util_Link
	 */
	function &createLink($addKeepVars = true) {
		$link = tx_rnbase::makeInstance('tx_rnbase_util_Link');
		$link->designatorString = $this->getQualifier();
		// Die KeepVars setzen
		if($addKeepVars)
			$link->overruled($this->getKeepVars());
		return $link;
	}
	function setParameters($parameters) {
		$this->_parameters = $parameters;
		// Make sure to keep all parameters
		$this->setKeepVars($parameters);
	}
	function getParameters() {
		return $this->_parameters;
	}

	/**
	 * Returns the KeepVars-Array
	 */
	function getKeepVars() {
		return $this->_keepVars;
	}
	/**
	 * Set an ArrayObject with variables to keep between requests
	 */
	function setKeepVars($keepVars) {
		$arr = $keepVars->getArrayCopy();

		foreach( $arr As $key => $value) {
			if(strpos($key, 'NK_') === FALSE)
				$this->_keepVars->offsetSet($key, $value);
		}
	}

  /**
   * Add a value that must be kept by parameters
   */
  function addKeepVar($name, $value) {
    $this->_keepVars->offsetSet($name, $value);
  }

  /**
   * Remove a value that must be kept by parameters
   */
  function removeKeepVar($name) {
    $this->_keepVars->offsetUnset($name);
  }
  /**
   *
   */
  function createParamName($name) {
    return $this->getQualifier().'[' . $name . ']';
  }

  /**
   * Returns the ExtensionKey
   * @return string
   */
  function getExtensionKey() {
    return $this->_extensionKey;
  }
  /**
   * Returns the qualifier for plugin links: qualifier[param]=value
   * @return string
   */
  function getQualifier() {
    return $this->_qualifier;
  }

  /**
   * Returns the flexform data of this plugin as array
   *
   * @return array by reference
   */
  function &getFlexFormArray() {
    static $flex;
    if (!is_array($flex)) {
      $flex = t3lib_div::getURL(t3lib_extMgm::extPath($this->getExtensionKey()) . $this->get('flexform'));
      $flex = t3lib_div::xml2array($flex);
    }
    return $flex;
  }
  
	/**
	 * Returns the localized label of the LOCAL_LANG key.
	 * This is a reimplementation from tslib_pibase::pi_getLL().
	 */
	public function getLL($key,$alt='',$hsc=FALSE) {
		return tx_rnbase_util_TYPO3::isTYPO46OrHigher() ? $this->getLL46($key,$alt,$hsc) : $this->getLL40($key,$alt,$hsc);
	}

	public function getLL46($key, $alternativeLabel = '', $hsc = FALSE) {
		if (isset($this->LOCAL_LANG[$this->LLkey][$key][0]['target'])) {
	
			// The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
			if (isset($this->LOCAL_LANG_charset[$this->LLkey][$key])) {
				$word = $GLOBALS['TSFE']->csConv(
				$this->LOCAL_LANG[$this->LLkey][$key][0]['target'],
				$this->LOCAL_LANG_charset[$this->LLkey][$key]
				);
			} else {
				$word = $this->LOCAL_LANG[$this->LLkey][$key][0]['target'];
			}
		} elseif ($this->altLLkey && isset($this->LOCAL_LANG[$this->altLLkey][$key][0]['target'])) {
			// The "from" charset of csConv() is only set for strings from TypoScript via _LOCAL_LANG
			if (isset($this->LOCAL_LANG_charset[$this->altLLkey][$key])) {
				$word = $GLOBALS['TSFE']->csConv(
				$this->LOCAL_LANG[$this->altLLkey][$key][0]['target'],
				$this->LOCAL_LANG_charset[$this->altLLkey][$key]
				);
			} else {
				$word = $this->LOCAL_LANG[$this->altLLkey][$key][0]['target'];
			}
		} elseif (isset($this->LOCAL_LANG['default'][$key][0]['target'])) {
			// Get default translation (without charset conversion, english)
			$word = $this->LOCAL_LANG['default'][$key][0]['target'];
		} else {
			// Im BE die LANG fragen...
			$word = is_object($GLOBALS['LANG']) ? $GLOBALS['LANG']->getLL($key) : '';
			if(!$word)
				$word = $this->LLtestPrefixAlt.$alt;
		}
	
		$output = (isset($this->LLtestPrefix)) ? $this->LLtestPrefix . $word : $word;
	
		if ($hsc) {
			$output = htmlspecialchars($output);
		}
	
		return $output;
	}
	
	private function getLL40($key,$alt='',$hsc=FALSE) {
		if(!strcmp(substr($key,0,4),'LLL:')) {
			return $GLOBALS['TSFE']->sL($key);
		}
		if (isset($this->LOCAL_LANG[$this->LLkey][$key]))       {
			$word = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->LLkey][$key], $this->LOCAL_LANG_charset[$this->LLkey][$key]); // The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
		} elseif ($this->altLLkey && isset($this->LOCAL_LANG[$this->altLLkey][$key]))   {
			$word = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$this->altLLkey][$key], $this->LOCAL_LANG_charset[$this->altLLkey][$key]);   // The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
		} elseif (isset($this->LOCAL_LANG['default'][$key]))    {
			$word = $this->LOCAL_LANG['default'][$key];     // No charset conversion because default is english and thereby ASCII
		} else {
			// Im BE die LANG fragen...
			$word = is_object($GLOBALS['LANG']) ? $GLOBALS['LANG']->getLL($key) : '';
			if(!$word)
				$word = $this->LLtestPrefixAlt.$alt;
		}
	
		$output = $this->LLtestPrefix.$word;
		if ($hsc)
			$output = htmlspecialchars($output);
	
		return $output;
	}

  /**
   * Returns a value from extension configuration.
   * Can be called static
   *
   * @param string $extKey
   * @param string $cfgKey
   * @return mixed
   */
  public static function getExtensionCfgValue($extKey, $cfgKey) {
    $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
    return (is_array($extConfig) && array_key_exists($cfgKey, $extConfig)) ? $extConfig[$cfgKey] : false;
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
	 * @param string  relative setupPath
	 * @return array  or string
	 */
	public function get($pathKey, $deep=false) {
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
//				$ret = array_merge(array($ret), $arr);
			}
		}
		if (is_array($ret)) {
			$ret = $this->renderTS($ret, $this->getCObj());
			$ret = $noEndingDot ? $ret['key'] : $ret;
		}
		return $ret;
	}
	/**
	 * Returns a boolean config value. The return value is false if the value is empty or 0 or 'false'
	 * @param string $pathKey
	 * @param boolean $deep
	 * @return boolean
	 */
	public function getBool($pathKey, $deep=false) {
		$value = $this->get($pathKey, $deep);
		if(is_array($value)) return true;
		return (!$value || strtolower($value) == 'false') ? false : true;
	}
	/**
	 * Returns a int config value.
	 * @param string $pathKey
	 * @param boolean $deep
	 * @return int
	 */
	public function getInt($pathKey, $deep=false) {
		return intval( $this->get($pathKey, $deep));
	}
	/**
	 * Returns the complete TS config array
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
	 * @return mixed but should be a string
	 */
	function getCfgOrLL($pathKey) {
		$ret = $this->_queryArrayByPath($this->_dataStore->getArrayCopy(), $pathKey);
		if(!$ret) {
			$pathKey = strtr($pathKey, '.', '_');
			$ret = $this->getLL($pathKey);
		}
		return $ret;
	}
   
	/**
	 * Returns the requested value splitted as an array
	 *
	 * @return	[type]		...
	 */
	function getExploded($pathKey, $pattern = '/[\s,]+/') {
		return (array) preg_split($pattern, $this->_dataStore->offsetGet($pathKey));
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
	 * @param string  relative pathKey
	 * @param string  key of of the wanted key
	 * @param string  key of of the wanted value
	 * @return array  wanted Hash (key-value-pairs)
	 */
	function queryHash($pathKey, $keyName, $valueName) {
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
	function getKeyNames($confId){
    $markers = array();
    $dynaMarkers = $this->get($confId);
    if(!$dynaMarkers) return $markers;
    $dynaMarkers = array_keys($dynaMarkers);
    if(!$dynaMarkers || !count($dynaMarkers)) return $markers;
    // Jetzt evt. vorhandene Punkt am Ende entfernen
    for($i=0, $size = count($dynaMarkers); $i < $size; $i++) {
      $markers[] = preg_replace('/\./', '', $dynaMarkers[$i]);
    }
    $markers = array_unique($markers);
    $markers = array_values($markers);
    return $markers;
	}

	/**
	 * Returns all keynames below a config branch. Any trailing points will be removed.
	 *
	 * @param array $conf configuration array
	 * @return array
	 */
	function getUniqueKeysNames($conf) {
	  $keys = array();
    $dynaMarkers = array_keys($conf);
    if(!$dynaMarkers || !count($dynaMarkers)) return $keys;

    // Jetzt evt. vorhandene Punkt am Ende entfernen
    for($i=0, $size = count($dynaMarkers); $i < $size; $i++) {
      $keys[] = preg_replace('/\./', '', $dynaMarkers[$i]);
    }
    return array_unique($keys);
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
			return false;
		}
		// Converting flexform data into array if neccessary
		if (is_array($xmlOrArray)) {
			$array = $xmlOrArray;
		} else {
			$array = t3lib_div::xml2array($xmlOrArray);
		}
		$data = $array['data'];
		// Looking for the special sheet s_tssetup
		$flexTs = false;
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
						$newArr = $this->insertIntoDataArray($dataArr, array_slice($pathArray,1), $newValue);
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
			$tsParser = t3lib_div::makeInstance('t3lib_TSparser');
			$tsParser->setup = $this->_dataStore->getArrayCopy();
			$tsParser->parse($flexTs);
			$flexTsData = $tsParser->setup;
			$this->_dataStore->exchangeArray($flexTsData);
		}
	}

  private function mergeTSReference($key, $conf) {
    $tsParser = t3lib_div::makeInstance('t3lib_TSparser');
			// $name and $conf is loaded with the referenced values.
		$old_conf=$conf;
		list($name, $conf) = $tsParser->getVal($key,$GLOBALS['TSFE']->tmpl->setup);
		if (is_array($old_conf) && count($old_conf))	{
			$conf = self::joinTSarrays($conf,$old_conf);
		}
		return $conf;
  }

	/**
	 * Merges two TypoScript propery array, overlaing the $old_conf onto the $conf array
	 *
	 * @param	array		TypoScript property array, the "base"
	 * @param	array		TypoScript property array, the "overlay"
	 * @return	array		The resulting array
	 * @see mergeTSRef(), tx_tstemplatestyler_modfunc1::joinTSarrays()
	 */
	static function joinTSarrays($conf,$old_conf)	{
		if (is_array($old_conf))	{
			reset($old_conf);
			while(list($key,$val)=each($old_conf))	{
				if (is_array($val))	{
					$conf[$key] = self::joinTSarrays($conf[$key],$val);
				} else {
					$conf[$key] = $val;
				}
			}
		}
		return $conf;
	}
 
  function _queryArrayByPath($array, $path) {
  	$pathArray = explode('.', trim($path));
  	for($i = 0, $cnt = count($pathArray); $i < $cnt; $i++) {
  		if ($i < ($cnt -1 )) {
  			// Noch nicht beendet. Auf Reference prüfen
  			$value = $array[$pathArray[$i]];
  			$array = $array[$pathArray[$i] . '.'];
  			if (substr($value,0,1)=='<')	{
					$key = trim(substr($value,1));
  				$array = $this->mergeTSReference($key,$array);
  			}
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
  function _loadLL($filename) {
    if (!$this->LOCAL_LANG_loaded && $filename)  {
      // Load language to use
      if($GLOBALS['TSFE']->config['config']['language']) {
        $this->LLkey = $GLOBALS['TSFE']->config['config']['language'];
        if($GLOBALS['TSFE']->config['config']['language_alt'])
          $this->altLLkey = $GLOBALS['TSFE']->config['config']['language_alt'];
      }

      // Find language file
      $basePath = t3lib_div::getFileAbsFileName($filename);
      // php or xml as source: In any case the charset will be that of the system language.
      // However, this function guarantees only return output for default language plus the specified language (which is different from how 3.7.0 dealt with it)
      $this->LOCAL_LANG = t3lib_div::readLLfile($basePath,$this->LLkey);
      if ($this->altLLkey)    {
        $tempLOCAL_LANG = t3lib_div::readLLfile($basePath,$this->altLLkey);
        $this->LOCAL_LANG = array_merge(is_array($this->LOCAL_LANG) ? $this->LOCAL_LANG : array(),$tempLOCAL_LANG);
      }
      
      // Overlaying labels from TypoScript (including fictitious language keys for non-system languages!):
      $langArr = $this->get('_LOCAL_LANG.');
      if (is_array($langArr)) {
        while(list($k,$lA)=each($langArr)) {
          if (is_array($lA)) {
            $k = substr($k,0,-1);
            foreach($lA as $llK => $llV) {
              if (!is_array($llV)) {
                $this->LOCAL_LANG[$k][$llK] = $llV;
                if ($k != 'default') {
                  $this->LOCAL_LANG_charset[$k][$llK] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];        // For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset" and if that is not set, assumed to be that of the individual system languages (thus no conversion)
                }
              }
            }
          }
        }
      }
    }
    $this->LOCAL_LANG_loaded = 1;
  }

  
  /**
  * Loads local-language values by looking for a "locallang.php" file in the plugin class directory ($this->scriptRelPath) and if found includes it.
  * Also locallang values set in the TypoScript property "_LOCAL_LANG" are merged onto the values found in the "locallang.php" file.
  *
  * @return	void
  */
  private function _loadLL46($filename) {
  	if (!$this->LOCAL_LANG_loaded && $filename) {
      // Load language to use
      if($GLOBALS['TSFE']->config['config']['language']) {
        $this->LLkey = $GLOBALS['TSFE']->config['config']['language'];
        if($GLOBALS['TSFE']->config['config']['language_alt'])
          $this->altLLkey = $GLOBALS['TSFE']->config['config']['language_alt'];
      }

      // Find language file
      $basePath = t3lib_div::getFileAbsFileName($filename);
  		// Read the strings in the required charset (since TYPO3 4.2)
  		$this->LOCAL_LANG = t3lib_div::readLLfile($basePath,$this->LLkey, $GLOBALS['TSFE']->renderCharset);
  		if ($this->altLLkey) {
  			$this->LOCAL_LANG = t3lib_div::readLLfile($basePath,$this->altLLkey);
  		}
  
  		// Overlaying labels from TypoScript (including fictitious language keys for non-system languages!):
  		//$confLL = $this->conf['_LOCAL_LANG.'];
  		$confLL = $this->get('_LOCAL_LANG.');
  		if (is_array($confLL)) {
  			foreach ($confLL as $languageKey => $languageArray) {
  				// Don't process label if the langue is not loaded
  				$languageKey = substr($languageKey,0,-1);
  				if (is_array($languageArray) && is_array($this->LOCAL_LANG[$languageKey])) {
  					// Remove the dot after the language key
  					foreach ($languageArray as $labelKey => $labelValue) {
  						if (!is_array($labelValue))	{
  							$this->LOCAL_LANG[$languageKey][$labelKey][0]['target'] = $labelValue;
  
  							// For labels coming from the TypoScript (database) the charset is assumed to be "forceCharset"
  							// and if that is not set, assumed to be that of the individual system languages
  							if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']) {
  								$this->LOCAL_LANG_charset[$languageKey][$labelKey] = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
  							} else {
  								$this->LOCAL_LANG_charset[$languageKey][$labelKey] = $GLOBALS['TSFE']->csConvObj->charSetArray[$languageKey];
  							}
  						}
  					}
  				}
  			}
  		}
  	}
  	$this->LOCAL_LANG_loaded = 1;
  }
  
  
	/**
	 * (Try to) Render Typoscript recursively
	 *
	 * tslib_cObj::cObjGetSingle() renders a TS array
	 * only if the passed array structure is directly
	 * defined renderable Typoscript - it does however
	 * not care for deep array structures.
	 * This method heals this lack by traversing the
	 * given TS array recursively and calling
	 * tslib_cObj::cObjGetSingle() on each sub-array
	 * which looks like being renderable.
	 *
	 * @param array            $data    Deep data array parsed from Typoscript text
	 * @param tslib_cObj    $cObj
	 * @return array                Data array with Typoscript rendered
	 * @author Lars Heber
	 */
	private function renderTS($data, tslib_cObj &$cObj) {
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
?>