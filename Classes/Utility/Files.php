<?php

namespace Sys25\RnBase\Utility;

use Exception;
use Sys25\RnBase\Frontend\Marker\Templates;
use tx_rnbase;
use TYPO3\CMS\Core\Utility\PathUtility;
use ZipArchive;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2023 Rene Nitzsche
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
 * Contains some helpful methods for file handling.
 */
class Files
{
    /**
     * Returns content of a file. If it's an image the content of the file is not returned but rather an image tag is.
     * This method is taken from tslib_content
     * TODO: cache result.
     *
     * @param string The filename, being a TypoScript resource data type or a FAL-Reference (file:123)
     * @param string Additional parameters (attributes). Default is empty alt and title tags.
     *
     * @return string If jpg,gif,jpeg,png: returns image_tag with picture in. If html,txt: returns content string
     *
     * @see FILE()
     */
    public static function getFileResource($fName, $options = [])
    {
        if (!(is_object($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE']->tmpl))) {
            Misc::prepareTSFE(['force' => true]);
        }

        $incFile = self::getFalFilename($fName);
        if (null === $incFile) {
            $incFile = self::getFileName($fName);
        }
        $ret = '';
        if ($incFile) {
            // Im BE muss ein absoluter Pfad verwendet werden
            $fullPath = Environment::isBackend() ? Environment::getPublicPath().$incFile : $incFile;
            $utility = Typo3Classes::getGeneralUtilityClass();
            $fileinfo = $utility::split_fileref($incFile);
            if ($utility::inList('jpg,gif,jpeg,png', $fileinfo['fileext'])) {
                $imgFile = $incFile;
                $imgInfo = @getimagesize($imgFile);
                $addParams = isset($options['addparams']) ? $options['addparams'] : 'alt="" title=""';
                $ret = '<img src="'.$GLOBALS['TSFE']->absRefPrefix.$imgFile.'" width="'.$imgInfo[0].'" height="'.$imgInfo[1].'"'.self::getBorderAttr(' border="0"').' '.$addParams.' />';
            } elseif (file_exists($fullPath) && filesize($fullPath) < 1024 * 1024) {
                $ret = @file_get_contents($fullPath);
                $subpart = isset($options['subpart']) ? $options['subpart'] : '';
                if ($subpart) {
                    $ret = Templates::getSubpart($ret, $subpart);
                }
            }
        }

        return $ret;
    }

    /**
     * Returns the reference to a 'resource' in TypoScript.
     * This could be from the filesystem if '/' is found in the value $fileFromSetup, else from the resource-list.
     *
     * @param string $file typoScript "resource" data type value
     *
     * @return string resulting filename, if any
     */
    public static function getFileName($file)
    {
        if (!TYPO3::isTYPO95OrHigher()) {
            return Templates::getTSTemplate()->getFileName($file);
        }
        /** @var \TYPO3\CMS\Frontend\Resource\FilePathSanitizer $fs */
        $fs = tx_rnbase::makeInstance(\TYPO3\CMS\Frontend\Resource\FilePathSanitizer::class);

        return $fs->sanitize($file, true);
    }

    /**
     * @return bool true if fName starts with file
     */
    public static function isFALReference($fName)
    {
        return Strings::isFirstPartOfStr($fName, 't3://file') || Strings::isFirstPartOfStr($fName, 'file:');
    }

    /**
     * Returns the 'border' attribute for an <img> tag only if the doctype is not xhtml_strict,xhtml_11 or xhtml_2 or if the config parameter 'disableImgBorderAttr' is not set.
     * This method is taken from tslib_content.
     *
     * @param string the border attribute
     *
     * @return string the border attribute
     */
    private static function getBorderAttr($borderAttr)
    {
        $utility = Typo3Classes::getGeneralUtilityClass();
        if (!$utility::inList('xhtml_strict,xhtml_11,xhtml_2', $GLOBALS['TSFE']->xhtmlDoctype) && !$GLOBALS['TSFE']->config['config']['disableImgBorderAttr']) {
            return $borderAttr;
        }

        return '';
    }

    /**
     * Check if a file exists and is readable within TYPO3.
     *
     * @param   string File name
     *
     * @return string file name with absolute path or FALSE
     *
     * @throws Exception
     */
    public static function checkFile($fName)
    {
        $absFile = self::getFileAbsFileName($fName);
        if (!(self::isAllowedAbsPath($absFile) && @is_file($absFile))) {
            throw new Exception('File not found: '.$fName);
        }
        if (!@is_readable($absFile)) {
            throw new Exception('File is not readable: '.$absFile);
        }

        return $absFile;
    }

    /**
     * Wrapper method from t3lib_div.
     *
     * Returns the absolute filename of a relative reference, resolves the "EXT:" prefix
     * (way of referring to files inside extensions) and checks that the file is inside
     * the \Sys25\RnBase\Utility\Environment::getPublicPath() of the TYPO3 installation and implies a check with
     * \TYPO3\CMS\Core\Utility\GeneralUtility::validPathStr().
     *
     * @param string $filename           The input filename/filepath/file:uid to evaluate
     * @param bool   $onlyRelative       if $onlyRelative is set (which it is by default), then only return values relative to the current \Sys25\RnBase\Utility\Environment::getPublicPath() is accepted
     * @param bool   $relToTYPO3_mainDir If $relToTYPO3_mainDir is set, then relative paths are relative to PATH_typo3 constant - otherwise (default) they are relative to \Sys25\RnBase\Utility\Environment::getPublicPath()
     *
     * @return string returns the absolute filename of $filename if valid, otherwise blank string
     */
    public static function getFileAbsFileName($fName, $onlyRelative = true, $relToTYPO3_mainDir = false)
    {
        $filepath = self::getFalFilename($fName);
        if (null === $filepath) {
            $utility = Typo3Classes::getGeneralUtilityClass();
            $filepath = $utility::getFileAbsFileName($fName, $onlyRelative, $relToTYPO3_mainDir);
        }

        return $filepath;
    }

    private static function getFalFilename($fName)
    {
        if (self::isFALReference($fName)) {
            /* @var \TYPO3\CMS\Core\Resource\FileRepository */
            $fileRepository = tx_rnbase::makeInstance('TYPO3\CMS\Core\Resource\FileRepository');
            if (preg_match('/(\d+)$/', $fName, $matches)) {
                $uid = (int) reset($matches);
                $fileObject = $fileRepository->findByUid($uid);

                return is_object($fileObject) ? $fileObject->getForLocalProcessing(false) : null;
            }
        }

        return null;
    }

    /**
     * Append file on directory
     * http://stackoverflow.com/a/15575293.
     */
    public static function join()
    {
        $paths = [];
        foreach (func_get_args() as $arg) {
            if ('' !== $arg) {
                $paths[] = $arg;
            }
        }

        return preg_replace('#/+#', '/', implode('/', $paths));
    }

    /**
     * Wir lassen als Dateinamen nur Buchstaben, Zahlen,
     * Bindestrich, Unterstrich und Punkt zu.
     * Umlaute und Sonderzeichen werden versucht in lesbare Buchstaben zu parsen.
     * Nicht zulässige Zeichen werden in einen Unterstrich umgewandelt.
     * Der Dateiuname wird optional in Kleinbuchstaben umgewandelt.
     *
     * @param string $name
     * @param bool   $forceLowerCase
     *
     * @return string
     */
    public static function cleanupFileName($name, $forceLowerCase = true)
    {
        $cleaned = $name;
        if (function_exists('iconv')) {
            $charset = Strings::isUtf8String($cleaned) ? 'UTF-8' : 'ISO-8859-1';
            $oldLocal = setlocale(LC_ALL, 0);
            setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'deu_deu', 'de', 'ge');
            $cleaned = iconv($charset, 'ASCII//TRANSLIT', $cleaned);
            setlocale(LC_ALL, $oldLocal);
        }
        $cleaned = preg_replace('/[^A-Za-z0-9-_.]/', '_', $cleaned);

        return $forceLowerCase ? strtolower($cleaned) : $cleaned;
    }

    /**
     * Create a zip-archive from a list of files.
     *
     * @param array  $files
     * @param string $destination full path of zip file
     * @param bool   $overwrite
     *
     * @return bool TRUE, if zip file was created
     */
    public static function makeZipFile($files = [], $destination = '', $overwrite = false)
    {
        if (!extension_loaded('zip')) {
            Logger::warn('PHP zip extension not loaded!', 'rn_base');

            return false;
        }
        // if the zip file already exists and overwrite is FALSE, return FALSE
        if (file_exists($destination) && !$overwrite) {
            return false;
        }

        // vars
        $valid_files = [];
        // if files were passed in...
        if (!is_array($files)) {
            return false;
        }
        foreach ($files as $file) {
            if (file_exists($file)) {
                $valid_files[] = $file;
            }
        }

        // if we have good files...
        if (!count($valid_files)) {
            return false;
        }

        // create the archive
        $zip = new ZipArchive();
        if (true !== $zip->open($destination, $overwrite ? ZipArchive::OVERWRITE : ZipArchive::CREATE)) {
            return false;
        }
        // add the files
        foreach ($valid_files as $file) {
            $filename = basename($file);
            $zip->addFile($file, iconv('UTF-8', 'IBM850', $filename));
        }

        // close the zip -- done!
        $zip->close();

        // check to make sure the file exists
        return file_exists($destination);
    }

    /**
     * Wrapper of mkdir_deep for the various TYPO3 Versions.
     *
     * (non-PHPdoc)
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep()
     */
    public static function mkdir_deep($directory, $deepDirectory = '')
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        $utility::mkdir_deep($directory, $deepDirectory);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::writeFile()
     *
     * @param string $file              Filepath to write to
     * @param string $content           Content to write
     * @param bool   $changePermissions If TRUE, permissions are forced to be set
     *
     * @return bool TRUE if the file was successfully opened and written to
     */
    public static function writeFile($file, $content, $changePermissions = false)
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::writeFile($file, $content, $changePermissions);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::writeFile()
     *
     * @param string $path           Absolute path to folder, see PHP rmdir() function. Removes trailing slash internally.
     * @param bool   $removeNonEmpty Allow deletion of non-empty directories
     *
     * @return bool TRUE if @rmdir went well!
     */
    public static function rmdir($path, $removeNonEmpty = false)
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::rmdir($path, $removeNonEmpty);
    }

    /**
     * (non-PHPdoc).
     *
     * @see PathUtility::isAbsolutePath()
     *
     * @param string $path File path to evaluate
     *
     * @return bool
     */
    public static function isAbsPath($path)
    {
        if (TYPO3::isTYPO115OrHigher()) {
            return PathUtility::isAbsolutePath($path);
        }

        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::isAbsPath($path);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::isAllowedAbsPath()
     *
     * @param string $path File path to evaluate
     *
     * @return bool
     */
    public static function isAllowedAbsPath($path)
    {
        $utility = Typo3Classes::getGeneralUtilityClass();

        return $utility::isAllowedAbsPath($path);
    }
}
