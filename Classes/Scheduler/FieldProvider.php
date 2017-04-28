<?php
/**
 *  Copyright notice
 *
 *  (c) 2015 DMK E-BUSINESS GmbH  <dev@dmk-ebusiness.de>
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
 */
tx_rnbase::load('Tx_Rnbase_Scheduler_FieldProviderBase');

tx_rnbase::load('tx_rnbase_util_TYPO3');
if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
    /**
     * Tx_Rnbase_Scheduler_FieldProvider
     *
     * Wrapper für \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface seit TYPO3 6.x
     *
     * @package         TYPO3
     * @subpackage      rn_base
     * @author          Hannes Bochmann <rene@system25.de>
     * @license         http://www.gnu.org/licenses/lgpl.html
     *                  GNU Lesser General Public License, version 3 or later
     */
    abstract class Tx_Rnbase_Scheduler_FieldProvider extends Tx_Rnbase_Scheduler_FieldProviderBase implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
    {

        /**
         * Gets additional fields to render in the form to add/edit a task
         *
         * @param array &$taskInfo Values of the fields from the add/edit task form
         * @param Tx_Rnbase_Scheduler_Task $task The task object being edited. Null when adding a task!
         * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
         * @return array
         */
        public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule)
        {
            return $this->_getAdditionalFields($taskInfo, $task, $schedulerModule);
        }

        /**
         * Validates the additional fields' values
         *
         * @param array $submittedData An array containing the data submitted by the add/edit task form
         * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
         * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
         */
        public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule)
        {
            return $this->_validateAdditionalFields($submittedData, $schedulerModule);
        }

        /**
         * Takes care of saving the additional fields' values in the task's object
         *
         * @param array $submittedData An array containing the data submitted by the add/edit task form
         * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task Reference to the scheduler backend module
         * @return void
         */
        public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task)
        {
            return $this->_saveAdditionalFields($submittedData, $task);
        }
    }
} else {
    if (!interface_exists('tx_scheduler_AdditionalFieldProvider')) {
        require_once tx_rnbase_util_Extensions::extPath(
            'scheduler',
            '/interfaces/interface.tx_scheduler_additionalfieldprovider.php'
        );
    }
    tx_rnbase::load('Tx_Rnbase_Scheduler_FieldProviderBase');

    /**
     * Tx_Rnbase_Scheduler_FieldProvider
     *
     * Wrapper für tx_scheduler_AdditionalFieldProvider bis TYPO3 6.x
     *
     * @package         TYPO3
     * @subpackage      rn_base
     * @author          Hannes Bochmann <rene@system25.de>
     * @license         http://www.gnu.org/licenses/lgpl.html
     *                  GNU Lesser General Public License, version 3 or later
     */
    abstract class Tx_Rnbase_Scheduler_FieldProvider extends Tx_Rnbase_Scheduler_FieldProviderBase implements tx_scheduler_AdditionalFieldProvider
    {

        /**
         * Gets additional fields to render in the form to add/edit a task
         *
         * @param array &$taskInfo Values of the fields from the add/edit task form
         * @param Tx_Rnbase_Scheduler_Task $task The task object being edited. Null when adding a task!
         * @param tx_mklib_scheduler_Generic $schedulerModule Reference to the scheduler backend module
         * @return array
         */
        public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $schedulerModule)
        {
            return $this->_getAdditionalFields($taskInfo, $task, $schedulerModule);
        }

        /**
         * Validates the additional fields' values
         *
         * @param array $submittedData An array containing the data submitted by the add/edit task form
         * @param tx_scheduler_Module $schedulerModule Reference to the scheduler backend module
         * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
         */
        public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject)
        {
            return $this->_validateAdditionalFields($submittedData, $parentObject);
        }

        /**
         * Takes care of saving the additional fields' values in the task's object
         *
         * @param array $submittedData An array containing the data submitted by the add/edit task form
         * @param Tx_Rnbase_Scheduler_Task $task Reference to the scheduler backend module
         * @return void
         */
        public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task)
        {
            return $this->_saveAdditionalFields($submittedData, $task);
        }
    }
}
