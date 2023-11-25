<?php

namespace Sys25\RnBase\Backend\Form;

use Sys25\RnBase\Backend\Form\Element\EnhancedLinkButton;
use Sys25\RnBase\Backend\Form\Element\InputText;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Backend\Template\Override\DocumentTemplate;
use Sys25\RnBase\Backend\Utility\BackendUtility;
use Sys25\RnBase\Backend\Utility\Icons;
use Sys25\RnBase\Backend\Utility\TCA;
use Sys25\RnBase\Frontend\Request\Parameters;
use Sys25\RnBase\Utility\Link;
use Sys25\RnBase\Utility\Math;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\T3General;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;

/***************************************************************
*  Copyright notice
*
*  (c) 2007-2023 Rene Nitzsche (rene@system25.de)
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
 * im Backend zur Verfügung.
 *
 * Ersetzt tx_rnbase_util_FormTool
 */
class ToolBox
{
    public $form; // TCEform-Instanz

    /**
     * @var IModule
     */
    private $module;

    /**
     * @var DocumentTemplate
     */
    protected $doc;

    public const CSS_CLASS_BTN = 'btn btn-default btn-sm';

    /** some defVals for new record links */
    public const OPTION_DEFVALS = 'defvals';

    public const OPTION_TITLE = 'title';

    public const OPTION_CONFIRM = 'confirm';
    public const OPTION_ICON_NAME = 'icon';
    public const OPTION_HOVER_TEXT = 'hover';
    public const OPTION_HIDE_LABEL = 'hide-label';

    public const OPTION_PARAMS = 'params';
    public const OPTION_CSS_CLASSES = 'class';
    public const OPTION_DATA_ATTR = 'data-attr';

    /**
     * Clipboard object.
     *
     * @var \TYPO3\CMS\Backend\Clipboard\Clipboard
     */
    private $clipObj;
    private $tceStack;
    /** @var LanguageService */
    private $lang;

    /** @var \TYPO3\CMS\Backend\Routing\UriBuilder */
    private $uriBuilder;

    /**
     * @param DocumentTemplate $doc
     * @param IModule $module
     */
    public function init(DocumentTemplate $doc, IModule $module)
    {
        $this->doc = $doc;
        $this->module = $module;
        $this->lang = $GLOBALS['LANG'];

        $this->uriBuilder = tx_rnbase::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);

