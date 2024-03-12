<?php

namespace Sys25\RnBase\Domain\Repository;

use Exception;
use PDO;
use Sys25\RnBase\Domain\Model\FeUser;
use Sys25\RnBase\Search\System\FeUserSearch;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/***************************************************************
 * Copyright notice
 *
 * (c) 2022 Rene Nitzsche (rene@system25.de)
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

class FeUserRepository extends PersistenceRepository
{
    private static $instances = [];

    /**
     * (non-PHPdoc).
     *
     * @see AbstractRepository::getSearchClass()
     */
    protected function getSearchClass()
    {
        return FeUserSearch::class;
    }

    /**
     * Es werden auch versteckte Instanzen geladen.
     *
     * @param int $uid
     *
     * @return FeUser|null
     */
    public function findByUidForced(int $uid)
    {
        /* @var $feuser FeUser */
        $feuser = $this->getEmptyModel();
        $feuser->setUid($uid);
        $options = [];
        $options['where'] = function (QueryBuilder $qb) use ($uid) {
            $qb->andWhere(sprintf('uid = %s', $qb->createNamedParameter($uid, PDO::PARAM_INT)));
        };
        $options['enablefieldsoff'] = true;
        $result = $this->getConnection()->doSelect('*', $feuser->getTableName(), $options);
        if (count($result) > 0) {
            $feuser->setProperty($result[0]);

            return $feuser;
        }

        return null;
    }

    /**
     * Liefert die Instance mit der übergebenen UID. Die Daten werden gecached, so daß
     * bei zwei Anfragen für die selbe UID nur ein DB Zugriff erfolgt.
     *
     * @param int $uid
     *
     * @return FeUser|null
     *
     * @throws Exception if parameter is not an integer
     */
    public function getInstance(int $uid)
    {
        $uid = (int) $uid;
        if (!$uid) {
            throw new Exception('No uid for fe_user given!');
        }
        if (!isset(self::$instances[$uid])) {
            self::$instances[$uid] = $this->findByUidForced($uid);
        }

        return self::$instances[$uid];
    }

    /**
     * Liefert die Instanz des aktuell angemeldeten Users oder false.
     *
     * @return FeUser|null
     */
    public function getCurrent()
    {
        $userId = TYPO3::getFEUserUID();

        return intval($userId) ? $this->getInstance($userId) : null;
    }

    /**
     * Find a user by mail address.
     *
     * @param string $email
     * @param string $pids
     *
     * @return FeUser|null
     */
    public function getUserByEmail($email, $pids = '')
    {
        if (!($email && Strings::validEmail($email))) {
            return null;
        }

        $fields = [];
        $options = [];
        $this->filterEmail($fields, $email);
        $this->filterPids($fields, $pids);
        $feusers = $this->search($fields, $options);

        return count($feusers) ? $feusers[0] : null;
    }

    /**
     * Find a disabled user by mail address.
     *
     * @param string $email
     * @param string $pids
     *
     * @return FeUser
     */
    public function getDisabledUserByEmail($email, $pids = '')
    {
        if (!($email && Strings::validEmail($email))) {
            return false;
        }

        $fields = [];
        $options = [];

        $options['limit'] = 1;
        $options['enablefieldsoff'] = 1;
        $fields['FEUSER.DISABLE'][OP_EQ_INT] = 1;
        $fields['FEUSER.DELETED'][OP_EQ_INT] = 0;
        $this->filterEmail($fields, $email);
        $this->filterPids($fields, $pids);
        $feusers = $this->search($fields, $options);

        return count($feusers) ? $feusers[0] : false;
    }

    private function filterEmail(array &$fields, $email)
    {
        if (strlen(trim($email))) {
            $fields['FEUSER.EMAIL'][OP_EQ_NOCASE] = $email;
        }
    }

    private function filterPids(array &$fields, $pids)
    {
        if (strlen(trim($pids))) {
            $pids = implode(',', Strings::intExplode(',', $pids));
            $joined['value'] = $pids;
            $joined['cols'] = ['FEUSER.PID'];
            $joined['operator'] = OP_INSET_INT;
            $fields[SEARCH_FIELD_JOINED][] = $joined;
        }
    }
}
