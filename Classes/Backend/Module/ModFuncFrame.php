<?php

namespace Sys25\RnBase\Backend\Module;

use Psr\Http\Message\ServerRequestInterface;
use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Backend\Template\ModuleParts;
use Sys25\RnBase\Backend\Template\ModuleTemplate;
use Sys25\RnBase\Backend\Utility\BackendUtility;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Configuration\Processor;
use Sys25\RnBase\Frontend\Marker\BaseMarker;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Utility\Arrays;
use Sys25\RnBase\Utility\Files;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\TYPO3;
use TYPO3\CMS\Backend\Module\ModuleInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

/**
 * Rendering von Modulen ab T3 12.
 * Die Klasse stellt den Rahmen für ein Backendmodul mit
 * ModFuncs bereit. Aus Sicht von TYPO3 sind die ModFuncs
 * Controllerklassen.
 * Ein Hauptmodul aus Sicht von rn_base ist nicht mehr notwendig.
 *
 * @php74 wird nur mit PHP 7.4 verwendet
 */
class ModFuncFrame implements IModule
{
    private IconFactory $iconFactory;
    private UriBuilder $uriBuilder;
    private PageRenderer $pageRenderer;
    private ModuleTemplate $moduleTemplate;
    private ?ToolBox $toolBox = null;

    /**
     * Current page id.
     */
    protected int $id;

    /**
     * ehemals perms_clause.
     */
    protected string $moduleIdentifier;

    protected ModuleInterface $currentModule;
    protected IModFunc $modFunc;
    protected ?ConfigurationInterface $configurations = null;
    protected $doc;
    protected $tabs;

    /**
     * @var array
     */
    protected $selector;

    public function __construct(
        IconFactory $iconFactory,
        UriBuilder $uriBuilder,
        PageRenderer $pageRenderer
    ) {
        $this->iconFactory = $iconFactory;
        $this->uriBuilder = $uriBuilder;
        $this->pageRenderer = $pageRenderer;
    }

    public function render(IModFunc $modFunc, callable $renderFunc, ServerRequestInterface $request)
    {
        $this->modFunc = $modFunc;
        $this->moduleIdentifier = $modFunc->getModuleIdentifier();
        $this->id = (int) ($request->getQueryParams()['id'] ?? $request->getParsedBody()['id'] ?? 0);
        $this->currentModule = $request->getAttribute('module');
        $this->getLanguageService()->includeLLFile('EXT:rn_base/Resources/Private/Language/locallang.xlf');
        $config = $this->getConfigurations();
        $files = $config->get('languagefiles.');
        foreach ($files as $filename) {
            $this->getLanguageService()->includeLLFile($filename);
        }

        $this->modFunc->init($this, [
                // 'form' => $this->getFormTag(),
                // 'docstyles' => $this->getDocStyles(),
                // 'template' => $this->getModuleTemplateFilename(),
        ]);
        // Rahmen rendern
        $this->moduleTemplate = $this->createModuleTemplate($request);
        // Die Variable muss gesetzt sein.
        $this->doc = $this->moduleTemplate->getDoc();
        /* @var $parts ModuleParts */
        $parts = \tx_rnbase::makeInstance(ModuleParts::class);
        $this->prepareModuleParts($parts, $renderFunc);

        $content = $this->renderContent($parts);

        $response = new \TYPO3\CMS\Core\Http\HtmlResponse($content);

        return $response;
    }

    protected function renderContent(ModuleParts $parts): string
    {
        $content = $this->moduleTemplate->renderContent($parts);

        $params = $markerArray = $subpartArray = $wrappedSubpartArray = [];
        BaseMarker::callModules($content, $markerArray, $subpartArray, $wrappedSubpartArray, $params, $this->getConfigurations()->getFormatter());
        $content = Templates::substituteMarkerArrayCached($content, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $content;
    }

    protected function prepareModuleParts($parts, $renderFunc)
    {
        // Access check. The page will show only if there is a valid page
        // and if this page may be viewed by the user
        $pageinfo = BackendUtility::readPageAccess($this->getPid(), $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW)) ?: [];

        $parts->setContent($renderFunc()); // $this->moduleContent()
        //        $parts->setButtons($this->getButtons());
        $parts->setTitle($this->getLanguageService()->getLL('title'));
        // Um das Hauptmenu kümmert sich jetzt TYPO3
        //        $parts->setFuncMenu($this->getFuncMenu());
        // if we got no array the user got no permissions for the
        // selected page or no page is selected
        $parts->setPageInfo(is_array($pageinfo) ? $pageinfo : []);
        $parts->setSubMenu($this->tabs);
        $parts->setSelector($this->selector ?? '');
    }

    protected function createModuleTemplate(ServerRequestInterface $request): ModuleTemplate
    {
        $moduleTemplate = \tx_rnbase::makeInstance(ModuleTemplate::class, $this, [
            'form' => $this->getFormTag(),
            'docstyles' => '',
            'request' => $request,
            'template' => $this->getModuleTemplateFilename(),
        ]);

        return $moduleTemplate;
    }

