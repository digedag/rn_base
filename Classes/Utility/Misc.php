<?php

namespace Sys25\RnBase\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2013 Rene Nitzsche
 *  Contact: rene@system25.de
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

use Exception;
use Sys25\RnBase\Configuration\Processor as ConfigurationProcessor;
use tx_rnbase;
use tx_rnbase_util_Dates;
use tx_rnbase_util_Debug;
use tx_rnbase_util_Lock;
use tx_rnbase_util_Logger;
use TYPO3\CMS\Core\Messaging\FlashMessageService;

/**
 * Contains some helpful methods.
 */
class Misc
{
    private static $enableTT = false;

    /**
     * Returns a service
     * Mayday is raised if service not found.
     *
     * @param string $type
     * @param string $subType
     *
     * @return \TYPO3\CMS\Core\Service\AbstractService
     */
    public static function getService($type, $subType = '')
    {
        /** @var \TYPO3\CMS\Core\Service\AbstractService $service */
        $service = tx_rnbase::makeInstanceService($type, $subType);

        if (!is_object($service)) {
            self::mayday('Service '.$type.' - '.$subType.' not found!');
        }

        return $service;
    }

    /**
     * Returns an array with all subtypes for given service key.
     *
     * @param string $type
     */
    public static function lookupServices($serviceType)
    {
        global $T3_SERVICES;
        $priority = []; // Remember highest priority
        $services = [];
        if (is_array($T3_SERVICES[$serviceType])) {
            foreach ($T3_SERVICES[$serviceType] as $info) {
                if ($info['available'] and (!isset($priority[$info['subtype']]) || $info['priority'] >= $priority[$info['subtype']])) {
                    $priority[$info['subtype']] = $info['priority'];
                    $services[$info['subtype']] = $info;
                }
            }
        }

        return $services;
    }

    /**
     * Zufällige Sortierung der Items in der Liste. Die Liste wird per PHP gemischt. Zusätzlich kann ein weiteres
     * Limit gesetzt werden, um die Anzahl der auszugebenden Items weiter einzuschränken.
     * Damit kann man aus einem Pool von Items (bspw. die neuesten 10 Items) per Zufall eine gewünschte
     * Anzahl von Items anzeigen.
     *
     * @param array $items
     * @param int   $limit
     *
     * @return array
     */
    public static function randomizeItems(array $items, $limit = 0)
    {
        $anzahl = count($items);
        $idxArr = range(1, $anzahl);
        shuffle($idxArr);
        $limit = ($limit > 0 && $limit < $anzahl) ? $limit : $anzahl;
        $ret = [];
        for ($i = 0; $i < $limit; ++$i) {
            $ret[] = $items[($idxArr[$i] - 1)];
        }
        reset($ret);

        return $ret;
    }

