<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Rene Nitzsche (rene@system25.de)
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

require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');
tx_div::load('tx_rnbase_util_DB');

define('SEARCH_FIELD_JOINED', 'JOINED'); // Sonderfall Freitextsuche in mehreren Feldern
define('SEARCH_FIELD_CUSTOM', 'CUSTOM'); // Sonderfall freie Where-Bedingung

define('OP_IN', 'IN STR');
/** IN für numerische Werte */
define('OP_NOTIN_INT', 'NOT IN');
define('OP_IN_INT', 'IN');
define('OP_IN_SQL', 'IN SQL');
define('OP_INSET_INT', 'FIND_IN_SET');
define('OP_LIKE', 'LIKE');
define('OP_EQ_INT', '=');
define('OP_NOTEQ', 'OP_NOTEQ');
define('OP_NOTEQ_INT', '!=');
define('OP_EQ_NOCASE', 'OP_EQ_NOCASE');
define('OP_LT_INT', '<');
define('OP_LTEQ_INT', '<=');
define('OP_GT_INT', '>');
define('OP_GTEQ_INT', '>=');


/**
 * Service for accessing team information
 * 
 * @author Rene Nitzsche
 */
abstract class tx_rnbase_util_SearchBase {
	private static $instances = array();

	/**
	 * Liefert eine Instanz einer konkreten Suchklasse. Der
	 * Klassenname sollte aber stimmen.
	 *
	 * @param string $classname
	 * @return tx_rnbase_util_SearchBase
	 */
	static function getInstance($classname) {
		if(!isset(self::$instances[$classname])) {
    	self::$instances[$classname] = tx_div::makeInstance($classname);
		}
		return self::$instances[$classname];
	}

  /**
   * Suchanfrage an die Datenbank
   * Bei den Felder findet ein Mapping auf die eigentlichen DB-Felder statt. Dadurch werden
   * SQL-Injections erschwert und es sind JOINs möglich.
   * Field-Schema: TABLEALIAS.COLNAME
   * Beispiel: TEAM.NAME, TEAM.UID
   * 
   * Options: Zusätzliche Bedingungen für Abfrage.
   * LIMIT, ORDERBY
   * 
   * Sonderfall Freitextsuche über mehrere Felder:
   * Hierfür gibt es das Sonderfeld SEARCH_FIELD_JOINED. Dieses erwartet ein Array der Form
   * 'value' => 'Suchbegriff'
   * 'cols' => array(FIELD1, FIELD2,...)
   * Hierfür gibt es das Sonderfeld SEARCH_FIELD_JOINED. Dieses erwartet ein Array der Form
   * 
   * Sonderfall SQL Sub-Select:
   * Hierfür gibt es das Sonderfeld SEARCH_FIELD_CUSTOM. Dieses erwartet ein String mit dem
   * Sub-Select. Dieser wird direkt in die Query eingebunden.
   * 
   * @param array $fields Felder nach denen gesucht wird 
   * @param array $options
   * @return array oder int
   */
  function search($fields, $options) {
  	$this->_initSearch();
  	$tableAliases = array();
  	if(isset($fields[SEARCH_FIELD_JOINED])) {
  		$joinedFields = $fields[SEARCH_FIELD_JOINED];
  		unset($fields[SEARCH_FIELD_JOINED]);
  	}
  	if(isset($fields[SEARCH_FIELD_CUSTOM])) {
  		$customFields = $fields[SEARCH_FIELD_CUSTOM];
  		unset($fields[SEARCH_FIELD_CUSTOM]);
  	}
  	// Die normalen Suchfelder abarbeiten
  	foreach ($fields As $field => $data) {
  		// Tabelle und Spalte ermitteln
  		list($tableAlias, $col) = explode('.', $field);
  		$tableAliases[$tableAlias][$col] = $data;
  	}
  	// Prüfen, ob in orderby noch andere Tabellen liegen
  	$orderbyArr = $options['orderby'];
  	if(is_array($orderbyArr)) {
  		$aliases = array_keys($orderbyArr);
  		foreach($aliases As $alias) {
  			list($tableAlias, $col) = explode('.', $alias);
  			if(!array_key_exists($tableAlias, $tableAliases))
  				$tableAliases[$tableAlias] = array();
  		}
  	}
  	if(is_array($joinedFields)) {
  		reset($joinedFields);
			foreach ($joinedFields As $key => $joinedField) {
				// Für die JOINED-Fields müssen die Tabellen gesetzt werden, damit der SQL-JOIN passt
	  		foreach($joinedField['cols'] AS $field) {
	  			list($tableAlias, $col) = explode('.', $field);
	  			if(!isset($tableAliases[$tableAlias]))
	  				$tableAliases[$tableAlias] = array();
	  			$joinedFields[$key]['fields'][] = $this->tableMapping[$tableAlias].'.' . strtolower($col);
	  		}
			}
  	}
  	

    $what = $this->getWhat($options);
    $from = $this->getFrom($options, $tableAliases);
    $where = '1=1';
    foreach($tableAliases AS $tableAlias => $colData) {
  		foreach($colData As $col => $data) {
  			foreach ($data As $operator => $value) {
					if(strlen($where) >0) $where .= ' AND ';
					$where .= tx_rnbase_util_DB::setSingleWhereField($this->tableMapping[$tableAlias], $operator, $col, $value);
  			}
  		}
    }
    // Jetzt die Freitextsuche über mehrere Felder
  	if(is_array($joinedFields)) {
  		foreach ($joinedFields As $joinedField) {
  			if($joinedField['operator'] == OP_INSET_INT) {
  				// Values splitten und einzelen Abfragen mit OR verbinden
	   			$addWhere = tx_rnbase_util_DB::searchWhere($joinedField['value'], implode(',',$joinedField['fields']), 'FIND_IN_SET_OR');
  			}
  			else {
  				$addWhere = tx_rnbase_util_DB::searchWhere($joinedField['value'], implode(',',$joinedField['fields']), $joinedField['operator']);
  			}
  			$where .= $addWhere;
  		}
  	}
  	if(isset($customFields)) {
  		$where .= ' AND ' . $customFields;
  	}
  	
		$sqlOptions['where'] = $where;
		if($options['pidlist'])
			$sqlOptions['pidlist'] = $options['pidlist'];
		if($options['recursive'])
			$sqlOptions['recursive'] = $options['recursive'];
		if($options['limit'])
			$sqlOptions['limit'] = $options['limit'];
		if($options['offset'])
			$sqlOptions['offset'] = $options['offset'];
		if($options['enablefieldsoff'])
			$sqlOptions['enablefieldsoff'] = $options['enablefieldsoff'];
		if($options['enablefieldsbe'])
			$sqlOptions['enablefieldsbe'] = $options['enablefieldsbe'];
		if($options['enablefieldsfe'])
			$sqlOptions['enablefieldsfe'] = $options['enablefieldsfe'];
		if($options['groupby'])
			$sqlOptions['groupby'] = $options['groupby'];
		if(!isset($options['count']) && is_array($options['orderby'])) {
			// Aus dem Array einen String bauen
			$orderby = array();
			if(array_key_exists('RAND', $options['orderby']) && $options['orderby']['RAND']) {
		    $orderby[] = 'RAND()';
		  }
		  else {
		  	if(array_key_exists('RAND', $options['orderby']))	unset($options['orderby']['RAND']);
				foreach ($options['orderby'] As $field => $order) {
					list($tableAlias, $col) = explode('.', $field);
					$tableAlias = $this->tableMapping[$tableAlias];
					if($tableAlias)
						$orderby[] = $tableAlias.'.' . strtolower($col) . ' ' . ( strtoupper($order) == 'DESC' ? 'DESC' : 'ASC');
					else {
						$orderby[] = $field . ' ' . ( strtoupper($order) == 'DESC' ? 'DESC' : 'ASC');
					}
				}
		  }
			$sqlOptions['orderby'] = implode(',', $orderby);
		}
		if(!(isset($options['count']) || isset($options['what']) || isset($options['groupby']) ))
			$sqlOptions['wrapperclass'] = $this->getWrapperClass();

		$result = tx_rnbase_util_DB::doSelect($what, $from, $sqlOptions, $options['debug'] ? 1 : 0);
		return isset($options['count']) ? $result[0]['cnt'] : $result;
	}

