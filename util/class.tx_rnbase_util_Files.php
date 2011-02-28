<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Rene Nitzsche
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase_configurations.php');
require_once(PATH_t3lib."class.t3lib_parsehtml.php");

/**
 * Contains some helpful methods for file handling
 */
class tx_rnbase_util_Files {
	/**
	 * Returns content of a file. If it's an image the content of the file is not returned but rather an image tag is.
	 * This method is taken from tslib_content
	 * TODO: cache result
	 *
	 * @param string The filename, being a TypoScript resource data type
	 * @param string Additional parameters (attributes). Default is empty alt and title tags.
	 * @return string If jpg,gif,jpeg,png: returns image_tag with picture in. If html,txt: returns content string
	 * @see FILE()
	 */
	public static function getFileResource($fName, $options=array())	{
		if(!is_object($GLOBALS['TSFE'])) {
			tx_rnbase::load('tx_rnbase_util_Misc');
			tx_rnbase_util_Misc::prepareTSFE();
		}
		$incFile = $GLOBALS['TSFE']->tmpl->getFileName($fName);
		if ($incFile)	{
			// Im BE muss ein absoluter Pfad verwendet werden
			$fullPath = (TYPO3_MODE == 'BE') ? PATH_site.$incFile : $incFile;
			$fileinfo = t3lib_div::split_fileref($incFile);
			if (t3lib_div::inList('jpg,gif,jpeg,png',$fileinfo['fileext']))	{
				$imgFile = $incFile;
				$imgInfo = @getImageSize($imgFile);
				$addParams= isset($options['addparams']) ? $options['addparams'] : 'alt="" title=""';
				$ret = '<img src="'.$GLOBALS['TSFE']->absRefPrefix.$imgFile.'" width="'.$imgInfo[0].'" height="'.$imgInfo[1].'"'.$this->getBorderAttr(' border="0"').' '.$addParams.' />';
			} elseif (filesize($fullPath)<1024*1024) {
				$ret = @file_get_contents($fullPath);
				$subpart = isset($options['subpart']) ? $options['subpart'] : '';
				if($subpart) {
					$ret = t3lib_parsehtml::getSubpart($ret,$subpart);
				}
			}
		}
		return $ret;
	}

	/**
	 * Returns the 'border' attribute for an <img> tag only if the doctype is not xhtml_strict,xhtml_11 or xhtml_2 or if the config parameter 'disableImgBorderAttr' is not set.
	 * This method is taken from tslib_content
	 *
	 * @param string the border attribute
	 * @return string the border attribute
	 */
	private static function getBorderAttr($borderAttr) {
		if (!t3lib_div::inList('xhtml_strict,xhtml_11,xhtml_2',$GLOBALS['TSFE']->xhtmlDoctype) && !$GLOBALS['TSFE']->config['config']['disableImgBorderAttr']) {
			return $borderAttr;
		}
	}

	/**
	 * Check if a file exists and is readable within TYPO3.
	 * @param	string File name
	 * @return string File name with absolute path or FALSE.
	 * @throws Exception
	 */
	public static function checkFile ($fName)	{
		$absFile = t3lib_div::getFileAbsFileName($fName);
		if(!(t3lib_div::isAllowedAbsPath($absFile) && @is_file($absFile))) {
			throw new Exception('File not found: '.$absFile);
		}
		if(!@is_readable($absFile)) {
			throw new Exception('File is not readable: '.$absFile);
		}
		return $absFile;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Files.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Files.php']);
}

?>