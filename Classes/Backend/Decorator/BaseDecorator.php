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
 * Base Decorator.
 *
 * @author Michael Wagner
 */
class Tx_Rnbase_Backend_Decorator_BaseDecorator implements Tx_Rnbase_Backend_Decorator_InterfaceDecorator
{
    /**
     * The module.
     *
     * @var tx_rnbase_mod_BaseModule
     */
    private $mod = null;

    /**
     * The internal options object.
     *
     * @var Tx_Rnbase_Domain_Model_Data
     */
    private $options = null;

    /**
     * Constructor.
     *
     * @param tx_rnbase_mod_BaseModule          $mod
     * @param array|Tx_Rnbase_Domain_Model_Data $options
     */
    public function __construct(
        tx_rnbase_mod_BaseModule $mod,
        $options = []
    ) {
        $this->mod = $mod;

        $this->options = Tx_Rnbase_Domain_Model_Data::getInstance($options);
    }

    /**
     * Returns the module.
     *
     * @return tx_rnbase_mod_IModule
     */
    protected function getModule()
    {
        return $this->mod;
    }

    /**
     * The internal options object.
     *
     * @return Tx_Rnbase_Domain_Model_Data
     */
    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns an instance of tx_rnbase_mod_IModule.
     *
     * @return tx_rnbase_util_FormTool
     */
    protected function getFormTool()
    {
        return $this->getModule()->getFormTool();
    }

    /**
     * Formats a value.
     *
     * @param string                               $columnValue
     * @param string                               $columnName
     * @param array                                $record
     * @param Tx_Rnbase_Domain_Model_DataInterface $entry
     *
     * @return string
     */
    public function format(
        $columnValue,
        $columnName,
        array $record,
        Tx_Rnbase_Domain_Model_DataInterface $entry
    ) {
        $return = $columnValue;

        $method = tx_rnbase_util_Strings::underscoredToLowerCamelCase($columnName);
        $method = 'format'.ucfirst($method).'Column';

        if (method_exists($this, $method)) {
            $return = $this->{$method}($entry);
        }

        return $this->wrapValue($return, $entry, $columnName);
    }

    /**
     * Wraps the Value.
     * A childclass can extend this and wrap each value in a spac.
     * For example a strikethrough for disabled entries.
     *
     * @param string                               $formatedValue
     * @param Tx_Rnbase_Domain_Model_DataInterface $entry
     * @param string                               $columnName
     *
     * @return string
     */
    protected function wrapValue(
        $formatedValue,
        Tx_Rnbase_Domain_Model_DataInterface $entry,
        $columnName
    ) {
        return $formatedValue;
    }

    /**
     * Renders the uid column.
     *
     * @param Tx_Rnbase_Domain_Model_DataInterface $entry
     *
     * @return string
     */
    protected function formatUidColumn(
        Tx_Rnbase_Domain_Model_DataInterface $entry
    ) {
        $value = $entry->getProperty('uid');

        return sprintf(
            '<span title="%2$s">%1$d</span>',
            $value,
            htmlentities(implode(CRLF, $this->buildSimpleEntryInfo($entry)))
        );
    }

    /**
     * Renders the uid column.
     *
     * @param Tx_Rnbase_Domain_Model_DataInterface $entry
     *
     * @return string
     */
    protected function formatLabelColumn(
        Tx_Rnbase_Domain_Model_DataInterface $entry
    ) {
        $label = '';

        // only for domain entries with table name
        if (!$entry instanceof Tx_Rnbase_Domain_Model_DomainInterface) {
            return $label;
        }

        if ($entry instanceof Tx_Rnbase_Domain_Model_Base) {
            $label = $entry->getTcaLabel();
        } else {
            $labelField = tx_rnbase_util_TCA::getLabelFieldForTable(
                $entry->getTableName()
            );

            if ('uid' !== $labelField && $entry->getProperty($labelField)) {
                $label = $entry->getProperty($labelField);
            } elseif ($entry->getLabel()) {
                $label = $entry->getLabel();
            } elseif ($entry->getName()) {
                $label = $entry->getName();
            }
        }

        return sprintf(
            '<span title="%2$s">%1$s</span>',
            (string) $label,
            htmlentities(implode(CRLF, $this->buildSimpleEntryInfo($entry)))
        );
    }

