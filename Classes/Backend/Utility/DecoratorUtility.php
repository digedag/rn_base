<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 René Nitzsche <rene@system25.de>
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
 * Decorator Utility
 *
 * @package TYPO3
 * @subpackage Tx_Rnbase
 * @author Michael Wagner
 */
class Tx_Rnbase_Backend_Utility_DecoratorUtility
{
    /**
     * Optional decorator instance to use for columns.
     *
     * @var Tx_Rnbase_Backend_Decorator_InterfaceDecorator
     */
    private $decorator = null;

    /**
     * The options object
     *
     * @var Tx_Rnbase_Domain_Model_Data
     */
    private $options = null;

    /**
     * Constructor
     *
     * @param Tx_Rnbase_Backend_Decorator_InterfaceDecorator $decorator
     * @param Tx_Rnbase_Domain_Model_Data|array $options
     *
     * @return Tx_Rnbase_Backend_Utility_DecoratorUtility
     */
    public static function getInstance(
        Tx_Rnbase_Backend_Decorator_InterfaceDecorator $decorator = null,
        $options = array()
    ) {
        return tx_rnbase::makeInstance(
            'Tx_Rnbase_Backend_Utility_DecoratorUtility',
            $decorator,
            $options
        );
    }

    /**
     * Constructor
     *
     * @param Tx_Rnbase_Backend_Decorator_InterfaceDecorator $decorator
     * @param Tx_Rnbase_Domain_Model_Data|array $options
     */
    public function __construct(
        Tx_Rnbase_Backend_Decorator_InterfaceDecorator $decorator = null,
        $options = array()
    ) {
        $this->decorator = $decorator;
        tx_rnbase::load('Tx_Rnbase_Domain_Model_Data');
        $this->options = Tx_Rnbase_Domain_Model_Data::getInstance($options);
    }

    /**
     * The decorator instace.
     *
     * @return Tx_Rnbase_Backend_Decorator_InterfaceDecorator
     */
    protected function getDecorator()
    {
        if ($this->decorator instanceof Tx_Rnbase_Backend_Decorator_InterfaceDecorator) {
            return $this->decorator;
        }

        return null;
    }

    /**
     * The decorator options object
     *
     * @return Tx_Rnbase_Domain_Model_Data
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
     * @return Tx_Rnbase_Backend_Utility_DecoratorUtility
     */
    public function addDecoratorColumnUid(
        array &$columns
    ) {
        $columns['uid'] = array(
            'title' => 'label_tableheader_uid',
            'decorator' => $this->getDecorator(),
        );

        return $this;
    }

    /**
     * Adds the column 'label' to the be list.
     *
     * @param array $columns
     *
     * @return Tx_Rnbase_Backend_Utility_DecoratorUtility
     */
    public function addDecoratorColumnLabel(
        array &$columns
    ) {
        if ($this->getOptions()->hasBaseTableName()) {
            tx_rnbase::load('tx_rnbase_util_TCA');
            $labelField = tx_rnbase_util_TCA::getLabelFieldForTable(
                $this->getOptions()->getBaseTableName()
            );
            if (!empty($labelField)) {
                $columns['label'] = array(
                    'title' => 'label_tableheader_title',
                    'decorator' => $this->getDecorator(),
                );
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
     * @return Tx_Rnbase_Backend_Utility_DecoratorUtility
     */
    public function addDecoratorColumnLanguage(
        array &$columns
    ) {
        if ($this->getOptions()->hasBaseTableName()) {
            tx_rnbase::load('tx_rnbase_util_TCA');
            $sysLanguageUidField = tx_rnbase_util_TCA::getLanguageFieldForTable(
                $this->getOptions()->getBaseTableName()
            );
            if (!empty($sysLanguageUidField)) {
                $columns['sys_language_uid'] = array(
                    'title' => 'label_tableheader_language',
                    'decorator' => $this->getDecorator(),
                );
            }
        }

        return $this;
    }

    /**
     * Adds the column 'actions' to the be list.
     * this column contains the edit, hide, remove, ... actions.
     *
     * @param array $columns

     * @return Tx_Rnbase_Backend_Utility_DecoratorUtility
     */
    public function addDecoratorColumnActions(
        array &$columns
    ) {
        $columns['actions'] = array(
            'title' => 'label_tableheader_actions',
            'decorator' => $this->getDecorator(),
        );

        return $this;
    }
}
