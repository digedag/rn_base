<?php

namespace Sys25\RnBase\Backend\Form;

use Sys25\RnBase\Backend\Form\Element\InputText;
use Sys25\RnBase\Backend\Template\Override\DocumentTemplate;
use Sys25\RnBase\Backend\Utility\BackendUtility;
use Sys25\RnBase\Backend\Utility\Icons;
use Sys25\RnBase\Backend\Utility\TCA;
use Sys25\RnBase\Frontend\Request\Parameters;
use Sys25\RnBase\Utility\Link;
use Sys25\RnBase\Utility\Math;
use Sys25\RnBase\Utility\Misc;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\T3General as T3GeneralAlias;
use Sys25\RnBase\Utility\TYPO3;
use tx_rnbase;

/***************************************************************
*  Copyright notice
*
*  (c) 2007-2022 Rene Nitzsche (rene@system25.de)
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

    protected $module;

    protected $doc;

    public const CSS_CLASS_BTN = 'btn btn-default btn-sm';

    /** some defVals for new record links */
    public const OPTION_DEFVALS = 'defvals';

    public const OPTION_TITLE = 'title';

    public const OPTION_CONFIRM = 'confirm';

    public const OPTION_PARAMS = 'params';

    /**
     * Clipboard object.
     *
     * @var \TYPO3\CMS\Backend\Clipboard\Clipboard
     */
    private $clipObj;

    /**
     * @param DocumentTemplate $doc
     * @param \tx_rnbase_mod_IModule $module
     */
    public function init($doc, $module)
    {
        global $BACK_PATH;
        $this->doc = $doc;
        $this->module = $module;

        // TCEform für das Formular erstellen
        $this->form = tx_rnbase::makeInstance(FormBuilder::class);
        $this->form->initDefaultBEmode();
        $this->form->backPath = $BACK_PATH;
    }

    /**
     * @return DocumentTemplate the BE template class
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * @return \tx_rnbase_mod_IModule
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

        $jsCode = BackendUtility::editOnClick($params, $GLOBALS['BACK_PATH']);
        if (isset($options['confirm']) && strlen($options['confirm']) > 0) {
            $jsCode = 'if(confirm('.Strings::quoteJSvalue($options['confirm']).')) {'.$jsCode.'} else {return false;}';
        }

        $btn = '<input type="button" name="'.$name.'" value="'.$title.'" ';
        $btn .= 'onclick="'.htmlspecialchars($jsCode).'"';
        $btn .= '/>';

        return $btn;
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
        $params = '&edit['.$editTable.']['.$editUid.']=edit';
        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="'.$class.'"';
        $label = isset($options['label']) ? $options['label'] : $label;
        $onClick = htmlspecialchars(BackendUtility::editOnClick($params));

        return '<a href="#" '.$class.' onclick="'.$onClick.'" title="Edit UID: '.$editUid.'">'
                .Icons::getSpriteIcon('actions-page-open')
                .$label
                .'</a>';
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
        $this->addBaseInlineJSCode();
        $image = Icons::getSpriteIcon('actions-document-history-open');
        $moduleUrl = BackendUtility::getModuleUrl('record_history', ['element' => $table.':'.$recordUid]);
        $onClick = 'return jumpExt('.Strings::quoteJSvalue($moduleUrl).',\'#latest\');';

        return '<a class="btn btn-default" href="#" onclick="'.htmlspecialchars($onClick).'" title="'
            .htmlspecialchars($GLOBALS['LANG']->getLL('history')).'">'
            .$image.'</a>';
    }

    /**
     * Creates a new-record-button.
     *
     * @param string $table
     * @param int    $pid
     * @param array  $options
     *
     * @return string
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

        $jsCode = BackendUtility::editOnClick($params, $GLOBALS['BACK_PATH']);
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
        if ($options['sprite']) {
            $label = Icons::getSpriteIcon($options['sprite']);
        }
        $jsCode = BackendUtility::viewOnClick($pid, '', '', '', '', $urlParams);
        $title = '';
        if ($options['hover']) {
            $title = ' title="'.$options['hover'].'" ';
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="'.$class.'"';

        return '<a href="#" '.$class.' onclick="'.htmlspecialchars($jsCode).'" '.$title.'>'.$label.'</a>';
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
        $params = '&edit['.$table.']['.$pid.']=new';
        if (isset($options[self::OPTION_PARAMS])) {
            $params .= $options[self::OPTION_PARAMS];
        }
        $params .= $this->buildDefVals($options);
        $title = isset($options[self::OPTION_TITLE]) ? $options[self::OPTION_TITLE] : $GLOBALS['LANG']->getLL('new', 1);

        $jsCode = BackendUtility::editOnClick($params, $GLOBALS['BACK_PATH']);
        if (isset($options[self::OPTION_CONFIRM]) && strlen($options[self::OPTION_CONFIRM]) > 0) {
            $jsCode = 'if(confirm('.Strings::quoteJSvalue($options[self::OPTION_CONFIRM]).')) {'.$jsCode.'} else {return false;}';
        }
        $image = Icons::getSpriteIcon('actions-document-new');

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="'.$class.'"';

        return '<a href="#" title="'.$title.'" '.$class.' onclick="'.htmlspecialchars($jsCode, -1).'">'.
                $image.$label.'</a>';
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

        $image = Icons::getSpriteIcon(
            $unhide ? 'actions-edit-unhide' : 'actions-edit-hide'
        );

        $options['hover'] = $unhide ? 'Show' : 'Hide'.' UID: '.$uid;

        return $this->createLinkForDataHandlerAction(
            'data['.$table.']['.$uid.']['.$sEnableColumn.']='.($unhide ? 0 : 1),
            $image.$label,
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
        $image = Icons::getSpriteIcon('actions-document-info');
        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="'.$class.'"';
        $label = isset($options['label']) ? $options['label'] : $label;

        return '<a '.$class.' href="#" onclick="top.launchView('."'".$editTable."', ' ".$editUid."'); return false;".'">'.
            $image.$label.'</a>';
    }

    /**
     * Erstellt einen Link zum Verschieben eines Datensatzes auf eine andere Seite.
     *
     * @param string $editTable  DB-Tabelle des Datensatzes
     * @param int    $recordUid  UID des Datensatzes
     * @param int    $currentPid PID der aktuellen Seite des Datensatzes
     * @param string $label      Bezeichnung des Links
     */
    public function createMoveLink($editTable, $recordUid, $currentPid, $label = 'Move')
    {
        $this->initClipboard();
        $this->addBaseInlineJSCode();
        $isSel = (string) $this->clipObj->isSelected($editTable, $recordUid);
        $image = Icons::getSpriteIcon('actions-edit-cut'.($isSel ? '-release' : ''));

        return '<a class="btn btn-default" href="#" onclick="'
            .htmlspecialchars('return jumpSelf('.
                Strings::quoteJSvalue(
                    $this->clipObj->selUrlDB($editTable, $recordUid, 0, 'cut' === $isSel, ['returnUrl' => ''])
                ).');')
            .'" title="'.htmlspecialchars($GLOBALS['LANG']->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:cm.cut')).'">'
                .$image.'</a>';
    }

    private function initClipboard()
    {
        if (!$this->clipObj) {
            $this->clipObj = \tx_rnbase::makeInstance(\TYPO3\CMS\Backend\Clipboard\Clipboard::class);
            // Initialize - reads the clipboard content from the user session
            $this->clipObj->initializeClipboard();

            $CB = T3GeneralAlias::_GET('CB');
            $this->clipObj->setCmd($CB);
            // Clean up pad
            $this->clipObj->cleanCurrent();
            // Save the clipboard content
            $this->clipObj->endClipboard();
        }
    }

    /**
     * Erstellt einen Link zum Verschieben eines Datensatzes.
     *
     * @param string $table
     * @param int    $uid
     * @param int    $moveId  die uid des elements vor welches das element aus $uid gesetzt werden soll
     * @param array  $options
     *
     * @TODO use $this->createLinkForDataHandlerAction
     */
    public function createMoveUpLink($table, $uid, $moveId, $options = [])
    {
        $jsCode = $this->getJavaScriptForLinkToDataHandlerAction('cmd['.$table.']['.$uid.'][move]=-'.$moveId.'&prErr=1&uPT=1', $options);
        $label = isset($options['label']) ? $options['label'] : 'Move up';
        $title = isset($options['title']) ? $options['title'] : $label;

        $image = Icons::getSpriteIcon('actions-move-up');

        return sprintf(
            '<a onclick="%1$s" href="#">%2$s%3$s</a>',
            $jsCode,
            $image,
            $label
        );
    }

    /**
     * Erstellt einen Link zum Verschieben eines Datensatzes.
     *
     * @param string $table
     * @param int    $uid
     * @param int    $moveId  die uid des elements nach welchem das element aus $uid gesetzt werden soll
     * @param array  $options
     *
     * @TODO use $this->createLinkForDataHandlerAction
     */
    public function createMoveDownLink($table, $uid, $moveId, $options = [])
    {
        $jsCode = $this->getJavaScriptForLinkToDataHandlerAction('cmd['.$table.']['.$uid.'][move]=-'.$moveId, $options);
        $label = isset($options['label']) ? $options['label'] : 'Move up';
        $title = isset($options['title']) ? $options['title'] : $label;

        $image = Icons::getSpriteIcon('actions-move-down');

        return sprintf(
            '<a onclick="%1$s" href="#">%2$s%3$s</a>',
            $jsCode,
            $image,
            $label
        );
    }

    /**
     * Creates js code with command for TCE datahandler and redirect to current script.
     * Simple example to delete a page record:
     * $this->getJavaScriptForLinkToDataHandlerAction('cmd[pages][123][delete]=1').
     *
     * @param string $urlParameters command for datahandler
     * @param array  $options
     *
     * @return string
     */
    protected function getJavaScriptForLinkToDataHandlerAction($urlParameters, array $options = [])
    {
        $jumpToUrl = BackendUtility::getLinkToDataHandlerAction('&'.$urlParameters, -1);
        // the jumpUrl method is no longer global available since TYPO3 8.7
        // furthermore we need the JS variable T3_THIS_LOCATION because it is used
        // as redirect in getLinkToDataHandlerAction when -1 is passed
        $this->addBaseInlineJSCode();

        return $this->getConfirmCode('return jumpToUrl('.$jumpToUrl.');', $options);
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
        $location = Link::linkThisScript($params);
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
        $image = Icons::getSpriteIcon('actions-delete');
        $options['hover'] = 'Delete UID: '.$uid;

        return $this->createLinkForDataHandlerAction(
            'cmd['.$table.']['.$uid.'][delete]=1',
            $image.$label,
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

    public function createRadio($name, $value, $checked = false, $onclick = '')
    {
        return '<input type="radio" name="'.$name.'" value="'.htmlspecialchars($value).'" '.($checked ? 'checked="checked"' : '').(strlen($onclick) ? ' onclick="'.$onclick.'"' : '').' />';
    }

    public function createCheckbox($name, $value, $checked = false, $onclick = '')
    {
        return '<input type="checkbox" name="'.$name.'" value="'.htmlspecialchars($value).'" '.($checked ? 'checked="checked"' : '').(strlen($onclick) ? ' onclick="'.$onclick.'"' : '').' />';
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
        $label = $this->buildIconTag($options, $label);
        if (!isset($_GET['id']) && !isset($params['id'])) {
            // ensure pid is set even on POST requests.
            $params['id'] = $pid;
        }
        $location = $this->getLinkThisScript(false, ['params' => $params]);

        $jsCode = "window.location.href='".$location."'; return false;";

        $title = '';
        if ($options['hover']) {
            $title = 'title="'.$options['hover'].'"';
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = 'class="'.$class.'"';

        return '<a href="#" '.$class.' onclick="'.htmlspecialchars($jsCode).'" '.$title.'>'.$label.'</a>';
    }

    protected function buildIconTag(array $options, $label = '')
    {
        $tag = $label;
        // $options['sprite'] für abwärtskompatibilität
        if ($options['icon'] || $options['sprite']) {
            $icon = isset($options['icon']) ? $options['icon'] : $options['sprite'];
            // FIXME: label get lost here??
            $tag = Icons::getSpriteIcon($icon, $options);
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
        $icon = $this->buildIconTag($options, '');
        $onClick = '';
        if (strlen($confirmMsg)) {
            $onClick = 'onclick="return confirm('.Strings::quoteJSvalue($confirmMsg).')"';
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="'.$class.'"';

        if ($icon) {
            $btn = '<button type="submit" '.$class.' name="'.$name.'" value="'.htmlspecialchars($value).'">'.
                $icon.'</button>';
        } else {
            $btn = '<input type="submit" '.$class.' name="'.$name.'" value="'.htmlspecialchars($value).'" ';
            $btn .= $onClick;
            $btn .= '/>';
        }

        return $btn;
    }

    /**
     * Erstellt ein Textarea.
     */
    public function createTextArea($name, $value, $cols = '30', $rows = '5', $options = 0)
    {
        $options = is_array($options) ? $options : [];
        $onChangeStr = $options['onchange'] ? ' onchange=" '.$options['onchange'].'" ' : '';

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
     *
     * @todo fix prefilling of field in TYPO3 8.7
     */
    public function createDateInput($name, $value)
    {
        // Take care of current time zone. Thanks to Thomas Maroschik!
        if (Math::isInteger($value)) {
            $value += date('Z', $value);
        }
        $this->initializeJavaScriptFormEngine();
        $dateElementClass = \TYPO3\CMS\Backend\Form\Element\InputDateTimeElement::class;

        return tx_rnbase::makeInstance(
            $dateElementClass,
            $this->getTCEForm()->getNodeFactory(),
            [
                'fieldName' => $name,
                'parameterArray' => [
                    'itemFormElValue' => $value,
                    'itemFormElName' => $name,
                    'fieldConf' => [
                        'config' => [
                            'width' => 20,
                            'maxlength' => 20,
                            'eval' => 'datetime',
                        ],
                    ],
                ],
            ]
        )->render()['html'];
    }

    /**
     * Inspired by TYPO3\CMS\Setup\Controller\SetupModuleController::__construct() and
     * TYPO3\CMS\Backend\Form\FormResultCompiler::JSbottom().
     */
    protected function initializeJavaScriptFormEngine()
    {
        $moduleUrl = Strings::quoteJSvalue(
            BackendUtility::getModuleUrl($this->getModule()->getName())
        );
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
        if ($options['onchange']) {
            $out .= 'onChange="'.$options['onchange'].'" ';
        }
        $out .= '>';

        // Die TCA laden
        TCA::loadTCA($table);

        // Die Options ermitteln
        foreach ($TCA[$table]['columns'][$column]['config']['items'] as $item) {
            $sel = '';
            if ($value == $item[1]) {
                $sel = 'selected="selected"';
            }
            $out .= '<option value="'.$item[1].'" '.$sel.'>'.$LANG->sL($item[0]).'</option>';
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

        $onChangeStr = $options['reload'] ? ' this.form.submit(); ' : '';
        if ($options['onchange']) {
            $onChangeStr .= $options['onchange'];
        }
        if ($onChangeStr) {
            $onChangeStr = ' onchange="'.$onChangeStr.'" ';
        }

        $multiple = $options['multiple'] ? ' multiple="multiple"' : '';
        $name .= $options['multiple'] ? '[]' : '';

        $size = $options['size'] ? ' size="'.$options['size'].'"' : '';

        $out = '<select name="'.$name.'" class="select"'.$onChangeStr.$multiple.$size.'>';

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
     */
    protected function getBaseJavaScriptCode($location = '')
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
            T3GeneralAlias::_GP('SET'),
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
                // jumpUrl ist ab TYPO3 6.2 nicht mehr nötig
                // @TODO jumpUrl entfernen wenn kein Support mehr für 4.5
                'url' => '#',
                'addParams' => 'onclick="jumpToUrl(\''.
                                $this->buildScriptURI(['id' => $pid, 'SET['.$name.']' => $key]).
                                '\',this);"',
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
        if (!BackendUtility::isDispatchMode()) {
            return 'index.php?'.http_build_query($urlParams);
//            'index.php?&amp;id='.$pid.'&amp;SET['.$name.']='. $key;
        } else {
            // In dem Fall die URI über den DISPATCH-Modus bauen
            return BackendUtility::getModuleUrl($this->getModule()->getName(), $urlParams, '');
        }
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

        return $ret[$key];
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
     * @param string $actionParameters
     * @param string $label
     * @param array  $options
     *
     * @return string
     */
    public function createLinkForDataHandlerAction($actionParameters, $label, array $options = [])
    {
        // $options['sprite'] für abwärtskompatibilität
        if ($options['icon'] || $options['sprite']) {
            $icon = isset($options['icon']) ? $options['icon'] : $options['sprite'];
            $label = Icons::getSpriteIcon($icon, $options);
        }

        $jsCode = $this->getJavaScriptForLinkToDataHandlerAction($actionParameters, $options);
        $title = '';
        if ($options['hover']) {
            $title = 'title="'.$options['hover'].'"';
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = 'class="'.$class.'"';

        return '<a href="#" '.$class.' onclick="'.htmlspecialchars($jsCode).'" '.
                $title.'>'.$label.'</a>';
    }
}
