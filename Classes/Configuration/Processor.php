<?php

namespace Sys25\RnBase\Configuration;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2023 Rene Nitzsche
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

use ArrayObject;
use Sys25\RnBase\Exception\SkipActionException;
use Sys25\RnBase\Frontend\Marker\FormatUtil;
use Sys25\RnBase\Frontend\View\ViewContext;
use Sys25\RnBase\Utility\Arrays;
use Sys25\RnBase\Utility\Debug;
use Sys25\RnBase\Utility\Extensions;
use Sys25\RnBase\Utility\Language;
use Sys25\RnBase\Utility\Link;
use Sys25\RnBase\Utility\Network;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;
use Sys25\RnBase\Utility\Typo3Classes;
use tx_rnbase;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

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
 */

/**
 * Configuration processor.
 */
class Processor implements ConfigurationInterface
{
    /**
     * Here, all configuration data is stored.
     *
     * @var ArrayObject
     */
    private $_dataStore;

    /**
     * This is a container to transfer data to the view.
     *
     * @var ArrayObject
     */
    private $_viewData;

    /**
     * @var \Sys25\RnBase\Frontend\Request\ParametersInterface
     */
    private $_parameters;

    /**
     * This is a container for variables necessary in links.
     *
     * @var ArrayObject
     */
    private $_keepVars;

    /**
     * @var string
     */
    private $_qualifier = '';

    /**
     * die UID des Plugins (also des Content-Objekts).
     *
     * @var int
     */
    private $pluginUid = 0;

    /**
     * das originale cObj des Plugins.
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     *
     * @internal DO NOT USE THIS DIRECTLY! Use method getCObj() instead
     */
    public $cObj;

    /**
     * Container für alternative cObjs innerhalb des Plugins.
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer[]
     */
    private $cObjs = [];

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
    public $altLLkey = '';

    /**
     * @var string
     */
    private $_extensionKey = '';

    /**
     * @var int[]
     */
    private static $libIds = [];

    /**
     * Set this in the derived class or give the setupPath to the loadTypoScript method.
     *
     * @var string
     */
    protected $setupPath = '';

    /**
     * Util used to load and retrieve local lang labels.
     *
     * @var Language
     */
    private $localLangUtil;

