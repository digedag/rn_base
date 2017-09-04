(add new changes on top of this file)

Änderungen
----------

v1.5.0 (??.??.2017)
 * Required PHP version raised to 5.6
 * better support of BE modules in TYPO3 7.6 and 8.7

v1.4.5 (02.08.2017)
 * new variable $doSearch in tx_rnbase_filter_BaseFilter to control return value of init() more convenient. $doSearch can be set anywhere inside initFilter(). This overrules the return value of initFilter() if not null. This way it's easier to control whether a search should be done or not
 * configurable if search with tx_rnbase_filter_BaseFilter should be stopped when no categories found


v1.4.4 (28.06.2017)
 * new features to make filtering by sys_category easy (e.g. in list views)

v1.4.3 (21.06.2017)
 * sorting can be set when adding file references (FAL)

v1.4.2 (19.06.2017)
 * set plugins USER_INT if a page outside the range of the pagebrowser is requested -> available through
 method tx_rnbase_util_PageBrowser::markPageNotFoundIfPointerOutOfRange()

v1.4.1 (30.05.2017)
 * add subpart to ts by default
   TypoScript path: `action.template.subpart`
 * add new path to configure the view template.
   Old, stil working Path: `actionTemplate`
   New: `action.template.file`
 * using MarkerBasedTemplateService with TYPO3 7 or Higher
 * model mocks in base unit tests fixed

v1.4.0 (11.05.2017)
 * First TER release with support for TYPO3 8.7. Thanks to all contributors!!

v1.3.3 (09.05.2017)
 * new storage trait added
 * dynamic tab menu fixed if there are no submenus

v1.3.2 (28.04.2017)
 * table name in db quote methods are optionaly now
 * reference error for fixVersioningPid in backend utility fixed
 * load t3 class fixed for typo3 8 and later
 * reference error for workspaceOL in backend utility fixed
 * create syspage after options check
 * fill page record in tsfe only if not set or pid is different
 * Method signature fixed in view_List
 * new wrapper method for mapRecordTypeToSpriteIconName in icon utility
 * eleminateNonTcaColumns fixed if there is no tca or model returns no columns
 * new method to prepare the old typo3 db global for unittests
 * add new method to create a new content object
 * title in be table rendering readded for info icon
 * pass variable to hooks in database connection by reference
 * new hook before doSelect is executed
 * add util_db_do_select_post Hook

v1.3.1 (11.04.2017)
 *  fill page record of TSFE when preparing the TSFE in the backend

v1.3.0 (11.04.2017)
 *  new wrapper for getPageRenderer (TYPO3 8 support)
 *  new wrapper for readLLfile (TYPO3 8 support)
 *  icon factory for sprite icons implemented (TYPO3 8 support)
 *  backend module runner disabled for typo3 8 or newer (TYPO3 8 support)
 *  get subpart refactored to new marker template service (TYPO3 8 support)
 *  new typo3 version checks added (TYPO3 8 support)
 *  It is possible to render other entities with view_List.
 *  New exception class for page not found handling
 *  Custom headers for 404 errors possible
 *  check for toUserInt before creating the parameters object
 *  removed check if cHash is set (is done by the core)
    Since now the content for USER plugins with parameters and no valid
    chash will be the one if no parameters were given. Before the cache was
    disabled in this case and the plugin was executed without caching.

v1.2.7 (23.03.2017)
 *  bugfix in typoscript utility to support php down to 5.3 again

v1.2.6 (22.03.2017) (TER Release)
 *  new methods to convert ts array to with dots and to without dots
 *  warning fixed if globals TYPO3_LOADED_EXT not set
 *  CS cleanup
 *  extend processor interface by already existing configuration methods
 *  utility to add cHashExcludedParameters
 *  don't set noHash for links in USER_INT plugins by default

v1.2.5 (07.02.2017)
 *  ModelBase: isValid fixed if loadrecord fails and there are only a uid in the record
 *  BaseTestCase: new getInaccessibleProperty method
 *  SimpleMarker: new method finishTemplate() for child classes
 *  fixed method signature of tx_rnbase_util_MediaMarker::prepareItem
 *  bugfix deactivate cache only in FE

v1.2.4 (08.02.2017)
 *  disable cache if loadHiddenObjects is active
 *  maximum pagesize in pagebrowser removed. The limit/pagesize can be greater than 1000 now. The default of 10 has been retained.

v1.2.3 (25.01.2017)
 *  in tx_rnbase_controller $action->execute is no longer executed if $action is no object

v1.2.2 (19.01.2017)
 *  new configuration processor as future replacement for tx_rnbase_configurations added
 *  fixed warning in BaseMarker
 *  Code cleanup
 *  new getLastPage method for page browser
 *  New TS option removeIfDisabled in initLink() of BaseMarker

v1.2.1 (04.01.2017)
 *  better handling of cached plugins
 *  New method setQualifier in tx_rnbase_configurations

v1.2.0 (23.12.2016)
 *  magic isset method for models added
 *  another XSS vector in BE modules closed
 *  TER release

v1.1.12 (19.12.2016)
 *  closed XSS vector in BE modules

v1.1.11 (13.12.2017)
 *  [CLEANUP] #64 Fix the casing of method calls
 *  [CLEANUP] #65 Rename all constructors to __construct
 *  [CLEANUP] #59 Remove unused local variables
 *  [TASK] #57 Generate ext_autoload.php
 *  fixed composer.json
 *  added colorpicker wizard
 *  fixed link wizard
 *  bugfix for wizard icons
 *  Better table layout in 7.6 (BE modules)
 *  Support for form fields with new command in BE modules
 *  Default values for getSoloField with new command possible in BE modules
 *  adding ressources in actions optimized
 *  stdWrap is executed on refUid field in fetchFirstReference in FAL utility
 *  special chars for charbrowser declared static and nondeprecated
 *  Configure TCA Wizard Icons for TYPO3 7.6

v1.1.10 (06.12.2016)
 *  special chars for charbrowser declared static and nondeprecated in tx_rnbase_util_SearchBase
 *  fix wrong var assignment in Tx_Rnbase_Database_Connection

v1.1.9 (15.11.2016)
 *  better table layout in BE modules for TYPO3 7.6
 *  added support in BE formbuilder for NEW-records
 *  Default values for getSoloField with "new" command possible
 *  enhanced and optimized adding CSS/JS ressources in actions based on BaseIOC
 *  stdWrap is executed on refUid field in tx_rnbase_util_TSFAL::fetchFirstReference

v1.1.8 (26.10.2016)
 *  added colorpicker wizard
 *  fixed link wizard
 *  fixed wizard icons

v1.1.7 (26.09.2016)
 *  optimized composer.json

v1.1.6 (19.09.2016)
 *  tx_rnbase_util_PageBrowser::getPointer returns only valid pointers
 *  class for getting a pidlist from a pagetree or hierarchical db structure with custom query options

