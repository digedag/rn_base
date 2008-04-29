<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Rene Nitzsche
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

/**
 * Contains some helpful methods
 */
class tx_rnbase_util_Misc {

	/**
	 * Returns a service
	 * Mayday is raised if service not found.
	 *
	 * @param string $type
	 * @param string $subType
	 * @return t3lib_svbase
	 */
	static function getService($type, $subType) {
    $srv = t3lib_div::makeInstanceService($type, $subType);
    if(!is_object($srv)) {
    	tx_div::load('tx_rnbase_util_Misc');
      return self::mayday('Service ' . $type . ' - ' . $subType . ' not found!');;
    }
    return $srv;
	}
	/**
	 * Returns an array with all subtypes for given service key.
	 *
	 * @param string $type
	 */
	static function lookupServices($serviceType) {
		global $T3_SERVICES;
		$priority = array(); // Remember highest priority
		$services = array();
		if(is_array($T3_SERVICES[$serviceType])) {
			foreach($T3_SERVICES[$serviceType] As $key => $info) {
				if($info['available'] AND (!isset($priority[$info['subtype']]) || $info['priority'] >= $priority[$info['subtype']]) ) {
					$priority[$info['subtype']] = $info['priority'];
					$services[$info['subtype']] = $info;
				}
			}
		}
		return $services;
	}

