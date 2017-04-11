<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2015 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Tx_Rnbase_Backend_Utility
 *
 * Wrapper für t3lib_BEfunc bzw \TYPO3\CMS\Backend\Utility\BackendUtility
 *
 * @package TYPO3
 * @subpackage rn_base
 * @author Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *		  GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Backend_Utility
{

	/**
	 * Magic method to forward the call to the right be util.
	 *
	 * @param string $method
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic($method, array $arguments)
	{
		return call_user_func_array(
			array(
				static::getBackendUtilityClass(),
				$method
			),
			$arguments
		);
	}

	/**
	 * Returns the be util class depending on TYPO3 version.
	 *
	 * @return string
	 */
	protected static function getBackendUtilityClass()
	{
		if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			$backendUtilityClass = '\TYPO3\CMS\Backend\Utility\BackendUtility';
		} else {
			$backendUtilityClass = 't3lib_BEfunc';
		}

		return $backendUtilityClass;
	}

	/**
	 * Generates a token and returns a parameter for the URL
	 *
	 * @param string $formName
	 * @param string $tokenName
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @deprecated since TYPO3 7
	 *
	 * @return string A URL GET variable including ampersand
	 */
	public static function getUrlToken($formName = 'securityToken', $tokenName = 'formToken')
	{
		\TYPO3\CMS\Core\Utility\GeneralUtility::logDeprecatedFunction();
		$formProtection = \TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get();
		return '&' . $tokenName . '=' . $formProtection->generateToken($formName);
	}

	/**
	 * Checks if we are in dispatch mode.
	 *
	 * @return bool
	 */
	public static function isDispatchMode()
	{
		return $GLOBALS['MCONF']['script'] === '_DISPATCH';
	}
}