    /**
     * Calls a hook.
     *
     * @param string $extKey
     * @param string $hookKey
     * @param array  $params
     * @param mixed  $parent  instance of calling class or 0
     */
    public static function callHook($extKey, $hookKey, $params, $parent = 0)
    {
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey][$hookKey])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey][$hookKey] as $funcRef) {
                $utility = Typo3Classes::getGeneralUtilityClass();
                $utility::callUserFunction(
                    $funcRef,
                    $params,
                    $parent
                );
            }
        }
    }

    /**
     * Calls a userdefined function/method in class
     * Such a function/method should look like this: "function proc(&$params, &$ref)    {...}"
     * Usage: 17.
     *
     * @param   string      Function/Method reference, '[file-reference":"]["&"]class/function["->"method-name]'. You can prefix this reference with "[file-reference]:" and tx_rnbase_util_Files::getFileAbsFileName() will then be used to resolve the filename and subsequently include it by "require_once()" which means you don't have to worry about including the class file either! Example: "EXT:realurl/class.tx_realurl.php:&tx_realurl->encodeSpURL". Finally; you can prefix the class name with "&" if you want to reuse a former instance of the same object call ("singleton").
     * @param   mixed       Parameters to be pass along (typically an array) (REFERENCE!)
     * @param   mixed       Reference to be passed along (typically "$this" - being a reference to the calling object) (REFERENCE!)
     * @param   string      Required prefix of class or function name
     * @param   int     Error mode (when class/function could not be found): 0 - call debug(), 1 - do nothing, 2 - raise an exception (allows to call a user function that may return FALSE)
     *
     * @return mixed Content from method/function call or false if the class/method/function was not found
     *
     * @see getUserObj()
     */
    public static function callUserFunction($funcName, &$params, &$ref, $checkPrefix = 'user_', $errorMode = 0)
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::callUserFunction($funcName, $params, $ref);
    }

    /**
     * Stops PHP execution : die() if some critical error appeared
     * This method is taken from the great ameos_formidable extension.
     *
     * @param string $msg: the error message
     */
    public static function mayday($msg, $extKey = '')
    {
        tx_rnbase_util_Logger::fatal($msg, $extKey ? $extKey : 'rn_base');
        $aTrace = debug_backtrace();
        $aLocation = array_shift($aTrace);
        $aTrace1 = array_shift($aTrace);
        $aTrace2 = array_shift($aTrace);
        $aTrace3 = array_shift($aTrace);
        $aTrace4 = array_shift($aTrace);

        $aDebug = [];

        $aDebug[] = '<h2 id="backtracetitle">Call stack</h2>';
        $aDebug[] = '<div class="backtrace">';
        $aDebug[] = '<span class="notice"><b>Call 0: </b>'.str_replace(\Sys25\RnBase\Utility\Environment::getPublicPath(), '/', $aLocation['file']).':'.$aLocation['line'].' | <b>'.$aTrace1['class'].$aTrace1['type'].
                                    $aTrace1['function'].'</b></span><br/>With parameters: '.(!empty($aTrace1['args']) ? self::viewMixed($aTrace1['args']) : ' no parameters');
        $aDebug[] = '<hr/>';
        $aDebug[] = '<span class="notice"><b>Call -1: </b>'.str_replace(\Sys25\RnBase\Utility\Environment::getPublicPath(), '/', $aTrace1['file']).':'.$aTrace1['line'].' | <b>'.$aTrace2['class'].$aTrace2['type'].
                                    $aTrace2['function'].'</b></span><br />With parameters: '.(!empty($aTrace2['args']) ? self::viewMixed($aTrace2['args']) : ' no parameters');
        $aDebug[] = '<hr/>';
        $aDebug[] = '<span class="notice"><b>Call -2: </b>'.str_replace(\Sys25\RnBase\Utility\Environment::getPublicPath(), '/', $aTrace2['file']).':'.$aTrace2['line'].' | <b>'.$aTrace3['class'].$aTrace3['type'].
                                    $aTrace3['function'].'</b></span><br />With parameters: '.(!empty($aTrace3['args']) ? self::viewMixed($aTrace3['args']) : ' no parameters');
        $aDebug[] = '<hr/>';
        $aDebug[] = '<span class="notice"><b>Call -3: </b>'.str_replace(\Sys25\RnBase\Utility\Environment::getPublicPath(), '/', $aTrace3['file']).':'.$aTrace3['line'].' | <b>'.$aTrace4['class'].
                                    $aTrace4['type'].$aTrace4['function'].'</b></span><br />With parameters: '.(!empty($aTrace4['args']) ? self::viewMixed($aTrace4['args']) : ' no parameters');
        $aDebug[] = '<hr/>';

        if ($debugTrail = tx_rnbase_util_Debug::getDebugTrail()) {
            $aDebug[] = '<span class="notice">'.$debugTrail.'</span>';
            $aDebug[] = '<hr/>';
        }

        $aDebug[] = '</div>';

        if (intval(ConfigurationProcessor::getExtensionCfgValue('rn_base', 'forceException4Mayday'))) {
            throw tx_rnbase::makeInstance('tx_rnbase_util_Exception', $msg, 0, ['Info' => $aDebug]);
        }

        $aDebug[] = '<br/>';

        $sContent = '<h1 id="title">Mayday</h1>';
        $sContent .= '<div id="errormessage">'.$msg.'</div>';
        $sContent .= '<hr />';
        $verbose = intval(ConfigurationProcessor::getExtensionCfgValue('rn_base', 'verboseMayday'));
        if ($verbose) {
            $sContent .= implode('', $aDebug);
        }

        $sPage = <<<MAYDAYPAGE
<!DOCTYPE html
    PUBLIC '-//W3C//DTD XHTML 1.1//EN'
    'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en'>
    <head>
        <title>${extKey}::Mayday</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="robots" content="noindex, nofollow" />
        <style type="text/css">

            #title {
                color: red;
                font-family: Verdana;
            }

            #errormessage {
                border: 2px solid red;
                padding: 10px;
                color: white;
                background-color: red;
                font-family: Verdana;
                font-size: 12px;
            }

            .notice {
                font-family: Verdana;
                font-size: 9px;
                font-style: italic;
            }

            #backtracetitle {
            }

            .backtrace {
                background-color: #FFFFCC;
            }

            HR {
                border: 1px solid silver;
            }
        </style>
    </head>
    <body>
        {$sContent}
    </body>
