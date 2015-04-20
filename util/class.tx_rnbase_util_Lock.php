<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2015 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase_configurations.php');

/**
 * util to handle locking of processes
 *
 * Usage:
 * $lock = tx_rnbase_util_Lock::getInstance('process-name', 1800);
 * if ($lock->isLocked()) {
 *     return FALSE;
 * }
 * $lock->lockProcess();
 * $this->doProcess()
 * $lock->unlockProcess();
 * return TRUE;
 *
 * @package tx_rnbase
 * @subpackage tx_rnbase_model
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_rnbase_util_Lock {

	/**
	 *
	 * @var string
	 */
	private $name = 'default';
	/**
	 *
	 * @var int
	 */
	private $lifeTime = 0;
	/**
	 *
	 * @var string
	 */
	private $logFile = NULL;

	/**
	 * creates a instance of the lock util.
	 *
	 * @param string $name
	 * @param int $lifeTime
	 * @return tx_rnbase_util_Lock
	 */
	public static function getInstance($name, $lifeTime = 0) {
		return tx_rnbase::makeInstance('tx_rnbase_util_Lock', $name, $lifeTime);
	}
	/**
	 * constructor
	 *
	 * @param string $name
	 * @param int $lifeTime
	 */
	function __construct($name, $lifeTime = 0) {
		$this->name = $name;
		$this->lifeTime = (int) $lifeTime;
	}

	/**
	 * returns the process name.
	 *
	 * @return string
	 */
	protected function getName() {
		return $this->name;
	}

	/**
	 * returns the lifetime of the lock.
	 *
	 * @return int
	 */
	protected function getLifeTime() {
		return $this->lifeTime;
	}

	/**
	 * returns the path to the lock file.
	 *
	 * @return string
	 */
	protected function getFile() {
		if ($this->logFile === NULL) {
			$this->logFile = PATH_site . 'typo3temp/rn_base/' . $this->getName() . '.lock';
		}
		return $this->logFile;
	}

	/**
	 * locks a process.
	 *
	 * @return boolean
	 */
	public function lockProcess()
	{
		if ($this->createLockFile()) {
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * unlocks a process.
	 *
	 * @return boolean
	 */
	public function unlockProcess()
	{
		if ($this->deleteLockFile()) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * check, if the process is locked.
	 * if there is no file, the process is not locked.
	 * is there a file, then check the lifetime of the lock
	 *
	 * @return boolean
	 */
	public function isLocked()
	{
		if (is_readable($this->getFile())) {
			$lastCall = (int) trim(file_get_contents($this->getFile()));
			if (
				!(
					$this->getLifeTime() > 0
					&& $lastCall < (time() - $this->getLifeTime())
				)
			) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * creates a lock file and stores the current time.
	 *
	 * @return boolean
	 */
	private function createLockFile()
	{
		$fileName = $this->getFile();

		if (!is_dir(dirname($fileName))) {
			t3lib_div::mkdir_deep(dirname($fileName));
		}

		file_put_contents($fileName, time());

		if (!is_readable($fileName)) {
			tx_rnbase::load('tx_rnbase_util_Logger');
			tx_rnbase_util_Logger::warn(
				'Lock file could not be created for "' . $this->getName() . '" process!',
				'rn_base',
				array(
					'process_name' => $this->getName(),
					'life_time' => $this->getLifeTime(),
					'lock_file' => $fileName,

				)
			);
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * deletes the lock file.
	 *
	 * @return boolean
	 */
	private function deleteLockFile()
	{
		if ($this->isLocked()) {
			unlink($this->getFile());
			return TRUE;
		}

		return FALSE;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Lock.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_Lock.php']);
}