        // TCEform für das Formular erstellen
        $this->form = tx_rnbase::makeInstance(FormBuilder::class);
        $this->form->initDefaultBEmode();
    }

    /**
     * @return DocumentTemplate the BE template class
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * @return \Sys25\RnBase\Backend\Module\IModule
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Erstellt einen Button zur Bearbeitung eines Datensatzes.
     *
     * @param string $editTable DB-Tabelle des Datensatzes
     * @param int    $editUid   UID des Datensatzes
     * @param array  $options   additional options (title, params)
     *
     * @return string
     */
    public function createEditButton($editTable, $editUid, $options = [])
    {
        $title = isset($options['title']) ? $options['title'] : 'Edit';
        $params = '&edit['.$editTable.']['.$editUid.']=edit';
        if (isset($options['params'])) {
            $params .= $options['params'];
        }
        $name = isset($options['name']) ? $options['params'] : '';

        $jsCode = BackendUtility::editOnClick($params);
        if (isset($options['confirm']) && strlen($options['confirm']) > 0) {
            $jsCode = 'if(confirm('.Strings::quoteJSvalue($options['confirm']).')) {'.$jsCode.'} else {return false;}';
        }

        $btn = '<input type="button" name="'.$name.'" value="'.$title.'" ';
        $btn .= 'onclick="'.htmlspecialchars($jsCode).'"';
        $btn .= '/>';

        return $btn;
    }

    /**
     * Creates a new-record-button.
     *
     * @param string $table
     * @param int    $pid
     * @param array  $options
     *
     * @return string
     *
     * @deprecated use createNewLink()
     */
    public function createNewButton($table, $pid, $options = [])
    {
        $params = '&edit['.$table.']['.$pid.']=new';
        if (isset($options[self::OPTION_PARAMS])) {
            $params .= $options[self::OPTION_PARAMS];
        }
        $params .= $this->buildDefVals($options);
        $title = isset($options[self::OPTION_TITLE]) ? $options[self::OPTION_TITLE] : $GLOBALS['LANG']->getLL('new', 1);
        $name = isset($options['name']) ? $options['params'] : '';

        $jsCode = BackendUtility::editOnClick($params);
        if (isset($options[self::OPTION_CONFIRM]) && strlen($options[self::OPTION_CONFIRM]) > 0) {
            $jsCode = 'if(confirm('.Strings::quoteJSvalue($options[self::OPTION_CONFIRM]).')) {'.$jsCode.'} else {return false;}';
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="'.$class.'"';

        $btn = '<input type="button" name="'.$name.'" value="'.$title.'" '.$class;
        $btn .= ' onclick="'.htmlspecialchars($jsCode, -1).'"';
        $btn .= '/>';

        return $btn;
    }

    /**
     * Creates a Link to show an item in frontend.
     *
     * @param $pid
     * @param $label
     * @param string $urlParams
     * @param array  $options
     *
     * @return string
     */
    public function createShowLink($pid, $label, $urlParams = '', $options = [])
    {
        if ($options['sprite'] ?? false) {
            $label = Icons::getSpriteIcon($options['sprite']);
        }
        $jsCode = BackendUtility::viewOnClick($pid, '', null, '', '', $urlParams);
        $title = '';
        if ($options['hover'] ?? false) {
            $title = ' title="'.$options['hover'].'" ';
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="'.$class.'"';

        return '<a href="#" '.$class.' onclick="'.htmlspecialchars($jsCode).'" '.$title.'>'.$label.'</a>';
    }

    /**
     * @return EnhancedLinkButton
     */
    private function makeLinkButton($uri, $label = '')
    {
        $btn = new EnhancedLinkButton();
        $btn->setHref($uri);
        if ($label) {
            $btn->setTitle($label)
                ->setShowLabelText(true);
        }

        return $btn;
    }

    /**
     * Erstellt einen Link zur Erstellung eines neuen Datensatzes
     * Possible options:
     * - params: some plain url params like "&myparam=4"
     * - title: label for this link
     * - confirm: some confirm message
     * - defvals: an array for defVals like this: ['mytable' => ['myfield' => 'initialvalue', ]]
     * - class: css class for a tag. default is "btn btn-default btn-sm".
     *
     * @param string $table   DB-Tabelle des Datensatzes
     * @param int    $pid     UID der Zielseite
     * @param string $label   Bezeichnung des Links
     * @param array  $options
     *
     * @return string
     */
    public function createNewLink($table, $pid, $label = 'New', $options = [])
    {
        $uri = $this->buildEditUri($table, $pid, 'new', $options);
        $uri .= $this->buildDefVals($options);

        $image = Icons::getSpriteIcon('actions-document-new', ['asIcon' => true]);
        $recordButton = $this->makeLinkButton($uri, $label)
            ->setIcon($image);

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : '';

        if (isset($options[self::OPTION_CONFIRM]) && strlen($options[self::OPTION_CONFIRM]) > 0) {
            $class .= ' t3js-modal-trigger';
            $recordButton->setDataAttributes(['content' => $options[self::OPTION_CONFIRM]]);
            $recordButton->setOverrideCss(false);
        }
        $recordButton->setClasses($class);

        return $recordButton->render();
    }

    /**
     * Erstellt einen Link zur Bearbeitung eines Datensatzes.
     *
     * @param string $editTable DB-Tabelle des Datensatzes
     * @param int    $editUid   UID des Datensatzes
     * @param string $label     Bezeichnung des Links
     * @param array  $options
     *
     * @return string
     */
    public function createEditLink($editTable, $editUid, $label = 'Edit', $options = [])
    {
        $uri = $this->buildEditUri($editTable, $editUid, 'edit', $options);

        $image = Icons::getSpriteIcon('actions-document-open', ['asIcon' => true]);
        $recordButton = $this->makeLinkButton($uri, $label)
            ->setIcon($image);

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : '';
        $recordButton->setClasses($class);

        return $recordButton->render();
    }

    /**
     * @param string $operation new or edit
     */
    private function buildEditUri($table, $pid, $operation, array $options)
    {
        $returnUrl = T3General::getIndpEnv('REQUEST_URI');
        $uri = (string) $this->uriBuilder->buildUriFromRoute(
            'record_edit',
            [
                'id' => $pid,
                'returnUrl' => $returnUrl,
                sprintf('edit[%s][%s]', $table, $pid) => $operation,
            ]
        );
        if (isset($options[self::OPTION_PARAMS])) {
            $uri .= $options[self::OPTION_PARAMS];
        }

        return $uri;
    }

    /**
     * Erstellt einen History-Link
     * Achtung: Benötigt die JS-Funktion jumpExt() in der Seite.
     *
     * @param string $table
     * @param int    $recordUid
     *
     * @return string
     */
    public function createHistoryLink($table, $recordUid, $label = '')
    {
        $moduleUrl = BackendUtility::getModuleUrl('record_history', ['element' => $table.':'.$recordUid]);
        $options = [
            self::OPTION_ICON_NAME => 'actions-document-history-open',
        ];

        $btn = $this->createModuleButton($moduleUrl, $label, $options);

        return $btn->render();
    }

    /**
     * Create a hide/unhide Link.
     *
     * @param string $table
     * @param int    $uid
     * @param bool   $unhide
     * @param array  $options
     */
    public function createHideLink($table, $uid, $unhide = false, $options = [])
    {
        $sEnableColumn = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'];
        // fallback
        $sEnableColumn = ($sEnableColumn) ? $sEnableColumn : 'hidden';
        $label = isset($options['label']) ? $options['label'] : '';

        $options[self::OPTION_ICON_NAME] = $unhide ? 'actions-edit-unhide' : 'actions-edit-hide';

        $options['hover'] = $unhide ? 'Show' : 'Hide UID: '.$uid;

        return $this->createLinkForDataHandlerAction(
            'data['.$table.']['.$uid.']['.$sEnableColumn.']='.($unhide ? 0 : 1),
            $label,
            $options
        );
    }

    /**
     * Erstellt einen Link zur Anzeige von Informationen über einen Datensatz.
     *
     * @param string $editTable DB-Tabelle des Datensatzes
     * @param int    $editUid   UID des Datensatzes
     * @param string $label     Bezeichnung des Links
     */
    public function createInfoLink($editTable, $editUid, $label = 'Info', $options = [])
    {
        if (!TYPO3::isTYPO104OrHigher()) {
            $image = Icons::getSpriteIcon('actions-document-info');
            $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
            $class = ' class="'.$class.'"';
            $label = isset($options['label']) ? $options['label'] : $label;

            return '<a '.$class.' href="#" onclick="top.launchView('."'".$editTable."', ' ".$editUid."'); return false;".'">'.
                $image.$label.'</a>';
        }
        $options[self::OPTION_ICON_NAME] = $options[self::OPTION_ICON_NAME] ?? 'actions-document-info';
        $options[self::OPTION_DATA_ATTR] = [
            'dispatch-action' => 'TYPO3.InfoWindow.showItem',
            'dispatch-args-list' => sprintf('%s,%d', $editTable, $editUid),
        ];
        $btn = $this->createModuleButton('#', $label, $options);

        return $btn->render();
    }

    /**
     * Erstellt einen Link zum Verschieben eines Datensatzes auf eine andere Seite.
     *
     * @param string $editTable  DB-Tabelle des Datensatzes
     * @param int    $recordUid  UID des Datensatzes
     * @param int    $currentPid PID der aktuellen Seite des Datensatzes
     * @param string $label Bezeichnung des Links
     */
    public function createMoveLink($editTable, $recordUid, $currentPid, $label = '')
    {
        $clipObj = $this->initClipboard();
        $isSel = (string) $clipObj->isSelected($editTable, $recordUid);
        $options = [];
        $options[self::OPTION_ICON_NAME] = 'actions-edit-cut'.($isSel ? '-release' : '');
        $tooltip = $isSel ? 'paste' : 'cut';
        $options[self::OPTION_HOVER_TEXT] = $this->getLanguageService()->sl('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:cm.'.$tooltip);

        $uri = $clipObj->selUrlDB($editTable, $recordUid, 0, 'cut' === $isSel, ['returnUrl' => '']);
        $btn = $this->createModuleButton($uri, $label, $options);

        return $btn->render();
    }

    /**
     * @return \TYPO3\CMS\Backend\Clipboard\Clipboard
     */
    private function initClipboard()
    {
        if (!$this->clipObj) {
            $this->clipObj = tx_rnbase::makeInstance(\TYPO3\CMS\Backend\Clipboard\Clipboard::class);
            // Initialize - reads the clipboard content from the user session
            $this->clipObj->initializeClipboard();

            $CB = T3General::_GET('CB');
            $this->clipObj->setCmd($CB ?? []);
            // Clean up pad
            $this->clipObj->cleanCurrent();
            // Save the clipboard content
            $this->clipObj->endClipboard();
        }

        return $this->clipObj;
    }

    /**
     * Erstellt einen Link zum Verschieben eines Datensatzes.
     *
     * @param string $table
     * @param int    $uid
     * @param int    $moveId  die uid des elements vor welches das element aus $uid gesetzt werden soll
     * @param array  $options
     */
    public function createMoveUpLink($table, $uid, $moveId, $options = [])
    {
        return $this->createMoveUpDownLink(
            'cmd['.$table.']['.$uid.'][move]=-'.$moveId.'&prErr=1&uPT=1',
            'actions-move-up',
            'Move up',
            $options
        );
    }

    /**
     * Erstellt einen Link zum Verschieben eines Datensatzes.
     *
     * @param string $table
     * @param int    $uid
     * @param int    $moveId  die uid des elements nach welchem das element aus $uid gesetzt werden soll
     * @param array  $options
     */
    public function createMoveDownLink($table, $uid, $moveId, $options = [])
    {
        return $this->createMoveUpDownLink(
            'cmd['.$table.']['.$uid.'][move]=-'.$moveId,
            'actions-move-down',
            'Move down',
            $options
        );
    }

    private function createMoveUpDownLink($cmd, $iconName, $defaultLabel, array $options)
    {
        $label = isset($options['label']) ? $options['label'] : $defaultLabel;
        if (isset($options['title']) && !isset($options[self::OPTION_HOVER_TEXT])) {
            $options[self::OPTION_HOVER_TEXT] = $options['title'];
        }

        $options[self::OPTION_ICON_NAME] = $iconName;

        return $this->createLinkForDataHandlerAction($cmd, $label, $options);
    }

    /**
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::linkThisScript
     *
     * @param bool  $encode
     * @param array $options possible key "params" with array of url params
     *
     * @return string
     */
    protected function getLinkThisScript($encode = true, array $options = [])
    {
        $params = [
            'CB' => '', 'SET' => '', 'cmd' => '', 'popViewId' => '',
        ];
        if (isset($options['params']) && is_array($options['params'])) {
            $params = array_merge($params, $options['params']);
        }
        if (!TYPO3::isTYPO121OrHigher()) {
            $location = Link::linkThisScript($params);
        } else {
            if (!isset($params['id'])) {
                $params['id'] = $this->module->getPid();
            }
            $location = $this->buildScriptURI($params);
        }

        if ($encode) {
            $location = str_replace('%20', '', rawurlencode($location));
        }

        return $location;
    }

    /**
     * Erstellt einen Link zum Löschen eines Datensatzes.
     *
     * @param string $table
     * @param int    $iUid
     * @param string $sLabel
     * @param array  $options
     */
    public function createDeleteLink($table, $uid, $label = 'Remove', $options = [])
    {
        $options[self::OPTION_HOVER_TEXT] = 'Delete UID: '.$uid;
        $options[self::OPTION_ICON_NAME] = 'actions-delete';

        return $this->createLinkForDataHandlerAction(
            'cmd['.$table.']['.$uid.'][delete]=1',
            $label,
            $options
        );
    }

    /**
     * Fügt den JS Code für eine Confirm-Meldung hinzu, wenn in den Options gesetzt.
     *
     * @param string $jsCode
     * @param array  $options
     */
    protected function getConfirmCode($jsCode, $options)
    {
        if (isset($options['confirm']) && strlen($options['confirm']) > 0) {
            return 'if(confirm('.Strings::quoteJSvalue($options['confirm']).')) {'.$jsCode.'} else {return false;}';
        }

        return $jsCode;
    }

    /**
     * Build defvals for URI from link options array.
     *
     * @param array $options
     *
     * @return string
     */
    protected function buildDefVals(array $options)
    {
        $params = '';
        if (isset($options['defvals']) && is_array($options['defvals'])) {
            $defParams = [];
            foreach ($options['defvals'] as $tableName => $fields) {
                foreach ($fields as $fName => $fValue) {
                    $defParams[] = sprintf('&defVals[%s][%s]=%s', rawurlencode($tableName), rawurlencode($fName), rawurlencode($fValue));
                }
            }
            $params .= implode('', $defParams);
        }

        return $params;
    }

    public function createHidden($name, $value)
    {
        return '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
    }

    /**
     * @param string $onclick deprecated wird zukünftig wegen CSP nicht mehr unterstützt
     */
    public function createRadio($name, $value, $checked = false, $onclick = '')
    {
        return '<input type="radio" class="rnbase-checkbox" name="'.$name.'" value="'.htmlspecialchars($value).'" '.($checked ? 'checked="checked"' : '').(strlen($onclick) ? ' onclick="'.$onclick.'"' : '').' />';
    }

    /**
     * @param string $onclick deprecated wird zukünftig wegen CSP nicht mehr unterstützt
     */
    public function createCheckbox($name, $value, $checked = false, $onclick = '')
    {
        return '<input type="checkbox" class="rnbase-checkbox" name="'.$name.'" value="'.htmlspecialchars($value).'" '.($checked ? 'checked="checked"' : '').(strlen($onclick) ? ' onclick="'.$onclick.'"' : '').' />';
    }

    /**
     * @deprecated alias for createModuleLink()
     * @see createModuleLink()
     */
    public function createLink($paramStr, $pid, $label, array $options = [])
    {
        $paramsArr = explode('&', $paramStr);
        $params = [];
        foreach ($paramsArr as $param) {
            list($key, $value) = explode('=', $param);
            $params[$key] = $value;
        }

        return $this->createModuleLink($params, $pid, $label, $options);
    }

    /**
     * Create a link to current module script with additional parameters.
     *
     * possible options:
     * - "icon": name of link icon
     * - "hover": - possible content for title attribute
     * - "class": - custom CSS class for link
     *
     * Statt einem Linktext kann auch ein Icon ausgegeben werden. Dazu muss in den
     * $options "icon" und optional
     * "size" (siehe TYPO3\CMS\Core\Imaging\Icon) gesetzt werden. Ab TYPO3 7.x muss
     * der Icon Name in der IconRegistry vorhanden sein. Vorher muss er in
     * $GLOBALS['BACK_PATH'] . 'gfx/' liegen.
     *
     * @param array  $params  additional url parameters for current script
     * @param int    $pid     PID of current page
     * @param string $label
     * @param array  $options
     *
     * @return string
     */
    public function createModuleLink(array $params, $pid, $label, array $options = [])
    {
        if (!isset($_GET['id']) && !isset($params['id'])) {
            // ensure pid is set even on POST requests.
            $params['id'] = $pid;
        }
        $uri = $this->getLinkThisScript(false, ['params' => $params]);
        $recordButton = $this->createModuleButton($uri, $label, $options);

        return $recordButton->render();
    }

    /**
     * @param string $uri
     * @param mixed $label
     * @param array $options
     * @return EnhancedLinkButton
     */
    private function createModuleButton(string $uri, $label, array $options = [])
    {
        $recordButton = $this->makeLinkButton($uri, $label);

        if (isset($options[self::OPTION_HOVER_TEXT])) {
            $recordButton->setHoverText($options[self::OPTION_HOVER_TEXT]);
        }

        if ($icon = $this->buildIcon($options)) {
            $recordButton->setIcon($icon);
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : '';
        if (isset($options[self::OPTION_CONFIRM]) && strlen($options[self::OPTION_CONFIRM]) > 0) {
            $class .= ' t3js-modal-trigger';
            $recordButton->setDataAttributes(['content' => $options[self::OPTION_CONFIRM]]);
            $recordButton->setOverrideCss(false);
        }
        $recordButton->setClasses($class);

        if (isset($options[self::OPTION_DATA_ATTR])) {
            $recordButton->addDataAttributes((array) $options[self::OPTION_DATA_ATTR]);
        }

        return $recordButton;
    }

    /**
     * @return \TYPO3\CMS\Core\Imaging\Icon|null
     */
    protected function buildIcon(array $options)
    {
        $tag = null;
        // $options['sprite'] für abwärtskompatibilität
        if (isset($options['icon']) || isset($options['sprite'])) {
            $icon = isset($options['icon']) ? $options['icon'] : $options['sprite'];
            // FIXME: label get lost here??
            $tag = Icons::getSpriteIcon($icon, array_merge(['asIcon' => true], $options));
        }

        return $tag;
    }

    /**
     * Submit button for BE form.
     * If you set an icon in options, the output will like this:
     * <button><img></button>.
     *
     * @param string $name
     * @param string $value
     * @param string $confirmMsg
     * @param array  $options
     */
    public function createSubmit($name, $value, $confirmMsg = '', $options = [])
    {
        $value = htmlspecialchars($value);
        $icon = $this->buildIcon($options);
        $icon = $icon ? $icon->render() : '';

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class .= ' rnbase-btn';

        $attributes = [
            'name' => $name,
            'value' => $value,
        ];

        if (strlen($confirmMsg)) {
            $class .= ' t3js-modal-trigger';
            $attributes['data-content'] = $confirmMsg;
            // Der Name des Submit-Buttons liegt nicht mehr im POST. Deshalb ist zusätzliches JS notwendig.
            $this->insertJsToolbox();
        }

        $attributes['class'] = $class;

        $attributesString = T3General::implodeAttributes($attributes, true);

        $btn = '<button type="submit" '.$attributesString.'>'.$icon.$value.'</button>';

        return $btn;
    }

    /**
     * Erstellt ein Textarea.
     */
    public function createTextArea($name, $value, $cols = '30', $rows = '5', $options = 0)
    {
        $options = is_array($options) ? $options : [];
        $onChangeStr = ($options['onchange'] ?? false) ? ' onchange=" '.$options['onchange'].'" ' : '';

        return '<textarea name="'.$name.'" style="width:288px;" class="formField1"'.$onChangeStr.
            ' cols="'.$cols.'" rows="'.$rows.'" wrap="virtual">'.$value.'</textarea>';
    }

    /**
     * Erstellt ein einfaches Textfield.
     */
    public function createTxtInput($name, $value, $width, $options = [])
    {
        $class = array_key_exists('class', $options) ? ' class="'.$options['class'].'"' : '';
        $onChange = array_key_exists('onchange', $options) ? ' onchange="'.$options['onchange'].'"' : '';
        $ret = '<input type="text" name="'.$name.'"'.$this->doc->formWidth($width).
            $onChange.
            $class.
            ' value="'.htmlspecialchars($value).'" />';

        return $ret;
    }

    /**
     * Erstellt ein Eingabefeld für Integers.
     */
    public function createIntInput($name, $value, $width, $maxlength = 10)
    {
        /* @var $inputField InputText */
        $inputField = tx_rnbase::makeInstance(InputText::class, $this->getTCEForm()->getNodeFactory(), []);
        $out = $inputField->renderHtml($name, $value, [
            'width' => $width,
            'maxlength' => $maxlength,
            'eval' => 'int',
        ]);

        return $out;
    }

    /**
     * Erstellt ein Eingabefeld für DateTime.
     */
    public function createDateInput($name, $value, array $options = [])
    {
        // Take care of current time zone. Thanks to Thomas Maroschik!
        if (Math::isInteger($value) && !TYPO3::isTYPO121OrHigher()) {
            $value += date('Z', $value);
        }
        $this->initializeJavaScriptFormEngine();
        $dateElementClass = TYPO3::isTYPO121OrHigher() ?
            \TYPO3\CMS\Backend\Form\Element\DatetimeElement::class :
            \TYPO3\CMS\Backend\Form\Element\InputDateTimeElement::class;

        // [itemFormElName] => data[tx_cfcleague_games][4][status]
        // [itemFormElID] => data_tx_cfcleague_games_4_status

        $renderedElement = tx_rnbase::makeInstance(
            $dateElementClass,
            $this->getTCEForm()->getNodeFactory(),
            [
                'fieldName' => $name,
                'tableName' => '',
                'databaseRow' => ['uid' => 0],
                'processedTca' => ['columns' => [$name => ['config' => ['type' => 'text']]]],
                'parameterArray' => [
                    'itemFormElValue' => $value,
                    'itemFormElName' => $name,
                    'itemFormElID' => $name,
                    'fieldConf' => [
                        'config' => [
                            'width' => 20,
                            'maxlength' => 20,
                            'eval' => 'datetime',
                        ],
                    ],
                ],
            ]
        )->render();

        if ($renderedElement['requireJsModules'] ?? null) {
            $pageRenderer = $this->getDoc()->getPageRenderer();
            foreach ($renderedElement['requireJsModules'] as $moduleName => $callbacks) {
                if ($callbacks instanceof JavaScriptModuleInstruction) {
                    $pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction($callbacks);
                    continue;
                }

                if (!is_array($callbacks)) {
                    $callbacks = [$callbacks];
                }
                foreach ($callbacks as $callback) {
                    $pageRenderer->loadRequireJsModule($moduleName, $callback);
                }
            }
        } elseif ($renderedElement['javaScriptModules'] ?? null) {
            $pageRenderer = $this->getDoc()->getPageRenderer();
            foreach ($renderedElement['javaScriptModules'] as $moduleName) {
                /* @var \TYPO3\CMS\Core\Page\JavaScriptModuleInstruction $moduleName */
                $this->insertJsModule($moduleName->getName());
            }
        }

        if (isset($options[self::OPTION_HIDE_LABEL])) {
            $renderedElement['html'] = preg_replace('/<code.*<\/code>/', '', $renderedElement['html']);
        }

        return $renderedElement['html'];
    }

    /**
     * Inspired by TYPO3\CMS\Setup\Controller\SetupModuleController::__construct() and
     * TYPO3\CMS\Backend\Form\FormResultCompiler::JSbottom().
     */
    protected function initializeJavaScriptFormEngine()
    {
        $moduleUrl = Strings::quoteJSvalue($this->buildScriptURI([]));
        $usDateFormat = 0;
        if (!TYPO3::isTYPO121OrHigher()) {
            $usDateFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ? '1' : '0';
            $initializeFormEngineCallback = 'function(FormEngine) {
                FormEngine.initialize(
                    '.$moduleUrl.','.$usDateFormat.'
                );
            }';

            $this->getDoc()->getPageRenderer()->loadRequireJsModule(
                'TYPO3/CMS/Backend/FormEngine',
                $initializeFormEngineCallback
            );
        }
        $this->getDoc()->getPageRenderer()->addInlineSetting('FormEngine', 'formName', 'editform');
    }

    /**
     * Erstellt eine Selectbox mit festen Werten in der TCA.
     * Die Labels werden in der richtigen Sprache angezeigt.
     */
    public function createSelectSingle($name, $value, $table, $column, $options = 0)
    {
        global $TCA, $LANG;
        $options = is_array($options) ? $options : [];

        $out = '<select  name="'.$name.'" class="select" ';
        if (isset($options['onchange'])) {
            $out .= 'onChange="'.$options['onchange'].'" ';
        }
        $out .= '>';

        // Die TCA laden
        TCA::loadTCA($table);

        // Die Options ermitteln
        foreach ($TCA[$table]['columns'][$column]['config']['items'] as $item) {
            $tcaLabel = $item['label'] ?? $item[0];
            $tcaVal = $item['value'] ?? $item[1];
            $sel = '';
            if ($value == $tcaVal) {
                $sel = 'selected="selected"';
            }
            $out .= '<option value="'.$tcaVal.'" '.$sel.'>'.$LANG->sL($tcaLabel).'</option>';
        }
        $out .= '
            </select>
        ';

        return $out;
    }

    /**
     * @deprecated use createSelectByArray instead
     */
    public function createSelectSingleByArray($name, $value, $arr, $options = 0)
    {
        return $this->createSelectByArray($name, $value, $arr, $options);
    }

    private function insertJsToolbox()
    {
        $this->insertJsModule('@sys25/rn_base/toolbox.js');
    }

    /**
     * Erstellt eine Select-Box aus dem übergebenen Array.
     * in den Options kann mit dem key reload angegeben werden,
     * ob das Formular bei Änderungen direkt abgeschickt werden soll.
     * mit dem key onchange kann ein eigene onchange Funktion
     * hinterlegt werden.
     * außerdem kann in den options mit multiple angegeben werden
     * ob es eine mehrfach Auswahl geben soll.
     *
     * @param string $name
     * @param string $currentValues comma separated
     * @param array  $selectOptions
     * @param array  $options
     *
     * @return string
     */
    public function createSelectByArray($name, $currentValues, array $selectOptions, $options = [])
    {
        $options = is_array($options) ? $options : [];

        $attrArr = [];
        $onChangeStr = '';
        if (!empty($options['reload'])) {
            if (TYPO3::isTYPO121OrHigher()) {
                $attrArr[] = 'data-global-event="change" data-action-submit="$form"';
            } else {
                // TODO: fix for older versions
                $onChangeStr = ' this.form.submit(); ';
            }
            $this->insertJsToolbox();
        }

        if (isset($options['onchange'])) {
            $onChangeStr .= $options['onchange'];
        }
        if ($onChangeStr) {
            $onChangeStr = 'onchange="'.$onChangeStr.'"';
            $attrArr[] = $onChangeStr;
        }

        $multiple = !empty($options['multiple']) ? 'multiple="multiple"' : '';
        if ($multiple) {
            $attrArr[] = $multiple;
        }
        $name .= !empty($options['multiple']) ? '[]' : '';

        $size = !empty($options['size']) ? 'size="'.$options['size'].'"' : '';
        if ($size) {
            $attrArr[] = $size;
        }
        $classes = $options[self::OPTION_CSS_CLASSES] ?? '';

        $attr = implode(' ', $attrArr);

        $out = sprintf('<select name="%s" class="%s"%s>',
            $name,
            trim('select '.$classes),
            $attr ? ' '.$attr : ''
        );

        $currentValues = Strings::trimExplode(',', $currentValues);

        // Die Options ermitteln
        foreach ($selectOptions as $value => $label) {
            $selected = '';
            if (in_array($value, $currentValues)) {
                $selected = 'selected="selected"';
            }
            $out .= '<option value="'.$value.'" '.$selected.'>'.$label.'</option>';
        }
        $out .= '</select>';

        return $out;
    }

    /**
     * Liefert einen Sortierungslink für das gegebene Feld.
     *
     * @param string $sSortField
     *
     * @return string
     */
    public function createSortLink($sSortField, $sLabel)
    {
        // das ist aktuell gesetzt
        $sCurrentSortField = Parameters::getPostOrGetParameter('sortField');
        $sCurrentSortRev = Parameters::getPostOrGetParameter('sortRev');
        // wir verweisen immer auf die aktuelle Seite
        // es kann aber schon ein sort parameter gesetzt sein
        // weshalb wir alte entfernen
        $sUrl = preg_replace('/&sortField=.*&sortRev=[^&]*/', '', Misc::getIndpEnv('TYPO3_REQUEST_URL'));

        // sort richtung rausfinden
        // beim initialen Aufruf (spalte noch nicht geklickt) wird immer aufsteigend sortiert
        if ($sCurrentSortField != $sSortField) {
            $sSortRev = 'asc';
        } else {// sonst das gegenteil vom aktuellen
            $sSortRev = ('desc' == $sCurrentSortRev) ? 'asc' : 'desc';
        }

        // prüfen ob Parameter mit ? oder & angehängt werden müssen
        $sAddParamsWith = (strstr($sUrl, '?')) ? '&' : '?';
        // jetzt setzen wir den aktuellen Sort parameter zusammen
        $sSortUrl = $sUrl.$sAddParamsWith.'sortField='.$sSortField.'&sortRev='.$sSortRev;
        // noch den Pfeil für die aktuelle Sortierungsrichtung ggf. einblenden
        $sSortArrow = '';
        if ($sCurrentSortField == $sSortField) {
            $sSortArrow = Icons::getSpriteIcon(
                'asc' == $sSortRev ? 'actions-move-up' : 'actions-move-down'
            );
        }

        return '<a href="'.htmlspecialchars($sSortUrl).'">'.$sLabel.$sSortArrow.'</a>';
    }

    public function addTCEfield2Stack($table, $row, $fieldname, $pre = '', $post = '')
    {
        $this->tceStack[] = $pre.$this->form->getSoloField($table, $row, $fieldname).$post;
    }

    /**
     * @return FormBuilder
     */
    public function getTCEForm()
    {
        return $this->form;
    }

    public function getTCEfields($formname)
    {
        $ret = [];
        $ret[] = $this->form->printNeededJSFunctions_top();
        $ret[] = implode('', $this->tceStack);
        $ret[] = $this->form->printNeededJSFunctions();
        $ret[] = $this->form->JSbottom($formname);

        return $ret;
    }

    /**
     * @param int    $pid      ID der aktuellen Seite
     * @param string $location module url or empty
     */
    public function getJSCode($pid, $location = '')
    {
        return $this->doc->wrapScriptTags($this->getBaseJavaScriptCode($location));
    }

    /**
     * Add inline JS-Code for functions jumpToUrl(), jumpExt(), jumpSelf(), setHighlight()
     * and global Vars T3_RETURN_URL and T3_THIS_LOCATION to PageRenderer.
     *
     * @param string $location
     *
     * @deprecated should not be used anymore
     */
    public function addBaseInlineJSCode($location = '')
    {
        // the jumpUrl method is no longer global available since TYPO3 8.7
        // furthermore we need the JS variable T3_THIS_LOCATION because it is used
        // as redirect in getLinkToDataHandlerAction when -1 is passed
        $this->getDoc()->getPageRenderer()->addJsInlineCode(
            'rnBaseMethods',
            $this->getBaseJavaScriptCode($location)
        );
    }

    /**
     * Provide JS-Code for functions jumpToUrl(), jumpExt(), jumpSelf(), setHighlight()
     * and global Vars T3_RETURN_URL and T3_THIS_LOCATION.
     *
     * @param string $location module url or empty
     *
     * @return string
     *
     * @deprecated should not be used anymore
     */
    private function getBaseJavaScriptCode($location = '')
    {
        $location = $location ? $location : $this->getLinkThisScript(false);

        $javaScriptCode = '
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
            var T3_RETURN_URL = '.
                Strings::quoteJSvalue(
                    str_replace(
                        '%20',
                        '',
                        rawurlencode(
                            Parameters::getPostOrGetParameter('returnUrl')
                        )
                    )
                ).';
            var T3_THIS_LOCATION='.
                Strings::quoteJSvalue(str_replace('%20', '', rawurlencode($location)));

        return $javaScriptCode;
    }

    /**
     * Zeigt ein TabMenu.
     *
     * @param int    $pid
     * @param string $name
     * @param array  $entries
     *
     * @return array with keys 'menu' and 'value'
     */
    public function showTabMenu($pid, $name, $modName, $entries)
    {
        $MENU = [
            $name => $entries,
        ];
        $SETTINGS = BackendUtility::getModuleData(
            $MENU,
            T3General::_GP('SET'),
            $modName
        );
        $menuItems = [];
        foreach ($entries as $key => $value) {
            if (0 === strcmp($value, '')) {
                // skip empty entries!
                continue;
            }
            $menuItems[] = [
                'isActive' => $SETTINGS[$name] == $key,
                'label' => $value,
                'url' => $this->buildScriptURI(['id' => $pid, 'SET['.$name.']' => $key]),
            ];
        }

        // In TYPO3 6 the getTabMenuRaw produces a division by zero error if there are no entries.
        if (!empty($menuItems)) {
            $out = '<div class="typo3-dyntabmenu-tabs">'.$this->getDoc()->getTabMenuRaw($menuItems).'</div>';
            // durch den Kommentar <!-- Tab menu --> wird das Menü 2-mal eingefügt vor TYPO3 7.6
            // also entfernen wir den Kommentar
            $out = str_replace('<!-- Tab menu -->', '', $out);
        } else {
            $out = '';
        }

        $ret = [
            'menu' => $out,
            'value' => $SETTINGS[$name],
        ];

        return $ret;
    }

    protected function buildScriptURI($urlParams)
    {
        // In dem Fall die URI über den DISPATCH-Modus bauen
        $routeIdent = TYPO3::isTYPO121OrHigher() ? $this->getModule()->getRouteIdentifier() : $this->getModule()->getName();

        return BackendUtility::getModuleUrl($routeIdent, $urlParams);
    }

    /**
     * Show function menu.
     *
     * @param int    $pid
     * @param string $name      name of menu
     * @param string $modName   name of be module
     * @param array  $entries   menu entries
     * @param string $script    The script to send the &id to, if empty it's automatically found
     * @param string $addParams additional parameters to pass to the script
     *
     * @return array with keys 'menu' and 'value'
     */
    public static function showMenu($pid, $name, $modName, $entries, $script = '', $addparams = '')
    {
        $MENU = [
            $name => $entries,
        ];
        $SETTINGS = BackendUtility::getModuleData(
            $MENU,
            Parameters::getPostOrGetParameter('SET'),
            $modName
        );

        $ret = [];
        if (is_array($MENU[$name]) && 1 == count($MENU[$name])) {
            $ret['menu'] = self::buildDummyMenu('SET['.$name.']', $MENU[$name]);
        } else {
            $funcMenu = 'getDropdownMenu';
            $ret['menu'] = BackendUtility::$funcMenu(
                $pid,
                'SET['.$name.']',
                $SETTINGS[$name],
                $MENU[$name],
                $script,
                $addparams
            );
        }
        $ret['value'] = $SETTINGS[$name];

        return $ret;
    }

    private static function buildDummyMenu($elementName, $menuItems)
    {
        // Ab T3 6.2 wird bei einem Menu-Eintrag keine Selectbox mehr erzeugt.
        // Also sieht man nicht, wo man sich befindet. Somit wird eine Dummy-Box
        // benötigt.
        $options = [];

        foreach ($menuItems as $value => $label) {
            $options[] = sprintf(
                '<option value="%1$s" selected="selected">%2$s</option>',
                htmlspecialchars($value),
                htmlspecialchars($label, ENT_COMPAT, 'UTF-8', false)
            );
        }

        return '<!-- Function Menu of module -->'.sprintf(
            '<select class="form-control" name="%1$s" >%2$s</select>',
            $elementName,
            implode(LF, $options)
        );
    }

    /**
     * Submit-Button like this: name="mykey[123]" value="label"
     * You will get 123 as long as no other submit changes this value.
     *
     * @param string $key
     * @param string $modName
     *
     * @return mixed
     */
    public function getStoredRequestData($key, $changed = [], $modName = 'DEFRNBASEMOD')
    {
        $data = Parameters::getPostOrGetParameter($key);
        if (is_array($data)) {
            reset($data);
            $itemid = key($data);
            $changed[$key] = $itemid;
        }
        $ret = BackendUtility::getModuleData([$key => ''], $changed, $modName);

        return $ret[$key] ?? null;
    }

    /**
     * Load a fullfilled TCE data array for a database record.
     *
     * @param string $table
     * @param int    $theUid
     * @param bool   $isNew
     */
    public function getTCEFormArray($table, $theUid, $isNew = false)
    {
        /** @var TYPO3\CMS\Backend\Form\DataPreprocessor $trData */
        $trData = tx_rnbase::makeInstance('TYPO3\\CMS\\Backend\\Form\\DataPreprocessor');
        $trData->addRawData = true;
        $trData->fetchRecord($table, $theUid, $isNew ? 'new' : '');    // 'new'
        reset($trData->regTableItems_data);

        return $trData->regTableItems_data;
    }

    /**
     * $this->createLinkForDataHandlerAction('cmd[pages][123][delete]=1').
     *
     * @param string $actionParameters
     * @param string $label
     * @param array  $options
     *
     * @return string
     */
    public function createLinkForDataHandlerAction($actionParameters, $label, array $options = [])
    {
        if (isset($options['sprite'])) {
            $options[self::OPTION_ICON_NAME] = $options['sprite'];
        }
        $redirect = TYPO3::isTYPO115OrHigher() ? null : -1;
        $uri = $this->buildDataHandlerUri($actionParameters, $redirect);

        $btn = $this->createModuleButton($uri, $label, $options);

        return $btn->render();
    }

    /**
     * Bietet eine Möglichkeit JS-Module sowohl per AMD als auch ES6 (ab TYPO3 12) zu laden.
     *
     * ES6-Modul:  @vendor/ext_key/some-es6-module.js
     * wird konvertiert in
     * AMD-Module: 'TYPO3/CMS/ExtKey/SomeEs6Module'
     *
     * Es die entsprechenden Dateien müssen natürlich in der jeweiligen Extension bereitgestellt werden.
     *
     * @param string $es6Module Name des ES6-Moduls
     * @param string $callBackFunction optionaler Startup-Code for AMD-Variante. Ab T3 12 nicht mehr verwendet.
     */
    public function insertJsModule(string $es6Module, $callBackFunction = null)
    {
        $pageRenderer = $this->doc->getPageRenderer();
        if (TYPO3::isTYPO121OrHigher()) {
            $pageRenderer->loadJavaScriptModule($es6Module);
        } else {
            list($vendor, $extKey, $jsModule) = explode('/', $es6Module, 3);
            $extKey = Strings::underscoredToUpperCamelCase($extKey);
            $jsModule = Strings::dashedToUpperCamelCase($jsModule);

            $pageRenderer->loadRequireJsModule(
                sprintf('TYPO3/CMS/%s/%s', $extKey, substr($jsModule, 0, -3)),
                $callBackFunction
            );
        }
    }

    protected function buildDataHandlerUri(string $params, $redirect)
    {
        return BackendUtility::getLinkToDataHandlerAction('&'.$params, $redirect);
    }

    /**
     * @return TYPO3\CMS\Core\Localization\LanguageService|TYPO3\CMS\Lang\LanguageService
     */
    public function getLanguageService()
    {
        return $this->lang;
    }
}