v1.1.5 (14.09.2016)
 *  Base model cleanup (unnecessary uid property removed)
 *  new method getProperties for models
 *  php version raised to support php 7

v1.1.4 (07.09.2016)
 *  new get trace paths debug method

v1.1.3 (31.08.2016)
 *  getPidList has options now and supports dontCheckEnableFields
 *  avoids throwing a 404 header on empty lists

v1.1.2 (24.08.2016)
 *  Pagebrowser sends 404 if pointer is out of list
 *  loadT3 checks for existing interface, so the outoloading can load interfaces too
 *  test folder added to autoloading class map in composer

v1.1.1 (17.08.2016)
 *  new Tx_Rnbase_Utility_Logger added
 *  hide show links in decorator fixed
 *  new table utility for be modules

v1.1.0 (11.08.2016)
 *  dirty state after setProperty([]) should be dirty, not clean
 *  new initial abstract detail handler for be modules added
 *  new abstract seach handler for be modules added
 *  new abstract lister for be modules added
 *  new repositories uses instance of Tx_Rnbase_Domain_Collection_Base instead of array per default
 *  collection support for select at connection added (usage: $options['collection'] = true;)
 *  redirect of link util supports a http status parameter now
 *  new redirect wraper method in networ util

v1.0.16 (18.07.2016)
 *  File permissions fixed for lock files
 *  [BREAKING CHANGE] class properties tx_rnbase_configurations have now a visibility. Thus access e.g. to _dataStore will now fail.

v1.0.15 (20.06.2016)
 *  #53 it is possible to order plugins language file includes with `plugin.myplugin.locallangFilename._cfg.naturalOrder = 1`
 *  added generic static method to add flash messages

v1.0.14 (15.06.2016)
 *  unused getSelector method in be lister removed
 *  new decorator interface and base class added
 *  initialized parameter object in be modules added (`$mod->getConfigurations()->getParameters()->getInt('uid')` returns `5` from url `?SET['uid']`)
 *  be lister deprecation log fixed

v1.0.13 (09.06.2016)
 *  php support lower then 5.4 restored in be module dispatcher
 *  fallbackPid check for be modules added
 *  action url for be modules fixed

v1.0.12 (08.06.2016)
 *  module runner for be module dispatcher added
 *  optionial support for arguments as references for the callInaccessibleMethod test method
 *  new repositories uses ArrayObjects instead of array per default
 *  new persistence repository added

v1.0.11 (26.05.2016)
 *  simple marker class can be used with data models now
 *  Fix: creating an instance of FlashMessageQueue in backend modules

v1.0.10 (24.05.2016)
 *  add label support for move up and move down links in backend modules
 *  minor bugixes

v1.0.9 (12.05.2016)
 *  new base exception class
 *  new wrapper methods in tx_rnbase_util_Typo3Classes

v1.0.8 (10.05.2016)
 *  added missing loads

v1.0.7 (10.05.2016)
 *  new overloading method for data model (getter) #42
 *  cached geo lookup via wec_map
 *  Pass $var to GeneralUtility _POST and _GET methods

v1.0.6 (04.05.2016)
 *  removed Tx_Rnbase_Utility_Extension_Devlog (can now be found in mklog -> Tx_Mklog_Utility_Devlog)
 *  New method to access TCA attributes from ctrl section
 *  bugfix for TYPO3 7.6 as the file path will be returned even if the file exists not
 *  set init method of domain models to protected

v1.0.5 (22.04.2016)
 *  added Tx_Rnbase_Backend_AbstractFunctionModule as wrapper for TYPO3\CMS\Backend\Module\AbstractFunctionModule and t3lib_extobjbase
 *  tx_rnbase_mod_Tables::getTableLayout() is now public

v1.0.4 (19.04.2016)
 *  Tx_Rnbase_Utility_Extension_Devlog to get table name, field names and extra data dependent on the devlog version
 *  added Tx_Rnbase_Service_Authentication as wrapper for \TYPO3\CMS\Sv\AuthenticationService and tx_sv_auth
 *  New mailing class
 *  some new wrapper classes and functions

v1.0.3 (13.04.2016)
 *  It is possible to include JS and CSS files for actions via typoscript.
 *  add note to phpdoc to avoid warnings in IDE when instantiating Exceptions
 *  util_Strings replaced with Tx_Rnbase_Utility_Strings
 *  T3General fixed for methods with parameters as reference
 *  TimeTrackClass fixed
 *  util_Lang::sL throws exception now, if no language service initialized


v1.0.2 (17.03.2016)
 *  Register name for RNBASE_LB_SIZE is configurable
 *  Some improvements for tx_rnbase_util_TSFAL

v1.0.1 (02.03.2016)
 *  public static wrapper function tx_rnbase_util_Lang::sL() to get LLL
 *  getDeletedFieldForTable and getDisabledFieldForTable added to tca util
 *  Ignore joined search without fields given to avoid sql/php errors

v1.0.0 (23.02.2016)
 *  first TYPO3 7.6 support #34
 *  array object support for select at connection added (usage: $options['array_object'] = TRUE;)
 *  New loadYaml method for UnitTests to simply build a mock stack
 *  new interfaces for domain and data models
 *  new Crypt Utility
 *  model and repository refactoring
 *  skipPersistedCheck check in simple marker added
 *  getSortbyFieldForTable method added to get sortable column of a table
 *  move up and down links in form util added to move records in a be module
 *  new class tx_rnbase_util_Typo3Classes to get TYPO3 classes independent of TYPO3 version
 *  Bugfix: tx_rnbase_util_Dates::date_mysql2tstamp liefert nun wieder NULL im Fehlerfall
 *  tx_rnbase_util_DB is now deprecated. Use Tx_Rnbase_Database_Connection instead and call all methods on an instance no statically
 *  TSFAL: better support for file reference. See [documentation for more info](picture_rendering.md).

v0.15.18 (02.12.2015)
 *  Bugfix: tx_rnbase_util_Dates::date_mysql2tstamp liefert nun immer einen Integer und nicht NULL im Fehlerfall
 *  Bugfix for offset usage in tx_rnbase_util_TSFAL::fetchFilesByTS

v0.15.17 (26.11.2015)
 *  Mail attachment support for TYPO3 >= 4.5
 *  tx_rnbase_util_Extensions::insertModule() fixed for 6.x

v0.15.16 (17.11.2015)
 *  new method tx_rnbase_util_String::isInteger() as wrapper for t3lib_div::testInt()
 *  tx_rnbase_action_BaseIOC has new utility method createLink()
 *  new method tx_rnbase_cache_Manager::registerCache()
 *  Method createURI renamed
 *  decode class names from requests in SearchBase
 *  ReplyTo Address added in MailUtility
 *  use TYPO3 404 Handling when tx_rnbase_exception_ItemNotFound404 or TYPO3\CMS\Core\Error\Http\PageNotFoundException is thrown in actions
 *  bugfix if refUid got its value thorugh stdWrap (TSFAL Utility)
 *  TYPO3 7.6 compaitbility

