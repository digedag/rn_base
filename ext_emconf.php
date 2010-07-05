<?php

########################################################################
# Extension Manager/Repository config file for ext "rn_base".
#
# Auto generated 11-05-2010 19:49
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'A base library for extensions.',
	'description' => 'Uses MVC design principles and is based on extension lib.',
	'category' => 'misc',
	'shy' => 0,
	'version' => '0.8.7',
	'dependencies' => 'cms,div,lib',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => 'typo3temp/rn_base/',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Rene Nitzsche',
	'author_email' => 'rene@system25.de',
	'author_company' => 'System 25',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'php' => '5.0.0-0.0.0',
			'div' => '',
			'lib' => '0.1.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:89:{s:9:"ChangeLog";s:4:"de28";s:10:"README.txt";s:4:"b282";s:19:"class.tx_rnbase.php";s:4:"f80b";s:34:"class.tx_rnbase_configurations.php";s:4:"0228";s:30:"class.tx_rnbase_controller.php";s:4:"5ad8";s:30:"class.tx_rnbase_parameters.php";s:4:"e318";s:21:"ext_conf_template.txt";s:4:"e54d";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"0a9c";s:38:"action/class.tx_rnbase_action_Base.php";s:4:"f7a7";s:41:"action/class.tx_rnbase_action_BaseIOC.php";s:4:"0ae1";s:38:"cache/class.tx_rnbase_cache_ICache.php";s:4:"8901";s:39:"cache/class.tx_rnbase_cache_Manager.php";s:4:"7db3";s:39:"cache/class.tx_rnbase_cache_NoCache.php";s:4:"a759";s:42:"cache/class.tx_rnbase_cache_TYPO3Cache.php";s:4:"c004";s:19:"doc/wizard_form.dat";s:4:"49ea";s:20:"doc/wizard_form.html";s:4:"f188";s:44:"filter/class.tx_rnbase_filter_BaseFilter.php";s:4:"3863";s:44:"filter/class.tx_rnbase_filter_FilterItem.php";s:4:"04a7";s:50:"filter/class.tx_rnbase_filter_FilterItemMarker.php";s:4:"dd4c";s:37:"maps/class.tx_rnbase_maps_BaseMap.php";s:4:"bc11";s:35:"maps/class.tx_rnbase_maps_Coord.php";s:4:"f622";s:43:"maps/class.tx_rnbase_maps_DefaultMarker.php";s:4:"3140";s:37:"maps/class.tx_rnbase_maps_Factory.php";s:4:"f0f7";s:38:"maps/class.tx_rnbase_maps_IControl.php";s:4:"d095";s:36:"maps/class.tx_rnbase_maps_ICoord.php";s:4:"9c36";s:35:"maps/class.tx_rnbase_maps_IIcon.php";s:4:"539c";s:34:"maps/class.tx_rnbase_maps_IMap.php";s:4:"e7ac";s:37:"maps/class.tx_rnbase_maps_IMarker.php";s:4:"3462";s:42:"maps/class.tx_rnbase_maps_TypeRegistry.php";s:4:"9925";s:51:"maps/google/class.tx_rnbase_maps_google_Control.php";s:4:"56ab";s:48:"maps/google/class.tx_rnbase_maps_google_Icon.php";s:4:"2e6a";s:47:"maps/google/class.tx_rnbase_maps_google_Map.php";s:4:"311b";s:38:"misc/class.tx_rnbase_misc_EvalDate.php";s:4:"7ad5";s:39:"mod/class.tx_rnbase_mod_BaseModFunc.php";s:4:"3125";s:38:"mod/class.tx_rnbase_mod_BaseModule.php";s:4:"e390";s:36:"mod/class.tx_rnbase_mod_IModFunc.php";s:4:"41ec";s:35:"mod/class.tx_rnbase_mod_IModule.php";s:4:"f02b";s:34:"mod/class.tx_rnbase_mod_Tables.php";s:4:"8fbf";s:17:"mod/locallang.xml";s:4:"6bbf";s:36:"model/class.tx_rnbase_model_base.php";s:4:"04a8";s:37:"model/class.tx_rnbase_model_media.php";s:4:"1c8f";s:22:"res/simplegallery.html";s:4:"7c49";s:39:"sv1/class.tx_rnbase_sv1_MediaPlayer.php";s:4:"b15b";s:17:"sv1/dewplayer.swf";s:4:"4e96";s:21:"sv1/ext_localconf.php";s:4:"dfee";s:47:"tests/class.tx_rnbase_tests_Logger_testcase.php";s:4:"6967";s:51:"tests/class.tx_rnbase_tests_basemarker_testcase.php";s:4:"7b6a";s:46:"tests/class.tx_rnbase_tests_cache_testcase.php";s:4:"7713";s:49:"tests/class.tx_rnbase_tests_calendar_testcase.php";s:4:"34dc";s:55:"tests/class.tx_rnbase_tests_configurations_testcase.php";s:4:"27c7";s:46:"tests/class.tx_rnbase_tests_dates_testcase.php";s:4:"e0cb";s:52:"tests/class.tx_rnbase_tests_listbuilder_testcase.php";s:4:"0621";s:45:"tests/class.tx_rnbase_tests_misc_testcase.php";s:4:"b652";s:47:"tests/class.tx_rnbase_tests_rnbase_testcase.php";s:4:"7f68";s:48:"tests/class.tx_rnbase_tests_util_DB_testcase.php";s:4:"18df";s:56:"tests/class.tx_rnbase_tests_util_SearchBase_testcase.php";s:4:"a029";s:36:"util/class.tx_rnbase_util_Arrays.php";s:4:"f0d7";s:37:"util/class.tx_rnbase_util_BEPager.php";s:4:"4812";s:40:"util/class.tx_rnbase_util_BaseMarker.php";s:4:"d340";s:38:"util/class.tx_rnbase_util_Calendar.php";s:4:"61c9";s:32:"util/class.tx_rnbase_util_DB.php";s:4:"d2ed";s:35:"util/class.tx_rnbase_util_Dates.php";s:4:"5226";s:39:"util/class.tx_rnbase_util_Exception.php";s:4:"c0fe";s:35:"util/class.tx_rnbase_util_Files.php";s:4:"60de";s:38:"util/class.tx_rnbase_util_FormTool.php";s:4:"9633";s:38:"util/class.tx_rnbase_util_FormUtil.php";s:4:"3b0d";s:40:"util/class.tx_rnbase_util_FormatUtil.php";s:4:"8f64";s:34:"util/class.tx_rnbase_util_Json.php";s:4:"de4a";s:34:"util/class.tx_rnbase_util_Link.php";s:4:"c58a";s:41:"util/class.tx_rnbase_util_ListBuilder.php";s:4:"6db9";s:45:"util/class.tx_rnbase_util_ListBuilderInfo.php";s:4:"a19a";s:40:"util/class.tx_rnbase_util_ListMarker.php";s:4:"cc71";s:44:"util/class.tx_rnbase_util_ListMarkerInfo.php";s:4:"4a40";s:36:"util/class.tx_rnbase_util_Logger.php";s:4:"c5ee";s:41:"util/class.tx_rnbase_util_MediaMarker.php";s:4:"e3ac";s:34:"util/class.tx_rnbase_util_Misc.php";s:4:"ab31";s:41:"util/class.tx_rnbase_util_PageBrowser.php";s:4:"594c";s:47:"util/class.tx_rnbase_util_PageBrowserMarker.php";s:4:"11d1";s:35:"util/class.tx_rnbase_util_Queue.php";s:4:"9edb";s:40:"util/class.tx_rnbase_util_SearchBase.php";s:4:"8514";s:43:"util/class.tx_rnbase_util_SearchGeneric.php";s:4:"02f3";s:42:"util/class.tx_rnbase_util_SimpleMarker.php";s:4:"3d49";s:34:"util/class.tx_rnbase_util_Spyc.php";s:4:"aa5d";s:35:"util/class.tx_rnbase_util_TSDAM.php";s:4:"a99e";s:35:"util/class.tx_rnbase_util_TYPO3.php";s:4:"50d6";s:39:"util/class.tx_rnbase_util_Templates.php";s:4:"bd45";s:34:"view/class.tx_rnbase_view_Base.php";s:4:"624c";s:47:"view/class.tx_rnbase_view_phpTemplateEngine.php";s:4:"674e";}',
	'suggests' => array(
	),
);

?>