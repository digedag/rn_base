<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2009 Rene Nitzsche (rene@system25.de)
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

require_once(PATH_t3lib . 'class.t3lib_scbase.php');

tx_rnbase::load('tx_rnbase_util_TYPO3');
/**
 */
abstract class tx_rnbase_mod_BaseModule extends t3lib_SCbase {
	public $doc;

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		// Einbindung externer Funktionen
		$this->checkExtObj();
		
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->getPid(),$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		$this->initDoc($this->getDoc());

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

			if(tx_rnbase_util_TYPO3::isTYPO42OrHigher()) {
				$this->content .= $this->moduleContent(); // Muss vor der Erstellung des Headers geladen werden
				$this->content .= $this->getDoc()->sectionEnd();  // Zur Sicherheit eine offene Section schließen
	
				$header = $this->getDoc()->header($LANG->getLL('title'));
				$this->content = $this->content; // ??
				// ShortCut
				if ($BE_USER->mayMakeShortcut())	{
					$this->content.=$this->getDoc()->spacer(20).$this->getDoc()->section('',$this->getDoc()->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
				}
				$this->content.=$this->getDoc()->spacer(10);
				// Setting up the buttons and markers for docheader
				$docHeaderButtons = $this->getButtons();
				$markers['CSH'] = $docHeaderButtons['csh'];
				$markers['HEADER'] = $header;
				$markers['SELECTOR'] = $this->subselector;
				$markers['TABS'] = $this->tabs;
				$markers['FUNC_MENU'] = t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function']);
				$markers['CONTENT'] = $this->content;
			}
			else {
				// HeaderSection zeigt Icons und Seitenpfad
				$headerSection = $this->getDoc()->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);
				$this->content .= $this->moduleContent(); // Muss vor der Erstellung des Headers geladen werden
				$this->content .= $this->getDoc()->sectionEnd();  // Zur Sicherheit einen offene Section schließen
	
				// startPage erzeugt alles bis Beginn Formular
				$header.=$this->getDoc()->startPage($LANG->getLL('title'));
				$header.=$this->getDoc()->header($LANG->getLL('title'));
				$header.=$this->getDoc()->spacer(5);
				$header.=$this->getDoc()->section('',$this->getDoc()->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->getPid(),'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
				$header.=$this->getDoc()->divider(5);
	
				$this->content = $header . $this->content;
				
				// ShortCut
				if ($BE_USER->mayMakeShortcut())	{
					$this->content.=$this->getDoc()->spacer(20).$this->getDoc()->section('',$this->getDoc()->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
				}
				$this->content.=$this->getDoc()->spacer(10);
			}

		} else {
			// User hat keine Zugriff
			if(tx_rnbase_util_TYPO3::isTYPO42OrHigher()) {
				$this->content = $this->getDoc()->section($LANG->getLL('title'), $LANG->getLL('clickAPage_content'), 0, 1);
		
					// Setting up the buttons and markers for docheader
				$docHeaderButtons = $this->getButtons();
				$markers['CSH'] = $docHeaderButtons['csh'];
				$markers['HEADER'] = $header;
				$markers['SELECTOR'] = $this->subselector;
				$markers['TABS'] = '';
				$markers['FUNC_MENU'] = '';
				$markers['CONTENT'] = $this->content;
			}
			else {
				// If no access or if ID == zero
				$this->content.=$this->getDoc()->startPage($LANG->getLL('title'));
				$this->content.=$this->getDoc()->header($LANG->getLL('title'));
				$this->content.=$this->getDoc()->spacer(15);
			}
		}
		if(tx_rnbase_util_TYPO3::isTYPO42OrHigher()) {
			$content = $this->getDoc()->startPage($LANG->getLL('title'));
			$content.= $this->getDoc()->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
			$this->content = $this->getDoc()->insertStylesAndJS($content);
		}
	}
	/**
	 * Generates the module content.
	 * Normaly we would call $this->extObjContent(); But this method writes the output to $this->content. We need
	 * the output directly so this is reimplementation of extObjContent()
	 *
	 * @return	void
	 */
	function moduleContent()	{
		$content = '';
		$this->extObj->pObj = &$this;
		if (is_callable(array($this->extObj, 'main')))	$content.=$this->extObj->main();
		
		return $content;
	}

	function checkExtObj()	{
		if (is_array($this->extClassConf) && $this->extClassConf['name'])	{
			$this->extObj = t3lib_div::makeInstance($this->extClassConf['name']);
			$this->extObj->init($this,$this->extClassConf);
				// Re-write:
			$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name'], $this->modMenu_type, $this->modMenu_dontValidateList, $this->modMenu_setDefaultList);
		}
	}