v0.15.15 (02.11.2015)
 *  prepareTSFE refactoring

v0.15.14 (21.10.2015)
 *  SimpleMarker has a new method **prepareTemplate()* *  as an entry point for child classes
 *  tx_rnbase_view_List integrates markers provided by controller class

v0.15.13 (14.10.2015)
 *  add missing return statement in language utility
 *  Geo-Coding class for google
 *  Support for hideResult() in list filter
 *  bugfix in link utility if schema is set as empty string not FALSE

v0.15.12 (07.10.2015) (hotfix)
 *  split label function to lang util added

v0.15.11 (07.10.2015)
 *  abs url scheme config for link util also works if there already an absolute url

v0.15.10 (29.09.2015)
 *  new option in form tool for multiple selects
 *  new option to set size for select

v0.15.9 (21.09.2015)
 *  new abstract repository class

v0.15.8 (16.09.2015)
 *  css class option for text inputs in be fields
 *  much documentation added

v0.15.7 (24.08.2015)
 *  Hotfix:  Fatal errors in util db for workspaces and language overlays fixed

v0.15.6 (19.08.2015)
 *  linker for mod tables gets a interface and a new show details class
 *  new possiblity to set enableFields for joined tables when using the searchframework
 *  when using the search framework the extension configuration for loadHiddenObjects is respected

v0.15.5 (10.08.2015)
 *  bugfix for new pagebrowser visualizations

v0.15.4 (10.08.2015)
 *  reset clean state for models on reset

v0.15.3 (10.08.2015)
 *  New subpart marker ###PAGEBROWSER_PREV_BULLETS_PAGE### and ###PAGEBROWSER_NEXT_BULLETS_PAGE### to visualize skipped pages in pagebrowser.
 *  simple Testcase for tx_rnbase_util_XmlElement

v0.15.2 (05.08.2015)
 *  Array syntax fixed in maps factory
 *  added missing brace in util_XmlElement
 *  decode image path to avoid problems with special chars when scaling images

v0.15.1 (29.07.2015)
 *  more map functions
 *  code cleanups
 *  Maps: define POIs by Typoscript

v0.15.0 (18.07.2015) (Public Release)
 *  Documentation added
 *  Some internal refactorings

v0.14.32 (15.07.2015)
 *  support for recursive param arrays in makeUrlParam in link util

v0.14.31 (14.07.2015)
 *  new simple boolean modified/dirty flag for models
 *  New Wrapperclass for ExtensionManager

v0.14.30 (08.07.2015)
 *  Some TSFAL issues fixed

v0.14.29 (03.07.2015)
 *  current action and confid added to base ioc debug
 *  new optional existing cobject instance for links, instead of always create a new object.
 *  perform sub template check in getSubpart method directly.

v0.14.28 (24.06.2015)
 *  massive refactoring of the base ioc caching. see: tx_rnbase_action_CacheHandlerDefault

v0.14.27 (23.06.2015)
 *  exception devlog fixed, since there can be a closure inside
 *  range from/to for pagebrowser
 *  aditional mock method support for models in base testcase
 *  custom check in orderby fixed for base searcher

v0.14.26 (17.06.2015)
 *  new charpointer feature for links in marker classes
 *  new section config for links to add anchors
 *  new isLastPartOfStr method and isFirstPartOfStr wrapper

v0.14.25 (09.06.2015)
 *  new overlay property for record in be table rendering.
 *  [BUGFIX] overlays in prepareTable only for untranslated records.
 *  document creation in be modules fixed. works now in newer versions and cli.
 *  new getModel method for base testcase

v0.14.24 (03.06.2015)
 *  support for styled i18 listing in be moduls added
 *  data model are traversable now
 *  new toArray and toString methods for models
 *  tx_rnbase_model_data::getInstance creates recursive instances now

v0.14.23 (03.06.2015)
 *  getLabel renamed to getTcaLabel

v0.14.22 (03.06.2015)
 *  template includes can be nested now
 *  new getLastModifyDateTime and getCreationDateTime methods for base model
 *  new getSysLanguageUid and getLabel methods for base model
 *  optional options and overlay parameters for tx_rnbase_mod_Util::getSpriteIcon
 *  [BUGFIX] custom fields typo in searchbase_handleTableMapping hook fixed

v0.14.21 (19.05.2015)
 *  [BUGFIX] COUNTWRAP nicht nutzen wenn im pagebrowser ein eigenes what mit count gesetzt wird

v0.14.20 (07.05.2015)
 *  util link redirect fixed for abs url config
 *  New setInaccessibleStaticProperty method for tests
 *  new mergeRecursiveWithOverrule method with typo3 version check
 *  disable includeSubTemplates cache, if $GLOBALS['TSFE']->no_cache = TRUE;
 *  add where clause only on joined fields search when not empty to avoid MySQL errors

v0.14.18 (27.04.2015)
 *  disable includeSubTemplates cache, if $GLOBALS['TSFE']->no_cache = TRUE;
 *  new mergeRecursiveWithOverrule wrapper method for arrays with TYPO3 version check.
 *  New setInaccessibleStaticProperty method for tests
 *  refactoring of watchOutDB and SQL Exceptions fixed (adds last query now)
 *  refactoring of plugin data rendering.
 *  flexdata added to plugin rendering, so the plugin data can be expanded by ts or flexform.
 *  count for queries with groupby, having, what or count in custom what
 *  count check in custom what fixed
 *  [BUGFIX] bei COUNTRWAP nicht auf uid Spalte gehen falls diese nicht vorhanden ist. Wir gehen nun direkt auf *

v0.14.17 (20.04.2015)
 *  new Lock util to simply lock a process (tx_rnbase_util_Lock::getInstance('process-name')->isLocked())

v0.14.16 (10.04.2015)
 *  tx_rnbase_util_TS::parseTsConfig checks now include lines
 *  optional pid for new sys_file_references

v0.14.15 (31.03.2015)
 *  possiblity to configure allowed and disallowed file extensions for FAL TCA fields in `$field['config']['allowedFileExtensions']` and `$field['config']['disallowedFileExtensions']`
 *  jQuery Plugin Flot 0.8.3 as resource added

v0.14.14 (23.03.2015)
 *  new wrapper class for network related TYPO3 functions like cmpIP

v0.14.13 (20.03.2015)
 *  Backward compatibility for TCA validate Methods to TYPO3 < 6
 *  New tca_overrides option for tca data validation.

v0.14.12 (19.03.2015)
 *  New validation methods, to check a mode, record or a field with the tca definition of a specific table
 *  404 header and robts meta tag for new ItemNotFound404 exception
 *  Fixed absolute url in link util, if the url are already absolute
 *  New isPropertyEmpty method in data model

v0.14.11 (03.02.2015) (TER release)
 *  New method deleteReferencesByReference to remove FAL references by reference id(s)

