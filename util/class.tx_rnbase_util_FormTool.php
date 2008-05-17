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
	 * Erstellt einen Link zur Bearbeitung eines Datensatzes
	 * @param $editTable DB-Tabelle des Datensatzes
	 * @param $editUid UID des Datensatzes
	 * @param $label Bezeichnung des Links
	 */
  function createEditLink($editTable, $editUid, $label = 'Edit') {
  	$params = '&edit['.$editTable.']['.$editUid.']=edit';
  	return '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'])).'">'.
  		'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="Edit UID: '.$editUid.'" border="0" alt="" />'.
  		$label .'</a>';
	}

	/**
	 * Erstellt einen Link zur Erstellung eines neuen Datensatzes
	 * @param $$table DB-Tabelle des Datensatzes
	 * @param $$pid UID der Zielseite
	 * @param $label Bezeichnung des Links
	 */
	function createNewLink($table, $pid, $label = 'New') {
		$params = '&edit['.$table.']['.$pid.']=new';
		return '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH']),-1).'">'.
			'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$GLOBALS['LANG']->getLL('new',1).'" alt="" />'.
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
     $label .'</a><br/>';
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
  function createTextArea($name, $value, $cols='30', $rows='5'){
    return '
      <textarea name="' . $name . '" style="width:288px;" class="formField1"'.
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
  function createSelectSingle($name, $value, $table, $column){
    global $TCA, $LANG;

    $out = '
      <select name="' . $name . '" class="select"> 
    ';
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
  function createSelectSingleByArray($name, $value, $arr, $reload=0){
    $reloadStr = $reload ? ' onChange="this.form.submit()" ' : '';

    $out = '
      <select name="' . $name . '" class="select"' . $reloadStr . '> 
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
   * @param $pid ID der aktuellen Seite
   */
  function getJSCode($pid) {
    // Add JavaScript functions to the page:
    $JScode=$this->doc->wrapScriptTags('
      function jumpToUrl(URL) {       //
        window.location.href = URL;
        return false;
      }
      function jumpExt(URL,anchor)    {       //
        var anc = anchor?anchor:"";
        window.location.href = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
        return false;
      }
      function jumpSelf(URL)  {       //
        window.location.href = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
        return false;
      }

      function setHighlight(id)       {       //
        top.fsMod.recentIds["web"]=id;
        top.fsMod.navFrameHighlightedID["web"]="pages"+id+"_"+top.fsMod.currentBank;    // For highlighting

        if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)  {
                top.content.nav_frame.refresh_nav();
        }
      }
      var T3_RETURN_URL = "";
      var T3_THIS_LOCATION = "db_list.php%3Fid%3D' . $pid . '%26table%3D";');


    // Setting up the context sensitive menu:
//    $CMparts=$this->doc->getContextMenuCode();
//    $this->doc->bodyTagAdditions = $CMparts[1];
//    $JScode.=$CMparts[0];
//    $this->doc->postCode.= $CMparts[2];
    return $JScode;
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league/class.tx_cfcleague_form_tool.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cfc_league/class.tx_cfcleague_form_tool.php']);
}

?>
