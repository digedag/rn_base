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

/**
 * Tx_Rnbase_Scheduler_FieldProviderBase
 *
 * stellt die abstrakten Methoden für Tx_Rnbase_Scheduler_FieldProvider bereit
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann <rene@system25.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
abstract class Tx_Rnbase_Scheduler_FieldProviderBase
{

    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param array &$taskInfo Values of the fields from the add/edit task form
     * @param Tx_Rnbase_Scheduler_Task $task The task object being edited. Null when adding a task!
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule $schedulerModule Reference to the scheduler backend module
     * @return array
     */
    abstract protected function _getAdditionalFields(
        array &$taskInfo,
        $task,
        $schedulerModule
    );

    /**
     * Validates the additional fields' values
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    abstract protected function _validateAdditionalFields(
        array &$submittedData,
        $parentObject
    );

    /**
     * Takes care of saving the additional fields' values in the task's object
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param Tx_Rnbase_Scheduler_Task $task Reference to the scheduler backend module
     * @return void
     */
    abstract protected function _saveAdditionalFields(array $submittedData, Tx_Rnbase_Scheduler_Task $task);
}