  private function _initSearch() {
  	if(!is_array($this->tableMapping)) {
  		$this->tableMapping = $this->getTableMappings();
  	}
  }

  /**
   * Kindklassen müssen ein Array bereitstellen, in denen die Aliases der
   * Tabellen zu den eigentlichen Tabellennamen gemappt werden.
   * @return array(alias => tablename, ...)
   */
  abstract protected function getTableMappings();
  /**
   * Kindklassen müssen ein Array bereitstellen, in denen die Operatoren der
   * für den Vergleich mit den DB-Felder definiert sind.
   * Die Operatoren sind als Konstanten definiert.
   * Da OP_LIKE als Default angenommen wird, müssen diese Felder nicht extra gesetzt 
   * werden.
   * @return array(colname => operator, ...)
   */
//  abstract protected function getFieldOperators();

  /**
   * Name der Basistabelle, in der gesucht wird
   */
  abstract protected function getBaseTable();
  /**
   * Name der Klasse, in die die Ergebnisse gemappt werden
   */
  abstract protected function getWrapperClass();

  /**
   * Kindklassen liefern hier die notwendigen DB-Joins. Ist kein JOIN erforderlich
   * sollte ein leerer String geliefert werden.
   *
   * @param array $tableAliases
   * @return string 
   */
  abstract protected function getJoins($tableAliases);

  protected function getWhat($options) {
  	if(isset($options['what'])) {
  		// Wenn "what" gesetzt ist, dann sollte es passen...
  		return $options['what'];
  	}
  	$table = $this->getBaseTable();
  	$distinct = isset($options['distinct']) ? 'DISTINCT ' : '';
  	$rownum = isset($options['rownum']) ? ', @rownum:=@rownum+1 AS rownum ' : '';
  	return isset($options['count']) ? 'count('. $distinct .$table.'.uid) as cnt' : $distinct.$table.'.*'.$rownum;
  }