</html>

MAYDAYPAGE;

        $dieOnMayday = (int) ConfigurationProcessor::getExtensionCfgValue('rn_base', 'dieOnMayday');
        if ($dieOnMayday) {
            exit($sPage);
        } else {
            echo $sPage;
        }
    }

    /**
     * Creates a html view for a php object
     * This method is taken from the great ameos_formidable extension.
     *
     * @param mixed $mMixed
     * @param bool  $bRecursive
     * @param int   $iLevel
     *
     * @return string
     */
    private static function viewMixed($mMixed, $bRecursive = true, $iLevel = 0)
    {
        $sStyle = 'font-family: Verdana; font-size: 9px;';
        $sStyleBlack = $sStyle.'color: black;';
        $sStyleRed = $sStyle.'color: red;';
        $sStyleGreen = $sStyle.'color: green;';

        $aBgColors = [
            'FFFFFF', 'F8F8F8', 'EEEEEE', 'E7E7E7', 'DDDDDD', 'D7D7D7',
            'CCCCCC', 'C6C6C6', 'BBBBBB', 'B6B6B6', 'AAAAAA', 'A5A5A5',
            '999999', '949494', '888888', '848484', '777777', '737373',
        ];

        if (is_array($mMixed)) {
            $result = "<table border=1 style='border: 1px solid silver' cellpadding=1 cellspacing=0 bgcolor='#".$aBgColors[$iLevel]."'>";

            if (!count($mMixed)) {
                $result .= "<tr><td><span style='".$sStyleBlack."'><b>".htmlspecialchars('EMPTY!').'</b></span></td></tr>';
            } else {
                foreach ($mMixed as $key => $val) {
                    $result .= "<tr><td valign='top'><span style='".$sStyleBlack."'>".htmlspecialchars((string) $key).'</span></td><td>';

                    if (is_array($val)) {
                        $result .= self::viewMixed($val, $bRecursive, $iLevel + 1);
                    } else {
                        $result .= "<span style='".$sStyleRed."'>".self::viewMixed($val, $bRecursive, $iLevel + 1).'<br /></span>';
                    }

                    $result .= '</td></tr>';
                }
            }

            $result .= '</table>';
        } elseif (is_resource($mMixed)) {
            $result = "<span style='".$sStyleGreen."'>RESOURCE: </span>".$mMixed;
        } elseif (is_object($mMixed)) {
            if ($bRecursive) {
                $result = "<span style='".$sStyleGreen."'>OBJECT (".get_class($mMixed).') : </span>'.self::viewMixed(get_object_vars($mMixed), false, $iLevel + 1);
            } else {
                $result = "<span style='".$sStyleGreen."'>OBJECT (".get_class($mMixed).') : !RECURSION STOPPED!</span>';
            }
        } elseif (is_bool($mMixed)) {
            $result = "<span style='".$sStyleGreen."'>BOOLEAN: </span>".($mMixed ? 'TRUE' : 'FALSE');
        } elseif (is_string($mMixed)) {
            if (empty($mMixed)) {
                $result = "<span style='".$sStyleGreen."'>STRING(0)</span>";
            } else {
                $result = "<span style='".$sStyleGreen."'>STRING(".strlen($mMixed).'): </span>'.nl2br(htmlspecialchars((string) $mMixed));
            }
        } elseif (is_null($mMixed)) {
            $result = "<span style='".$sStyleGreen."'>!NULL!</span>";
        } elseif (is_int($mMixed)) {
            $result = "<span style='".$sStyleGreen."'>INTEGER: </span>".$mMixed;
        } else {
            $result = "<span style='".$sStyleGreen."'>MIXED: </span>".nl2br(htmlspecialchars(strval($mMixed)));
        }

        return $result;
    }

    /**
     * Prepare classes for FE-rendering if it is needed in TYPO3 backend.
     *
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    public static function prepareTSFE($options = [])
    {
        $pid = array_key_exists('pid', $options) ? $options['pid'] : 1;
        $type = array_key_exists('type', $options) ? $options['type'] : 99;

        $force = array_key_exists('force', $options) ? true : false;

        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = Typo3Classes::getTimeTracker();
            $GLOBALS['TT']->start();
        }

        $typoScriptFrontendControllerClass = Typo3Classes::getTypoScriptFrontendControllerClass();
        if (!is_object($GLOBALS['TSFE']) ||
            !($GLOBALS['TSFE'] instanceof $typoScriptFrontendControllerClass) ||
            $force
        ) {
            if (!TYPO3::isTYPO70OrHigher() && !defined('PATH_tslib')) {
                // PATH_tslib setzen
                if (@is_dir(\Sys25\RnBase\Utility\Environment::getPublicPath().'typo3/sysext/cms/tslib/')) {
                    define('PATH_tslib', \Sys25\RnBase\Utility\Environment::getPublicPath().'typo3/sysext/cms/tslib/');
                } elseif (@is_dir(\Sys25\RnBase\Utility\Environment::getPublicPath().'tslib/')) {
                    define('PATH_tslib', \Sys25\RnBase\Utility\Environment::getPublicPath().'tslib/');
                } else {
                    define('PATH_tslib', '');
                }
            }

            if (TYPO3::isTYPO90OrHigher()) {
                $rootLine = null;
                if ($pid > 0) {
                    $rootLine = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($pid);
                }
                $siteMatcher = tx_rnbase::makeInstance(\TYPO3\CMS\Core\Routing\SiteMatcher::class);
                $site = $siteMatcher->matchByPageId($pid, $rootLine);
                $GLOBALS['TSFE'] = tx_rnbase::makeInstance(
                    $typoScriptFrontendControllerClass,
                    $GLOBALS['TYPO3_CONF_VARS'],
                    $site,
                    $site->getDefaultLanguage()
                );
            } else {
                $GLOBALS['TSFE'] = tx_rnbase::makeInstance(
                    $typoScriptFrontendControllerClass,
                    $GLOBALS['TYPO3_CONF_VARS'],
                    $pid,
                    $type
                );
            }
        }
        /* @var $tsfe \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController */
        $tsfe = $GLOBALS['TSFE'];

        // base user groups
        // todo:         $userAspect = $this->context->getAspect('frontend.user');
        if (!TYPO3::isTYPO95OrHigher() && empty($tsfe->gr_list) || $force) {
            $GLOBALS['TSFE']->gr_list = '0,-1';
        }

        // init the syspage for pageSelect
        if (!is_object($tsfe->sys_page) || $force) {
            $tsfe->sys_page = TYPO3::getSysPage();
        }

        // init the template
        if (!TYPO3::isTYPO95OrHigher() && (!is_object($tsfe->tmpl) || $force)) {
            $tsfe->initTemplate();
            if (empty($GLOBALS['TSFE']->tmpl->getFileName_backPath)) {
                $GLOBALS['TSFE']->tmpl->getFileName_backPath = Environment::getPublicPath();
            }
        }

        // initial empty config
        if (!is_array($tsfe->config)) {
            $tsfe->config = [];
        }
        if (!is_array($tsfe->config['config'])) {
            $tsfe->config['config'] = [];
        }

        // init the language
        if (!TYPO3::isTYPO95OrHigher() && (empty($tsfe->lang) || $force)) {
            $tsfe->initLLvars();
        }

        if (!$options['dontSetPageToTsfe']
            && (!is_array($tsfe->page) || $tsfe->page['uid'] != $pid)
        ) {
            $tsfe->page = $tsfe->sys_page->getPage($pid);
        }

        // Den Backpath aus dem PageRenderer entfernen. Der wurde auf typo3/ gesetzt
        if (method_exists(TYPO3::getPageRenderer(), 'setBackPath')) {
            TYPO3::getPageRenderer()->setBackPath('');
        }

        return $tsfe;
    }

    /**
     * Umlaute durch normale Buchstaben erstetzen. Aus Ü wird Ue.
     *
     * @param string $str
     *
     * @return string
     */
    public static function removeUmlauts($str)
    {
        $array = ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue'];

        return strtr($str, $array);
    }

    public static function objImplode($sep, $arr)
    {
        $uids = [];
        foreach ($arr as $obj) {
            $uids[] = $obj->uid;
        }

        return implode($sep, $uids);
    }

    /**
     * Validate a search string for minimum length. All smaller parts are removed.
     *
     * @param string $searchterm
     * @param int    $minLength
     *
     * @return string
     */
    public static function validateSearchString($searchterm, $minLength = 3)
    {
        // Suchteile splitten
        $ret = [];
        $arr = Strings::trimExplode(' ', $searchterm);
        foreach ($arr as $term) {
            if (strlen($term) >= $minLength) {
                $ret[] = $term;
            }
        }

        return trim(implode(' ', $ret));
    }

    /**
     * Translates a string starting with LLL:
     * This method works in FE and BE as well.
     *
     * @param string $title
     *
     * @return string
     */
    public static function translateLLL($title)
    {
        if ('LLL:' === substr($title, 0, 4)) {
            // Prefer TSFE in FE_MODE.
            if (TYPO3_MODE == 'FE' && array_key_exists('TSFE', $GLOBALS)) {
                return $GLOBALS['TSFE']->sL($title);
            } elseif (array_key_exists('LANG', $GLOBALS)) {
                return $GLOBALS['LANG']->sL($title);
            }

            return '';
        }

        return $title;
    }

    /**
     * Create a short hash from all values in $params. This can be used as additional link parameter to
     * ensure submitted parameters are not modified.
     * The order of values doesn't matter.
     *
     * @param array  $params
     * @param string $salt   a secret salt string
     * @param bool   $daily  Hash values changed with every new day
     *
     * @return string with 8 characters
     */
    public static function createHash($params, $salt = 'secret', $daily = true)
    {
        $str = '';
        if ($daily) {
            $str .= tx_rnbase_util_Dates::getTodayDateString();
        }
        sort($params);
        foreach ($params as $value) {
            if (is_array($value)) {
                $value = 1;
            } // Arrays werden erstmal nicht unterstützt
            $str .= strval($value);
        }
        $str .= $salt;
        $hash = md5($str);

        return substr($hash, 5, 8);
    }

    /**
     * Start TimeTrack section.
     *
     * @param string $message
     */
    public static function pushTT($label, $message = '')
    {
        if (self::$enableTT && is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->push($label, $message);
        }
    }

    /**
     * End TimeTrack section.
     */
    public static function pullTT()
    {
        if (self::$enableTT && is_object($GLOBALS['TT'])) {
            $GLOBALS['TT']->pull();
        }
    }

    /**
     * The TimeTracking uses a lot of memory. So it should be used for testcases only.
     * By default the timetracking is not enabled.
     *
     * @param bool $flag
     */
    public static function enableTimeTrack($flag)
    {
        self::$enableTT = $flag;
    }

    /**
     * Explode a list into an array.
     *
     * Explodes a string by any number of the given charactrs.
     * By default it uses comma, semicolon, colon and whitespace.
     *
     * The returned values are trimmed.
     * Method taken from tx_div
     *
     * @param   string      string to split
     * @param   string      regular expression that defines the splitter
     *
     * @return array with the results
     */
    public static function explode($value, $splitCharacters = ',;:\s')
    {
        $pattern = '/['.$splitCharacters.']+/';
        $results = preg_split($pattern, $value, -1, PREG_SPLIT_NO_EMPTY);
        $return = [];
        foreach ($results as $result) {
            $return[] = trim($result);
        }

        return (array) $return;
    }

    /**
     * Same method as tslib_pibase::pi_getPidList().
     *
     * @param string    $pidList Commaseparated list of pids
     * @param int|array $options The options array with deprecated recursive support
     *
     * @return string commaseparated list of pids
     */
    public static function getPidList(
        $pidList,
        $options = 0
    ) {
        if (!strcmp($pidList, '')) {
            $pidList = TYPO3::getTSFE()->id;
        }
        $options = is_array($options) ? $options : ['recursive' => $options];
        $options['recursive'] = Math::intInRange($options['recursive'], 0);
        $pidListArr = array_unique(Strings::trimExplode(',', $pidList, 1));

        $pidList = [];
        foreach ($pidListArr as $val) {
            $val = Math::intInRange($val, 0);
            if ($val) {
                $cObj = TYPO3::getContentObject();
                $list = $cObj->getTreeList(
                    (-1 * $val),
                    $options['recursive'],
                    0,
                    !empty($options['dontCheckEnableFields'])
                );
                if ($list) {
                    $pidList[] = $list;
                }
            }
        }

        return implode(',', $pidList);
    }

    /**
     * Returns a given CamelCasedString as an lowercase string with underscores.
     * Example: Converts BlogExample to blog_example, and minimalValue to minimal_value
     * Taken from t3lib_div for backward compatibility.
     *
     * @deprecated use Strings::camelCaseToLowerCaseUnderscored instead
     *
     * @param string $string: String to be converted to lowercase underscore
     *
     * @return string lowercase_and_underscored_string
     */
    public static function camelCaseToLowerCaseUnderscored($string)
    {
        self::logDeprecatedFunction();

        return Strings::camelCaseToLowerCaseUnderscored($string);
    }

    /**
     * Sendout an error mail.
     *
     * @param string    $mailAddr   commaseperated recipients
     * @param string    $actionName
     * @param \Exception $e
     * @param array     $options
     */
    public static function sendErrorMail($mailAddr, $actionName, Exception $e, array $options = [])
    {
        $ignoreMailLock = (array_key_exists('ignoremaillock', $options) && $options['ignoremaillock']);

        if (!$ignoreMailLock) {
            tx_rnbase::load('tx_rnbase_util_Lock');
            // Only one mail within one minute!
            $lock = tx_rnbase_util_Lock::getInstance('errormail', 60);
            if ($lock->isLocked()) {
                return;
            } else {
                $lock->lockProcess();
            }
        } else {
            $lock = null;
        }

        $textPart = self::getErrorMailText($e, $actionName);
        $htmlPart = self::getErrorMailHtml($e, $actionName);

        /* @var $mail \tx_rnbase_util_Mail */
        $mail = tx_rnbase::makeInstance('tx_rnbase_util_Mail');
        $mail->setSubject('Exception on site '.$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);

        $from = ConfigurationProcessor::getExtensionCfgValue('rn_base', 'fromEmail');
        $from = $from ? $from : 'error@'.self::getIndpEnv('TYPO3_HOST_ONLY');
        $mail->setFrom($from);

        $mail->setTo($mailAddr);
        $mail->setTextPart($textPart);
        $mail->setHtmlPart($htmlPart);

        if ($lock && !$ignoreMailLock) {
            // refresh the lock
            $lock->lockProcess();
        }

        $mail->send();
    }

    protected static function getErrorMailText($e, $actionName)
    {
        $textPart = 'This is an automatic email from TYPO3. Don\'t answer!'."\n\n";
        $textPart .= 'UNCAUGHT EXCEPTION FOR VIEW: '.$actionName."\n\n";
        $textPart .= 'Message: '.$e->getMessage()."\n\n";
        $textPart .= "Stacktrace:\n".$e->__toString()."\n";
        $textPart .= 'SITE_URL: '.self::getIndpEnv('TYPO3_SITE_URL')."\n";

        tx_rnbase::load('tx_rnbase_util_TYPO3');
        $textPart .= 'BE_USER: '.TYPO3::getBEUserUID()."\n";
        $textPart .= 'FE_USER: '.TYPO3::getFEUserUID()."\n";

        return $textPart;
    }

    protected static function getErrorMailHtml($e, $actionName)
    {
        $htmlPart = '<strong>This is an automatic email from TYPO3. Don\'t answer!</strong>';
        $htmlPart .= '<div><strong>UNCAUGHT EXCEPTION FOR VIEW: '.$actionName.'</strong></div>';
        $htmlPart .= '<p><strong>Message:</strong><br />'.$e->getMessage().'</p>';
        $htmlPart .= '<p><strong>Stacktrace:</strong><pre>'.$e->__toString().'</pre></p>';
        $htmlPart .= '<p><strong>SITE_URL</strong><br />'.self::getIndpEnv('TYPO3_SITE_URL').'</p>';

        $get = self::removePasswordParams($_GET);
        if (count($get)) {
            $htmlPart .= '<p><strong>_GET</strong><br />'.var_export($get, true).'</p>';
        }

        $post = self::removePasswordParams($_POST);
        if (count($post)) {
            $htmlPart .= '<p><strong>_POST</strong><br />'.var_export($post, true).'</p>';
        }

        $cookie = self::removePasswordParams($_COOKIE);
        if (count($cookie)) {
            $htmlPart .= '<p><strong>_COOKIE</strong><br />'.var_export($cookie, true).'</p>';
        }

        $htmlPart .= '<p><strong>_SERVER</strong><br />'.var_export(self::removePasswordParams($_SERVER), true).'</p>';
        if ($e instanceof \tx_rnbase_util_Exception) {
            $additional = $e->getAdditional();
            if ($additional) {
                $htmlPart .= '<p><strong>Additional Data:</strong><br />'.strval($additional).'</p>';
            }
        }

        tx_rnbase::load('tx_rnbase_util_TYPO3');
        $htmlPart .= '<p><strong>BE_USER:</strong> '.TYPO3::getBEUserUID().'</p>';
        $htmlPart .= '<p><strong>FE_USER:</strong> '.TYPO3::getFEUserUID().'</p>';

        return $htmlPart;
    }

    protected static function removePasswordParams(array $parameters)
    {
        foreach ($parameters as $parameterName => $parameterValue) {
            if (is_array($parameterValue)) {
                $parameters[$parameterName] = self::removePasswordParams($parameterValue);
            } elseif (preg_match('/passwor(t|d)/', $parameterName)) {
                unset($parameters[$parameterName]);
            }
        }

        return $parameters;
    }

    /**
     * Returns currently loaded LL files.
     *
     * @return array
     */
    public static function getLoadedLLFiles()
    {
        return array_keys($GLOBALS['LANG']->LL_files_cache);
    }

    /**
     * Abstraction method which returns System Environment Variables regardless of server OS, CGI/MODULE version etc. Basically this is SERVER variables for most of them.
     * This should be used instead of getEnv() and $_SERVER/ENV_VARS to get reliable values for all situations.
     *
     * @param   string      Name of the "environment variable"/"server variable" you wish to use. Valid values are SCRIPT_NAME, SCRIPT_FILENAME, REQUEST_URI, PATH_INFO, REMOTE_ADDR, REMOTE_HOST, HTTP_REFERER, HTTP_HOST, HTTP_USER_AGENT, HTTP_ACCEPT_LANGUAGE, QUERY_STRING, TYPO3_DOCUMENT_ROOT, TYPO3_HOST_ONLY, TYPO3_HOST_ONLY, TYPO3_REQUEST_HOST, TYPO3_REQUEST_URL, TYPO3_REQUEST_SCRIPT, TYPO3_REQUEST_DIR, TYPO3_SITE_URL, _ARRAY
     *
     * @return string value based on the input key, independent of server/os environment
     */
    public static function getIndpEnv($getEnvName)
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::getIndpEnv($getEnvName);
    }

    /**
     * Wrapper method for t3lib_div::milliseconds() or \TYPO3\CMS\Core\Utility\GeneralUtility::milliseconds().
     *
     * @return int The unixtime as milliseconds
     */
    public static function milliseconds()
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::milliseconds();
    }

    /**
     * Wrapper method for t3lib_div::logDeprecatedFunction() or \TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction().
     */
    public static function logDeprecatedFunction()
    {
        $utility = Typo3Classes::getGeneralUtilityClass();
        $utility::logDeprecatedFunction();
    }

    /**
     * @param string       $message
     * @param string       $title
     * @param number       $severity
     * @param string|false $storeInSession
     */
    public static function addFlashMessage($message, $title = '', $severity = 0, $storeInSession = false)
    {
        $flashMessage = tx_rnbase::makeInstance(
            Typo3Classes::getFlashMessageClass(),
            $message,
            $title,
            $severity,
            $storeInSession
        );

        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = tx_rnbase::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
        $flashMessageService->getMessageQueueByIdentifier()->enqueue($flashMessage);
    }
}
