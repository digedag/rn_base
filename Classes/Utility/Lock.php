<?php

namespace Sys25\RnBase\Utility;

use tx_rnbase;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2021 Rene Nitzsche (rene@system25.de)
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

/**
 * Util to handle locking of processes.
 *
 * Usage:
 * $lock = tx_rnbase_util_Lock::getInstance('process-name', 1800);
 * if ($lock->isLocked()) {
 *     return false;
 * }
 * $lock->lockProcess();
 * $this->doProcess()
 * $lock->unlockProcess();
 * return true;
 *
 * @author Michael Wagner
 */
class Lock
{
    /**
     * The name of the lock.
     *
     * @var string
     */
    private $name = 'default';

    /**
     * The lifetime of the lock.
     *
     * @var int
     */
    private $lifeTime = 0;

    /**
     * The log ressource.
     *
     * @var string
     */
    private $logFile = null;

    /**
     * Creates a instance of the lock util.
     *
     * @param string $name
     * @param int    $lifeTime
     *
     * @return Lock
     */
    public static function getInstance($name, $lifeTime = 0)
    {
        return tx_rnbase::makeInstance('tx_rnbase_util_Lock', $name, $lifeTime);
    }

    /**
     * Constructor.
     *
     * @param string $name
     * @param int    $lifeTime
     */
    public function __construct($name, $lifeTime = 0)
    {
        $this->name = $name;
        $this->lifeTime = (int) $lifeTime;
    }

    /**
     * Returns the process name.
     *
     * @return string
     */
    protected function getName()
    {
        return $this->name;
    }

    /**
     * Returns the lifetime of the lock.
     *
     * @return int
     */
    protected function getLifeTime()
    {
        return $this->lifeTime;
    }

    /**
     * Returns the path to the lock file.
     *
     * @return string
     */
    protected function getFile()
    {
        if (null === $this->logFile) {
            if (\Sys25\RnBase\Utility\TYPO3::isTYPO95OrHigher()) {
                $folder = \TYPO3\CMS\Core\Core\Environment::getVarPath().'/lock/rn_base/';
            } else {
                $folder = \Sys25\RnBase\Utility\Environment::getPublicPath().'typo3temp/rn_base/';
            }
            if (!is_dir($folder)) {
                \tx_rnbase_util_Files::mkdir_deep($folder);
            }
            $this->logFile = $folder.$this->getName().'.lock';
        }

        return $this->logFile;
    }

    /**
     * Locks a process.
     *
     * @return bool
     */
    public function lockProcess()
    {
        if ($this->createLockFile()) {
            return true;
        }

        return false;
    }

    /**
     * Unlocks a process.
     *
     * @return bool
     */
    public function unlockProcess()
    {
        if ($this->deleteLockFile()) {
            return true;
        }

        return false;
    }

    /**
     * Check, if the process is locked.
     *
     * If there is no file, the process is not locked.
     * Is there a file, then check the lifetime of the lock
     *
     * @return bool
     */
    public function isLocked()
    {
        // when the file is on a NFS it might happen that file_get_contents
        // can't find the file. This is due to the fact that the NFS
        // most likely will cache file information and the file might
        // been created only a few moments ago. So we work around this
        // by opening and closing a file handle for the folder of the file
        // which will invalidate the NFS cache.
        // @see https://stackoverflow.com/questions/41723458/php-file-exists-or-is-file-does-not-answer-correctly-for-10-20s-on-nfs-files-ec
        closedir(opendir(dirname($this->getFile())));
        if (is_readable($this->getFile()) && file_exists($this->getFile())) {
            $lastCall = (int) trim(file_get_contents($this->getFile()));
            if (!(
                $this->getLifeTime() > 0 &&
                $lastCall < (time() - $this->getLifeTime())
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates a lock file and stores the current time.
     *
     * @return bool
     */
    private function createLockFile()
    {
        $fileName = $this->getFile();

        if (!is_dir(dirname($fileName))) {
            Files::mkdir_deep(dirname($fileName));
        }
        if (!Files::writeFile($fileName, time(), true)) {
            Logger::warn(
                'Lock file could not be created for "'.$this->getName().'" process!',
                'rn_base',
                [
                    'process_name' => $this->getName(),
                    'life_time' => $this->getLifeTime(),
                    'lock_file' => $fileName,
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * Deletes the lock file.
     *
     * @return bool
     */
    private function deleteLockFile()
    {
        if ($this->isLocked()) {
            unlink($this->getFile());

            return true;
        }

        return false;
    }
}
