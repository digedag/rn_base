<?php

namespace Sys25\RnBase\Domain\Repository;

use Exception;
use Sys25\RnBase\Backend\Utility\TCA;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Domain\Model\BaseModel;
use Sys25\RnBase\Domain\Model\DataInterface;
use Sys25\RnBase\Domain\Model\DataModel;
use Sys25\RnBase\Domain\Model\DomainModelInterface as DomainInterface;
use Sys25\RnBase\Utility\Arrays;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016-2021 René Nitzsche <rene@system25.de>
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
 * Abstracte Persistance Repository.
 *
 * @author Michael Wagner
 */
abstract class PersistenceRepository extends AbstractRepository implements PersistenceInterface
{
    /**
     * Creates an new model instance.
     *
     * @param array $record
     *
     * @return DomainInterface
     */
    public function createNewModel(
        array $record = []
    ) {
        return $this->getEmptyModel()->setProperty($record);
    }

    /**
     * Check the model wor the right instance.
     *
     * @param mixed $model
     *
     * @return bool
     */
    protected function isModelWrapperClass($model)
    {
        $wrapperClass = $this->getWrapperClass();

        return $model instanceof $wrapperClass;
    }

    /**
     * Persists an model.
     *
     * @param DomainInterface $model
     * @param array|DataModel      $options
     *
     * @throws Exception
     */
    public function persist(
        DomainInterface $model,
        $options = null
    ) {
        $options = DataModel::getInstance($options);

        // check for right instance
        if (!$this->isModelWrapperClass($model)) {
            throw new Exception(get_class($this).'->persist only allows'.' handling instances of "'.$this->getWrapperClass().'"'.', but "'.get_class($model).'" given!');
        }

        // nothing todo, if the model has no changes
        if (!$options->getSkipDirtyCheck() && (
            $model instanceof DataInterface &&
            !$model->isDirty() &&
            $model->isPersisted()
        )) {
            return;
        }

        $tableName = $this->getEmptyModel()->getTableName();

        // the data to write
        // @TODO: should only changed data, not the whole record
        $data = $model->getProperty();

        // reduce the data by the configured field at the tca
        if (!$options->getSkipTcaColumnElimination()) {
            $data = $this->getCleanModelData($model);
        }

        if (empty($data)) {
            // throw an exception for new models
            if (!$model->isPersisted()) {
                throw new Exception('There is no data in "'.get_class($model).'" to persist.');
            }

            return;
        }

        // update the tstamp field
        if (!$options->getSkipTstampUpdate()) {
            $tstamp = TCA::getTstampFieldForTable($tableName);
            if ($tstamp) {
                $data[$tstamp] = $GLOBALS['EXEC_TIME'];
            }
        }

        // @TODO: use an persistance transport model!
        $transport = DataModel::getInstance();
        $transport->setOptions($options);
        $transport->setModel($model);
        $transport->setTableName($tableName);
        $transport->setData($data);

        if ($model->isPersisted()) {
            $this->persistUpdate($transport);
        } else {
            $this->persistNew($transport);
        }
    }

    /**
     * Bring db entry up to date.
     *
     * @param DataModel $transport
     */
    private function persistUpdate(
        DataModel $transport
    ) {
        $model = $transport->getModel();

        // update the entity with the raw uid
        $this->getConnection()->doUpdate(
            $transport->getTableName(),
            'uid='.(int) $model->getProperty('uid'),
            $transport->getData()
        );

        $this->refreshModelData($model, $transport->getData());
    }

    /**
     * Creates a new entry in the db.
     *
     * @param DataModel $transport
     */
    private function persistNew(
        DataModel $transport
    ) {
        $model = $transport->getModel();
        $data = $transport->getData();

        // set the crdate for new entries
        if (!$transport->getOptions()->getSkipCrdateUpdate()) {
            $crdate = TCA::getCrdateFieldForTable(
                $transport->getTableName()
            );
            if ($crdate) {
                $data[$crdate] = $GLOBALS['EXEC_TIME'];
            }
        }

        // append the pid, only on creation mode
        if ($model->hasPid()) {
            $data['pid'] = (int) $model->getPid();
        }

        // create the entity
        $data['uid'] = $this->getConnection()->doInsert(
            $transport->getTableName(),
            $data
        );

        $this->refreshModelData($model, $data);
    }

    /**
     * Refres the model with data after db operation and reset dirty flag.
     *
     * @param DomainInterface $model
     * @param array $data
     *
     * @TODO there is curently no interface for a reset, implement one!
     */
    protected function refreshModelData(
        DomainInterface $model,
        array $data
    ) {
        // merge the model data with the stored one, so nontca columns kept
        if ($model instanceof DataModel) {
            $model->setProperty(
                array_merge(
                    $model->getProperty(),
                    $data
                )
            );
        }

        // set the uid and force a record reload
        if ($model instanceof BaseModel) {
            $model->setProperty(
                ['uid' => (int) $data['uid']]
            );
            $model->reset();
        }
    }

    /**
     * Returns the properties of the model, cleaned by the configured tca columns.
     *
     * @param DomainInterface $model
     *
     * @return array
     */
    protected function getCleanModelData(
        DomainInterface $model
    ) {
        $columns = $model->getColumnNames();

        if (!is_array($columns) || empty($columns)) {
            // @TODO: throw exception or log into devlog?
            return [];
        }

        $data = Arrays::removeNotIn(
            $model->getProperty(),
            array_merge(
                $columns,
                [
                    // allow to delete a entity
                    TCA::getDeletedFieldForTable(
                        $model->getTableName()
                    ),
                    // allow to hide a entity
                    TCA::getDisabledFieldForTable(
                        $model->getTableName()
                    ),
                ]
            )
        );

        return $data;
    }

    /**
     * The database connection.
     *
     * @return Connection
     */
    protected function getConnection()
    {
        return Connection::getInstance();
    }
}
