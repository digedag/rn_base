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
 * Get a class name independent of the TYPO3 Version. The API
 * of the desired class should be the same
 *
 * @package 		TYPO3
 * @subpackage	 	rn_base
 * @author 			Hannes Bochmann <rene@system25.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_rnbase_util_Typo3Classes {

	const LOWER6 = 'lower6';
	const HIGHER6 = 'higher6';

	/**
	 * @return string
	 */
	public static function getFlashMessageClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_FlashMessage',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\Messaging\\FlashMessage'
		));
	}

	/**
	 * @return string
	 */
	public static function getFlashMessageQueueClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_FlashMessageQueue',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\Messaging\\FlashMessageQueue'
		));
	}

	/**
	 * @return string
	 */
	public static function getBackendFormEngineClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_tceforms',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Backend\\Form\\FormEngine'
		));
	}

	/**
	 * @return string
	 */
	public static function getBasicFileUtilityClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_basicFileFunctions',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\Utility\\File\\BasicFileUtility'
		));
	}

	/**
	 * @return string
	 */
	public static function getExtendedTypoScriptTemplateServiceClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_tsparser_ext',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService'
		));
	}

	/**
	 * @return string
	 */
	public static function getContentObjectRendererClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 'tslib_cObj',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'
		));
	}

	/**
	 * @return string
	 */
	public static function getTypoScriptFrontendControllerClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 'tslib_fe',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'
		));
	}

	/**
	 * @return string
	 */
	public static function getFrontendUserAuthenticationClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 'tslib_feUserAuth',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication'
		));
	}

	/**
	 * @return string
	 */
	public static function getCharsetConverterClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_cs',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\Charset\\CharsetConverter'
		));
	}

	/**
	 * @return string
	 */
	public static function getDataHandlerClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_tcemain',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\DataHandling\\DataHandler'
		));
	}

	/**
	 * @return string
	 */
	public static function getSpriteManagerClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_SpriteManager',
			self::HIGHER6 	=> 'TYPO3\\CMS\Backend\\Sprite\\SpriteManager'
		));
	}
	/**
	 * @return string
	 */
	public static function getTimeTrackClass() {
		// TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_timeTrack',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\TimeTracker\\NullTimeTracker'
		));
	}

	/**
	 * @return string
	 */
	public static function getCommandUtilityClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_exec',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\Utility\\CommandUtility'
		));
	}

	/**
	 * @return string
	 */
	public static function getMailMessageClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_mail_Message',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\Mail\\MailMessage'
		));
	}

	/**
	 * @return string
	 */
	public static function getHtmlParserClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_parsehtml',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\Html\\HtmlParser'
		));
	}

	/**
	 * @return string
	 * @see Tx_Rnbase_Utility_T3General for better usage
	 */
	public static function getGeneralUtilityClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_div',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility'
		));
	}

	/**
	 * @return string
	 */
	public static function getTypoScriptParserClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_TSparser',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\TypoScript\\Parser\\TypoScriptParser'
		));
	}

	/**
	 * @return string
	 */
	public static function getDocumentTemplateClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 'template',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate'
		));
	}

	/**
	 * @return string
	 */
	public static function getTemplateServiceClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_TStemplate',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\TypoScript\\TemplateService'
		));
	}

	/**
	 * @return string
	 */
	public static function getHttpUtilityClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 't3lib_utility_Http',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Core\\Utility\\HttpUtility'
		));
	}

	/**
	 * @return string
	 */
	public static function getTypoScriptFrontendControllerClass() {
		return self::getClassByCurrentTypo3Version(array(
			self::LOWER6	=> 'tslib_fe',
			self::HIGHER6 	=> 'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'
		));
	}

	/**
	 * @param array $possibleClasses
	 * @return string
	 */
	protected static function getClassByCurrentTypo3Version(array $possibleClasses) {
		return tx_rnbase_util_TYPO3::isTYPO60OrHigher() ?
			$possibleClasses[self::HIGHER6] : $possibleClasses[self::LOWER6];
	}
}
