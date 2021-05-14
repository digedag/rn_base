<?php

namespace Sys25\RnBase\Backend\Utility;

use Sys25\RnBase\Backend\Decorator\InterfaceDecorator;
use Sys25\RnBase\Domain\Model\DataModel;
use tx_rnbase;
use tx_rnbase_util_TCA;

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
 * Decorator Utility.
 *
 * @author Michael Wagner
 */
class DecoratorUtility
{
    /**
     * Optional decorator instance to use for columns.
     *
     * @var InterfaceDecorator
     */
    private $decorator = null;

    /**
     * The options object.
     *
     * @var DataModel
     */
    private $options = null;

    /**
     * Constructor.
     *
     * @param InterfaceDecorator $decorator
     * @param DataModel|array $options
     *
     * @return DecoratorUtility
     */
    public static function getInstance(
        InterfaceDecorator $decorator = null,
        $options = []
    ) {
        return tx_rnbase::makeInstance(
            DecoratorUtility::class,
            $decorator,
            $options
        );
    }

    /**
     * Constructor.
     *
     * @param InterfaceDecorator $decorator
     * @param DataModel|array $options
     */
    public function __construct(
        InterfaceDecorator $decorator = null,
        $options = []
    ) {
        $this->decorator = $decorator;
        $this->options = DataModel::getInstance($options);
    }

    /**
     * The decorator instace.
     *
     * @return InterfaceDecorator
     */
    protected function getDecorator()
    {
        if ($this->decorator instanceof InterfaceDecorator) {
            return $this->decorator;
        }

        return null;
    }

    /**
     * The decorator options object.
     *
     * @return DataModel
     */
    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * Adds the column 'uid' to the be list.
     *
     * @param array $columns
     *
     * @return DecoratorUtility
     */
    public function addDecoratorColumnUid(array &$columns)
    {
        $columns['uid'] = [
            'title' => 'label_tableheader_uid',
            'decorator' => $this->getDecorator(),
        ];

        return $this;
    }

    /**
     * Adds the column 'label' to the be list.
     *
     * @param array $columns
     *
     * @return DecoratorUtility
     */
    public function addDecoratorColumnLabel(array &$columns)
    {
        if ($this->getOptions()->hasBaseTableName()) {
            $labelField = tx_rnbase_util_TCA::getLabelFieldForTable(
                $this->getOptions()->getBaseTableName()
            );
            if (!empty($labelField)) {
                $columns['label'] = [
                    'title' => 'label_tableheader_title',
                    'decorator' => $this->getDecorator(),
                ];
            }
        }

        // fallback, the uid column
        if (!isset($columns['label']) && !isset($columns['uid'])) {
            $this->addDecoratorColumnUid($columns);
        }

        return $this;
    }

    /**
     * Adds the column 'sys_language_uid' to the be list.
     *
     * @param array $columns
     *
     * @return DecoratorUtility
     */
    public function addDecoratorColumnLanguage(array &$columns)
    {
        if ($this->getOptions()->hasBaseTableName()) {
            $sysLanguageUidField = tx_rnbase_util_TCA::getLanguageFieldForTable(
                $this->getOptions()->getBaseTableName()
            );
            if (!empty($sysLanguageUidField)) {
                $columns['sys_language_uid'] = [
                    'title' => 'label_tableheader_language',
                    'decorator' => $this->getDecorator(),
                ];
            }
        }

        return $this;
    }

    /**
     * Adds the column 'actions' to the be list.
     * this column contains the edit, hide, remove, ... actions.
     *
     * @param array $columns
     *
     * @return DecoratorUtility
     */
    public function addDecoratorColumnActions(array &$columns)
    {
        $columns['actions'] = [
            'title' => 'label_tableheader_actions',
            'decorator' => $this->getDecorator(),
        ];

        return $this;
    }
}
