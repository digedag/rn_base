<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2014 Rene Nitzsche
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

tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_util_Strings');

/**
 * Contains some helpful methods for file handling
 */
class tx_rnbase_util_Files {
	/**
	 * Returns content of a file. If it's an image the content of the file is not returned but rather an image tag is.
	 * This method is taken from tslib_content
	 * TODO: cache result
	 *
	 * @param string The filename, being a TypoScript resource data type or a FAL-Reference (file:123)
	 * @param string Additional parameters (attributes). Default is empty alt and title tags.
	 * @return string If jpg,gif,jpeg,png: returns image_tag with picture in. If html,txt: returns content string
	 * @see FILE()
	 */
	public static function getFileResource($fName, $options=array())	{
		if(!(is_object($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE']->tmpl))) {
			tx_rnbase::load('tx_rnbase_util_Misc');
			tx_rnbase_util_Misc::prepareTSFE(array('force'=>TRUE));
		}
		if(self::isFALReference($fName)) {
				/** @var FileRepository $fileRepository */
				$fileRepository = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Resource\FileRepository');
				$fileObject = $fileRepository->findByUid(intval(substr($fName, 5)));
				$incFile = is_object($fileObject) ? $fileObject->getForLocalProcessing(FALSE) : FALSE;
		}
		else
			$incFile = $GLOBALS['TSFE']->tmpl->getFileName($fName);
		if ($incFile)	{
			// Im BE muss ein absoluter Pfad verwendet werden
			$fullPath = (TYPO3_MODE == 'BE') ? PATH_site.$incFile : $incFile;
			$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
			$fileinfo = $utility::split_fileref($incFile);
			if ($utility::inList('jpg,gif,jpeg,png', $fileinfo['fileext']))	{
				$imgFile = $incFile;
				$imgInfo = @getImageSize($imgFile);
				$addParams= isset($options['addparams']) ? $options['addparams'] : 'alt="" title=""';
				$ret = '<img src="'.$GLOBALS['TSFE']->absRefPrefix.$imgFile.'" width="'.$imgInfo[0].'" height="'.$imgInfo[1].'"'.$this->getBorderAttr(' border="0"').' '.$addParams.' />';
			} elseif (filesize($fullPath)<1024*1024) {
				$ret = @file_get_contents($fullPath);
				$subpart = isset($options['subpart']) ? $options['subpart'] : '';
				if($subpart) {
					$htmlParserClass = tx_rnbase_util_Typo3Classes::getHtmlParserClass();
					$ret = $htmlParserClass::getSubpart($ret, $subpart);
				}
			}
		}
		return $ret;
	}

	/**
	 * @return boolean TRUE if fName starts with file:
	 */
	public static function isFALReference($fName) {
		return tx_rnbase_util_TYPO3::isTYPO60OrHigher() ?
				tx_rnbase_util_Strings::isFirstPartOfStr($fName, 'file:')
			: FALSE;
	}
	/**
	 * Returns the 'border' attribute for an <img> tag only if the doctype is not xhtml_strict,xhtml_11 or xhtml_2 or if the config parameter 'disableImgBorderAttr' is not set.
	 * This method is taken from tslib_content
	 *
	 * @param string the border attribute
	 * @return string the border attribute
	 */
	private static function getBorderAttr($borderAttr) {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		if (!$utility::inList('xhtml_strict,xhtml_11,xhtml_2', $GLOBALS['TSFE']->xhtmlDoctype) && !$GLOBALS['TSFE']->config['config']['disableImgBorderAttr']) {
			return $borderAttr;
		}
	}

	/**
	 * Check if a file exists and is readable within TYPO3.
	 * @param	string File name
	 * @return string File name with absolute path or FALSE.
	 * @throws Exception
	 */
	public static function checkFile($fName)	{
		$absFile = self::getFileAbsFileName($fName);
		if(!(self::isAllowedAbsPath($absFile) && @is_file($absFile))) {
			throw new Exception('File not found: ' . $fName);
		}
		if(!@is_readable($absFile)) {
			throw new Exception('File is not readable: '.$absFile);
		}
		return $absFile;
	}

	/**
	 * Wrapper method from t3lib_div.
	 *
	 * Returns the absolute filename of a relative reference, resolves the "EXT:" prefix
	 * (way of referring to files inside extensions) and checks that the file is inside
	 * the PATH_site of the TYPO3 installation and implies a check with
	 * \TYPO3\CMS\Core\Utility\GeneralUtility::validPathStr().
	 *
	 * @param string $filename The input filename/filepath to evaluate
	 * @param boolean $onlyRelative If $onlyRelative is set (which it is by default), then only return values relative to the current PATH_site is accepted.
	 * @param boolean $relToTYPO3_mainDir If $relToTYPO3_mainDir is set, then relative paths are relative to PATH_typo3 constant - otherwise (default) they are relative to PATH_site
	 * @return string Returns the absolute filename of $filename if valid, otherwise blank string.
	 */
	public static function getFileAbsFileName($fName, $onlyRelative = TRUE, $relToTYPO3_mainDir = FALSE)	{
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		return $utility::getFileAbsFileName($fName, $onlyRelative, $relToTYPO3_mainDir);
	}
	/**
	 * Append file on directory
	 * http://stackoverflow.com/a/15575293
	 */
	public static function join()	{
		$paths = array();
		foreach (func_get_args() as $arg) {
			if ($arg !== '') { $paths[] = $arg; }
		}
		return preg_replace('#/+#','/',join('/', $paths));
	}

	/**
	 * Wir lassen als Dateinamen nur Buchstaben, Zahlen,
	 * Bindestrich, Unterstrich und Punkt zu.
	 * Umlaute und Sonderzeichen werden versucht in lesbare Buchstaben zu parsen.
	 * Nicht zulässige Zeichen werden in einen Unterstrich umgewandelt.
	 * Der Dateiuname wird optional in Kleinbuchstaben umgewandelt.
	 *
	 * @param string $name
	 * @param boolean $forceLowerCase
	 * @return string
	 */
	public static function cleanupFileName($name, $forceLowerCase=TRUE) {
		$cleaned = $name;
		if (function_exists('iconv')) {
			tx_rnbase::load('tx_rnbase_util_Strings');
			$charset = tx_rnbase_util_Strings::isUtf8String($cleaned)
			? 'UTF-8' : 'ISO-8859-1';
			$oldLocal = setlocale(LC_ALL, 0);
			setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'deu_deu', 'de', 'ge');
			$cleaned = iconv($charset, 'ASCII//TRANSLIT', $cleaned);
			setlocale(LC_ALL, $oldLocal);
		}
		$cleaned = preg_replace('/[^A-Za-z0-9-_.]/', '_', $cleaned);
		return $forceLowerCase ? strtolower($cleaned) : $cleaned;
	}
	/**
	 * Create a zip-archive from a list of files
	 *
	 * @param array $files
	 * @param string $destination full path of zip file
	 * @param boolean $overwrite
	 * @return boolean TRUE, if zip file was created
	 */
	public static function makeZipFile($files = array(), $destination = '', $overwrite = FALSE) {
		if(!extension_loaded('zip')) {
			tx_rnbase::load('tx_rnbase_util_Logger');
			tx_rnbase_util_Logger::warn('PHP zip extension not loaded!', 'rn_base');
			return false;
		}
		//if the zip file already exists and overwrite is FALSE, return FALSE
		if(file_exists($destination) && !$overwrite) { return FALSE; }

		//vars
		$valid_files = array();
		//if files were passed in...
		if(!is_array($files)) return FALSE;
		foreach($files as $file) {
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}

		//if we have good files...
		if(!count($valid_files)) return FALSE;

		//create the archive
		$zip = new ZipArchive();
		if($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== TRUE) {
			return FALSE;
		}
		//add the files
		foreach($valid_files as $file) {
			$filename = basename($file);
			$zip->addFile($file, iconv('UTF-8', 'IBM850', $filename));
		}

		//close the zip -- done!
		$zip->close();

		//check to make sure the file exists
		return file_exists($destination);
	}

	/**
	 * Wrapper of mkdir_deep for the various TYPO3 Versions
	 *
	 * (non-PHPdoc)
	 * @see t3lib_div::mkdir_deep()
	 * @see TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep()
	 */
	static public function mkdir_deep($directory, $deepDirectory = '') {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		return $utility::mkdir_deep($directory, $deepDirectory);
	}

	/**
	 * (non-PHPdoc)
	 * @see t3lib_div::writeFile()
	 * @see TYPO3\CMS\Core\Utility\GeneralUtility::writeFile()
	 *
	 * @param string $file Filepath to write to
	 * @param string $content Content to write
	 * @param boolean $changePermissions If TRUE, permissions are forced to be set
	 * @return boolean TRUE if the file was successfully opened and written to.
	 */
	static public function writeFile($file, $content, $changePermissions = FALSE) {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		return $utility::writeFile($file, $content, $changePermission);
	}

	/**
	 * (non-PHPdoc)
	 * @see t3lib_div::writeFile()
	 * @see TYPO3\CMS\Core\Utility\GeneralUtility::writeFile()
	 *
	 * @param string $path Absolute path to folder, see PHP rmdir() function. Removes trailing slash internally.
	 * @param boolean $removeNonEmpty Allow deletion of non-empty directories
	 * @return boolean TRUE if @rmdir went well!
	 */
	static public function rmdir($path, $removeNonEmpty = FALSE) {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		return $utility::rmdir($path, $removeNonEmpty);
	}

	 /**
	 * (non-PHPdoc)
	 * @see t3lib_div::isAbsPath()
	 * @see TYPO3\CMS\Core\Utility\GeneralUtility::isAbsPath()
	 *
	 * @param string $path File path to evaluate
	 * @return bool
	 */
	public static function isAbsPath($path) {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		return $utility::isAbsPath($path);
	}

 	/**
	 * (non-PHPdoc)
	 * @see t3lib_div::isAllowedAbsPath()
	 * @see TYPO3\CMS\Core\Utility\GeneralUtility::isAllowedAbsPath()
	 *
	 * @param string $path File path to evaluate
	 * @return bool
	 */
	public static function isAllowedAbsPath($path) {
		$utility = tx_rnbase_util_Typo3Classes::getGeneralUtilityClass();
		return $utility::isAllowedAbsPath($path);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Files.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Files.php']);
}