<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2015 Rene Nitzsche
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
 * tx_rnbase_util_Typo3Classes
 *
 * Get a class name independent of the TYPO3 Version
 *
 * @package 		TYPO3
 * @subpackage	 	rn_base
 * @author 			Hannes Bochmann <rene@system25.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_util_Typo3Classes {

	/**
	 * @return string
	 */
	static public function getFlashMessageClass() {
		return self::getClassByCurrentTypo3Version(array(
			'lower6'	=> 't3lib_FlashMessage',
			'higher4' 	=> '\\TYPO3\\CMS\\Core\\Messaging\\FlashMessage'
		));
	}

	/**
	 * @return string
	 */
	static public function getFlashMessageQueueClass() {
		return self::getClassByCurrentTypo3Version(array(
			'lower6'	=> 't3lib_FlashMessageQueue',
			'higher4' 	=> '\\TYPO3\\CMS\\Core\\Messaging\\FlashMessageQueue'
		));
	}

	/**
	 * @return string
	 */
	static public function getBackendFormEngineClass() {
		return self::getClassByCurrentTypo3Version(array(
			'lower6'	=> 't3lib_tceforms',
			'higher4' 	=> '\\TYPO3\\CMS\\Backend\\Form\\FormEngine'
		));
	}

	/**
	 * @return string
	 */
	static public function getBasicFileUtilityClass() {
		return self::getClassByCurrentTypo3Version(array(
			'lower6'	=> 't3lib_basicFileFunctions',
			'higher4' 	=> '\\TYPO3\\CMS\\Core\\Utility\\File\\BasicFileUtility'
		));
	}

	/**
	 * @return string
	 */
	static public function getExtendedTypoScriptTemplateServiceClass() {
		return self::getClassByCurrentTypo3Version(array(
			'lower6'	=> 't3lib_tsparser_ext',
			'higher4' 	=> '\\TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService'
		));
	}

	/**
	 * @param array $possibleClasses
	 * @return string
	 */
	static protected function getClassByCurrentTypo3Version(array $possibleClasses) {
		return tx_rnbase_util_TYPO3::isTYPO60OrHigher() ?
			$possibleClasses['higher4'] : $possibleClasses['lower6'];
	}
}