<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche (rene@system25.de)
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

// Die Datenbank-Klasse
require_once(t3lib_extMgm::extPath('rn_base') . 'util/class.tx_rnbase_util_DB.php');
require_once(PATH_t3lib.'class.t3lib_befunc.php');

/**
 * Basisklasse für die meisten Model-Klassen. Sie stellt einen Konstruktor bereit, der sowohl
 * mit einer UID als auch mit einem Datensatz aufgerufen werden kann. Die Daten werden
 * in den Instanzvariablen $uid und $record abgelegt. Diese beiden Variablen sind also immer
 * verfügbar. Der Umfang von $record kann aber je nach Aufruf unterschiedlich sein!
 */
class tx_rnbase_model_base{

  var $uid;
  var $record;

  /**
   * Most model-classes will be initialized by a uid or a database record. So
   * this is a common contructor.
   * Ensure to overwrite getTableName()!
   */
  function tx_rnbase_model_base($rowOrUid) {
    if(is_array($rowOrUid)) {
      $this->uid = $rowOrUid['uid'];
      $this->record = $rowOrUid;
    }
    else{
      $this->uid = $rowOrUid;
      if($this->getTableName())
        $this->record = t3lib_BEfunc::getRecord($this->getTableName(),$this->uid);
      // Der Record sollte immer ein Array sein
      $this->record = is_array($this->record) ? $this->record : array();
    }
//    t3lib_div::debug($this->record, 'record');
  }

  /**
   * Kindklassen müssen diese Methode überschreiben und den Namen der gemappten Tabelle liefern!
   * @return Tabellenname als String
   */
  function getTableName() {
    return 0;
  }
  /**
   * Liefert bei Tabellen, die im $TCA definiert sind, die Namen der Tabellenspalten als Array.
   * @return Array mit Spaltennamen oder 0
   */
  function getColumnNames() {
    global $TCA;
    t3lib_div::loadTCA($this->getTableName());
    $cols = $this->getTCAColumns();
    return is_array($cols) ? array_keys($cols) : 0;
  }

  /**
   * Liefert die TCA-Definition der in der Tabelle definierten Spalten
   *
   * @return unknown
   */
  function getTCAColumns() {
    global $TCA;
    t3lib_div::loadTCA($this->getTableName());
    return isset($TCA[$this->getTableName()]) ? $TCA[$this->getTableName()]['columns'] : 0;
  }
  
  /**
   * Liefert den Inhalt eine Spalte formatiert durch eine stdWrap. Per Konvention wird
   * erwartet, das der Name der Spalte auch in der TS-Config verwendet wird.
   * Wenn in einem Objekt der Klasse event eine Spalte/Attribut "date" existiert, dann sollte
   * das passende TypoScript folgendes Aussehen haben:
   * <pre>
   * event.date.strftime = %d-%b-%y
   * </pre>
   * Hier wäre <b>event.</b> die $confId und <b>date</b> der Spaltename
   * @param $formatter ein voll initialisierter Formatter für den Wrap
   * @param $columnName der Name der Spalte
   * @param $baseConfId Id der übergeordneten Config
   * @param $colConfId Id der Spalte in der Config zum Aussetzen der Konvention (muss mit Punkt enden)
   */
  function getColumnWrapped($formatter, $columnName, $baseConfId, $colConfId = '') {
    $colConfId = ( strlen($colConfId) ) ? $colConfId : $columnName . '.';
    return $formatter->wrap($this->record[$columnName], $baseConfId . $colConfId);
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_base.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/model/class.tx_rnbase_model_base.php']);
}

?>