    /**
     * @var \Sys25\RnBase\Frontend\Marker\FormatUtil
     */
    private $_formatter;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_dataStore = new ArrayObject();
        $this->_viewData = new ViewContext();
        $this->_keepVars = new ArrayObject();
        $this->localLangUtil = tx_rnbase::makeInstance(Language::class);
    }

    /**
     * Initialize this instance with Configuration Array and cObj-Data.
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
     * @param array                      $configurationArray the typoscript configuration array given from TYPO3
     * @param ContentObjectRenderer|null $cObj
     * @param string                     $extensionKey       the extension key for TYPO3
     * @param string                     $qualifier          the prefix string for plugin parameters
     */
    public function init(array &$configurationArray, $cObj, $extensionKey, $qualifier)
    {
        // keep the cObj
        if (is_object($cObj)) {
            $this->cObj = $cObj;
            $this->cObjs[0] = $this->cObj;
            $this->pluginUid = is_object($cObj) ? $this->createPluginId() : 0;

            // make the data of the cObj available
            $this->_setCObjectData($cObj->data);
        }

        // If configurationArray['setupPath'] is provided it will be used.
        // if configurationArray['setupPath'] is empty the subclass will use it's internally defined setupPath.
        $this->_setTypoScript($configurationArray['setupPath'] ?? '');

        // Add the local configuration, overwriting TS setup
        $this->_setConfiguration($configurationArray);

        if (is_object($cObj) && !$this->getBool('ignoreFlexFormConfiguration')) {
            // Flexformvalues have the maximal precedence
            $this->setFlexForm($cObj->data['pi_flexform'] ?? null);
        }

        // A qualifier and extkey from TS are preferred
        $this->_extensionKey = $this->get('extensionKey') ? $this->get('extensionKey') : $extensionKey;
        $this->_qualifier = $this->get('qualifier') ? $this->get('qualifier') : $qualifier;

        $this->_formatter = tx_rnbase::makeInstance(FormatUtil::class, $this);

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
    public function getPluginId()
    {
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
     * @throws SkipActionException
     */
    public function convertToUserInt($convert = true)
    {
        // set this only, if we are not an USER_INTERNAL
        if ($convert && $this->isPluginUserInt()) {
            return;
        }
        $this->getCObj()->doConvertToUserIntObject = $convert;
        if ($convert) {
            throw tx_rnbase::makeInstance(SkipActionException::class);
        }

        return true;
    }

    /**
     * Whether or not the current plugin is executed as USER_INT.
     *
     * @return bool
     */
    public function isPluginUserInt()
    {
        $contentObjectRendererClass = Typo3Classes::getContentObjectRendererClass();

        return $this->getCObj()->getUserObjectType() == $contentObjectRendererClass::OBJECTTYPE_USER_INT;
    }

    /**
     * Whether or not the plugins uses its own parameters. This will add the plugin id to all
     * parameters of the given plugin.
     *
     * @return bool
     */
    public function isUniqueParameters()
    {
        return true == $this->getBool('uniqueParameters');
    }

    /**
     * Returns a unique ID for this plugin.
     *
     * @return int
     */
    private function createPluginId()
    {
        $id = 0;
        if (is_array($this->cObj->data)) {
            $id = $this->cObj->data['uid'] ?? 0;
            if (array_key_exists('doktype', $this->cObj->data)) {
                // Es handelt sich um ein Plugin, daß per TS eingebunden wurde. In data steht der
                // Record der Seite.
                $base = array_key_exists($id, self::$libIds) ? (intval(self::$libIds[$id]) + 1) : 1;
                self::$libIds[$id] = $base;
                $id = (100000 + $id) * $base;
            }
        }

        return $id;
    }

    /**
     * Create your individual instance of cObj. For each id only one instance is created.
     * If id == 0 the will get the plugins original cOBj.
     *
     * @param string      $id        any
     * @param string|null $cObjClass String Optional cObj-classname
     *
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public function getCObj($id = 0, $cObjClass = null)
    {
        $cObjClass = null === $cObjClass ? Typo3Classes::getContentObjectRendererClass() : $cObjClass;
        if (0 == strcmp($id, '0')) {
            if (!is_object($this->cObj)) {
                $this->cObj = tx_rnbase::makeInstance($cObjClass);
                $this->cObjs[0] = $this->cObj;
            }

            return $this->cObj;
        }

        $cObj = isset($this->cObjs[$id]) ? $this->cObjs[$id] : null;

        if (!is_object($cObj)) {
            $this->cObjs[$id] = tx_rnbase::makeInstance($cObjClass);
        }

        return $this->cObjs[$id];
    }

    /**
     * The plugins original content object.
     *
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public function getContentObject()
    {
        return $this->getCObj();
    }

    /**
     * Returns the formatter connected to this configuration object.
     *
     * @return \Sys25\RnBase\Frontend\Marker\FormatUtil
     */
    public function getFormatter()
    {
        return $this->_formatter;
    }

    /**
     * Return the data container for view by reference. This container should be filled
     * by Controller-Action.
     *
     * @return ArrayObject
     */
    public function getViewData()
    {
        return $this->_viewData;
    }

    /**
     * Returns the defined path to template directory. This is by default
     * 'EXT:your_extension/templates/'. You can change this by TS setting templatePath.
     *
     * @return string
     */
    public function getTemplatePath()
    {
        $path = $this->get('templatePath');

        return $path ? $path : 'EXT:'.$this->getExtensionKey().'/views/templates/';
    }

    /**
     * Factory-Method for links. The new link is initialized with qualifier and optional
     * with keepVars set.
     *
     * @param bool $addKeepVars whether or not keepVars should be set
     *
     * @return Link
     */
    public function createLink($addKeepVars = true)
    {
        /** @var Link $link */
        $link = tx_rnbase::makeInstance(Link::class, $this->getCObj());
        $link->designatorString = $this->getQualifier();
        // Die KeepVars setzen
        if ($addKeepVars) {
            $link->overruled($this->getKeepVars());
        }
        if ($this->isUniqueParameters()) {
            $link->setUniqueParameterId($this->getPluginId());
        }

        return $link;
    }

    /**
     * @param \Sys25\RnBase\Frontend\Request\ParametersInterface $parameters
     */
    public function setParameters($parameters)
    {
        $this->_parameters = $parameters;
        // Make sure to keep all parameters
        $this->setKeepVars($parameters);
    }

    /**
     * Returns request parameters.
     *
     * @return \Sys25\RnBase\Frontend\Request\ParametersInterface
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Returns the KeepVars-Array.
     *
     * @return ArrayObject
     */
    public function getKeepVars()
    {
        return $this->_keepVars;
    }

    /**
     * Set an ArrayObject with variables to keep between requests.
     *
     * @param \Sys25\RnBase\Frontend\Request\ParametersInterface $keepVars
     */
    public function setKeepVars($keepVars)
    {
        $arr = $keepVars->getArrayCopy();

        foreach ($arr as $key => $value) {
            if (false === strpos($key, 'NK_')) {
                $this->_keepVars->offsetSet($key, $value);
            }
        }
    }

    /**
     * Add a value that must be kept by parameters.
     *
     * @param mixed $name
     * @param mixed $value
     */
    public function addKeepVar($name, $value)
    {
        $this->_keepVars->offsetSet($name, $value);
    }

    /**
     * Removes a value that must be kept by parameters.
     *
     * @param mixed $name
     */
    public function removeKeepVar($name)
    {
        $this->_keepVars->offsetUnset($name);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function createParamName($name)
    {
        return $this->getQualifier().'['.$name.']';
    }

    /**
     * Returns the extension key.
     *
     * @return string
     */
    public function getExtensionKey()
    {
        return $this->_extensionKey;
    }

    /**
     * Returns the qualifier for plugin links: qualifier[param]=value.
     *
     * @return string
     */
    public function getQualifier()
    {
        return $this->_qualifier;
    }

    /**
     * Force a new qualifier for link creation.
     *
     * @param string $qualifier
     *
     * @return ConfigurationInterface
     */
    public function setQualifier($qualifier)
    {
        $this->_qualifier = $qualifier;

        return $this;
    }

    /**
     * Returns the flexform data of this plugin as array.
     *
     * @return array by reference
     */
    public function &getFlexFormArray()
    {
        static $flex;
        if (!is_array($flex)) {
            $flex = Network::getUrl(Extensions::extPath($this->getExtensionKey()).$this->get('flexform'));
            $flex = Arrays::xml2array($flex);
        }

        return $flex;
    }

    /**
     * The current language utility.
     *
     * @return Language
     */
    protected function getLocalLangUtil()
    {
        return $this->localLangUtil;
    }

    /**
     * Returns the localized label of the LOCAL_LANG key.
     * This is a reimplementation from tslib_pibase::pi_getLL().
     *
     * @param string $key
     * @param string $alt
     * @param bool   $hsc
     *
     * @return string
     */
    public function getLL($key, $alt = '', $hsc = false)
    {
        return $this->getLocalLangUtil()->getLL(
            $key,
            $alt,
            $hsc,
            Debug::isLabelDebugEnabled($this)
        );
    }

    /**
     * Returns a value from extension configuration.
     * Can be called static.
     *
     * @param string $extKey
     * @param string $cfgKey
     *
     * @return mixed
     */
    public static function getExtensionCfgValue($extKey, $cfgKey = '')
    {
        if (TYPO3::isTYPO90OrHigher()) {
            $extConfig = tx_rnbase::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get(
                $extKey,
                $cfgKey
            );
        } else {
            $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);

            if ($cfgKey) {
                $extConfig = (is_array($extConfig) && array_key_exists($cfgKey, $extConfig)) ? $extConfig[$cfgKey] : false;
            }
        }

        return $extConfig;
    }

    /**
     * Get a value or an array by providing a relative pathKey.
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
     * @param string $pathKey relative setupPath
     * @param bool   $deep
     *
     * @return array|string|null
     */
    public function get($pathKey, $deep = false)
    {
        if (!$deep) {
            return $this->queryArrayByPath($this->_dataStore->getArrayCopy(), $pathKey);
        }

        // Wenn am Ende kein Punkt steht, ist das Ergebnis ein String
        // deep ist nur dann sinnvoll, wenn ohne Punkt am Ende gefragt wird.
        $ret = $this->queryArrayByPath($this->_dataStore->getArrayCopy(), $pathKey);
        $noEndingDot = '.' != substr($pathKey, strlen($pathKey) - 1, 1);
        if (!is_array($ret) && $noEndingDot) {
            $arr = $this->queryArrayByPath($this->_dataStore->getArrayCopy(), $pathKey.'.');
            if (is_array($arr)) {
                $ret = ['key' => $ret, 'key.' => $arr];
            }
        }
        if (is_array($ret)) {
            $ret = $this->renderTS($ret, $this->getCObj());
            $ret = $noEndingDot ? ($ret['key'] ?? null) : $ret;
        }

        return $ret;
    }

    /**
     * Returns a boolean config value. The return value is FALSE if the value is empty or 0 or 'FALSE'.
     *
     * @param string $pathKey
     * @param bool   $deep
     * @param bool   $notDefined value to return if no value configured or empty
     *
     * @return bool
     */
    public function getBool($pathKey, $deep = false, $notDefined = false)
    {
        $value = $this->get($pathKey, $deep);
        if (is_array($value)) {
            return true;
        }
        if ('' == $value) {
            return $notDefined;
        }

        return (!$value || 'false' == strtolower($value)) ? false : true;
    }

    /**
     * Returns a int config value.
     *
     * @param string $pathKey
     * @param bool   $deep
     *
     * @return int
     */
    public function getInt($pathKey, $deep = false)
    {
        return intval($this->get($pathKey, $deep));
    }

    /**
     * Returns the complete TS config array.
     *
     * @return array
     */
    public function getConfigArray()
    {
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
    public function getCfgOrLL($pathKey)
    {
        $ret = $this->queryArrayByPath($this->_dataStore->getArrayCopy(), $pathKey);
        if (!$ret) {
            $pathKey = strtr($pathKey, '.', '_');
            $ret = $this->getLL($pathKey);
        }

        return $ret;
    }

    /**
     * Get a exploded value.
     *
     * @param string $pathKey
     * @param string $delim
     * @param bool   $deep
     *
     * @return array
     */
    public function getExploded($pathKey, $delim = ',', $deep = false)
    {
        $value = $this->get($pathKey, $deep);
        if (is_array($value)) {
            return $value;
        }
        if (empty($value)) {
            return [];
        }

        return Strings::trimExplode($delim, $value, true);
    }

    /**
     * Query a uniform hash from a dataset like setup.
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
     * @param string $pathKey   relative pathKey
     * @param string $keyName   key of of the wanted key
     * @param string $valueName key of of the wanted value
     *
     * @return array wanted Hash (key-value-pairs)
     */
    public function queryHash($pathKey, $keyName, $valueName)
    {
        $selection = $this->_dataStore->get($pathKey);
        $array = [];
        foreach ($selection as $set) {
            $array[$set[$keyName]] = $set[$valueName];
        }

        return $array;
    }

    /**
     * Query a single dataset from a list of datasets by a key entry.
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
     *
     * @return array wanted dataset
     *
     * @deprecated should be removed
     */
    public function queryDataSet($path, $key, $value)
    {
        $selection = $this->_dataStore->get($path);
        foreach ($selection as $set) {
            if ($set[$key] == $value) {
                return $set;
            }
        }

        return [];
    }

    /**
     * Query a single data from a list of datasets by a combination of key entries.
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
     *
     * @return string wanted value
     *
     * @deprecated should be removed
     */
    public function queryData($path, $key, $value, $wanted)
    {
        $selection = $this->_dataStore->get($path);
        foreach ($selection as $set) {
            if ($set[$key] == $value) {
                return $set[$wanted];
            }
        }

        return '';
    }

    /**
     * Returns all keynames below a config branch. Any trailing points will be removed.
     *
     * @param string $confId
     *
     * @return array of strings or empty array
     */
    public function getKeyNames($confId)
    {
        $dynaMarkers = $this->get($confId);
        if (!is_array($dynaMarkers)) {
            return [];
        }

        return $this->getUniqueKeysNames($dynaMarkers);
    }

    /**
     * Returns all keynames below a config branch. Any trailing points will be removed.
     *
     * @param array $conf configuration array
     *
     * @return array
     */
    public function getUniqueKeysNames(array $conf)
    {
        $keys = [];

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

    protected function _setCObjectData($data)
    {
        $this->_dataStore->offsetSet('tt_content.', $data);
    }

    /**
     * Load a (local) configuration array into the object.
     *
     * An configuration array of rendered TypoScript like it is handeled to the main function
     * of plugins by the outer framework: tx_myextension_controller->main($out, $configurationArray).
     *
     * @param array  direct setup input in form of a renderd TS array
     */
    protected function _setConfiguration($configuration)
    {
        foreach ((array) $configuration as $key => $value) {
            $this->_dataStore->offsetSet($key, $value);
        }
    }

    /**
     * Load TypoScript Setup for an extension.
     *
     * Loads TS form the TS tree down from a node that you define by the $setupPath.
     * If no parameter is provided the setupPath is taken from a class variable of the same name.
     *
     * @param string  setup path from TS, example: 'plugin.tx_myextension.configuration.'
     */
    protected function _setTypoScript($setupPath = '')
    {
        $setupPath = $setupPath ? $setupPath : $this->setupPath;
        if ($setupPath) {
            $array = $this->queryArrayByPath($GLOBALS['TSFE']->tmpl->setup, $setupPath);
            if (is_array($array)) {
                foreach ((array) $array as $key => $value) {
                    $this->_dataStore->offsetSet($key, $value);
                }
            }
        }
    }

    private function insertIntoDataArray($dataArr, $pathArray, $newValue)
    {
        // Cancel Recursion on value level
        if (1 == count($pathArray)) {
            if (!is_array($dataArr)) {
                $dataArr = [];
            }
            $dataArr[$pathArray[0]] = $newValue;

            return $dataArr;
        }

        $ret = [];

        if (!$dataArr) {
            $dataArr = [$pathArray[0].'.' => ''];
        }
        if (!array_key_exists($pathArray[0].'.', $dataArr)) {
            $dataArr[$pathArray[0].'.'] = '';
        }

        foreach ($dataArr as $key => $value) {
            if ($pathArray[0].'.' == $key) {
                // Go deeper
                $ret[$key] = $this->insertIntoDataArray($value, array_slice($pathArray, 1), $newValue);
            } else {
                $ret[$key] = $value;
            }
        }

        return $ret;
    }

    /**
     * Load flexformdata into the object.
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
     */
    protected function setFlexForm($xmlOrArray)
    {
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
            $array = Arrays::xml2array($xmlOrArray);
        }
        $data = $array['data'];
        // Looking for the special sheet s_tssetup
        $flexTs = false;
        if (isset($data['s_tssetup'])) {
            $flexTs = $data['s_tssetup']['lDEF']['flexformTS']['vDEF'];
            unset($data['s_tssetup']);
        }
        foreach ((array) $data as $sheet => $languages) {
            if (!is_array($languages)) {
                continue;
            }
            foreach ($languages[$languagePointer] as $key => $def) {
                // Wir nehmen Flexformwerte nur, wenn sie sinnvolle Daten enthalten
                // Sonst werden evt. vorhandenen Daten überschrieben
                if (!(0 == strlen($def[$valuePointer] ?? ''))) { // || $def[$valuePointer] == '0')
                    $pathArray = explode('.', trim($key));
                    if (count($pathArray) > 1) {
                        // Die Angabe im Flexform ist in Punktnotation
                        // Wir holen das Array im höchsten Knoten
                        $dataArr = $this->_dataStore->offsetExists($pathArray[0].'.')
                            ? $this->_dataStore->offsetGet($pathArray[0].'.')
                            : null;
                        $newValue = $def[$valuePointer];
                        $newArr = $this->insertIntoDataArray($dataArr, array_slice($pathArray, 1), $newValue);
                        $this->_dataStore->offsetSet($pathArray[0].'.', $newArr);
                    } else {
                        $this->_dataStore->offsetSet($key, $def[$valuePointer]);
                    }
                }
            }
        }
        if ($flexTs) {
            // This handles ts setup from flexform
            $tsParser = tx_rnbase::makeInstance(Typo3Classes::getTypoScriptParserClass());
            $tsParser->setup = $this->_dataStore->getArrayCopy();
            $tsParser->parse($flexTs);
            $flexTsData = $tsParser->setup;
            $this->_dataStore->exchangeArray($flexTsData);
        }
    }

    /**
     * @param string $value
     * @param array|null $conf
     *
     * @return array|string|null
     */
    private function mergeTSReference($value, $conf)
    {
        if ('<' != substr($value, 0, 1)) {
            return $conf;
        }

        // das < abschneiden, um den pfad zum link zu erhalten
        $key = trim(substr($value, 1));

        $tsParser = tx_rnbase::makeInstance(Typo3Classes::getTypoScriptParserClass());

        // $name and $conf is loaded with the referenced values.
        list($linkValue, $linkConf) = $tsParser->getVal($key, $GLOBALS['TSFE']->tmpl->setup);

        // Konfigurationen mergen
        if (is_array($conf) && count($conf)) {
            $linkConf = self::joinTSarrays($linkConf, $conf);
        }

        // auf rekursion hin prüfen
        $linkConf = $this->mergeTSReference($linkValue, $linkConf);

        return $linkConf;
    }

    /**
     * Merges two TypoScript propery array, overlaing the $old_conf onto the $conf array.
     *
     * @param   array       TypoScript property array, the "base"
     * @param   array       TypoScript property array, the "overlay"
     *
     * @return array The resulting array
     *
     * @see mergeTSRef(), tx_tstemplatestyler_modfunc1::joinTSarrays()
     */
    public static function joinTSarrays($conf, $old_conf)
    {
        if (is_array($old_conf)) {
            foreach ($old_conf as $key => $val) {
                if (is_array($val)) {
                    $conf[$key] = self::joinTSarrays($conf[$key] ?? [], $val);
                } else {
                    $conf[$key] = $val;
                }
            }
        }

        return $conf;
    }

    /**
     * @param array  $array
     * @param string $path
     *
     * @return array|string|null
     */
    protected function queryArrayByPath($array, $path)
    {
        $pathArray = explode('.', trim($path));
        for ($i = 0, $cnt = count($pathArray); $i < $cnt; ++$i) {
            if ($i < ($cnt - 1)) {
                // Noch nicht beendet. Auf Reference prüfen
                $array = $this->mergeTSReference(
                    $array[$pathArray[$i]] ?? '',
                    $array[$pathArray[$i].'.'] ?? null
                );
            } elseif (empty($pathArray[$i])) {
                // It ends with a dot. We return the rest of the array
                return $array;
            } else {
                // It endes without a dot. We return the value.
                return $array[$pathArray[$i]] ?? null;
            }
        }

        return null;
    }

    /**
     * Loads local language file for frontend rendering if defined in configuration.
     * Also locallang values from TypoScript property "_LOCAL_LANG" are merged onto the
     * values. This is a reimplementation from tslib_pibase::pi_loadLL().
     */
    private function loadLL()
    {
        $this->getLocalLangUtil()->loadLLFile($this->get('locallangFilename'));

        // Overlaying labels from additional locallangs are minor prior
        // we support comma separated lists and arrays
        $locallangOverlays = (array) $this->get('locallangFilename.');
        if (array_key_exists('_cfg.', $locallangOverlays)) {
            unset($locallangOverlays['_cfg.']);
            if ($this->getBool('locallangFilename._cfg.naturalOrder')) {
                ksort($locallangOverlays);
            }
        }

        if (!empty($locallangOverlays)) {
            foreach ($locallangOverlays as $locallangOverlayFilename) {
                $this->getLocalLangUtil()->loadLLFile($locallangOverlayFilename);
            }
        }
        // Overlaying labels from TypoScript are higher prior (including fictitious language keys for non-system languages!):
        $this->getLocalLangUtil()->loadLLTs($this->get('_LOCAL_LANG.'));
    }

    /**
     * (Try to) Render Typoscript recursively.
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
     * @param array                                                                 $data Deep data array parsed from Typoscript text
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer or tslib_cObj $cObj
     *
     * @return array Data array with Typoscript rendered
     *
     * @author Lars Heber
     */
    private function renderTS($data, $cObj)
    {
        foreach ($data as $key => $value) {
            // Array key with trailing '.'?
            if ('.' == substr($key, strlen($key) - 1, 1)) {
                // Remove last character
                $key_1 = substr($key, 0, strlen($key) - 1);
                // Same key WITHOUT '.' exists as well? Treat as renderable Typoscript!
                if (isset($data[$key_1])) {
                    $data[$key_1] = $cObj->cObjGetSingle($data[$key_1], $data[$key]);
                    unset($data[$key]);
                } // Traverse recursively
                else {
                    $data[$key] = $this->renderTS($data[$key], $cObj);
                }
            }
        }

        return $data;
    }
}