    /**
     * Builds a simple info of the entitiy.
     * Is curently used for title tags.
     *
     * @param Tx_Rnbase_Domain_Model_DataInterface $entry
     *
     * @return array
     */
    protected function buildSimpleEntryInfo(
        Tx_Rnbase_Domain_Model_DataInterface $entry
    ) {
        $infos = [];

        $infos['uid'] = 'UID: '.$entry->getProperty('uid');

        // only for domain entries with table name
        if ($entry instanceof Tx_Rnbase_Domain_Model_DomainInterface) {
            $labelField = tx_rnbase_util_TCA::getLabelFieldForTable($entry->getTableName());
            if ('uid' !== $labelField && $entry->getProperty($labelField)) {
                $infos['label'] = 'Label: '.(string) $entry->getProperty($labelField);
            }

            $datefields = [
                'Creation' => tx_rnbase_util_TCA::getCrdateFieldForTable($entry->getTableName()),
                'Last Change' => tx_rnbase_util_TCA::getTstampFieldForTable($entry->getTableName()),
            ];
            foreach ($datefields as $dateTitle => $datefield) {
                $date = $entry->getProperty($datefield);
                if (!empty($date)) {
                    $infos[$datefield] = $dateTitle.': '.strftime(
                        '%d.%m.%y %H:%M:%S',
                        $date
                    );
                }
            }
        }

        return $infos;
    }

    /**
     * Renders the useractions.
     *
     * @param Tx_Rnbase_Domain_Model_DataInterface $item
     *
     * @return string
     */
    protected function formatActionsColumn(
        Tx_Rnbase_Domain_Model_DataInterface $item
    ) {
        $return = '';

        // only for domain entries with table name
        if (!$item instanceof Tx_Rnbase_Domain_Model_DomainInterface) {
            return $return;
        }

        $actionConf = $this->getActionsConfig($item);

        foreach ($actionConf as $actionKey => $actionConfig) {
            $method = 'formatAction'.ucfirst($actionKey);
            if (method_exists($this, $method)) {
                $return .= $this->{$method}($item, $actionConfig);
            }
        }

        return $return;
    }

    /**
     * Renders the useractions.
     *
     * @param Tx_Rnbase_Domain_Model_DomainInterface $item
     * @param array                                  $actionConfig
     *
     * @return string
     */
    protected function formatActionEdit(
        Tx_Rnbase_Domain_Model_DomainInterface $item,
        array $actionConfig = []
    ) {
        return $this->getFormTool()->createEditLink(
            $item->getTableName(),
            // we use the real uid, not the uid of the parent!
            $item->getProperty('uid'),
            $actionConfig['title']
        );
    }

    /**
     * Renders the useractions.
     *
     * @param Tx_Rnbase_Domain_Model_DomainInterface $item
     * @param array                                  $actionConfig
     *
     * @return string
     */
    protected function formatActionHide(
        Tx_Rnbase_Domain_Model_DomainInterface $item,
        array $actionConfig = []
    ) {
        return $this->getFormTool()->createHideLink(
            $item->getTableName(),
            // we use the real uid, not the uid of the parent!
            $item->getProperty('uid'),
            $item->isHidden(),
            [
                'label' => $actionConfig['title'],
            ]
        );
    }

    /**
     * Renders the useractions.
     *
     * @param Tx_Rnbase_Domain_Model_DomainInterface $item
     * @param array                                  $actionConfig
     *
     * @return string
     */
    protected function formatActionRemove(
        Tx_Rnbase_Domain_Model_DomainInterface $item,
        array $actionConfig = []
    ) {
        return $this->getFormTool()->createDeleteLink(
            $item->getTableName(),
            // we use the real uid, not the uid of the parent!
            $item->getProperty('uid'),
            $actionConfig['title'],
            [
                'confirm' => $actionConfig['confirm'],
            ]
        );
    }

