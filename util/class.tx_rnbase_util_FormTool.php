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
	 * @return template the BE template class
	 */
	public function getDoc() {
		return $this->doc;
	}
	/**
	 * Erstellt einen Button zur Bearbeitung eines Datensatzes
	 * @param string $editTable DB-Tabelle des Datensatzes
	 * @param int $editUid UID des Datensatzes
	 * @param array $options additional options (title, params)
	 * @return string
	 */
	public function createEditButton($editTable, $editUid, $options = array()) {
		$title = isset($options['title']) ? $options['title'] : 'Edit';
		$params = '&edit['.$editTable.']['.$editUid.']=edit';
		if(isset($options['params']))
			$params .= $options['params'];

		$jsCode = t3lib_BEfunc::editOnClick($params, $GLOBALS['BACK_PATH']);
		if(isset($options['confirm']) && strlen($options['confirm']) > 0) {
    	$jsCode = 'if(confirm('.$GLOBALS['LANG']->JScharCode($options['confirm']).')) {' . $jsCode .'} else {return false;}';
		}

		$btn = '<input type="button" name="'. $name.'" value="' . $title . '" ';
		$btn .= 'onclick="'.htmlspecialchars($jsCode).'"';
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
	public function createEditLink($editTable, $editUid, $label = 'Edit') {
		$params = '&edit['.$editTable.']['.$editUid.']=edit';
		return '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params, $GLOBALS['BACK_PATH'])).'">'.
			'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/edit2.gif', 'width="11" height="12"').' title="Edit UID: '.$editUid.'" border="0" alt="" />'.
		$label .'</a>';
	}
	/**
	 * Erstellt einen History-Link
	 * Achtung: Benötigt die JS-Funktion jumpExt() in der Seite.
	 *
	 * @param string $table
	 * @param int $recordUid
	 * @return string
	 */
	public function createHistoryLink($table, $recordUid, $label = '') {
		return "<a href=\"#\" onclick=\"return jumpExt('".$GLOBALS['BACK_PATH'].
				"show_rechis.php?element=".rawurlencode($table.':'.$recordUid).
				"','#latest');\"><img ".t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/history2.gif', 'width="13" height="12"').
				' title="'.$GLOBALS['LANG']->getLL('history', 1).'\" alt="" >'.$label.'</a>';
	}

	/**
	 * Creates a new-record-button
	 *
	 * @param string $table
	 * @param int $pid
	 * @param array $options
	 * @return string
	 */
	public function createNewButton($table, $pid, $options=array()) {
		$params = '&edit['.$table.']['.$pid.']=new';
		if(isset($options['params']))
			$params .= $options['params'];
		$title = isset($options['title']) ? $options['title'] : $GLOBALS['LANG']->getLL('new', 1);

		$jsCode = t3lib_BEfunc::editOnClick($params, $GLOBALS['BACK_PATH']);
		if(isset($options['confirm']) && strlen($options['confirm']) > 0) {
    	$jsCode = 'if(confirm('.$GLOBALS['LANG']->JScharCode($options['confirm']).')) {' . $jsCode .'} else {return false;}';
		}

		$btn = '<input type="button" name="'. $name.'" value="' . $title . '" ';
		$btn .= 'onclick="'.htmlspecialchars($jsCode, -1).'"';
		$btn .= '/>';
		return $btn;
	}
	/**
	 * Creates a Link to show an item in frontend.
	 */
	public function createShowLink($pid, $label, $urlParams = '', $options=array()) {
		if($options['icon']) {
			$label = "<img ".t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/'.$options['icon']).
				' title="'.$label.'\" alt="" >';
		}
		if($options['sprite']) {
			tx_rnbase::load('tx_rnbase_mod_Util');
			$label = tx_rnbase_mod_Util::getSpriteIcon($options['sprite']);
		}
		$jsCode = t3lib_BEfunc::viewOnClick($pid, '', '', '', '', $urlParams);
		$title = '';
		if($options['hover']) {
			$title = ' title="'.$options['hover'].'" ';
		}
		return '<a href="#" onclick="'.htmlspecialchars($jsCode).'" '. $title.">". $label .'</a>';
	}

	/**
	 * Erstellt einen Link zur Erstellung eines neuen Datensatzes
	 * @param string $table DB-Tabelle des Datensatzes
	 * @param int $pid UID der Zielseite
	 * @param string $label Bezeichnung des Links
	 * @param array $options
	 * @return string
	 */
	public function createNewLink($table, $pid, $label = 'New', $options=array()) {
		$params = '&edit['.$table.']['.$pid.']=new';
		if(isset($options['params']))
			$params .= $options['params'];
		$title = isset($options['title']) ? $options['title'] : $GLOBALS['LANG']->getLL('new', 1);

		$jsCode = t3lib_BEfunc::editOnClick($params, $GLOBALS['BACK_PATH']);
		if(isset($options['confirm']) && strlen($options['confirm']) > 0) {
    	$jsCode = 'if(confirm('.$GLOBALS['LANG']->JScharCode($options['confirm']).')) {' . $jsCode .'} else {return false;}';
		}

		return '<a href="#" onclick="'.htmlspecialchars($jsCode, -1).'">'.
			'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/new_'.($table=='pages'?'page':'el').'.gif', 'width="'.($table=='pages'?13:11).'" height="12"').' title="'.$title.'" alt="" />'.
			$label .'</a>';
  }

  /**
   * Create a hide/unhide Link
   * @param string $table
   * @param int $uid
   * @param boolean $unhide
   * @param array $options
   */
	public function createHideLink($table, $uid, $unhide=FALSE, $options=array()) {
		$location = t3lib_div::linkThisScript(array('CB'=>'', 'SET'=>'', 'cmd' => '', 'popViewId'=>''));
		$location = str_replace('%20','', rawurlencode($location));

		$sEnableColumn = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'];
		//fallback
		$sEnableColumn = ($sEnableColumn) ? $sEnableColumn : 'hidden';
		$jumpToUrl = "'".$GLOBALS['BACK_PATH'].'tce_db.php?redirect='.$location.'&amp;data['.$table.'][' . $uid .']['. $sEnableColumn .']='.($unhide ? 0 : 1);
		$label = isset($options['label']) ? $options['label'] : '';

		//jetzt noch alles zur Formvalidierung einfügen damit
		//TYPO3 den Link akzeptiert und als valide einstuft
		//der Formularname ist immer tceAction
		$jumpToUrl .= "&amp;vC=".$GLOBALS['BE_USER']->veriCode();
		if(tx_rnbase_util_TYPO3::isTYPO45OrHigher())
			$jumpToUrl .= t3lib_BEfunc::getUrlToken('tceAction');
		$jumpToUrl .="'";

		return '<a onclick="return jumpToUrl('.$jumpToUrl.');" href="#">'.
				'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/'.($unhide ? 'button_hide.gif' : 'button_unhide.gif'), 'width="11" height="12"').' title="'.($unhide ? 'Show' : 'Hide').' UID: '.$uid.'" border="0" alt="" />'.
				$label.'</a>';
	}

	/**
	 * Erstellt einen Link zur Anzeige von Informationen über einen Datensatz
	 * @param $editTable DB-Tabelle des Datensatzes
	 * @param $editUid UID des Datensatzes
	 * @param $label Bezeichnung des Links
	 */
	public function createInfoLink($editTable, $editUid, $label = 'Info') {
//    $params = '&edit['.$editTable.']['.$editUid.']=edit';
		return '<a href="#" onclick="top.launchView(' . "'" . $editTable . "', ' " . $editUid . "'); return false;" . '">'.
		 '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/zoom2.gif', 'width="11" height="12"').' title="UID: '.$editUid.'" border="0" alt="" />'.
		 $label .'</a>';
	}

  /**
   * Erstellt einen Link zum Verschieben eines Datensatzes auf eine andere Seite
   * @param $editTable DB-Tabelle des Datensatzes
   * @param $recordUid UID des Datensatzes
   * @param $currentPid PID der aktuellen Seite des Datensatzes
   * @param $label Bezeichnung des Links
   */
  public function createMoveLink($editTable, $recordUid, $currentPid, $label = 'Move') {

    return "<a href=\"#\" onclick=\"return jumpSelf('/typo3/db_list.php?id=". $currentPid ."&amp;CB[el][" . $editTable
           . "%7C" . $recordUid . "]=1');\"><img " .t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/clip_cut.gif', 'width="16" height="16"'). ' title="UID: '. $recordUid . '" alt="" />' . $label .'</a>';

  }

	/**
	 * Erstellt einen Link zum Löschen eines Datensatzes
	 *
	 * @param string $table
	 * @param int $iUid
	 * @param string $sLabel
	 * @param array $options
	 */
	public function createDeleteLink($table, $uid, $label = 'Remove', $options = array()) {
		// @TODO: das verwenden wir bereit beim createHideLink, sollte besser ausgelagert werden!
		$location = t3lib_div::linkThisScript(array('CB'=>'', 'SET'=>'', 'cmd' => '', 'popViewId'=>''));
		$location = str_replace('%20','', rawurlencode($location));

		$jumpToUrl = '\''.$GLOBALS['BACK_PATH'].'tce_db.php?redirect='.$location.'&amp;cmd['.$table.']['.$uid.'][delete]=1';
		//jetzt noch alles zur Formvalidierung einfügen damit
		//TYPO3 den Link akzeptiert und als valide einstuft
		// der Formularname ist immer tceAction

		$jumpToUrl .= "&amp;vC=".$GLOBALS['BE_USER']->veriCode();
		if(tx_rnbase_util_TYPO3::isTYPO45OrHigher())
			$jumpToUrl .= t3lib_BEfunc::getUrlToken('tceAction');
		$jumpToUrl .="'";

		$jsCode = $this->getConfirmCode('return jumpToUrl('.$jumpToUrl.');', $options);
		return '<a onclick="'.$jsCode.'" href="#">'.
			'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/deletedok.gif', 'width="16" height="16"').' title="Delete UID: '.$uid.'" border="0" alt="" />'.
				$label.'</a>';
	}

	/**
	 * Fügt den JS Code für eine Confirm-Meldung hinzu, wenn in den Options gesetzt.
	 * @param string $jsCode
	 * @param array $options
	 */
	private function getConfirmCode($jsCode, $options) {
		if(isset($options['confirm']) && strlen($options['confirm']) > 0) {
			return 'if(confirm('.$GLOBALS['LANG']->JScharCode($options['confirm']).')) {' . $jsCode .'} else {return false;}';
		}
		return $jsCode;
	}

  public function createHidden($name, $value){
    return '<input type="hidden" name="'. $name.'" value="' . $value . '" />';
  }

	public function createRadio($name, $value, $checked = FALSE, $onclick = ''){
		return '<input type="radio" name="'. $name.'" value="' . $value . '" '. ($checked ? 'checked="checked"' : '') . (strlen($onclick) ? ' onclick="' . $onclick . '"' : '') . ' />';
	}

	public function createCheckbox($name, $value, $checked = FALSE, $onclick = ''){
		return '<input type="checkbox" name="'. $name.'" value="' . $value . '" '. ($checked ? 'checked="checked"' : '') . (strlen($onclick) ? ' onclick="' . $onclick . '"' : '') .' />';
	}

	/**
	 * Erstellt einen Link auf die aktuelle Location mit zusätzlichen Parametern
	 */
	public function createLink($urlParams, $pid, $label, $options=array()) {
		if($options['icon']) {
			$label = "<img ".t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/'.$options['icon']).
				' title="'.$label.'\" alt="" >';
		}
		if($options['sprite']) {
			tx_rnbase::load('tx_rnbase_mod_Util');
			$label = tx_rnbase_mod_Util::getSpriteIcon($options['sprite']);
		}

		$jsCode = "window.location.href='index.php?id=".$pid . $urlParams. "'; return false;";
		if(isset($options['confirm']) && strlen($options['confirm']) > 0) {
    	$jsCode = 'if(confirm('.$GLOBALS['LANG']->JScharCode($options['confirm']).')) {' . $jsCode .'} else {return false;}';
		}
		$title = '';
		if($options['hover']) {
			$title = ' title="'.$options['hover'].'" ';
		}

		return '<a href="#" onclick="'.htmlspecialchars($jsCode).'" '. $title.">". $label .'</a>';
	}

	/**
	 * Submit button for BE form.
	 * @param string $name
	 * @param string $value
	 * @param string $confirmMsg
	 * @param array $options
	 */
	public function createSubmit($name, $value, $confirmMsg = '', $options=array()){
		$icon = '';
		if($options['icon']) {
			$icon = t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/'.$options['icon']);
		}
//		if($options['sprite']) {
//			tx_rnbase::load('tx_rnbase_mod_Util');
//			$icon = tx_rnbase_mod_Util::getSpriteIcon($options['sprite']);
//		}

		$btn = '<input type="'.($icon ? 'image' : 'submit').'" name="'. $name.'" value="' . $value . '" ';
		if(strlen($confirmMsg))
			$btn .= 'onclick="return confirm('.$GLOBALS['LANG']->JScharCode($confirmMsg).')"';
		if(strlen($icon))
			$btn .= $icon;
		$btn .= '/>';
		return $btn;
	}

	/**
	 * Erstellt ein Textarea
	 */
	public function createTextArea($name, $value, $cols='30', $rows='5', $options=0){
		$options = is_array($options) ? $options : array();
		$onChangeStr = $options['onchange'] ? ' onchange=" ' . $options['onchange'] . '" ' : '';
		return '
			<textarea name="' . $name . '" style="width:288px;" class="formField1"'. $onChangeStr .
			' cols="'.$cols.'" rows="'.$rows.'" wrap="virtual">' . $value . '</textarea>';
	}

	/**
	 * Erstellt ein einfaches Textfield
	 */
	public function createTxtInput($name, $value, $width, $options = array()){

		$onChange = array_key_exists('onchange', $options) ? ' onchange="' . $options['onchange'].'"' : '';
		$ret = '<input type="text" name="'. $name.'"'.$this->doc->formWidth($width).
			$onChange .
			' value="' . $value . '" />';
		return $ret;
	}

	/**
	 * Erstellt ein Eingabefeld für Integers
	 */
	public function createIntInput($name, $value, $width, $maxlength=10){
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
	public function createDateInput($name, $value){
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
			if ($value === $item[1]) $sel = 'selected="selected"';
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
	public function createSelectSingleByArray($name, $value, $arr, $options=0){
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
		foreach($arr As $key => $val) {
			$sel = '';
			if ($value == $key) $sel = 'selected="selected"';
			$out .= '<option value="' . $key . '" ' . $sel . '>' . $val . '</option>';
		}
		$out .= '
			</select>
		';
		return $out;
	}

	/**
	 * Liefert einen Sortierungslink für das gegebene Feld
	 * @param string $sSortField
	 * @return string
	 */
	public function createSortLink($sSortField, $sLabel) {
		//das ist aktuell gesetzt
		$sCurrentSortField = t3lib_div::_GET('sortField');
		$sCurrentSortRev = t3lib_div::_GET('sortRev');
		//wir verweisen immer auf die aktuelle Seite
		//es kann aber schon ein sort parameter gesetzt sein
		//weshalb wir alte entfernen
		$sUrl = preg_replace('/&sortField=.*&sortRev=[^&]*/', '', t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));

		//sort richtung rausfinden
		//beim initialen Aufruf (spalte noch nicht geklickt) wird immer aufsteigend sortiert
		if($sCurrentSortField != $sSortField)
			$sSortRev = 'asc';
		else//sonst das gegenteil vom aktuellen
			$sSortRev = ($sCurrentSortRev == 'desc') ? 'asc' : 'desc';

		//prüfen ob Parameter mit ? oder & angehängt werden müssen
		$sAddParamsWith = (strstr($sUrl, '?')) ? '&' : '?';
		//jetzt setzen wir den aktuellen Sort parameter zusammen
		$sSortUrl = $sUrl.$sAddParamsWith.'sortField=' . $sSortField . '&sortRev=' . $sSortRev;
		//noch den Pfeil für die aktuelle Sortierungsrichtung ggf. einblenden
		$sSortArrow = ($sCurrentSortField==$sSortField?'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/red'.($sSortRev == 'asc'?'up':'down').'.gif', 'width="7" height="4"').' alt="" />':'');
		return '<a href="'.htmlspecialchars($sSortUrl).'">'.$sLabel.$sSortArrow.'</a>';
	}

  function addTCEfield2Stack($table, $row, $fieldname, $pre='', $post='') {
		$this->tceStack[] = $pre . $this->form->getSoloField($table, $row, $fieldname) . $post;
  }
  /**
   * @return t3lib_TCEforms
   */
  public function getTCEForm() {
  	return $this->form;
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
		$location = $location ? $location : t3lib_div::linkThisScript(array('CB'=>'', 'SET'=>'', 'cmd' => '', 'popViewId'=>''));
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
			var T3_RETURN_URL = "'.str_replace('%20', '', rawurlencode(t3lib_div::_GP('returnUrl'))).'";
			var T3_THIS_LOCATION="'.str_replace('%20', '', rawurlencode($location)).'"');

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
			$MENU, t3lib_div::_GP('SET'), $modName
		);

		$out = '
		<div class="typo3-dyntabmenu-tabs">
			<table class="typo3-dyntabmenu" border="0" cellpadding="0" cellspacing="0">
			<tbody><tr>';

		foreach($entries As $key => $value) {
			//$out .= '<td class="tab" onmouseover="DTM_mouseOver(this);" onmouseout="DTM_mouseOut(this);" nowrap="nowrap">';
			$out .= '
				<td class="tab'.($SETTINGS[$name] == $key ? 'act' : '').'" nowrap="nowrap">';
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
	 * Show function menu.
	 *
	 * @param int $pid
	 * @param string $name name of menu
	 * @param string $modName name of be module
	 * @param array $entries menu entries
	 * @param string $script The script to send the &id to, if empty it's automatically found
	 * @param string $addParams Additional parameters to pass to the script.
	 * @return array with keys 'menu' and 'value'
	 */
	public static function showMenu($pid, $name, $modName, $entries, $script = '', $addparams = '') {
		$MENU = Array (
			$name => $entries
		);
		$SETTINGS = t3lib_BEfunc::getModuleData(
			$MENU, t3lib_div::_GP('SET'), $modName
		);

		$ret['menu'] = (tx_rnbase_util_TYPO3::isTYPO62OrHigher() && is_array($MENU[$name]) && count($MENU[$name]) == 1) ?
				self::buildDummyMenu('SET['.$name.']', $MENU[$name]) :
				t3lib_BEfunc::getFuncMenu(
			$pid, 'SET['.$name.']', $SETTINGS[$name],
			$MENU[$name], $script, $addparams
		);
		$ret['value'] = $SETTINGS[$name];
		return $ret;
  }

	private static function buildDummyMenu($elementName, $menuItems) {
		// Ab T3 6.2 wird bei einem Menu-Eintrag keine Selectbox mehr erzeugt.
		// Also sieht man nicht, wo man sich befindet. Somit wird eine Dummy-Box
		// benötigt.
		$options = array();

		foreach ($menuItems as $value => $label) {
			$options[] = '<option value="' . htmlspecialchars($value) . '" selected="selected">' . htmlspecialchars($label, ENT_COMPAT, 'UTF-8', FALSE) . '</option>';
		}
		return '
				<!-- Function Menu of module -->
				<select name="' . $elementName . '" >
					' . implode('
					', $options) . '
				</select>
						';
	}

  /**
   * Submit-Button like this:  name="mykey[123]" value="label"
   * You will get 123 as long as no other submit changes this value.
   * @param string $key
   * @param string $modName
   * @return mixed
   */
	public function getStoredRequestData($key, $changed=array(), $modName='DEFRNBASEMOD') {
		$data = t3lib_div::_GP($key);
		if(is_array($data)) {
			list($itemid, ) = each($data);
			$changed[$key] = $itemid;
		}
		$ret = t3lib_BEfunc::getModuleData(array ($key => ''), $changed, $modName );
		return $ret[$key];
	}
	/**
	 * Load a fullfilled TCE data array for a database record.
	 * @param string $table
	 * @param int $theUid
	 * @param boolean $isNew
	 */
	public function getTCEFormArray($table, $theUid, $isNew = FALSE) {
		$trData = t3lib_div::makeInstance('t3lib_transferData');
		$trData->addRawData = TRUE;
//		$trData->defVals = $this->defVals;
//		$trData->lockRecords=1;
//		$trData->disableRTE = $this->MOD_SETTINGS['disableRTE'];
//		$trData->prevPageID = $prevPageID;
		$trData->fetchRecord($table, $theUid, $isNew?'new':'');	// 'new'
		reset($trData->regTableItems_data);
		return $trData->regTableItems_data;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_FormTool.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/util/class.tx_rnbase_util_FormTool.php']);
}


