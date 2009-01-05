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

require_once (PATH_t3lib.'class.t3lib_tceforms.php');

/**
 * Diese Klasse stellt hilfreiche Funktionen zur Erstellung von Formularen
 * im Backend zur Verfügung
 */
class tx_rnbase_util_FormTool {
	var $form; // TCEform-Instanz

	function init($doc) {
		global $BACK_PATH;
		$this->doc = $doc;

		// TCEform für das Formular erstellen
		$this->form = t3lib_div::makeInstance('t3lib_TCEforms');
		$this->form->initDefaultBEmode();
		$this->form->backPath = $BACK_PATH;
	}
	/**
	 * Erstellt einen Button zur Bearbeitung eines Datensatzes
	 * @param string $editTable DB-Tabelle des Datensatzes
	 * @param int $editUid UID des Datensatzes
	 * @param array $options additional options (title, params)
	 * @return string
	 */
	function createEditButton($editTable, $editUid, $options = array()) {
		$title = isset($options['title']) ? $options['title'] : 'Edit';
		$params = '&edit['.$editTable.']['.$editUid.']=edit';
		if(isset($options['params']))
			$params .= $options['params'];
		
		$btn = '<input type="button" name="'. $name.'" value="' . $title . '" ';
		$btn .= 'onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'])).'"';
		$btn .= '/>';
		return $btn;
	}
	
	/**
	 * Erstellt einen Link zur Bearbeitung eines Datensatzes
	 * @param $editTable DB-Tabelle des Datensatzes
	 * @param $editUid UID des Datensatzes
	 * @param $label Bezeichnung des Links
	 * @return string
	 */
	function createEditLink($editTable, $editUid, $label = 'Edit') {
		$params = '&edit['.$editTable.']['.$editUid.']=edit';
		return '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'])).'">'.
			'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="Edit UID: '.$editUid.'" border="0" alt="" />'.
		$label .'</a>';
	}
	/**
	 * Erstellt einen History-Link
	 *
	 * @param string $table
	 * @param int $recordUid
	 * @return string
	 */
	function createHistoryLink($table, $recordUid, $label = '') {
		return "<a href=\"#\" onclick=\"return jumpExt('".$GLOBALS['BACK_PATH'].
				"show_rechis.php?element=".rawurlencode($table.':'.$recordUid).
				"','#latest');\"><img ".t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/history2.gif','width="13" height="12"').
				' title="'.$GLOBALS['LANG']->getLL('history',1).'\" alt="" >'.$label.'</a>';
	}

	/**
	 * Creates a new-record-button
	 *
	 * @param string $table
	 * @param int $pid
	 * @param array $options
	 * @return string
	 */
	function createNewButton($table, $pid, $options=array()) {
		$params = '&edit['.$table.']['.$pid.']=new';
		if(isset($options['params']))
			$params .= $options['params'];
		$title = isset($options['title']) ? $options['title'] : $GLOBALS['LANG']->getLL('new',1);

		$btn = '<input type="button" name="'. $name.'" value="' . $title . '" ';
		$btn .= 'onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH']),-1).'"';
		$btn .= '/>';
		return $btn;
	}
	/**
	 * Erstellt einen Link zur Erstellung eines neuen Datensatzes
	 * @param string $table DB-Tabelle des Datensatzes
	 * @param int $pid UID der Zielseite
	 * @param string $label Bezeichnung des Links
	 * @param array $options
	 * @return string
	 */
	function createNewLink($table, $pid, $label = 'New', $options=array()) {
		$params = '&edit['.$table.']['.$pid.']=new';
		if(isset($options['params']))
			$params .= $options['params'];
		$title = isset($options['title']) ? $options['title'] : $GLOBALS['LANG']->getLL('new',1);
		return '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH']),-1).'">'.
			'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$title.'" alt="" />'.
			$label .'</a>';
  }

  /**
   * Erstellt einen Link zur Anzeige von Informationen über einen Datensatz
   * @param $editTable DB-Tabelle des Datensatzes
   * @param $editUid UID des Datensatzes
   * @param $label Bezeichnung des Links
   */
  function createInfoLink($editTable, $editUid, $label = 'Info') {
//    $params = '&edit['.$editTable.']['.$editUid.']=edit';
    return '<a href="#" onclick="top.launchView(' . "'" . $editTable . "', ' " . $editUid . "'); return false;" . '">'.
     '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/zoom2.gif','width="11" height="12"').' title="UID: '.$editUid.'" border="0" alt="" />'.
     $label .'</a>';
  }

  /**
   * Erstellt einen Link zum Verschieben eines Datensatzes auf eine andere Seite
   * @param $editTable DB-Tabelle des Datensatzes
   * @param $recordUid UID des Datensatzes
   * @param $currentPid PID der aktuellen Seite des Datensatzes
   * @param $label Bezeichnung des Links
   */
  function createMoveLink($editTable, $recordUid, $currentPid, $label = 'Move') {

    return "<a href=\"#\" onclick=\"return jumpSelf('/typo3/db_list.php?id=". $currentPid ."&amp;CB[el][" . $editTable
           . "%7C" . $recordUid . "]=1');\"><img " .t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/clip_cut.gif','width="16" height="16"'). ' title="UID: '. $recordUid . '" alt="" />' . $label .'</a>';

  }

  /**
   * Erstellt einen Link auf die aktuelle Location mit zusätzlichen Parametern
   */
  function createLink($params, $pid, $label) {

    return '<a href="#" onclick="'.htmlspecialchars("window.location.href='index.php?id=".$pid . $params)."'; return false;\">".
       $label .'</a>';
  }


  function createHidden($name, $value){
    return '<input type="hidden" name="'. $name.'" value="' . $value . '" />';
  }

	function createRadio($name, $value, $checked = false, $onclick = ''){
		return '<input type="radio" name="'. $name.'" value="' . $value . '" '. ($checked ? 'checked="checked"' : '') . (strlen($onclick) ? ' onclick="' . $onclick . '"' : '') . ' />';
	}

	function createCheckbox($name, $value, $checked = false, $onclick = ''){
		return '<input type="checkbox" name="'. $name.'" value="' . $value . '" '. ($checked ? 'checked="checked"' : '') . (strlen($onclick) ? ' onclick="' . $onclick . '"' : '') .' />';
	}
  
  function createSubmit($name, $value, $confirmMsg = ''){
    $btn = '<input type="submit" name="'. $name.'" value="' . $value . '" ';
    if(strlen($confirmMsg)) 
    	$btn .= 'onclick="return confirm('.$GLOBALS['LANG']->JScharCode($confirmMsg).')"';
    $btn .= '/>';
    return $btn;
  }
  
	/**
	 * Erstellt ein Textarea
	 */
	function createTextArea($name, $value, $cols='30', $rows='5', $options=0){
		$options = is_array($options) ? $options : array();
		$onChangeStr = $options['onchange'] ? ' onchange=" ' . $options['onchange'] . '" ' : '';
		return '
			<textarea name="' . $name . '" style="width:288px;" class="formField1"'. $onChangeStr .
      ' cols="'.$cols.'" rows="'.$rows.'" wrap="virtual">' . $value . '</textarea>';
  }

  /**
   * Erstellt ein einfaches Textfield
   */
  function createTxtInput($name, $value, $width){
    return '<input type="text" name="'. $name.'"'.$this->doc->formWidth($width).' value="' . $value . '" />';
  }

  /**
   * Erstellt ein Eingabefeld für Integers
   */
  function createIntInput($name, $value, $width, $maxlength=10){
//t3lib_div::debug($GLOBALS['TBE_TEMPLATE']->formWidth(10), 'form');
//      <input type="text" name="' . $name . '_hr"'.$this->doc->formWidth($width).

    $out = '
      <input type="text" name="' . $name . '_hr"'.$GLOBALS['TBE_TEMPLATE']->formWidth($width).
      ' onchange="typo3FormFieldGet(\'' . $name . '\', \'int\', \'\', 0,0);"'.
      $GLOBALS['TBE_TEMPLATE']->formWidth(12). ' maxlength="' . $maxlength . '"/>'.' 
      <input type="hidden" value="'.htmlspecialchars($value).'" name="' . $name . '" />';

    // JS-Code für die Initialisierung im TCEform eintragen
    $this->form->extJSCODE .= 'typo3FormFieldSet("' . $name . '", "int", "", 0,0);';


    return $out;
  }

	/**
	 * Erstellt ein Eingabefeld für DateTime
	 */
	function createDateInput($name, $value){
		// Take care of current time zone. Thanks to Thomas Maroschik!
		$value += date('Z', $value);
		$out = '
			<input type="text" name="' . $name . '_hr"
				onchange="typo3FormFieldGet(\'' . $name . '\', \'datetime\', \'\', 0,0);"'.
			$GLOBALS['TBE_TEMPLATE']->formWidth(11).
		' />'.'
			<input type="hidden" value="'.htmlspecialchars($value).'" name="' . $name . '" />';

		// JS-Code für die Initialisierung im TCEform eintragen
		$this->form->extJSCODE .= 'typo3FormFieldSet("' . $name . '", "datetime", "", 0,0);';
		return $out;
	}

  /**
   * Erstellt eine Selectbox mit festen Werten in der TCA. 
   * Die Labels werden in der richtigen Sprache angezeigt.
   */
  function createSelectSingle($name, $value, $table, $column, $options = 0){
    global $TCA, $LANG;
    $options = is_array($options) ? $options : array();

    $out = '<select name="' . $name . '" class="select" ';
    if($options['onchange'])
    	$out .= 'onChange="' . $options['onchange'] .'" ';
    $out .= '>';

    // Die TCA laden
    t3lib_div::loadTCA($table);

    // Die Options ermitteln
    foreach($TCA[$table]['columns'][$column]['config']['items'] As $item){
      $sel = '';
      if (intval($value) == intval($item[1])) $sel = 'selected="selected"';
      $out .= '<option value="' . $item[1] . '" ' . $sel . '>' . $LANG->sL($item[0]) . '</option>';
    }
    $out .= '
      </select>
    ';
    return $out;
  }

  /**
   * Erstellt eine Select-Box aus dem übergebenen Array
   */
  function createSelectSingleByArray($name, $value, $arr, $options=0){
  	$options = is_array($options) ? $options : array();
  	
    $onChangeStr = $options['reload'] ? ' this.form.submit(); ' : '';
    if($options['onchange']) {
    	$onChangeStr .= $options['onchange'];
    }
    if($onChangeStr)
    	$onChangeStr = ' onchange="'.$onChangeStr.'" ';

    $out = '
      <select name="' . $name . '" class="select"' . $onChangeStr . '> 
    ';
    
    // Die Options ermitteln
    foreach($arr As $key => $val){
      $sel = '';
      if (intval($value) == intval($key)) $sel = 'selected="selected"';
      $out .= '<option value="' . $key . '" ' . $sel . '>' . $val . '</option>';
    }
    $out .= '
      </select>
    ';
    return $out;
  }

  function addTCEfield2Stack($table,$row,$fieldname,$pre='',$post='') {
		$this->tceStack[] = $pre . $this->form->getSoloField($table,$row,$fieldname) . $post;
  }
	function getTCEfields($formname) {
		$ret[] = $this->form->printNeededJSFunctions_top();
		$ret[] = implode('', $this->tceStack);
		$ret[] = $this->form->printNeededJSFunctions();
		$ret[] = $this->form->JSbottom($formname);
		return $ret;
	}
	/**
	 * @param int $pid ID der aktuellen Seite
	 * @param string $location module url or empty
	 */
	function getJSCode($pid, $location='') {
		$location = $location ? $location : t3lib_div::linkThisScript(array('CB'=>'','SET'=>'','cmd' => '','popViewId'=>''));
		// Add JavaScript functions to the page:
		$JScode=$this->doc->wrapScriptTags('
			function jumpToUrl(URL) {
				window.location.href = URL;
				return false;
			}
			function jumpExt(URL,anchor) {
				var anc = anchor?anchor:"";
				window.location.href = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
				return false;
			}
			function jumpSelf(URL) {
				window.location.href = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
				return false;
			}
			function setHighlight(id) {
				top.fsMod.recentIds["web"]=id;
				top.fsMod.navFrameHighlightedID["web"]="pages"+id+"_"+top.fsMod.currentBank;    // For highlighting

				if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)  {
					top.content.nav_frame.refresh_nav();
				}
			}
			var T3_RETURN_URL = "'.str_replace('%20','',rawurlencode(t3lib_div::_GP('returnUrl'))).'";
			var T3_THIS_LOCATION="'.str_replace('%20','',rawurlencode($location)).'"');

    // Setting up the context sensitive menu:
//    $CMparts=$this->doc->getContextMenuCode();
//    $this->doc->bodyTagAdditions = $CMparts[1];
//    $JScode.=$CMparts[0];
//    $this->doc->postCode.= $CMparts[2];
    return $JScode;
  }
	/**
	 * Zeigt ein TabMenu
	 *
	 * @param int $pid
	 * @param string $name
	 * @param array $entries
	 * @return array with keys 'menu' and 'value'
	 */
	public function showTabMenu($pid, $name, $modName, $entries) {
		$MENU = Array (
			$name => $entries
		);
		$SETTINGS = t3lib_BEfunc::getModuleData(
			$MENU,t3lib_div::_GP('SET'),$modName
		);

		$out = '
		<div class="typo3-dyntabmenu-tabs">
			<table class="typo3-dyntabmenu" border="0" cellpadding="0" cellspacing="0">
			<tbody><tr>';

		foreach($entries As $key => $value) {
			//$out .= '<td class="tab" onmouseover="DTM_mouseOver(this);" onmouseout="DTM_mouseOut(this);" nowrap="nowrap">';
			$out .= '
				<td class="tab'.($SETTINGS[$name] == $key ? 'Act' : '').'" nowrap="nowrap">';
			//$out .= '<a href="#" onclick="jumpToUrl(\'index.php?&amp;id='.$pid.'&amp;SET['.$name.']='. $key .',this);\'>'.$value.'<img name="DTM-307fab8d03-1-REQ" src="clear.gif" alt="" height="10" hspace="4" width="10"></a></td>';
			$out .= '<a href="#" onclick="jumpToUrl(\'index.php?&amp;id='.$pid.'&amp;SET['.$name.']='. $key .'\',this);">'.$value.'</a></td>';
		}
		$out .= '
				</tr>
			</tbody></table></div>
		';
		$ret['menu'] = $out;
		$ret['value'] = $SETTINGS[$name];
		return $ret;
	}
	/**
	 * Zeigt eine Art Tab-Menu
	 *
	 */
	public function showMenu($pid, $name, $modName, $entries) {
		$MENU = Array (
			$name => $entries
		);
		$SETTINGS = t3lib_BEfunc::getModuleData(
			$MENU,t3lib_div::_GP('SET'),$modName
		);
		$ret['menu'] = t3lib_BEfunc::getFuncMenu(
			$pid,'SET['.$name.']',$SETTINGS[$name],$MENU[$name]
		);
		$ret['value'] = $SETTINGS[$name];
		return $ret;
  }

  function getTCEFormArray($table,$theUid, $isNew = false) {
		$trData = t3lib_div::makeInstance('t3lib_transferData');
		$trData->addRawData = TRUE;
//		$trData->defVals = $this->defVals;
//		$trData->lockRecords=1;
//		$trData->disableRTE = $this->MOD_SETTINGS['disableRTE'];
//		$trData->prevPageID = $prevPageID;
		$trData->fetchRecord($table,$theUid,$isNew?'new':'');	// 'new'
		reset($trData->regTableItems_data);
		return $trData->regTableItems_data;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_FormTool.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_FormTool.php']);
}

?>