    /**
     * Renders the useractions.
     *
     * @param Tx_Rnbase_Domain_Model_DomainInterface $item
     * @param array                                  $actionConfig
     *
     * @return string
     */
    protected function formatActionMoveup(
        Tx_Rnbase_Domain_Model_DomainInterface $item,
        array $actionConfig = []
    ) {
        $uid = $item->getProperty('uid');
        $fromUid = $uid;
        $uidMap = $this->getUidMap($item);
        // zwei schritte in der map zurück,
        // denn wir wollen das aktuelle element vor das vorherige.
        // typo3 verschiebt aber immer hinter elemente, also muss es hinter das vorvorletzte.
        // wenn es kein vorvorletztes gibt,
        // verschieben wir das vorletzte element hinter das aktuelle element
        prev($uidMap);
        $prevId = key($uidMap);
        if ($prevId) {
            prev($uidMap);
            if (key($uidMap)) {
                $prevId = key($uidMap);
            } else {
                $fromUid = $prevId;
                $prevId = $uid;
            }
        }
        if ($prevId) {
            $action = $this->getFormTool()->createMoveUpLink(
                $item->getTableName(),
                $fromUid,
                $prevId,
                [
                    'label' => '',
                    'title' => 'Move '.$fromUid.' after '.$prevId,
                ]
            );
        } else {
            $action = tx_rnbase_mod_Util::getSpriteIcon('empty-icon');
        }

        return $action;
    }

    /**
     * Renders the useractions.
     *
     * @param Tx_Rnbase_Domain_Model_DomainInterface $item
     * @param array                                  $actionConfig
     *
     * @return string
     */
    protected function formatActionMovedown(
        Tx_Rnbase_Domain_Model_DomainInterface $item,
        array $actionConfig = []
    ) {
        $uid = $item->getProperty('uid');
        $uidMap = $this->getUidMap($item);
        // einen schritt in der map nach vorne, denn wir wollen das aktuelle hinter dem nächsten platzieren.
        next($uidMap);
        $nextId = key($uidMap);
        if ($nextId) {
            $action = $this->getFormTool()->createMoveDownLink(
                $item->getTableName(),
                $uid,
                $nextId,
                [
                    'label' => '',
                    'title' => 'Move '.$uid.' after '.$nextId,
                ]
            );
        } else {
            $action = tx_rnbase_mod_Util::getSpriteIcon('empty-icon');
        }

        return $action;
    }

    /**
     * Returns the uid map and sets the pointer to the current element.
     *
     * @param Tx_Rnbase_Domain_Model_RecordInterface $item
     *
     * @return array
     */
    protected function getUidMap(
        Tx_Rnbase_Domain_Model_RecordInterface $item
    ) {
        if (!$this->getOptions()->hasUidMap()) {
            return [];
        }

        $currentId = $item->getUid();
        $map = $this->getOptions()->getUidMap();

        while (null !== key($map) && key($map) != $currentId) {
            next($map);
        }

        return $map;
    }

    /**
     * Liefert die möglichen Optionen für die actions.
     *
     * @param Tx_Rnbase_Domain_Model_DomainInterface $item
     *
     * @return array
     */
    protected function getActionsConfig(
        Tx_Rnbase_Domain_Model_DomainInterface $item
    ) {
        $def = ['title' => ''];
        $actions = [
            'edit' => $def,
            'hide' => $def,
        ];

        // add mopve up and move down buttons for sortable entities
        if (tx_rnbase_util_TCA::getSortbyFieldForTable($item->getTableName())) {
            $actions['moveup'] = $def;
            $actions['movedown'] = $def;
        }

        // add remove button only for admins
        if ($this->isAdmin()) {
            $actions['remove'] = $def;
            $actions['remove']['confirm'] = '###LABEL_ENTRY_DELETE_CONFIRM###';
        }

        return $actions;
    }

    /**
     * Is the current iser a admin?
     *
     * @return bool
     */
    protected function isAdmin()
    {
        if (is_object($GLOBALS['BE_USER'])) {
            return (bool) $GLOBALS['BE_USER']->isAdmin();
        }

        return false;
    }
}
