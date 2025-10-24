<?php

namespace Sys25\RnBase\Backend\Utility;

use Exception;
use Sys25\RnBase\Backend\Module\Linker\LinkerInterface;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Domain\Model\BaseModel;
use Sys25\RnBase\Domain\Model\DataInterface;
use Sys25\RnBase\Domain\Model\DataModel;
use Sys25\RnBase\Domain\Model\RecordInterface;
use Sys25\RnBase\Utility\LanguageTool;
use Traversable;
use tx_rnbase;

/**
 *  Copyright notice.
 *
 *  (c) 2016-2023 René Nitzsche <rene@system25.de>
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
class Tables
{
    /**
     * LanguageTool.
     *
     * @var mixed
     */
    private $lang;

    public function __construct(?LanguageTool $lang = null)
    {
        $this->lang = $lang ?: tx_rnbase::makeInstance(LanguageTool::class);
    }

    /**
     * @param array                                $entries
     * @param array                                $columns
     * @param \Sys25\RnBase\Backend\Form\ToolBox              $formTool
     * @param DataInterface $options
     *
     * @return array 0 are data and 1 layout
     */
    public function prepareTable($entries, $columns, $formTool, $options)
    {
        $options = tx_rnbase::makeInstance(DataModel::class, $options);
        // das initiale TableLayout nicht mehr aus dem Doc holen. Damit wird in 7.6 das
        // Bootstrap-Layout verwendet.
        $tableLayout = $this->getTableLayout();
        $tableData = [$this->getHeadline($columns, $options, $formTool)];
        $rowCount = 1;
        $isRowOdd = false;
        foreach ($entries as $entry) {
            $tableData[$rowCount] = $this->prepareRow(
                $entry,
                $columns,
                $formTool,
                $options
            );
            ++$rowCount;

            // now add the language overlays!
            // sample css for mod template:
            // table.typo3-dblist tr.localization { opacity: 0.5; font-size: 0.92em; }
            // table.typo3-dblist tr.localization td:nth-child(1), table.typo3-dblist tr.localization td:nth-child(2) { padding-left: 24px; }
            if ($entry instanceof RecordInterface
                && $options->getAddI18Overlays()
                // skip if the entry already translated!
                && $entry->getUid() == $entry->getProperty('uid')
                && !$entry->getSysLanguageUid()
            ) {
                // set the layout for the original (last) row
                $defName = $isRowOdd ? 'defRowOdd' : 'defRowEven';
                $tableLayout[$rowCount - 1] = is_array($tableLayout[$defName]) ? $tableLayout[$defName] : $tableLayout['defRow'];
                // the spacial layout for the overlay rows
                $layout = $tableLayout[$rowCount - 1];
                $layout['tr'][0] = '<tr class="'.($isRowOdd ? 'db_list_normal' : 'db_list_alt').' localization">';
                $isRowOdd = !$isRowOdd;

                // render the overlays with the special layout
                foreach ($this->getLangOverlayEntries($entry) as $overlay) {
                    $overlay->setProperty('_MOD_OVERLAY', true);
                    $tableData[$rowCount] = $this->prepareRow(
                        $overlay,
                        $columns,
                        $formTool,
                        $options
                    );
                    $overlay->unsProperty('_MOD_OVERLAY');
                    $tableLayout[$rowCount] = $layout;
                    ++$rowCount;
                }
            }
        }

        return [$tableData, $tableLayout];
    }

    /**
     * @param array $entry
     * @param array $columns
     * @param \Sys25\RnBase\Backend\Form\ToolBox $formTool
     * @param DataInterface $options
     *
     * @return array
     */
    protected function prepareRow($entry, $columns, $formTool, $options)
    {
        $record = $entry;
        if ($entry instanceof DataInterface) {
            $record = $entry->getProperty();
        }
        if ($entry instanceof RecordInterface) {
            $record = $entry->getRecord();
        }
        $row = [];
        if (null !== $options->getCheckbox()) {
            $checkName = $options->getCheckboxname() ? $options->getCheckboxname() : 'checkEntry';
            $dontcheck = is_array($options->getDontcheck()) ? $options->getDontcheck() : [];
            // Check if entry is checkable
            if (!array_key_exists($record['uid'], $dontcheck)) {
                $row[] = $formTool->createCheckbox($checkName.'[]', $record['uid']);
            } else {
                $row[] = sprintf(
                    '<span title="Info: %s">%s</span>',
                    $dontcheck[$record['uid']],
                    Icons::getSpriteIcon(
                        'actions-document-info'
                    )
                );
            }
        }

        if ($options->getAddRecordSprite()) {
            $spriteIconName = 'mimetypes-other-other';
            if ($entry instanceof RecordInterface && $entry->getTableName()) {
                $spriteIconName = Icons::mapRecordTypeToSpriteIconName(
                    $entry->getTableName(),
                    $record
                );
            }
            $row[] = Icons::getSpriteIcon($spriteIconName);
        }

        foreach ($columns as $column => $data) {
            $columnValue = $record[$column] ?? '';
            // Da wir Daten für eine HTML Tabelle haben, werden
            // diese immer escaped, um XSS zu verhindern.
            $record[$column] = htmlspecialchars($columnValue);

            // Hier erfolgt die Ausgabe der Daten für die Tabelle. Wenn eine method angegeben
            // wurde, dann muss das Entry als Objekt vorliegen. Es wird dann die entsprechende
            // Methode aufgerufen. Es kann auch ein Decorator-Objekt gesetzt werden. Dann wird
            // von diesem die Methode format aufgerufen und der Wert, sowie der Name der aktuellen
            // Spalte übergeben. Ist nichts gesetzt wird einfach der aktuelle Wert verwendet.
            if (isset($data['method'])) {
                $row[] = call_user_func([$entry, $data['method']]);
            } elseif (isset($data['decorator'])) {
                $decor = $data['decorator'];
                $row[] = $decor->format($columnValue, $column, $record, $entry);
            } else {
                $row[] = $columnValue;
            }
        }
        if ($options->getLinker()) {
            $row[] = $this->addLinker($options, $entry, $formTool);
        }

        return $row;
    }

    /**
     * Liefert die passenden Überschrift für die Tabelle.
     *
     * @param array $columns
     * @param DataModel $options
     * @param \Sys25\RnBase\Backend\Form\ToolBox $formTool
     *
     * @return array
     */
    private function getHeadline($columns, $options, $formTool)
    {
        $arr = [];
        if ($options->getCheckbox()) {
            $arr[] = '&nbsp;'; // Spalte für Checkbox
        }
        if ($options->getAddRecordSprite()) {
            $arr[] = '&nbsp;';
        }

        foreach ($columns as $column => $data) {
            if ((int) ($data['nocolumn'] ?? 0) > 0) {
                continue;
            }
            if ((int) ($data['notitle'] ?? 0) > 0) {
                $arr[] = '';

                continue;
            }

            $label = $this->getLang()->getLL(isset($data['title']) ? $data['title'] : $column);
            if (!$label && isset($data['title'])) {
                $label = $this->getLang()->sL($data['title']);
            }
            // es gibt die Möglichkeit sortable zu setzen. damit wird
            // nach dem title eine sortierung eingeblendet.
            // in $data['sortable'] sollte ein prefix für das feld stehen, sprich
            // der alias der tabelle um damit direkt weiterabeiten zu können.
            // einfach leer lassen wenn auf einen prefix verzichtet werden soll
            if (isset($data['sortable'])) {
                $label = $formTool->createSortLink($column, $label);
            }
            $arr[] = $label ? $label : $data['title'] ?? '';
        }
        if ($options->getLinker()) {
            $arr[] = $this->getLang()->getLL('label_action');
        }

        return $arr;
    }

    /**
     * returns all language overlays.
     *
     * @param BaseModel $entry
     *
     * @return BaseModel[]
     */
    private function getLangOverlayEntries(RecordInterface $entry)
    {
        $parentField = TCA::getTransOrigPointerFieldForTable($entry->getTableName());
        $overlays = Connection::getInstance()->doSelect(
            '*',
            $entry->getTableName(),
            [
                'where' => $parentField.'='.$entry->getUid(),
                'wrapperclass' => get_class($entry),
            ]
        );

        return $overlays;
    }

    /**
     * @param DataInterface $options
     * @param BaseModel $obj
     * @param \Sys25\RnBase\Backend\Form\ToolBox $formTool
     *
     * @return string
     */
    private function addLinker($options, $obj, $formTool)
    {
        $out = '';
        $linkerArr = $options->getLinker();
        if ((is_array($linkerArr) || $linkerArr instanceof Traversable) && !empty($linkerArr)) {
            $linkerimplode = $options->getLinkerimplode() ? $options->getLinkerimplode() : '<br />';
            $currentPid = (int) $options->getPid();
            foreach ($linkerArr as $linker) {
                if (!$linker instanceof LinkerInterface) {
                    // backward compatibility, the interface with the makeLink method is new!
                    if (!is_callable([$linker, 'makeLink'])) {
                        throw new Exception(sprintf('Linker "%s" has to implement interface "%s".', get_class($linker), LinkerInterface::class));
                    }
                }
                $out .= $linker->makeLink($obj, $formTool, $currentPid, $options);
                $out .= $linkerimplode;
            }
        }

        return $out;
    }

    /**
     * Returns a table based on the input $data
     * This method is taken from TYPO3 core. It will be removed there for version 8.
     *
     * Typical call until now:
     * $tables = new Tables();
     * $content .= $tables->buildTable($data, $module->getTableLayout());
     * Should we include a better default layout here??
     *
     * @param array $data   Multidim array with first levels = rows, second levels = cells
     * @param array $layout If set, then this provides an alternative layout array instead of $this->tableLayout
     *
     * @return string the HTML table
     */
    public function buildTable($data, $layout = null)
    {
        $resultHead = $result = '';
        if (is_array($data)) {
            $tableLayout = is_array($layout) ? $layout : $this->getTableLayout();
            $rowCount = 0;
            foreach ($data as $tableRow) {
                if ($rowCount % 2) {
                    $layout = isset($tableLayout['defRowOdd']) ? $tableLayout['defRowOdd'] : $tableLayout['defRow'];
                } else {
                    $layout = isset($tableLayout['defRowEven']) ? $tableLayout['defRowEven'] : $tableLayout['defRow'];
                }
                $rowLayout = isset($tableLayout[$rowCount]) ? $tableLayout[$rowCount] : $layout;
                $rowResult = '';
                if (is_array($tableRow)) {
                    $cellCount = 0;
                    foreach ($tableRow as $tableCell) {
                        $cellWrap = isset($layout[$cellCount]) ? $layout[$cellCount] : $layout['defCol'];
                        $cellWrap = isset($rowLayout['defCol']) ? $rowLayout['defCol'] : $cellWrap;
                        $cellWrap = isset($rowLayout[$cellCount]) ? $rowLayout[$cellCount] : $cellWrap;
                        $rowResult .= $cellWrap[0].$tableCell.$cellWrap[1];
                        ++$cellCount;
                    }
                }
                $rowWrap = isset($layout['tr']) ? $layout['tr'] : ['<tr>', '</tr>'];
                $rowWrap = isset($rowLayout['tr']) ? $rowLayout['tr'] : $rowWrap;

                if (isset($tableLayout['headRows']) && in_array($rowCount, $tableLayout['headRows'])) {
                    $resultHead .= $rowWrap[0].$rowResult.$rowWrap[1];
                } else {
                    $result .= $rowWrap[0].$rowResult.$rowWrap[1];
                }
                ++$rowCount;
            }
            if (isset($tableLayout['headRows'])) {
                $result = '<thead>'.$resultHead.'</thead><tbody>'.$result.'</tbody>';
            } else {
                $result = $resultHead.$result;
            }
            $tableTag = '<table class="table table-striped table-hover table-condensed">';
            $tableWrap = isset($tableLayout['table']) ? $tableLayout['table'] : [$tableTag, '</table>'];
            $result = $tableWrap[0].$result.$tableWrap[1];
        }

        return $result;
    }

    /**
     * Returns a default table layout.
     *
     * @return array
     */
    public function getTableLayout()
    {
        return [
            'headRows' => [0],
            'table' => ['<table class="table table-striped table-hover table-condensed">', '</table><br/>'],
            '0' => [ // Format für 1. Zeile
                'tr' => ['<tr class="">', '</tr>'],
                // Format für jede Spalte in der 1. Zeile
                'defCol' => ['<td>', '</td>'],
            ],
            'defRow' => [ // Formate für alle Zeilen
                'tr' => ['<tr class="">', '</tr>'],
                'defCol' => ['<td>', '</td>'], // Format für jede Spalte in jeder Zeile
            ],
            'defRowEven' => [ // Formate für alle geraden Zeilen
                'tr' => ['<tr class="">', '</tr>'],
                // Format für jede Spalte in jeder Zeile
                'defCol' => ['<td>', '</td>'],
            ],
        ];
    }

    private function getLang()
    {
        return $this->lang;
    }
}