    /**
     * Returns the filename for module HTML template. This can be overwritten.
     * This should be configured in your pageTs: mod.module_ident.template
     * The former fallback at EXT:[your_ext_key]/mod1/template.html is not supported anymore.
     * If there is no file configured the default from rn_base is used.
     *
     * @return string
     */
    protected function getModuleTemplateFilename()
    {
        $filename = $this->getConfigurations()->get('template');
        if (file_exists(Files::getFileAbsFileName($filename, true, true))) {
            return $filename;
        }

        return 'EXT:rn_base/Resources/Private/Templates/template2.html';
    }

    /**
     * Builds the form open tag.
     *
     * @TODO: per TS einstellbar machen
     *
     * @return string
     */
    protected function getFormTag()
    {
        $modUrl = (string) $this->uriBuilder->buildUriFromRoute($this->currentModule->getIdentifier());

        return '<form action="'.$modUrl.'" method="post" name="editform" enctype="multipart/form-data"><input type="hidden" name="id" value="'.htmlspecialchars($this->id).'" />';
    }

    public function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Liefert eine Instanz von ConfigurationInterface. Da wir uns im BE bewegen, wird diese mit einem
     * Config-Array aus der TSConfig gefüttert. Dabei wird die Konfiguration unterhalb von mod.extkey. genommen.
     * Für "extkey" wird der Wert der Methode getExtensionKey() verwendet.
     * Zusätzlich wird auch die Konfiguration von "lib." bereitgestellt.
     * Wenn Daten für BE-Nutzer oder Gruppen überschrieben werden sollen, dann darauf achten, daß die
     * Konfiguration mit "page." beginnen muss. Also bspw. "page.lib.test = 42".
     *
     * Ein eigenes TS-Template für das BE wird in der ext_localconf.php mit dieser Anweisung eingebunden:
     * \Sys25\RnBase\Utility\Extensions::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:myext/mod1/pageTSconfig.txt">');
     *
     * @return ConfigurationInterface
     */
    public function getConfigurations()
    {
        if (null === $this->configurations) {
            Misc::prepareTSFE(); // Ist bei Aufruf aus BE notwendig!
            $cObj = TYPO3::getContentObject();

            $pageTSconfigFull = BackendUtility::getPagesTSconfig($this->getPid());
            $pageTSconfig = $pageTSconfigFull['mod.'][$this->moduleIdentifier.'.'] ?? [];
            $pageTSconfig['lib.'] = $pageTSconfigFull['lib.'] ?? [];

            $userTSconfig = $this->getBackendUser()->getTSConfig('mod.'.$this->moduleIdentifier.'.');
            if (!empty($userTSconfig['properties'])) {
                $pageTSconfig = Arrays::mergeRecursiveWithOverrule($pageTSconfig, $userTSconfig['properties']);
            }

            $qualifier = $pageTSconfig['qualifier'] ?? $this->moduleIdentifier;
            $this->configurations = \tx_rnbase::makeInstance(Processor::class);
            $this->configurations->init($pageTSconfig, $cObj, $this->moduleIdentifier, $qualifier);

            // init the parameters object
            $this->configurations->setParameters(
                \tx_rnbase::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class)
            );
            $this->configurations->getParameters()->init('SET');
        }

        return $this->configurations;
    }

    /**
     * @see IModule::getFormTool()
     *
     * @return \Sys25\RnBase\Backend\Form\ToolBox
     */
    public function getFormTool()
    {
        if (!$this->toolBox) {
            $this->toolBox = \tx_rnbase::makeInstance(ToolBox::class);
            $this->toolBox->init($this->getDoc(), $this);
        }

        return $this->toolBox;
    }

    public function getPid(): int
    {
        return $this->id;
    }

    /**
     * @return \Sys25\RnBase\Backend\Template\Override\DocumentTemplate
     */
    public function getDoc()
    {
        return $this->moduleTemplate->getDoc();
    }

    public function getName()
    {
        return $this->moduleIdentifier;
    }

    /**
     * Submenu String for the marker ###TABS###.
     *
     * @param $menuString
     */
    public function setSubMenu($menuString)
    {
        $this->tabs = $menuString;
    }

    /**
     * Selector String for the marker ###SELECTOR###.
     *
     * @param $selectorString
     */
    public function setSelector($selectorString)
    {
        $this->selector = $selectorString;
    }

    /**
     * @param string $message
     * @param string $title;
     * @param int    $severity       Optional severity, must be either of t3lib_message_AbstractMessage::INFO, t3lib_message_AbstractMessage::OK,
     *                               t3lib_message_AbstractMessage::WARNING or t3lib_message_AbstractMessage::ERROR. Default is t3lib_message_AbstractMessage::OK.
     *                               const NOTICE  = -2;
     *                               const INFO    = -1;
     *                               const OK      = 0;
     *                               const WARNING = 1;
     *                               const ERROR   = 2;
     * @param bool   $storeInSession Optional, defines whether the message should be stored in the session or only for one request (default)
     */
    public function addMessage($message, $title = '', $severity = 0, $storeInSession = false)
    {
    }

    public function getRouteIdentifier()
    {
        return $this->currentModule->getIdentifier();
    }

    public function getTitle()
    {
        return $this->currentModule->getTitle();
    }
}
