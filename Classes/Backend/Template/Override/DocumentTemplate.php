<?php

namespace Sys25\RnBase\Backend\Template\Override;

use Sys25\RnBase\Backend\Utility\Icons;
use Sys25\RnBase\Utility\Files;
use Sys25\RnBase\Utility\LanguageTool;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\T3General;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Localization\Locale;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/* *******************************************************
 *  Copyright notice
 *
 *  (c) 2017-2023 René Nitzsche <rene@system25.de>
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
 */

class DocumentTemplate
{
    public const STATE_NOTICE = -2;
    public const STATE_INFO = -1;
    public const STATE_OK = -1;
    public const STATE_DEFAULT = 0;
    public const STATE_SUCCESS = 0;
    public const STATE_WARNING = 1;
    public const STATE_ERROR = 2;

    public $divClass = false;

    /** @deprecated use external js files */
    public $JScode = '';
    public $endOfPageJsBlock = '';
    /**
     * Similar to $JScode but for use as array with associative keys to prevent double inclusion of JS code. a <script> tag is automatically wrapped around.
     *
     * @var array
     */
    public $JScodeArray = ['jumpToUrl' => '
        function jumpToUrl(URL) {
            window.location.href = URL;
            return false;
        }
	'];

    /**
     * JavaScript files loaded for every page in the Backend.
     *
     * @var array
     */
    protected $jsFiles = [];

    /**
     * JavaScript files loaded for every page in the Backend, but explicitly excluded from concatenation (useful for libraries etc.).
     *
     * @var array
     */
    protected $jsFilesNoConcatenation = [];

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var string
     */
    public $postCode;

    /** @var string */
    protected $moduleTemplate;

    /** @var string */
    protected $moduleTemplateFilename;
    public $form;

    /** @var \TYPO3\CMS\Core\Messaging\FlashMessageService */
    protected $flashMessageService;

    /** @var LanguageService */
    private $lang;
    /** @var LanguageTool */
    private $languageTool;

    /**
     * Constructor.
     */
    public function __construct(
        ?LanguageTool $languageTool = null,
        ?\TYPO3\CMS\Core\Messaging\FlashMessageService $flashMessageService = null
    ) {
        $this->languageTool = $languageTool ?? tx_rnbase::makeInstance(LanguageTool::class);
        $this->lang = $this->languageTool->getLanguageService();
        // Initializes the page rendering object:
        $this->initPageRenderer();

        $this->flashMessageService = $flashMessageService ?? tx_rnbase::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
    }

    /**
     * Override deprecated and removed method.
     */
    public function getPageRenderer()
    {
        return $this->pageRenderer;
    }

    /* *** ************************************************ *** *
     * *** ************************************************ *** *
     * *** Removed Tab-menu Methods (removed since TYPO3 9) *** *
     * *** ************************************************ *** *
     * *** ************************************************ *** */

    /**
     * Creates a tab menu from an array definition.
     *
     * Returns a tab menu for a module
     * Requires the JS function jumpToUrl() to be available
     *
     * @param mixed  $mainParams   is the "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
     * @param string $elementName  it the form elements name, probably something like "SET[...]
     * @param string $currentValue is the value to be selected currently
     * @param array  $menuItems    is an array with the menu items for the selector box
     * @param string $script       is the script to send the &id to, if empty it's automatically found
     * @param string $addparams    is additional parameters to pass to the script
     *
     * @return string HTML code for tab menu
     */
    public function getTabMenu($mainParams, $elementName, $currentValue, $menuItems, $script = '', $addparams = '')
    {
        $content = '';
        if (is_array($menuItems)) {
            if (!is_array($mainParams)) {
                $mainParams = ['id' => $mainParams];
            }
            $mainParams = T3General::implodeArrayForUrl('', $mainParams);
            if (!$script) {
                $script = basename(\Sys25\RnBase\Utility\Environment::getCurrentScript());
            }
            $menuDef = [];
            foreach ($menuItems as $value => $label) {
                $menuDef[$value]['isActive'] = (string) $currentValue === (string) $value;
                $menuDef[$value]['label'] = htmlspecialchars($label, ENT_COMPAT, 'UTF-8', false);
                $menuDef[$value]['url'] = $script.'?'.$mainParams.$addparams.'&'.$elementName.'='.$value;
            }
            $content = $this->getTabMenuRaw($menuDef);
        }

        return $content;
    }

    /**
     * Creates the HTML content for the tab menu.
     *
     * @param array $menuItems Menu items for tabs
     *
     * @return string Table HTML
     */
    public function getTabMenuRaw($menuItems)
    {
        if (!is_array($menuItems)) {
            return '';
        }
        $options = '';
        foreach ($menuItems as $def) {
            $class = $def['isActive'] ? ' active' : '';
            $label = $def['label'];
            $url = htmlspecialchars($def['url'] ?? '');
            $params = $def['addParams'] ?? '';
            $options .= '<li><a class="'.$class.'" href="'.$url.'" '.$params.'>'.$label.'</a></li>';
        }

        return '<ul class="nav nav-tabs" role="tablist">'.$options.'</ul>';
    }

    /**
     * Returns a blank <div>-section with a height.
     *
     * @param int $dist Padding-top for the div-section (should be margin-top but konqueror (3.1) doesn't like it :-(
     *
     * @return string HTML content
     *
     * @todo Define visibility
     */
    public function spacer($dist)
    {
        if ($dist > 0) {
            return '<!-- Spacer element --><div style="padding-top: '.(int) $dist.'px;"></div>';
        }

        return '';
    }

    /**
     * Begins an output section and sets header and content.
     *
     * @param string $label             The header
     * @param string $text              The HTML-content
     * @param bool   $nostrtoupper      A flag that will prevent the header from being converted to uppercase
     * @param bool   $sH                Defines the type of header (if set, "<h3>" rather than the default "h4")
     * @param int    $type              The number of an icon to show with the header (see the icon-function). -1,1,2,3
     * @param bool   $allowHTMLinHeader If set, HTML tags are allowed in $label (otherwise this value is by default htmlspecialchars()'ed)
     *
     * @return string HTML content
     *
     * @see icons(), sectionHeader()
     */
    public function section($label, $text, $nostrtoupper = false, $sH = false, $type = 0, $allowHTMLinHeader = false)
    {
        $title = $label;
        $message = $text;
        $disableIcon = 0 == $type;

        $classes = [
            self::STATE_NOTICE => 'info',
            self::STATE_OK => 'success',
            self::STATE_WARNING => 'warning',
            self::STATE_ERROR => 'danger',
            self::STATE_DEFAULT => 'default',
        ];
        $icons = [
            self::STATE_NOTICE => 'lightbulb-o',
            self::STATE_OK => 'check',
            self::STATE_WARNING => 'exclamation',
            self::STATE_ERROR => 'times',
            self::STATE_DEFAULT => '',
        ];
        $stateClass = isset($classes[$type]) ? $classes[$type] : self::STATE_DEFAULT;
        $icon = isset($icons[$type]) ? $icons[$type] : null;
        $iconTemplate = '';
        if (!$disableIcon) {
            $iconTemplate = ''.
                '<div class="media-left">'.
                  '<span class="fa-stack fa-lg callout-icon">'.
                    '<i class="fa fa-circle fa-stack-2x"></i>'.
                    '<i class="fa fa-'.$icon.' fa-stack-1x"></i>'.
                  '</span>'.
                '</div>';
        }
        $titleTemplate = '';
        if (null !== $title) {
            $title = $allowHTMLinHeader ? $title : htmlspecialchars($title);
            $titleTemplate = '<h4 class="callout-title">'.$title.'</h4>';
        }

        return '<div class="callout callout-'.htmlspecialchars($stateClass).'">'.
                 '<div class="media">'.
                    $iconTemplate.
                   '<div class="media-body">'.
                     $titleTemplate.
                   '<div class="callout-body">'.$message.'</div>'.
                   '</div>'.
                 '</div>'.
               '</div>';
    }

    /**
     * Inserts a hr tag divider.
     *
     * @param int $dist the margin-top/-bottom of the <hr> ruler
     *
     * @return string HTML content
     */
    public function divider($dist)
    {
        $dist = (int) $dist;

        return '<!-- DIVIDER --><hr style="margin-top: '.$dist.'px; margin-bottom: '.$dist.'px;" />';
    }

    /**
     * Insert post rendering document style into already rendered content.
     *
     * @param string $content style-content to insert
     *
     * @return string content with inserted styles
     *
     * @deprecated should be removed
     */
    public function insertStylesAndJS($content)
    {
        // Insert accumulated JS
        $jscode = '';
        // TODO: check lowest version
        if (!TYPO3::isTYPO121OrHigher()) {
            $jscode = $this->JScode.LF.GeneralUtility::wrapJS(implode(LF, $this->JScodeArray));
            $content = str_replace('<!--###POSTJSMARKER###-->', $jscode, $content);
        }

        return $content;
    }

    /**
     * Returns <input> attributes to set the width of an text-type input field.
     * For client browsers with no CSS support the cols/size attribute is returned.
     * For CSS compliant browsers (recommended) a ' style="width: ...px;"' is returned.
     *
     * @param int    $size          A relative number which multiplied with approx. 10 will lead to the width in pixels
     * @param bool   $textarea      A flag you can set for textareas - DEPRECATED as there is no difference any more between the two
     * @param string $styleOverride A string which will be returned as attribute-value for style="" instead of the calculated width (if CSS is enabled)
     *
     * @return string Tag attributes for an <input> tag (regarding width)
     */
    public function formWidth($size = 48, $textarea = false, $styleOverride = '')
    {
        return ' style="'.($styleOverride ?: 'width:'.ceil($size * 9.58).'px;').'"';
    }

    /**
     * Define the template for the module.
     *
     * @param string $filename filename
     */
    public function setModuleTemplate($filename)
    {
        $this->moduleTemplate = $this->getHtmlTemplate($filename);
    }

    /**
     * Function to load a HTML template file with markers.
     * When calling from own extension, use  syntax getHtmlTemplate('EXT:extkey/template.html').
     *
     * @param string $filename tmpl name, usually in the typo3/template/ directory
     *
     * @return string HTML of template
     */
    public function getHtmlTemplate($filename)
    {
        // setting the name of the original HTML template
        $this->moduleTemplateFilename = $filename;
        if (!empty($GLOBALS['TBE_STYLES']['htmlTemplates'][$filename])) {
            $filename = $GLOBALS['TBE_STYLES']['htmlTemplates'][$filename];
        }

        if (Strings::isFirstPartOfStr($filename, 'EXT:')) {
            $filename = GeneralUtility::getFileAbsFileName($filename, true, true);
        } elseif (!Files::isAbsPath($filename)) {
            $filename = GeneralUtility::resolveBackPath($filename);
        } elseif (!GeneralUtility::isAllowedAbsPath($filename)) {
            $filename = '';
        }
        $htmlTemplate = '';
        if ('' !== $filename) {
            $htmlTemplate = GeneralUtility::getUrl($filename);
        }

        return $htmlTemplate;
    }

    /**
     * Returns page end; This includes finishing form, div, body and html tags.
     *
     * @return string The HTML end of a page
     *
     * @see startPage()
     */
    public function endPage()
    {
        if (TYPO3::isTYPO121OrHigher()) {
            return;
        }

        $str = $this->postCode.$this->wrapScriptTags(BackendUtility::getUpdateSignalCode()).($this->form ? '
</form>' : '');
        // If something is in buffer like debug, put it to end of page
        if (ob_get_contents()) {
            $str .= ob_get_clean();
            if (!headers_sent()) {
                header('Content-Encoding: None');
            }
        }
        $str .= ($this->divClass ? '

<!-- Wrapping DIV-section for whole page END -->
</div>' : '').$this->endOfPageJsBlock;

        return $str;
    }

    /**
     * Wraps the input string in script tags.
     * Automatic re-identing of the JS code is done by using the first line as ident reference.
     * This is nice for identing JS code with PHP code on the same level.
     *
     * @param string $string    Input string
     * @param bool   $linebreak wrap script element in linebreaks? Default is TRUE
     *
     * @return string Output string
     */
    public function wrapScriptTags($string, $linebreak = true)
    {
        if (trim($string)) {
            // <script wrapped in nl?
            $cr = $linebreak ? LF : '';
            // Remove nl from the beginning
            $string = ltrim($string, LF);
            // Re-ident to one tab using the first line as reference
            if ("\t" === $string[0]) {
                $string = "\t".ltrim($string, "\t");
            }
            $string = $cr.'<script>
/*<![CDATA[*/
'.$string.'
/*]]>*/
</script>'.$cr;
        }

        return trim($string);
    }

    /**
     * Initializes the page renderer object.
     */
    protected function initPageRenderer()
    {
        if (null !== $this->pageRenderer) {
            return;
        }
        $lang = $this->getLangSrv()->lang;
        if (TYPO3::isTYPO121OrHigher()) {
            $lang = new Locale($lang);
        }

        $this->pageRenderer = T3General::makeInstance(PageRenderer::class);
        $this->pageRenderer->setLanguage($lang);
        $this->pageRenderer->enableConcatenateCss();
        $this->pageRenderer->enableConcatenateJavascript();
        $this->pageRenderer->enableCompressCss();
        $this->pageRenderer->enableCompressJavascript();
        // Add all JavaScript files defined in $this->jsFiles to the PageRenderer
        foreach ($this->jsFilesNoConcatenation as $file) {
            $this->pageRenderer->addJsFile(
                $file,
                'text/javascript',
                true,
                false,
                '',
                true
            );
        }
        // Add all JavaScript files defined in $this->jsFiles to the PageRenderer
        foreach ($this->jsFiles as $file) {
            $this->pageRenderer->addJsFile($file);
        }
        if (!TYPO3::isTYPO130OrHigher() && 1 === (int) $GLOBALS['TYPO3_CONF_VARS']['BE']['debug']) {
            $this->pageRenderer->enableDebugMode();
        }
    }

    public function showFlashMessage($message, $severity = self::STATE_NOTICE, $header = 'Notice')
    {
        $lang = $this->getLangSrv();
        $message = $lang->getLL($message) ?: $message;
        $header = $lang->getLL($header) ?: $header;
        $severityMap = [];
        if (TYPO3::isTYPO121OrHigher()) {
            $severityMap = [
                self::STATE_NOTICE => \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::NOTICE,
                self::STATE_INFO => \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO,
                self::STATE_DEFAULT => \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK,
                self::STATE_WARNING => \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::WARNING,
                self::STATE_ERROR => \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR,
            ];
        } else {
            $severityMap = [
                self::STATE_NOTICE => \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE,
                self::STATE_INFO => \TYPO3\CMS\Core\Messaging\FlashMessage::INFO,
                self::STATE_DEFAULT => \TYPO3\CMS\Core\Messaging\FlashMessage::OK,
                self::STATE_WARNING => \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING,
                self::STATE_ERROR => \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
            ];
        }

        $message = T3General::makeInstance(
            \TYPO3\CMS\Core\Messaging\FlashMessage::class,
            $message,
            $header,
            $severityMap[$severity] ?? null,
            false
        );
        $messageQueue = $this->flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->addMessage($message);
    }

    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService|\TYPO3\CMS\Lang\LanguageService
     */
    public function getLangSrv()
    {
        return $this->lang;
    }
}