v0.14.10 (22.01.2015)
 *  Bugfix in model_Media

v0.14.9 (21.01.2015)
 *  New setInaccessibleProperty for base testcase
 *  tx_rnbase_util_db_MySQL uses now mysqli
 *  New method tx_rnbase_util_Misc::getIndpEnv()

v0.14.8 (15.01.2015)
 *  Do not enable cHash system in controller for USER_INT plugins
 *  fixed composer version for composer projects

v0.14.7 (07.01.2015)
 *  fal handling and indixing added
 *  indexing proces for dam files
 *  security fixes for dam handling
 *  some quote string wrapper methods
 *  New Method tx_rnbase_util_TSFAL::fetchFileList as tx_dam_tsfe->fetchFileList equivalent for easier dam2file migration.
 *  composer.json added. install rn_base with { "require" : { "typo3-ter/rn-base" : "dev-master" } }

v0.14.6 (04.12.2014)
 *  New feature, to skip empty values for keepvars
 *  Postprocess hooks for doInsert, doUpdate and doDelete in the DB util.

v0.14.5 (17.11.2014)
 *  Bugfix: auto translation causes error in TCE forms.
 *  New method getCleaned removes xss from the parameter value.
 *  New method join for Files
 *  Preprocess hooks for doInsert, doUpdate and doDelete in the DB util.
 *  New method loadTCA, to load the TCA, for excample in Ajax-calls or eID's.
 *  Bugfix: label markers without translation will be removed from template.

v0.14.4 (06.11.2014)
 *  Support for label markers with salutation suffix.
In HTML template: ###LABEL_GREETING###
in language file:
label_greeting_informal = Hello guys!
label_greeting_formal = Good day, Sir!
Configure salutation in plugin config:
plugin.tx_myplugin.salutation = formal
 *  Fixed some FAL issues and enhanced support.
 *  New XmlElement class for better XML parsing.
 *  New wrapper method tx_rnbase::makeInstanceService()
 *  New wrapper method tx_rnbase_util_Strings::getRandomHexString().

v0.14.3 (07.10.2014)
 *  To avoid caching an USER-Plugin, it can now easily converted to USER_INT by calling $this->getConfigurations()->convertToUserInt(). Thanks to Michael Wagner!
 *  Action can now be skipped with the Skip Exception. Thanks to Michael Wagner!
 *  New subtemplate parsing. Thanks to Michael Wagner!
 *  Bugfix for Modulemarkers. Modulemarkers and Labels can now contain a Minus (-). Thanks to Michael Wagner!
 *  Metadata for FAL to mediamodel added. Thanks to Hannes Bochmann!
 *  UserTS For BE-Modules added.


v0.14.2 (06.09.2014)
 *  New method model_Base::setTableName(). Thanks to Christian Riesche!
 *  Support for attachments in tx_rnbase_util_Mail. Works for T3 versions < 4.5
 *  basic data model with generic getters, setters. Thanks to Michael Wagner!
 *  New label debug in BaseMarker.

v0.14.1 (26.04.2014)
 *  Changes to apply code conventions

v0.14.0 (19.04.2014) (not released)
 *  Support for TYPO3 6.2
 *  Better localization support. Thanks to Hannes Bochmann!
 *  New TS-Option links.linkid.applyHtmlSpecialChars to mask special characters. Default is false. Thanks to Michael Wagner!
 *  PageBrowser with more setters
 *  Bugfix in BE-Lister. Thanks to Thomas Reuleke!

v0.13.6 (10.02.2014) (not released)
 *  Default dummy button in BE module
 *  Substitution cache in marker classes disabled by default. Thanks to Michael Wagner!

v0.13.5 (28.11.2013) (not released)
 *  Exception handling is now configurable by custom handler classes. Thanks to Hannes Bochmann!

v0.13.4 (19.11.2013) (not released)
 *  util_Template::substMarkerArrayCached() with better caching. Thanks to Hannes Bochmann!

v0.13.3 (05.11.2013)
 *  Warning mail address from TYPO3 is not used for error mails anymore.
 *  util_Files::getFileResource() supports FAL references
 *  view_Base works with file references from flexform in TYPO3 6.1
 *  tx_rnbase_configurations: Even better support for references. Thanks to Michael Wagner!
 *  util_Files: New method cleanupFileNames(). Thanks to dMK!

v0.13.2 (27.06.2013) (not released)
 *  SimpleMarker supports dynamic subpart-markers configured by typoscript. Thanks to Michael Wagner!
 *  util_DB::doSelect: Automatic translation of selected records.
 *  util_Link: Bugfix for external URIs
 *  New method util_FormUtil::getHiddenFieldsForUrlParams(). Thanks to Michael Wagner!

v0.13.1 (03.06.2013) (not released)
 *  Support for unique plugin parameters. Simple enabled by TS: plugin.tx_myplugin.uniqueParameters = 1
 *  tx_rnbase_model_base implements interface tx_rnbase_IModel

v0.13.0 (01.06.2013)
 *  Bugfix for pagebrowser. Thanks to Hannes Bochmann!
 *  BE-Modules: Generic sorting in tables. Thanks to Hannes Bochmann and Thomas Reuleke!
 *  BE-Modules: Some changes in base_Lister. Thanks to Thomas Reuleke!
 *  TYPO3 dependency for TER added
 *  Initial implementation for FAL support in TYPO3 6.0
 *  tx_rnbase_util_DB: update for TYPO3 6.1
 *  Link creation optimized. No cHash per default for plugins in USER_INT mode.

v0.12.4 (14.03.2013) (not released)
 *  Bugfix for SQL-error-logging. Thanks to Michael Wagner!
 *  forceException4Mayday is now on as default
 *  Bugfix in util_DB. Thanks to Michael Wagner!

v0.12.2 (21.02.2013) (not released)
 *  Bugfix for MSSQL-Database-Access.  Thanks to Michael Wagner!
 *  Cache-API extended for TYPO3 6.0! Once again...
 *  PageBrowser fixed for TYPO3 6.0
 *  TSDAM: HTML-Template looked up by tx_rnbase_util_Templates now
 *  util_Templates::getSubpartFromFile(): Template loading in BE improved.
 *  util_Misc::initTSFE(): init languages for better TS processing
 *  util_TYPO3: deprecation log for version number fixed

v0.12.1 (29.11.2012) (not released)
 *  Log sql errors for Select-Statements.
 *  New method util_Debug::getDebugTrail(). Used for TYPO3 4.7 compatibility. Thanks to Hannes Bochmann!

v0.12.0 (05.10.2012) (not released)
 *  Full support for MSSQL database access. Thanks to Michael Wagner!
 *  New non verbose unhandled exception output. Thanks to Hannes Bochmann!

v0.11.16 (13.09.2012)
 *  tx_rnbase_util_DB: enablefieldsbe removes more fields like starttime, fe_group etc.