	/**
	 * Calls a hook
	 *
	 * @param string $extKey
	 * @param string $hookKey
	 * @param array $params
	 */
	function callHook($extKey, $hookKey, $params) {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey][$hookKey])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey][$hookKey] as $funcRef) {
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}
	}
		
	/**
	 * Stops PHP execution : die() if some critical error appeared
   * This method is taken from the great ameos_formidable extension.
	 * 
	 * @param	string		$msg: the error message
	 * @return	void
	 */
	function mayday($msg, $extKey = '') {
		$aTrace		= debug_backtrace();
		$aLocation	= array_shift($aTrace);
		$aTrace1	= array_shift($aTrace);
		$aTrace2	= array_shift($aTrace);
		$aTrace3	= array_shift($aTrace);
		$aTrace4	= array_shift($aTrace);

		$aDebug = array();

		$aDebug[] = '<h2 id="backtracetitle">Call stack</h2>';
		$aDebug[] = '<div class="backtrace">';
		$aDebug[] = '<span class="notice"><b>Call 0: </b>' . str_replace(PATH_site, '/', $aLocation['file']) . ':' . $aLocation['line']  . ' | <b>' . $aTrace1['class'] . $aTrace1['type'] . $aTrace1['function'] . '</b></span><br/>With parameters: ' . (!empty($aTrace1['args']) ? self::viewMixed($aTrace1['args']) : ' no parameters');
		$aDebug[] = '<hr/>';
		$aDebug[] = '<span class="notice"><b>Call -1: </b>' . str_replace(PATH_site, '/', $aTrace1['file']) . ':' . $aTrace1['line']  . ' | <b>' . $aTrace2['class'] . $aTrace2['type'] . $aTrace2['function'] . '</b></span><br />With parameters: ' . (!empty($aTrace2['args']) ? self::viewMixed($aTrace2['args']) : ' no parameters');
		$aDebug[] = '<hr/>';
		$aDebug[] = '<span class="notice"><b>Call -2: </b>' . str_replace(PATH_site, '/', $aTrace2['file']) . ':' . $aTrace2['line']  . ' | <b>' . $aTrace3['class'] . $aTrace3['type'] . $aTrace3['function'] . '</b></span><br />With parameters: ' . (!empty($aTrace3['args']) ? self::viewMixed($aTrace3['args']) : ' no parameters');
		$aDebug[] = '<hr/>';
		$aDebug[] = '<span class="notice"><b>Call -3: </b>' . str_replace(PATH_site, '/', $aTrace3['file']) . ':' . $aTrace3['line']  . ' | <b>' . $aTrace4['class'] . $aTrace4['type'] . $aTrace4['function'] . '</b></span><br />With parameters: ' . (!empty($aTrace4['args']) ? self::viewMixed($aTrace4['args']) : ' no parameters');
		$aDebug[] = '<hr/>';

		if(is_callable(array('t3lib_div', 'debug_trail'))) {
			$aDebug[] = '<span class="notice">' . t3lib_div::debug_trail() . '</span>';
			$aDebug[] = '<hr/>';
		}

		$aDebug[] = '</div>';

		$aDebug[] = '<br/>';

		$sContent =	'<h1 id="title">Mayday</h1>';
		$sContent .= '<div id="errormessage">' . $msg . '</div>';
		$sContent .= '<hr />';
		$sContent .= implode('', $aDebug);

		$sPage =<<<MAYDAYPAGE
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

		die($sPage);
	}

	/**
	 * Creates a html view for a php object
   * This method is taken from the great ameos_formidable extension.
	 * 
	 * @param mixed $mMixed
	 * @param boolean $bRecursive
	 * @param int $iLevel
	 * @return string
	 */
	function viewMixed($mMixed, $bRecursive = TRUE, $iLevel=0) {

		$sStyle = "font-family: Verdana; font-size: 9px;";
		$sStyleBlack = $sStyle . "color: black;";
		$sStyleRed = $sStyle . "color: red;";
		$sStyleGreen = $sStyle . "color: green;";

		$aBgColors = array(
			"FFFFFF", "F8F8F8", "EEEEEE", "E7E7E7", "DDDDDD", "D7D7D7", "CCCCCC", "C6C6C6", "BBBBBB", "B6B6B6", "AAAAAA", "A5A5A5", "999999", "949494", "888888", "848484", "777777", "737373"
		);

		if(is_array($mMixed)) {

			$result="<table border=1 style='border: 1px solid silver' cellpadding=1 cellspacing=0 bgcolor='#" . $aBgColors[$iLevel] . "'>";

			if(!count($mMixed)) {
				$result.= "<tr><td><span style='" . $sStyleBlack . "'><b>".htmlspecialchars("EMPTY!")."</b></span></td></tr>";
			} else {
				while(list($key, $val)=each($mMixed)) {

					$result.= "<tr><td valign='top'><span style='" . $sStyleBlack . "'>".htmlspecialchars((string)$key)."</span></td><td>";

					if(is_array($val))	{
						$result.=self::viewMixed($val, $bRecursive, $iLevel + 1);
					} else {
						$result.= "<span style='" . $sStyleRed . "'>".self::viewMixed($val, $bRecursive, $iLevel + 1)."<br /></span>";
					}

					$result.= "</td></tr>";
				}
			}

			$result.= "</table>";

		} elseif(is_resource($mMixed)) {
			$result = "<span style='" . $sStyleGreen . "'>RESOURCE: </span>" . $mMixed;
		} elseif(is_object($mMixed)) {
			if($bRecursive) {
				$result = "<span style='" . $sStyleGreen . "'>OBJECT (" . get_class($mMixed) .") : </span>" . self::viewMixed(get_object_vars($mMixed), FALSE, $iLevel + 1);
			} else {
				$result = "<span style='" . $sStyleGreen . "'>OBJECT (" . get_class($mMixed) .") : !RECURSION STOPPED!</span>";// . t3lib_div::view_array(get_object_vars($mMixed), FALSE);
			}
		} elseif(is_bool($mMixed)) {
			$result = "<span style='" . $sStyleGreen . "'>BOOLEAN: </span>" . ($mMixed ? "TRUE" : "FALSE");
		} elseif(is_string($mMixed)) {
			if(empty($mMixed)) {
				$result = "<span style='" . $sStyleGreen . "'>STRING(0)</span>";
			} else {
				$result = "<span style='" . $sStyleGreen . "'>STRING(" . strlen($mMixed) . "): </span>" . nl2br(htmlspecialchars((string)$mMixed));
			}
		} elseif(is_null($mMixed)) {
			$result = "<span style='" . $sStyleGreen . "'>!NULL!</span>";
		} elseif(is_integer($mMixed)) {
			$result = "<span style='" . $sStyleGreen . "'>INTEGER: </span>" . $mMixed;
		} else {
			$result = "<span style='" . $sStyleGreen . "'>MIXED: </span>" . nl2br(htmlspecialchars(strVal($mMixed)));
		}

		return $result;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Misc.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Misc.php']);
}

?>