  /**
   * Build the from part of sql statement
   *
   * @param array $options
   * @param array $tableAliases
   * @return array
   */
  protected function getFrom($options, $tableAliases) {
  	$table = $this->getBaseTable();
  	$from = array($table,$table);
  	$joins = $this->getJoins($tableAliases);
  	if(isset($options['rownum'])) $from[0] = '(SELECT @rownum:=0) _r, ' . $from[0];
 
  	if(strlen($joins))
  		$from[0] .= $joins;
  	return $from;
  }


	/**
	 * Optionen aus der TS-Config setzen
	 * 
	 * @param array $options
	 * @param tx_rnbase_configurations $configurations
	 * @param string $confId Id der TS-Config z.B. myview.options.
	 */
	static function setConfigOptions(&$options, &$configurations, $confId) {
		$cfgOptions = $configurations->get($confId);
		if(is_array($cfgOptions))
			foreach($cfgOptions As $option => $cfg) {
				// Auf einfache Option ohne Klammerung prüfen
				if(substr($option, -1) != '.') {
					$options[$option] = $cfg;
					continue;
				}
				// Zuerst den Namen der Option holen. Dieser ist immer klein
				// Beispiel orderby, count...
				$optionName = strtolower(substr($option, 0, strlen($option) -1));
				if(!is_array($cfg)) continue; // Ohne Angaben nix zu tun

				// Hier jetzt die Implementierung für orderby. da gibt es mehr
				// Angaben als z.B. bei count.
				while(list($table, $data) = each($cfg)) {
					$tableAlias = strtoupper(substr($table, 0, strlen($table) -1));
					if(is_array($data))
						foreach($data AS $col => $value) {
							$options[$optionName][$tableAlias.'.'.$col] = $value;
						}
					else // Ohne Array erfolgt direkt eine Ausgabe (Beispiel RAND = 1)
						$options[$optionName][$table] = $data;
				}
			}
	}

	/**
	 * Felder über ein Configarray setzen
	 *
	 * @param array $fields
	 * @param array $cfgFields
	 */
	static function setConfigFieldsByArray(&$fields, &$cfgFields) {
		if(is_array($cfgFields))
			foreach($cfgFields As $field => $cfg) {
				// Tabellen-Alias
				$tableAlias = (substr($field, strlen($field) -1, 1) == '.') ? 
											strtoupper(substr($field, 0, strlen($field) -1)) : strtoupper($field);

				if($tableAlias == SEARCH_FIELD_JOINED) {
					// Hier sieht die Konfig etwas anders aus
					foreach($cfg As $jField) {
						$jField['operator'] = constant($jField['operator']);
						$jField['cols'] = t3lib_div::trimExplode(',',$jField['cols']);
						$fields[SEARCH_FIELD_JOINED][] = $jField;
					}
					continue;
				}
				if($tableAlias == SEARCH_FIELD_CUSTOM) {
					$fields[SEARCH_FIELD_CUSTOM] = $cfg;
				}

				// Spaltenname
				if(!is_array($cfg)) continue;
				while(list($col, $data) = each($cfg)) {
					$colName = strtoupper(substr($col, 0, strlen($col) -1));
					// Operator und Wert
					if(!is_array($data)) continue;
					list($op, $value) = each($data);
					$fields[$tableAlias.'.'.$colName][constant($op)] = $value;
				}
			}
	}

	/**
   * Vergleichsfelder aus der TS-Config setzen
   * 
   * @param array $fields
   * @param tx_rnbase_configurations $configurations
   * @param string $confId Id der TS-Config z.B. myview.fields.
   */
  static function setConfigFields(&$fields, &$configurations, $confId) {
  	$cfgFields = $configurations->get($confId);
  	self::setConfigFieldsByArray($fields, $cfgFields);
  }

  /**
   * Checks existence of search field in parameters and adds it to fieldarray.
   *
   * @param string $idstr
   * @param array $fields
   * @param arrayObject $parameters
   * @param tx_rnbase_configurations $configurations
   * @param string $operator
   */
  function setField($idstr, &$fields, &$parameters, &$configurations, $operator = OP_LIKE) {
  	if(!isset($fields[$idstr][$operator]) && $parameters->offsetGet($idstr)) {
  		$fields[$idstr][$operator] = $parameters->offsetGet($idstr);
  		// Parameter als KeepVar merken
  		// TODO: Ist das noch notwendig??
  		$configurations->addKeepVar($configurations->createParamName($idstr),$fields[$idstr]);
  	}
  }
  function getSpecialChars() {
  	$specials['0-9'] = array('1','2','3','4','5','6','7','8','9','0','.','@','');
  	$specials['A'] = array('A','Ä');
  	$specials['O'] = array('O','Ö');
  	$specials['U'] = array('U','Ü');
  	return $specials;
  }
  
  
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/search/class.tx_rnbase_util_SearchBase.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/search/class.tx_rnbase_util_SearchBase.php']);
}

?>