v0.11.14 (02.08.2012) (not released)
 *  filter_Base. Always set SearchCallback. Thanks to Michael Wagner!
 *  Small fix in SimpleMarker. Thanks to Thomas Reuleke!
 *  FormTool::createSortLink() fixed if no params in url present. Thanks to Thomas Reuleke!
 *  New compatibility-wrapper to send simple emails out of TYPO3.

v0.11.13 (11.06.2012) (not released)
 *  Better check for valid TSFE
 *  First git tagged version

v0.11.12 (15.05.2012) (not released)
 *  INSERT and UPDATE support for external databases. Thanks to Michael Wagner!
 *  constructor name of model_base changed to __construct
 *  util_Misc::getPidList forces TSFE creation
 *  Filtering passwords in exception mailings. Thanks to Hannes Bochmann!
 *  Cookie information in error mails added.
 *  It is possible to point pagebrowser to a specific item by parameter:
        $filter->handlePageBrowser($configurations,
            $confId.'news.pagebrowser', $viewData, $fields, $options, array(
                'searchcallback'=> array($srv, 'search'),
                'pbid' => 'mknews',
                'pointerFromItem' => array('field' => 'uid', 'param' => 'tt_news')
            )
        );
        Thanks to Michael Wagner!

v0.11.11 (21.02.2012) (not released)
 *  tx_rnbase_parameters: new method init()
 *  tx_rnbase_configurations: getCObj() will always return an instance, even if not set on init().
 *  util_Exception::getAdditional() respects asString flag
 *  util_Link::makeTag() support for getAbsUrlSchema()
 *  util:db_MySQL throws exception if mysql error occures

v0.11.10 (09.02.2012) (not released)
 *  tx_rnbase_mod_FormTool: new option "sprite" for BE-links
 *  BaseModule: lib. included in TS configuration
 *  BaseModule: ModFunc-Menu can be switched by TS from select-box to tabs
 *  BaseModule: It is possible to enable or disable SubModules by TS.

v0.11.9 (28.01.2012)
 *  tx_rnbase_mod_Util: New methods for sprites.
 *  Support for multiple language files in plugins. Thanks to Hannes Bochmann!
 *  Fix for charbrowser

v0.11.8 (07.01.2012)
 *  tx_rnbase_util_Date: TCA methods fixed for value 0
 *  tx_rnbase_util_Misc: translateLLL works in BE and FE

v0.11.7 (03.01.2012) (not released)
 *  CharBrowser: parameter name for link can be changed.

v0.11.6 (29.12.2011) (not released)
 *  tx_rnbase_mod_Table: links for sorting in BE tables changed.

v0.11.5 (29.12.2011) (not released)
 *  tx_rnbase_util_Date: new methods to convert dates in TCA

v0.11.4 (17.12.2011)
 *  tx_rnbase::load() requireOnce from t3lib_div used for file loading. Thanks to Michael Wagner!
 *  tx_rnbase_util_Exception: return readable values as default. Thanks to Michael Wagner!
 *  tx_rnbase_util_Templates: It is possible to disable caching in substituteMarkerArrayCached.
 *  util_TSDAM: getMediaTCA supports options array to override config.
 *  It is possible to disable caching in substituteMarkerCached by Typoscript. This saves a lot of memory for large listviews.

v0.11.3 (24.11.2011) (not released)
 *  util_Link::makeUrlParam(): Sonderfall weiteres Array im Parameter
 *  util_BaseMarker::checkLinkExistence(): default values for last parameters

v0.11.2 (14.11.2011) (not released)
 *  tx_rnbase_configurations::getPluginUid() will always return a unique id even if plugin is defined by Typoscript.
 *  CacheHandlerDefault is ready to use

v0.11.1 (11.11.2011) (not released)
 *  tx_rnbase_util_DB can be used to access external databases.
 *  First caching stategy implemented
 *  tx_rnbase_util_FormTool: createTxtInput with $options array.

v0.11.0 (05.11.2011) (not released)
 *  CacheManager changed for TYPO3 4.6. It is still possible to use unconfigured caches.
 *  tx_rnbase_configurations::getLL fixed for T3 4.6. Thanks to Christian Riesche!

v0.10.9 (27.10.2011) (not released)
 *  tx_rnbase_util_FormTool: Hide and Delete Link fixed. FormValidationToken added. Thanks to Hannes Bochmann!

v0.10.8 (27.10.2011) (not released)
 *  Default html template for BE modules
 *  BE-modules it is possible to add sort links for columns. Thanks to Hannes Bochmann!
 *  All debug calls switched to tx_rnbase_util_Debug to avoid deprecation logs
 *  tx_rnbase_util_Misc: new method randomizeItems()

v0.10.7 (11.10.2011)
 *  BE-Module: New method addMessage to integrate TYPO3 flash messages
 *  BE-Handler fully integrated in ExtendedModFunc
 *  New common language file locallang.xml

v0.10.6 (30.09.2011) (not released)
 *  It is possible to send a 503 service unavailable header if an exception occures. Thanks to Hannes Bochmann!
 *  tx_rnbase_util_TYPO3: convenience methods to access uids of BE and FE users
 *  new interface tx_rnbase_mod_IModHandler

v0.10.5 (15.09.2011) (not released)
 *  FormTool: New method createShowLink.
 *  FormTool: use TCA to find hidden and deleted column in createHideLink and createDeleteLink. Thanks to Hannes Bochmann!
 *  ErrorMail: Lookup and show current FE and BE user. Thanks to Michael Wagner!

v0.10.4 (16.08.2011) (not released)
 *  SearchBase: it is possible to change wrapperclass by options

v0.10.3 (12.08.2011) (not released)
 *  Constants for log levels

v0.10.2 (05.08.2011) (not released)
 *  SimpleMarker: Default constructor with options array
 *  SimpleMarker: it is possible to add dynamic parameters by PHP code.
 *  SimpleMarker: check existence of configured links
 *  ListBuilder: SubpartMarker respects english orthography. ###COMPANIES### -> ###COMPANY###
 *  It is optional possible to load hidden objects if BE user is logged in. This works mainly for detail pages of plugins.
 *  New class for default single view
 *  New class tx_rnbase_util_Debug to encapsulate TYPO3 debug functionality for backward compatibility
 *  New generic marker MINFO to output all available markers: ###MYOBECT___MINFO###

v0.10.1 (18.07.2011) (not released)
 *  BaseFilter: Additional TS path to define filter class: filter.class

v0.10.0 (07.07.2011) (not released)
 *  Internal Changes in SearchBase and new hook in MediaMarker
 *  Full Workspace-Support added! Automatic version overlay for database queries!
 *  SearchBase: new hook searchbase_handleTableMapping
 *  tx_rnbase::load(): Make TYPO3_CONF_VARS visible for loaded classes. Used for XClass-handling.
 *  util_Link: it is possible to configure fixed server schema with absurl

v0.9.15 (30.05.2011) (not released)
 *  Workspace-Support: util_DB builds correct SQL-queries for preview mode
 *  util_Misc: new method sendErrorMail()
 *  External DB integration started
 *  PageBrowser: Offset will be never lower than 0

