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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_mod_IModule');
tx_rnbase::load('tx_rnbase_mod_IModFunc');

$GLOBALS['LANG']->includeLLFile('EXT:rn_base/mod/locallang.xml');

/**
 * Fertige Implementierung eines BE-Moduls. Das Modul ist dabei nur eine Hülle für die einzelnen Modulfunktionen.
 * Die Klasse stellt also lediglich eine Auswahlbox mit den verfügbaren Funktionen bereit. Neue Funktionen können
 * dynamisch über die ext_tables.php angemeldet werden:
 * 	t3lib_extMgm::insertModuleFunction('user_txmkmailerM1', 'tx_mkmailer_mod1_FuncOverview',
 *    t3lib_extMgm::extPath($_EXTKEY).'mod1/class.tx_mkmailer_mod1_FuncOverview.php',
 *    'LLL:EXT:mkmailer/mod1/locallang_mod.xml:func_overview'
 *  );
 * Die Funktionsklassen sollten das Interface tx_rnbase_mod_IModFunc implementieren. Eine Basisklasse mit nützlichen
 * Methoden steht natürlich auch bereit: tx_rnbase_mod_BaseModFunc
 */
abstract class tx_rnbase_mod_BaseModule extends t3lib_SCbase implements tx_rnbase_mod_IModule {
	public $doc;
	private $configurations, $formTool;

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{

		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		// Einbindung externer Funktionen
		$this->checkExtObj();
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->getPid(), $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		$this->initDoc($this->getDoc());

		if(tx_rnbase_util_TYPO3::isTYPO42OrHigher()) {
			$this->content .= $this->moduleContent(); // Muss vor der Erstellung des Headers geladen werden
			$this->content .= $this->getDoc()->sectionEnd();  // Zur Sicherheit eine offene Section schließen

			$header = $this->getDoc()->header($LANG->getLL('title'));
			$this->content = $this->content; // ??
			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->getDoc()->spacer(20).$this->getDoc()->section('', $this->getDoc()->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
			}
			$this->content.=$this->getDoc()->spacer(10);
			// Setting up the buttons and markers for docheader
			$docHeaderButtons = $this->getButtons();
			$markers['CSH'] = $docHeaderButtons['csh'];
			$markers['HEADER'] = $header;
			$markers['SELECTOR'] = $this->selector ? $this->selector : $this->subselector; // SubSelector is deprecated!!

			// Das FUNC_MENU enthält die Modul-Funktionen, die per ext_tables.php registriert werden
			$markers['FUNC_MENU'] = $this->getFuncMenu();
			// SUBMENU sind zusätzliche Tabs die eine Modul-Funktion bei Bedarf einblenden kann.
			$markers['SUBMENU'] = $this->tabs;
			$markers['TABS'] = $this->tabs; // Deprecated use ###SUBMENU###
			$markers['CONTENT'] = $this->content;
		}
		else {
			// HeaderSection zeigt Icons und Seitenpfad
			$headerSection = $this->getDoc()->getHeader('pages', $this->pageinfo, $this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'], 50);
			$this->content .= $this->moduleContent(); // Muss vor der Erstellung des Headers geladen werden
			$this->content .= $this->getDoc()->sectionEnd();  // Zur Sicherheit einen offene Section schließen

			// startPage erzeugt alles bis Beginn Formular
			$header.=$this->getDoc()->startPage($LANG->getLL('title'));
			$header.=$this->getDoc()->header($LANG->getLL('title'));
			$header.=$this->getDoc()->spacer(5);
			$header.=$this->getDoc()->section('', $this->getDoc()->funcMenu($headerSection, t3lib_BEfunc::getFuncMenu($this->getPid(), 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'])));
			$header.=$this->getDoc()->divider(5);

			$this->content = $header . $this->content;

			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->getDoc()->spacer(20).$this->getDoc()->section('', $this->getDoc()->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
			}
			$this->content.=$this->getDoc()->spacer(10);
		}

		if(tx_rnbase_util_TYPO3::isTYPO42OrHigher()) {
			$content = $this->getDoc()->startPage($LANG->getLL('title'));
			$content.= $this->getDoc()->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
			$this->content = $this->getDoc()->insertStylesAndJS($content);
		}
	}
	/**
	 * Returns the module ident name
	 * @return string
	 */
	public function getName() {
		return $this->MCONF['name'];
	}
	/**
	 * Generates the module content.
	 * Normaly we would call $this->extObjContent(); But this method writes the output to $this->content. We need
	 * the output directly so this is reimplementation of extObjContent()
	 *
	 * @return	void
	 */
	function moduleContent()	{
		// Dummy-Button für automatisch Submit
		$content = '<p style="position:absolute; top:-5000px; left:-5000px;">'
            . '<input type="submit" />'
            . '</p>';
		$this->extObj->pObj = &$this;
		if (is_callable(array($this->extObj, 'main')))	$content.=$this->extObj->main();
		else $content .= 'Module has no method main.';

		return $content;
	}

	function checkExtObj()	{
		if (is_array($this->extClassConf) && $this->extClassConf['name'])	{
			$this->extObj = t3lib_div::makeInstance($this->extClassConf['name']);
			$this->extObj->init($this, $this->extClassConf);
				// Re-write:
			$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name'], $this->modMenu_type, $this->modMenu_dontValidateList, $this->modMenu_setDefaultList);
		}
	}

	/**
	 * @see tx_rnbase_mod_IModule::getFormTool()
	 * @return tx_rnbase_util_FormTool
	 */
	public function getFormTool() {
		if(!$this->formTool) {
			$this->formTool = tx_rnbase::makeInstance('tx_rnbase_util_FormTool');
			$this->formTool->init($this->getDoc());
		}
		return $this->formTool;
	}
	/**
	 * Liefert eine Instanz von tx_rnbase_configurations. Da wir uns im BE bewegen, wird diese mit einem
	 * Config-Array aus der TSConfig gefüttert. Dabei wird die Konfiguration unterhalb von mod.extkey. genommen.
	 * Für "extkey" wird der Wert der Methode getExtensionKey() verwendet.
	 * Zusätzlich wird auch die Konfiguration von "lib." bereitgestellt.
	 * Wenn Daten für BE-Nutzer oder Gruppen überschrieben werden sollen, dann darauf achten, daß die
	 * Konfiguration mit "page." beginnen muss. Also bspw. "page.lib.test = 42".
	 *
	 * Ein eigenes TS-Template für das BE wird in der ext_localconf.php mit dieser Anweisung eingebunden:
	 * t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:myext/mod1/pageTSconfig.txt">');
	 * @return tx_rnbase_configurations
	 */
	public function getConfigurations() {
		if(!$this->configurations) {
			tx_rnbase::load('tx_rnbase_configurations');
			tx_rnbase::load('tx_rnbase_util_Misc');

			tx_rnbase_util_Misc::prepareTSFE(); // Ist bei Aufruf aus BE notwendig!
			$cObj = t3lib_div::makeInstance('tslib_cObj');

			$pageTSconfigFull = t3lib_BEfunc::getPagesTSconfig($this->getPid());
			$pageTSconfig = $pageTSconfigFull['mod.'][$this->getExtensionKey().'.'];
			$pageTSconfig['lib.'] = $pageTSconfigFull['lib.'];

			$userTSconfig = $GLOBALS['BE_USER']->getTSConfig('mod.' . $this->getExtensionKey().'.');
			if (!empty($userTSconfig['properties'])) {
				$pageTSconfig = t3lib_div::array_merge_recursive_overrule($pageTSconfig, $userTSconfig['properties']);
			}

			$qualifier = $pageTSconfig['qualifier'] ? $pageTSconfig['qualifier'] : $this->getExtensionKey();
			$this->configurations = new tx_rnbase_configurations();
			$this->configurations->init($pageTSconfig, $cObj, $this->getExtensionKey(), $qualifier);
		}
		return $this->configurations;
	}
	/**
	 * Liefert bei Web-Modulen die aktuelle Pid
	 * @return int
	 */
	public function getPid() {
		return $this->id;
	}
	public function setSubMenu($menuString) {
		$this->tabs = $menuString;
	}
	/**
	 * Selector String for the marker ###SELECTOR###
	 * @param $selectorString
	 */
	public function setSelector($selectorString){
		$this->selector = $selectorString;
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.=$this->getDoc()->endPage();

		$params = Array();
		tx_rnbase::load('tx_rnbase_util_BaseMarker');
		tx_rnbase::load('tx_rnbase_util_Templates');
		tx_rnbase_util_BaseMarker::callModules($this->content, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $this->getConfigurations()->getFormatter());
		$content = tx_rnbase_util_Templates::substituteMarkerArrayCached($this->content, $markerArray, $subpartArray, $wrappedSubpartArray);

		echo $content;
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
	/**
	 * Erstellt das Menu mit den Submodulen. Die ist als Auswahlbox oder per Tabs möglich und kann per TS eingestellt werden:
	 * mod.mymod._cfg.funcmenu.useTabs
	 */
	protected function getFuncMenu() {
		$items = $this->getFuncMenuItems($this->MOD_MENU['function']);
		$useTabs = intval($this->getConfigurations()->get('_cfg.funcmenu.useTabs')) > 0;
		if($useTabs)
			$menu = $this->getFormTool()->showTabMenu($this->getPid(), 'function', $this->getName(), $items);
		else
			$menu = $this->getFormTool()->showMenu($this->getPid(), 'function', $this->getName(), $items, $this->getModuleScript());
		return $menu['menu'];
	}
	/**
	 * index.php or empty
	 * @return string
	 */
	protected function getModuleScript() {
		return '';
	}
	/**
	 * Find out all visible sub modules for the current user.
	 * mod.mymod._cfg.funcmenu.deny = className of submodules
	 * mod.mymod._cfg.funcmenu.allow = className of submodules
	 * @param array $items
	 * @return array
	 */
	protected function getFuncMenuItems($items) {
		$visibleItems = $items;
		if($denyItems = $this->getConfigurations()->get('_cfg.funcmenu.deny')) {
			$denyItems = t3lib_div::trimExplode(',', $denyItems);
			foreach ($denyItems As $item)
				unset($visibleItems[$item]);
		}
		if($allowItems = $this->getConfigurations()->get('_cfg.funcmenu.allow')) {
			$visibleItems = array();
			$allowItems = t3lib_div::trimExplode(',', $allowItems);
			foreach ($allowItems As $item)
				$visibleItems[$item] = $items[$item];
		}
		return $visibleItems;
	}
	protected function getFormTag() {
		// TODO: per TS einstellbar machen
		return '<form action="" method="post" enctype="multipart/form-data">';
	}
	/**
	 * Returns the file for module HTML template. This can be overwritten.
	 * The first place to search for template is EXT:[your_ext_key]/mod1/template.html. If this file
	 * not exists the default from rn_base is used. Overwrite this method to set your own location.
	 * @return string
	 */
	protected function getModuleTemplate() {
		$filename = $this->getConfigurations()->get('template');
		if(file_exists(t3lib_div::getFileAbsFileName($filename, TRUE, TRUE))) {
			return $filename;
		}
		// '../'.t3lib_extMgm::siteRelPath($this->getExtensionKey()) .  'mod1/template.html'
		$filename = 'EXT:'.$this->getExtensionKey() .  '/mod1/template.html';
		if(file_exists(t3lib_div::getFileAbsFileName($filename, TRUE, TRUE))) {
			return $filename;
		}
		return 'EXT:rn_base/mod/template.html';
	}
	protected function initDoc($doc) {
		$doc->backPath = $GLOBALS['BACK_PATH'];
		$doc->form= $this->getFormTag();
		$doc->docType = 'xhtml_trans';
		$doc->inDocStyles = $this->getDocStyles();
		$doc->tableLayout = $this->getTableLayout();
		if(tx_rnbase_util_TYPO3::isTYPO42OrHigher()) {
			$doc->setModuleTemplate($this->getModuleTemplate());
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
					'tr'		=> Array('<tr class="t3-row-header c-headLineTable">', '</tr>'),
					// Format für jede Spalte in der 1. Zeile
					'defCol' => (tx_rnbase_util_TYPO3::isTYPO42OrHigher() ?
												Array('<td>', '</td>') :
												Array('<td class="c-headLineTable" style="font-weight:bold; color:white;">', '</td>'))
				),
				'defRow' => Array ( // Formate für alle Zeilen
		//          '0' => Array('<td valign="top">', '</td>'), // Format für 1. Spalte in jeder Zeile
					'tr'	   => Array('<tr class="db_list_normal">', '</tr>'),
					'defCol' => Array('<td>', '</td>') // Format für jede Spalte in jeder Zeile
				),
				'defRowEven' => Array ( // Formate für alle geraden Zeilen
					'tr'	   => Array('<tr class="db_list_alt">', '</tr>'),
					// Format für jede Spalte in jeder Zeile
					'defCol' => Array((tx_rnbase_util_TYPO3::isTYPO42OrHigher() ? '<td>' :
														'<td class="db_list_alt">'), '</td>')
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
			if ($BE_USER->check('modules', 'web_list'))	{
				$href = $BACK_PATH . 'db_list.php?id=' . $this->pageinfo['uid'] . '&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
				$buttons['record_list'] = '<a href="' . htmlspecialchars($href) . '">' .
						'<img' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/list.gif') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showList', 1) . '" alt="" />' .
						'</a>';
			}
		}

		return $buttons;
	}
	/*
	 * (Non PHP-doc)
	 */
	public function addMessage($message, $title = '', $severity = 0, $storeInSession = FALSE) {
		$message = t3lib_div::makeInstance(
			't3lib_FlashMessage',
			$message,
			$title,
			$severity,
			$storeInSession
		);

		t3lib_FlashMessageQueue::addMessage($message);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_BaseModule.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/mod/class.tx_rnbase_mod_BaseModule.php']);
}