	/**
	 * Liefert bei Web-Modulen die aktuelle Pid
	 * @return unknown_type
	 */
	public function getPid() {
		return $this->id;
	}
	/**
	 * Returns a template instance
	 * Liefert die Instanzvariable doc. Die muss immer Public bleiben, weil auch einige TYPO3-Funktionen
	 * direkt darauf zugreifen.
	 * @return template
	 */
	public function getDoc() {
		if(!$this->doc) {
			$this->doc = tx_rnbase_util_TYPO3::isTYPO42OrHigher() ? $GLOBALS['TBE_TEMPLATE'] : t3lib_div::makeInstance('bigDoc');
		}
		return $this->doc;
	}
	protected function initDoc($doc) {
		$doc->backPath = $GLOBALS['BACK_PATH'];
		$doc->form='<form action="" method="post" enctype="multipart/form-data">';
		$doc->docType = 'xhtml_trans';
		$doc->inDocStyles = $this->getDocStyles();
		$doc->tableLayout = $this->getTableLayout();
		if(tx_rnbase_util_TYPO3::isTYPO42OrHigher()) {
			$doc->setModuleTemplate('../'.t3lib_extMgm::siteRelPath($this->getExtensionKey()) .  'mod1/template.html');
			$doc->loadJavascriptLib('contrib/prototype/prototype.js');
		}
		// JavaScript
		$doc->JScode .= '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
			</script>
			';

		// TODO: Die Zeile könnte problematisch sein...
		$doc->postCode='
			<script language="javascript" type="text/javascript">
				script_ended = 1;
				if (top.fsMod) top.fsMod.recentIds["web"] = ' . $this->id . ';</script>';
	}

	/**
	 * Liefert den Extension-Key des Moduls
	 * @return string
	 */
	abstract function getExtensionKey();

	function getDocStyles() {
		if(tx_rnbase_util_TYPO3::isTYPO42OrHigher())
		$css .= '
	.rnbase_selector div {
		float:left;
		margin: 0 5px 10px 0;
	}
	.rnbase_content div {
		float:left;
		margin: 5px 5px 10px 0;
	}
	.cleardiv {clear:both;}
	.rnbase_content .c-headLineTable td {
		font-weight:bold;
		color:#FFF!important;
	}';
	  return $css;
	}
	function getTableLayout() {
		return Array (
				'table' => Array('<table class="typo3-dblist" width="100%" cellspacing="0" cellpadding="0" border="0">', '</table><br/>'),
				'0' => Array( // Format für 1. Zeile
					'tr'		=> Array('<tr class="c-headLineTable">','</tr>'),
					'defCol' => (tx_rnbase_util_TYPO3::isTYPO42OrHigher() ? Array('<td>','</td>') : Array('<td class="c-headLineTable" style="font-weight:bold; color:white;">','</td>'))  // Format für jede Spalte in der 1. Zeile
				),
				'defRow' => Array ( // Formate für alle Zeilen
		//          '0' => Array('<td valign="top">','</td>'), // Format für 1. Spalte in jeder Zeile
					'tr'	   => Array('<tr class="db_list_normal">', '</tr>'),
					'defCol' => Array('<td>','</td>') // Format für jede Spalte in jeder Zeile
				),
				'defRowEven' => Array ( // Formate für alle Zeilen
					'tr'	   => Array('<tr class="db_list_alt">', '</tr>'),
					'defCol' => Array((tx_rnbase_util_TYPO3::isTYPO42OrHigher() ?'<td>' : '<td class="db_list_alt">'),'</td>')
//				'defCol' => Array('<td>','</td>') // Format für jede Spalte in jeder Zeile
				)
			);
	}


	/**
	 * Create the panel of buttons for submitting the form or otherwise perform operations.
	 *
	 * @return	array	all available buttons as an assoc. array
	 */
	function getButtons()	{
		global $TCA, $LANG, $BACK_PATH, $BE_USER;

		$buttons = array(
			'csh' => '',
			'view' => '',
			'record_list' => '',
			'shortcut' => '',
		);
			// TODO: CSH
		$buttons['csh'] = t3lib_BEfunc::cshItem('_MOD_'.$this->MCONF['name'], '', $GLOBALS['BACK_PATH'], '', TRUE);

		if($this->id && is_array($this->pageinfo)) {

				// View page
			$buttons['view'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::viewOnClick($this->pageinfo['uid'], $BACK_PATH, t3lib_BEfunc::BEgetRootLine($this->pageinfo['uid']))) . '">' .
					'<img' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/zoom.gif') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showPage', 1) . '" hspace="3" alt="" />' .
					'</a>';

				// Shortcut
			if ($BE_USER->mayMakeShortcut())	{
				$buttons['shortcut'] = $this->getDoc()->makeShortcutIcon('id, edit_record, pointer, new_unique_uid, search_field, search_levels, showLimit', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']);
			}

				// If access to Web>List for user, then link to that module.
			if ($BE_USER->check('modules','web_list'))	{
				$href = $BACK_PATH . 'db_list.php?id=' . $this->pageinfo['uid'] . '&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
				$buttons['record_list'] = '<a href="' . htmlspecialchars($href) . '">' .
						'<img' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/list.gif') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showList', 1) . '" alt="" />' .
						'</a>';
			}
		}

		return $buttons;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_BaseModule.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_BaseModule.php']);
}

?>