v0.9.14 (26.05.2011) (not released)
 *  tx_rnbase_configurations::get(): option $deep fixed to keep former behavier.

v0.9.13 (21.05.2011)
 *  tx_rnbase: Class loader supports extbase classes.
 *  New DB operator constants OP_NOTIN and OP_NOTIN_SQL
 *  New base class for services tx_rnbase_sv1_Base
 *  New view class for list views: tx_rnbase_views_List
 *  util_SearchBase: method getWrapperClass is now public
 *  tx_rnbase_util_DB::getRecord() checks for TCA configuration of table
 *  tx_rnbase_configurations::get(): option $deep reimplemented. Should work now.
 *  SimpleMarker can render simple links configured by Typoscript

v0.9.12 (02.05.2011) (not released)
 *  model_base: Magic method __call implemented to support FLUID. Thanks to Stephan Reuther!
 *  action_BaseIOC: It is possible to set view class by Typoscript. Thanks to Stephan Reuther!

v0.9.11 (01.04.2011) (not released)
 *  PageBrowser: Handle pointers out of listsize
 *  BaseView: Render PLUGIN_ before CallModules
 *  tx_rnbase_util_Files: new method makeZipFile

v0.9.9 (17.03.2011) (not released)
 *  FormTool: Some new Buttons. Thanks to Michael Wagner
 *  StartValue for totalline-Marker is configureable


v0.9.8 (24.02.2011)
 *  tx_rnbase_util_TYPO3: new check methods for TYPO3 versions 4.4 and 4.5
 *  tx_rnbase_model_base: fetch single record in default constructor with respect of hidden and deleted flags
 *  tx_rnbase_Parameters: Accessing parameters from other extensions is possible. New method getAll().
 *  tx_rnbase_util_Dates::datetime_mysql2timestamp() with timezone support
 *  tx_rnbase_util_Misc new method getPidList()

v0.9.7 (04.01.2011)
 *  New class tx_rnbase_util_Strings

v0.9.6 (27.12.2010)
 *  Bugfix in tx_rnbase_util_DB::searchWhere: it is possible to test joined values against 0
 *  tx_rnbase_util_DB: new methods enableFields() and doQuery()
 *  tx_rnbase_util_TSDAM: some new methods to access DAM information

v0.9.5 (16.11.2010)
 *  Bugfix: Avoid warning while parsing TS configuration for plots
 *  Bugfix: Missing include in datebase unit test added. Thanks to Hannes Bochmann
 *  FormTool: Submit-Buttons and Links with icons possible
 *  FormTool: New method getTCEForm()
 *  FormTool: New buttons with confirm message

v0.9.4 (21.10.2010)
 *  tx_rnbase_configurations: new methods getInt() and getBool()
 *  BaseModule: more modificatios possible for concrete modules

v0.9.3 (26.09.2010)
 *  SearchBase and util_DB: Support for HAVING clause.

v0.9.2 (26.09.2010) (not released)
 *  ListBuilder renders character browser
 *  Bugfix BaseFilter: CharBrowser works now with special characters (Thanks to Michael Wagner)
 *  BaseController: new option ignoreActionParam to disable action setting by url parameters

v0.9.1 (13.09.2010)
 *  SearchBase: ignore forcewrapper for count

v0.9.0 (03.09.2010)
 *  Chart builder for extension pbimagegraph

v0.8.9 (09.08.2010)
 *  All references to extensions lib/div removed.
 *  PageBrowser: Respect maximum list limit.
 *  tx_rnbase_controller: Verbose error messages possible if label found. Thanks to Lars Heber!
 *  tx_rnbase_controller: Sending error mailings fixed. Thanks to Lars Heber!
 *  Default file based cache can be activated in extension configuration.
 *  Action based caching interface for frontend plugins. Not finished yet!
 *  tx_rnbase_util_Link: KeepVars of other extension can be configured. Thanks to Pavel Klinkov!
   linkcfg.useKeepVars.add = tx_ttnews::ttnews, tx_ttnews::*

v0.8.8 (17.07.2010)
 *  Avoid warnings for NumberFormat called with string values
 *  new class tx_rnbase_maps_Util

v0.8.7 (05.07.2010) (not released)
 *  tx_rnbase_util_Files::getFileResource() works in BE

v0.8.6 (03.07.2010)
 *  prepareTSFE: timetrack init before tsfe
 *  CharBrowser: new option to place special fields at last
 *  PageBrowser: init link by Typoscript

v0.8.5 (29.06.2010) (not released)
 *  ListBuilder: support for multiple list subparts in a single template

v0.8.4 (28.06.2010) (not released)
 *  tx_rnbase_maps_google_Map: JS initialize fixed
 *  tx_rnbase_maps_Coord: variables fixed
 *  $_SERVER is included in error mail
 *  Visitors for ListBuilder
 *  New method tx_rnbase_util_Templates::getSubpartFromFile()

v0.8.3 (07.06.2010) (not released)
 *  New marker ###.._TOTALLINE### in ListBuilder. It shows current line number of a large list with pagebrowser.
 *  tx_rnbase_util_SearchBase: New option "callback" for doSelect

v0.8.2 (07.06.2010) (not released)
 *  mod_Tables: getHeadline() uses title string as fallback
 *  util_FormTool: new method getStoredRequestData()
 *  tx_rnbase_util_DB: New option "callback" for doSelect

v0.8.1 (01.06.2010) (not released)
 *  tx_rnbase_util_FormTool::createEditButton with confirm option
 *  tx_rnbase_util_Link: new TS options noCache and noHash
 *  SearchBase: Ignore invalid queries for SEARCH_FIELD_JOINED
 *  tx_rnbase_util_DB: Bugfix for OP_LIKE with multiple strings.
 *  tx_rnbase_util_DB: New operator OP_LIKE_CONST.

v0.8.0 (11.05.2010)
 *  Additional check for installed DAM in tx_rnbase_util_TSDAM
 *  Current SITE_URL is included in error mail

v0.7.8 (02.05.2010) (not released)
 *  New option forceException4Mayday in EM
 *  New method tx_rnbase_util_Templates::getSubpart

v0.7.7 (01.05.2010) (not released)
 *  New class tx_rnbase_util_SimpleMarker
 *  GET and POST vars for error mail added
 *  tx_rnbase_Controller: missing include fixed

v0.7.6 (29.04.2010) (not released)
 *  ErrorMail: Send only one mail within one minute.
 *  It is possible to configure error messages for uncaught exceptions. Create a default language label ERROR_default in your extension language file. You can define labels for each exception error code like ERROR_123.

v0.7.5 (29.04.2010) (not released)
 *  mod_BaseModFunc: new default marker with performance data: ###MOD_PARSETIME###, ###MOD_MEMUSED###, ###MOD_MEMSTART###, ###MOD_MEMEMD###

