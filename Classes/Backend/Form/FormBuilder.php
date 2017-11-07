<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016-2017 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Replacement class for former FormEngine-class.
 * Use one instance per formular.
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          René Nitzsche <rene@system25.de>
 */

class Tx_Rnbase_Backend_Form_FormBuilder
{
    private $nodeFactory = null;
    /**
     * @var \TYPO3\CMS\Backend\Form\FormDataCompiler
     */
    private $formDataCompiler = null;
    private $formResultCompiler; // TODO
    protected $formDataCache = array();

    /**
     */
    public function __construct()
    {
        /**
 * @var TcaDatabaseRecord $formDataGroup
*/
        $formDataGroup = tx_rnbase::makeInstance('TYPO3\\CMS\\Backend\\Form\\FormDataGroup\\TcaDatabaseRecord');
        $this->formDataCompiler = tx_rnbase::makeInstance('TYPO3\\CMS\\Backend\\Form\\FormDataCompiler', $formDataGroup);
        $this->nodeFactory = tx_rnbase::makeInstance('TYPO3\\CMS\\Backend\\Form\\NodeFactory');
        $this->formResultCompiler = tx_rnbase::makeInstance('TYPO3\\CMS\\Backend\\Form\\FormResultCompiler');
    }
    public function initDefaultBEmode()
    {
    }

    /**
     *
     * @return \TYPO3\CMS\Backend\Form\NodeFactory
     */
    public function getNodeFactory()
    {
        return $this->nodeFactory;
    }

    protected function isNEWRecord($uid)
    {
        return substr($uid, 0, 3) == 'NEW';
    }
    /**
     * Compile formdata for database record. Result is cached.
     * Bei der Neuanlage ($uid beginnt mit NEW) muss in $record die 'pid' gesetzt sein. Zusätzlich
     * können werden Attribute mit default-Werten übergeben werden. Die Attribute müssen aber in der
     * TCA[$table]['ctrl']['useColumnsForDefaultValues'] konfiguriert sein.
     * @param string $table
     * @param string $uid
     * @param array $record should contain pid and other default values for record
     * @return multitype:
     */
    protected function compileFormData($table, $uid, $record)
    {
        $cacheKey = $table.'_'.$uid;//.intval($uid);
        if (!array_key_exists($cacheKey, $this->formDataCache)) {
            if ($this->isNEWRecord($uid)) {
                // Die UID ist hier die PID
                // Es wird intern beim compile eine NEWuid festgelegt
                // Vorbelegung von Felder ist noch nicht möglich...
                $formDataCompilerInput = [
                    'tableName' => $table,
                    'vanillaUid' => (int)$record['pid'],
                    'command' => 'new',
                    'returnUrl' => '',
                    'neighborRow' => [],
                ];
                foreach ($record as $key => $value) {
                    if ($key == 'pid') {
                        continue;
                    }
                    $formDataCompilerInput['neighborRow'][$key] = $value;
                }
            } else {
                $formDataCompilerInput = [
                        'tableName' => $table,
                        'vanillaUid' => (int)$uid,
                        'command' => 'edit',
                        'returnUrl' => '',
                ];
            }
            $this->formDataCache[$cacheKey] = $this->formDataCompiler->compile($formDataCompilerInput);
            if ($this->isNEWRecord($uid)) {
                // Override generated with given uid
                $this->formDataCache[$cacheKey]['databaseRow']['uid'] = $uid;
            }
        }

        return $this->formDataCache[$cacheKey];
    }
    /**
     *
     * @param string $table
     * @param array $row
     * @param string $fieldName
     * @return string
     */
    public function getSoloField($table, $row, $fieldName)
    {

        // Wir benötigen pro DB-Tabelle ein data-Array mit den vorbereiteten Formular-Daten
        $formData = $this->compileFormData($table, $row['uid'], $row);
//         $options = $this->data;
        $options = $formData;
        // in den folgenden Key müssen die Daten aus der TCA rein. Wie geht das?
        $options['tableName'] = $table;
        $options['fieldName'] = $fieldName;
        $options['renderType'] = 'singleFieldContainer';

        $childResultArray = $this->nodeFactory->create($options)->render();

        // TODO: dieser Aufruf sollte einmalig für das gesamte Formular erfolgen!
        $this->formResultCompiler->mergeResult($childResultArray);


        return $childResultArray['html'];
    }

    public function printNeededJSFunctions_top()
    {
        return $this->formResultCompiler->JStop();
    }
    public function printNeededJSFunctions()
    {
        return $this->formResultCompiler->printNeededJSFunctions(); // TODO
    }
}