v0.7.4 (24.04.2010) (not released)
 *  Configuration of TYPO3 Cache fixed
 *  New method has() for Cache

v0.7.3 (23.04.2010) (not released)
 *  New method configurations::getConfigArray()
 *  ListBuilder: Markerclass for PageBrowser is configurable by Typoscript

v0.7.2 (20.04.2010) (not released)
 *  Exception handling fixed

v0.7.1 (16.04.2010) (not released)
 *  Mail info for uncaught exceptions
 *  New class tx_rnbase_util_Logger

v0.7.0 (05.04.2010)
 *  Some fixes in DB layer

v0.6.8 (01.04.2010) (not released)
 *  PageBrowser in ListBuilder fixed
 *  util_DB: Some fixes in searchWhere(). Unit test added.

v0.6.7 (26.03.2010) (not released)
 *  util_DB: Some fixes in searchWhere(). Unit test added.
 *  New class rx_rnbase_util_Json

v0.6.6 (25.03.2010) (not released)
 *  New unit test for ListBuilder
 *  MediaMarker: multilanguage support and hooks
 *  util_Dates::date_mysql2tstamp returns null for a MySQL-Date 0000-00-00. Thanks to Lars Heber!
 *  util_DB: The leading AND is removed in methods _getSearchOr, _getSearchSetOr and _getSearchLike. This can cause errors in some usecases!

v0.6.5 (25.02.2010) (not released)
 *  ListBuilder supports subpart for empty lists.
 *  All occurrences of split() replaced with explode() to avoid warnings in PHP 5.3
 *  Some Bugfixes in tx_rnbase_util_Templates

v0.6.4 (17.02.2010) (not released)
 *  New API for Caches
 *  util_Link: it is possible to add more static parameter by Typoscript:
linkcfg.useKeepVars.add = ::type=300, mykey=myvalue


v0.6.3 (05.02.2010) (not released)
 *  tx_rnbase_util_Link: New method initByTS() and it is possible to generate absolute URLs

v0.6.1 (05.02.2010) (not released)
 *  tx_rnbase_util_Misc: Include tstemplate.php in prepareTSFE()
 *  BaseMarker: Missing include of tx_rnbase fixed
 *  tx_rnbase_configurations: New option deep in method get(). Thanks to Lars Heber.
 *  Update for TYPO3 4.3. New usage for tx_rnbase::makeInstance(). This method can be used to instanciate object with parameters for constructor. Also backward compatible with lower versions of TYPO3.
 *  Deprecated function calls removed
 *  BaseFilter: new static methods for handlePageBrowser() and handleCharBrowser()
 *  Link: Support Parameters for other extensions. Use "qualifier::param" as key.
 *  All dependencies to tx_div removed

v0.6.0 (30.12.2009)
 *  SearchBase: CUSTOM orderby clause is possible
 *  SearchGeneric: double alias declaration for base table removed

v0.5.5 (23.12.2009) (not released)
 *  There is a new class tx_rnbase_util_Templates for marker substitutions HTML templates. It does the same as substituteMarkerArrayCached but has no call to $GLOBALS['TT']. This heavily reduces memory consumption.

v0.5.4 (15.12.2009) (not released)
 *  New class tx_rnbase_mod_Tables
 *  New method tx_rnbase::getClassInfo()
 *  additional BE user access removed from BaseModule
 *  tx_rnbase_configurations::createLink will now return instances of tx_rnbase_util_Link

v0.5.3 (22.11.2009)
 *  TSDAM: Usage of tx_dam_media removed. This class can't handle two dam records for one single media path.
 *  New framework for backend modules
 *  PageBrowser: limit is never larger then list size

v0.5.2 (07.11.2009)
 *  SearchBase: OP_INSET_INT works for multiple values
 *  TSDAM: new implementation of fetchFileList()
 *  BaseView: automatic call of tx_rnbase_util_BaseMarker::callModules()
 *  Small bugfix for map rendering

v0.5.1 (28.10.2009) (not released)
 *  tx_rnbase_util_DB::doSelect(): new options sqlonly and union
 *  tx_rnbase_util_SearchBase: Support for sqlonly and union
 *  tx_rnbase_util_SearchBase: Some modifications for alias support. Thanks to Lars Heber!
 *  New class tx_rnbase_util_SearchGeneric: a configurable implementation for tx_rnbase_util_SearchBase.

v0.5.0 (25.09.2009)
 *  Performancetests for views (subclasses of BaseIOC) and ListBuilder.
 *  TSDAM: i18n-Support for DAM 1.1.0 and higher only
 *  BaseMarker: new method findUnusedCols()

v0.4.5 (21.09.2009) (not released)
 *  Memory output in sql debug
 *  New parameter $template for BaseMarker::initLink()
 *  BaseFilter::init() with return value

v0.4.4 (20.09.2009) (not released)
 *  FormatUtil: Support for dynamic markers. Fields starting with "dc" will be added as columns to marker arrays.
 *  More debug information for ListBuilder and Plugin-Views.

v0.4.3 (05.09.2009)
 *  New option forcewrapper in SearchBase. This allows userdefined "what" and "groupby" in combination with "wrapperclass".
 *  New method getConfId() in tx_rnbase_action_BaseIOC
 *  tx_rnbase_util_BaseMarker::initLink() has support for page aliases. Thanks to Holger Gebhardt!
 *  tx_rnbase_util_TSDAM: New option forcedIdField.
 *  New method tx_rnbase_util_TSDAM::deleteReferences()
 *  TSDAM: Output of translated DAM records. Thanks to Lars Heber.
 *  SearchBase: Support for aliases in SQL statements.

v0.4.2 (22.05.2009)
 *  Rendering of DAM images with new marker ###MEDIA_PARENTUID###. This contains the uid of the parent record.
 *  tx_rnbase_util_Dates: New methods to handle dateformat yyyymmdd

v0.4.1 (20.04.2009)
 *  Maps-API extended: MapTypes, Controls, Icons
 *  GoogleMap can be configured by Typoscript

v0.4.0 (18.04.2009)
 *  First version of a common maps API. Current implementation uses extension wec_map to show a Google Map. To be extended...

v0.3.5 (12.03.2009)
 *  tx_rnbase_controller catches exceptions from actions
 *  New methods get() and getInt() in tx_rnbase_IParameters


v0.3.4 (05.03.2009)
 *  Check for invalid records in tx_rnbase_util_TSDAM
 *  New method tx_rnbase_util_Misc::createHash
 *  Method tx_rnbase_util_BaseMarker::disableLink is now public
 *  Check for invalid records in tx_rnbase_util_TSDAM::printImages()

v0.3.3 (04.03.2009)
 *  simplegallery.html used as default template for DAM pictures
 *  New filter method hideResult()

v0.3.2 (26.02.2009)
 *  Bugfix in DefaultFilter
 *  Offset parameter in tx_rnbase_util_TSDAM

v0.3.1 (26.02.2009)
 *  ListBuilder support for filters
 *  LinkUrls created without htmlspecialschars()

v0.3.0 (20.02.2009)
 *  New class for parameters
 *  New util class for date conversions
 *  New filter package
 *  SearchBase extended
 *  allow and deny list for useKeepVars option in tx_rnbase_util_BaseMarker::initLink

v0.2.16 (06.01.2009)
 *  Bugfix: sysPage init used $this in tx_rnbase_util_DB::doSelect
 *  New method tx_rnbase_util_Misc::translateLLL
 *  New method tx_rnbase_util_FormTool::createHistoryLink
 *  New method tx_rnbase_models_base::getUid()
 *  Info methods about TYPO3 extensions in tx_rnbase_util_TYPO3
 *  Some new methods in tx_rnbase_util_TSDAM

v0.2.15 (30.12.2008)
 *  Neue Vergleichsoperatoren in SearchBase
 *  doUpdate und doDelete liefern als Ergebnis die Anzahl der betroffenen Datensätze

v0.2.14 (13.12.2008)
 *  tx_rnbase_util_formTool extended

v0.2.13 (06.12.2008)
 *  Neue Option im PageBrowser hideIfSinglePage. Damit kann der Browser bei einer Einzelseite komplett ausgeblendet werden.

v0.2.12 (28.11.2008)
 *  Neue Klasse tx_rnbase_util_TYPO3
 *  Bugfix im Basemarker bei Einbindung von Subpart-ModulMarkern.
 *  tx_rnbase_util_DB::doUpdate mit neuem Parameter noQuoteFields.

v0.2.11 (06.11.2008)
 *  Verbesserte Ausgabe von DAM-Bildern per Typoscript. Die Ausgabe erfolgt über ein USER-Objekt.

v0.2.9 (03.11.2008)
 *  In SearchBase kann das CUSTOM-SQL über Typoscript gesetzt werden

v0.2.8 (25.10.2008)
 *  tx_rnbase_util_DB::doSelect() unterscheidet bei den enableFields automatisch zwischen BE und FE.
 *  Für den Check von enableFields wird jetzt eine eigene Instanz von t3lib_pageSelect verwendet um Fehler im BE zu vermeiden
 *  Die Extension verlangt nun PHP 5

v0.2.7 (08.08.2008)
 *  Die Configuration wertet automatisch das Typoscript-Setup aus einem Flexform aus. Das Sheet muss "s_tssetup" und das Feld "flexformTS" heissen.
 *  FormTool::createDateInput() fixed. There was an issue with time zone. Thanks to Thomas Maroschik!

v0.2.6 (31.07.2008)
 *  Neue Methode tx_rnbase_util_Misc::validateSearchString()

v0.2.5 (20.07.2008)
 *  Fix in disableLink (Marker für LinkUrl)
 *  Neue Methoden showMenu() und showTabMenu() in util_FormTool
 *  Neue Methode objImplode in util_Misc

v0.2.4 (11.07.2008)
 *  Neue Methoden FormUtil::getTCAFormArray(), FormUtil::getMenu() und FormUtil::getTabMenu()

v0.2.3 (30.06.2008)
 *  xclass definitions korrigiert
 *  Parameter können über die Configurations abgefragt werden

v0.2.2 (11.06.2008)
 *  in prepareTSFE wird die Konstante PATH_tslib geprüft und ggf. gesetzt.

v0.2.1 (06.06.2008)
 *  Das Verhalten der Mayday Ausgaben kann konfiguriert werden.
 *  In der Suche kann die Sortierung per RAND() erfolgen
 *  Neue Methode removeUmlauts()

v0.1.5 (31.05.2008)
 *  Kleine Bugfix in getKeyNames() der Config

v0.1.4 (25.05.2008)
 *  eigenes Image-Tag für DAM-Bilder: ###_IMGTAG###

v0.1.2 (17.05.2008)
 *  Kleinere Erweiterungen

v0.1.2 (05.05.2008)
 *  Automatische Perfmancemessungen mit t3lib_timeTrack.
 *  BaseMarker hat Prüfmethode für das Vorhandensein von bestimmten Markern.

v0.1.0 (02.05.2008)
## UMSTELLUNG AUF PHP5 ###
Es sind erstmals Methoden integriert, die nur mit PHP5 funktionieren! Abhängige Extensions, die
auch mit PHP4 funktionierten, sind ggf. unter PHP4 nicht kompatibel mit dieser Version. Bei Verwendung
von PHP5 sollte es keine Probleme geben.

 *  Configurations-Klasse löst jetzt auch TS-Referenzen auf
 *  Configurations-Klasse liest jetzt auch Keys mit Punktnotation aus Flexforms
 *  SearchBase für einfache DB-Afragen
 *  Klassen zur Erstellung von Listenausgaben
 *  Neue Methode watchOutDB() zum Debug von fehlerhaften SQL-Statements
 *  Neue Klasse tx_rnbase_util_Misc mit MayDay-Methode analog zu ameos_formidable
 *  Neue Methode zur Abfrage der Extensionkonfiguration in tx_rnbase_configuration::getExtensionCfgValue()
 *  Neue Methode zur Abfrage von Informationen aus der TCA in tx_rnbase_util_DB
 *  Bugfix: getTemplatePath() in configuration hatte einen Schreibfehler
 *  bei DB-Abfragen kann enableFields deaktiviert werden. Somit kann auch auf Tabellen ohne TCA-Beschreibung zugegriffen werden.
 *  Neue Klasse FormUtil für Backend-Formulare
 *  BaseMarker hat Methoden um Marker dynamisch per Service einzulesen

v0.0.9 (12.11.2007)
- die wrap-Funktionen im FormatUtil haben einen neuen Parameter für das Data-Array. Dieses
    wird, wenn vorhanden im verwendeten cObject gesetzt.
- in configurations gibt es die neue Methode getKeynames()

v0.0.7 (30.09.2007)
- Handling der freien Label im BaseMarker geändert. Die Punktnotation wird für
    Labels automatisch in Underscores umgewandelt. Dadurch können die Strings dann im Sprachfile
    gefunden werden

v0.0.6 (27.09.2007)
- in Configuration bei der Methode removeKeepVar den Qualifier entfernt
- Neue Klasse für PageBrowser
- FormatUtil Case-Func bei getItemMarkerArrayWrapped()
- FormatUtil neue Methode dataStdWrap()
- FormatUtil neue Methode numberFormat()


v0.0.5 (28.08.2007)
- Im FormatUtil wird in der Methode getItemMarkerArrayWrapped() der Datensatz in die Instanz von
    cObject kopiert. Damit sind erweiterte TS-Anweisungen möglich.
- Verwendung des korrekten Klassennamens für das tx_lib_spl_arrayObject
v0.0.4
- Änderung der KeepVars in der Configuration-Klasse
v0.0.3
- Neue Methode getColumnWrapped() in tx_rnbase_model_base
