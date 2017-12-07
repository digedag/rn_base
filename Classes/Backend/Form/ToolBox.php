<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2007-2017 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('Tx_Rnbase_Backend_Utility');
tx_rnbase::load('Tx_Rnbase_Utility_Strings');
tx_rnbase::load('tx_rnbase_util_Link');
tx_rnbase::load('tx_rnbase_util_Typo3Classes');
tx_rnbase::load('Tx_Rnbase_Backend_Utility_Icons');



/**
 * Diese Klasse stellt hilfreiche Funktionen zur Erstellung von Formularen
 * im Backend zur Verfügung.
 *
 * Ersetzt tx_rnbase_util_FormTool
 */
class Tx_Rnbase_Backend_Form_ToolBox
{
    public $form; // TCEform-Instanz
    protected $module;
    protected $doc;

    const CSS_CLASS_BTN = 'btn btn-default btn-sm';

    /**
     *
     * @param template $doc
     * @param tx_rnbase_mod_IModule $module
     */
    public function init($doc, $module)
    {
        global $BACK_PATH;
        $this->doc = $doc;
        $this->module = $module;

        // TCEform für das Formular erstellen
        $this->form = tx_rnbase_util_TYPO3::isTYPO76OrHigher() ?
            tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_FormBuilder') :
            tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getBackendFormEngineClass());
        $this->form->initDefaultBEmode();
        $this->form->backPath = $BACK_PATH;
    }
    /**
     * @return template the BE template class
     */
    public function getDoc()
    {
        return $this->doc;
    }
    /**
     * @return tx_rnbase_mod_IModule
     */
    public function getModule()
    {
        return $this->module;
    }
    /**
     * Erstellt einen Button zur Bearbeitung eines Datensatzes
     * @param string $editTable DB-Tabelle des Datensatzes
     * @param int $editUid UID des Datensatzes
     * @param array $options additional options (title, params)
     * @return string
     */
    public function createEditButton($editTable, $editUid, $options = array())
    {
        $title = isset($options['title']) ? $options['title'] : 'Edit';
        $params = '&edit['.$editTable.']['.$editUid.']=edit';
        if (isset($options['params'])) {
            $params .= $options['params'];
        }
        $name = isset($options['name']) ? $options['params'] : '';

        $jsCode = Tx_Rnbase_Backend_Utility::editOnClick($params, $GLOBALS['BACK_PATH']);
        if (isset($options['confirm']) && strlen($options['confirm']) > 0) {
            $jsCode = 'if(confirm('.Tx_Rnbase_Utility_Strings::quoteJSvalue($options['confirm']).')) {' . $jsCode .'} else {return false;}';
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
     * @param Bezeichnung|string $label Bezeichnung des Links
     * @param array $options
     * @return string
     */
    public function createEditLink($editTable, $editUid, $label = 'Edit', $options = array())
    {
        $params = '&edit['.$editTable.']['.$editUid.']=edit';
        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="' . $class .'"';

        $label = isset($options['label']) ? $options['label'] : $label;

        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $onClick = htmlspecialchars(Tx_Rnbase_Backend_Utility::editOnClick($params));

            return '<a href="#" ' . $class . ' onclick="' . $onClick . '" title="Edit UID: '.$editUid.'">'
                    . Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon('actions-page-open')
                    . $label
                    . '</a>';
        } else {
            return '<a href="#" onclick="'.htmlspecialchars(Tx_Rnbase_Backend_Utility::editOnClick($params, $GLOBALS['BACK_PATH'])).'">'.
                    '<img'.Tx_Rnbase_Backend_Utility_Icons::skinImg($GLOBALS['BACK_PATH'], 'gfx/edit2.gif', 'width="11" height="12"').' title="Edit UID: '.$editUid.'" border="0" alt="Edit" />'.
                    $label .'</a>';
        }
    }

    /**
     * Erstellt einen History-Link
     * Achtung: Benötigt die JS-Funktion jumpExt() in der Seite.
     *
     * @param string $table
     * @param int $recordUid
     * @return string
     */
    public function createHistoryLink($table, $recordUid, $label = '')
    {
        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $image = Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon('actions-document-history-open');
        } else {
            $image = sprintf(
                '<img %s title="%s" alt="">',
                Tx_Rnbase_Backend_Utility_Icons::skinImg(
                    $GLOBALS['BACK_PATH'],
                    'gfx/history2.gif',
                    'width="13" height="12"'
                ),
                $GLOBALS['LANG']->getLL('history', 1)
            );
        }

        return "<a href=\"#\" onclick=\"return jumpExt('".$GLOBALS['BACK_PATH'].
                'show_rechis.php?element='.rawurlencode($table.':'.$recordUid).
                "','#latest');\">" . $image . $label . '</a>';
    }

    /**
     * Creates a new-record-button
     *
     * @param string $table
     * @param int $pid
     * @param array $options
     * @return string
     */
    public function createNewButton($table, $pid, $options = array())
    {
        $params = '&edit['.$table.']['.$pid.']=new';
        if (isset($options['params'])) {
            $params .= $options['params'];
        }
        $title = isset($options['title']) ? $options['title'] : $GLOBALS['LANG']->getLL('new', 1);
        $name = isset($options['name']) ? $options['params'] : '';

        $jsCode = Tx_Rnbase_Backend_Utility::editOnClick($params, $GLOBALS['BACK_PATH']);
        if (isset($options['confirm']) && strlen($options['confirm']) > 0) {
            $jsCode = 'if(confirm('.Tx_Rnbase_Utility_Strings::quoteJSvalue($options['confirm']).')) {' . $jsCode .'} else {return false;}';
        }

        $btn = '<input type="button" name="'. $name.'" value="' . $title . '" ';
        $btn .= 'onclick="'.htmlspecialchars($jsCode, -1).'"';
        $btn .= '/>';

        return $btn;
    }

    /**
     * Creates a Link to show an item in frontend.
     * @param $pid
     * @param $label
     * @param string $urlParams
     * @param array $options
     * @return string
     */
    public function createShowLink($pid, $label, $urlParams = '', $options = array())
    {
        if ($options['icon'] && !tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            $label = '<img '.Tx_Rnbase_Backend_Utility_Icons::skinImg($GLOBALS['BACK_PATH'], 'gfx/'.$options['icon']).
                ' title="'.$label.'\" alt="" >';
        }
        if ($options['sprite']) {
            tx_rnbase::load('tx_rnbase_mod_Util');
            $label = tx_rnbase_mod_Util::getSpriteIcon($options['sprite']);
        }
        $jsCode = Tx_Rnbase_Backend_Utility::viewOnClick($pid, '', '', '', '', $urlParams);
        $title = '';
        if ($options['hover']) {
            $title = ' title="'.$options['hover'].'" ';
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="' . $class .'"';

        return '<a href="#" ' . $class . ' onclick="'.htmlspecialchars($jsCode).'" '. $title.'>'. $label .'</a>';
    }

    /**
     * Erstellt einen Link zur Erstellung eines neuen Datensatzes
     * @param string $table DB-Tabelle des Datensatzes
     * @param int $pid UID der Zielseite
     * @param string $label Bezeichnung des Links
     * @param array $options
     * @return string
     */
    public function createNewLink($table, $pid, $label = 'New', $options = array())
    {
        $params = '&edit['.$table.']['.$pid.']=new';
        if (isset($options['params'])) {
            $params .= $options['params'];
        }
        $title = isset($options['title']) ? $options['title'] : $GLOBALS['LANG']->getLL('new', 1);

        $jsCode = Tx_Rnbase_Backend_Utility::editOnClick($params, $GLOBALS['BACK_PATH']);
        if (isset($options['confirm']) && strlen($options['confirm']) > 0) {
            $jsCode = 'if(confirm('.Tx_Rnbase_Utility_Strings::quoteJSvalue($options['confirm']).')) {' . $jsCode .'} else {return false;}';
        }

        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $image = Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon('actions-document-new');
        } else {
            $image = '<img' .
                Tx_Rnbase_Backend_Utility_Icons::skinImg(
                    $GLOBALS['BACK_PATH'],
                    'gfx/new_'.($table == 'pages' ? 'page' : 'el') . '.gif',
                    'width="'.($table == 'pages' ? 13 : 11).'" height="12"'
                ).' alt="" />';
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="' . $class .'"';

        return    '<a href="#" title="'.$title.'" ' . $class . ' onclick="'.htmlspecialchars($jsCode, -1).'">' .
                $image . $label . '</a>';
    }

/**
 * Create a hide/unhide Link
 * @param string $table
 * @param int $uid
 * @param bool $unhide
 * @param array $options
 */
    public function createHideLink($table, $uid, $unhide = false, $options = array())
    {
        $sEnableColumn = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'];
        //fallback
        $sEnableColumn = ($sEnableColumn) ? $sEnableColumn : 'hidden';
        $label = isset($options['label']) ? $options['label'] : '';
        $jumpToUrl = $this->getJavaScriptForLinkToDataHandlerAction('data['.$table.']['.$uid.']['. $sEnableColumn .']='.($unhide ? 0 : 1), $options);

        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $image = Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon(
                $unhide ? 'actions-edit-unhide' : 'actions-edit-hide'
            );
        } else {
            $image = '<img'.Tx_Rnbase_Backend_Utility_Icons::skinImg($GLOBALS['BACK_PATH'], 'gfx/'.($unhide ? 'button_hide.gif' : 'button_unhide.gif'), 'width="11" height="12"').' border="0" alt="" />';
        }

        return '<a onclick="'.$jumpToUrl.'" href="#" title="' . ($unhide ? 'Show' : 'Hide').' UID: '.$uid . '">'.
                $image .
                $label.'</a>';
    }

    /**
     * Erstellt einen Link zur Anzeige von Informationen über einen Datensatz
     * @param $editTable DB-Tabelle des Datensatzes
     * @param $editUid UID des Datensatzes
     * @param $label Bezeichnung des Links
     */
    public function createInfoLink($editTable, $editUid, $label = 'Info')
    {
        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $image = Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon('actions-document-info');
        } else {
            $image = sprintf(
                '<img %s title="Edit %s" alt="">',
                Tx_Rnbase_Backend_Utility_Icons::skinImg(
                    $GLOBALS['BACK_PATH'],
                    'gfx/zoom2.gif',
                    'width="13" height="12"'
                ),
                $editUid
            );
        }

        return '<a href="#" onclick="top.launchView(' . "'" . $editTable . "', ' " . $editUid . "'); return false;" . '">' . $image . $label .'</a>';
    }

    /**
     * Erstellt einen Link zum Verschieben eines Datensatzes auf eine andere Seite
     * @param $editTable DB-Tabelle des Datensatzes
     * @param $recordUid UID des Datensatzes
     * @param $currentPid PID der aktuellen Seite des Datensatzes
     * @param $label Bezeichnung des Links
     */
    public function createMoveLink($editTable, $recordUid, $currentPid, $label = 'Move')
    {
        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $image = Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon('actions-edit-cut');
        } else {
            $image = sprintf(
                '<img %s title="UID:  %s" alt="">',
                Tx_Rnbase_Backend_Utility_Icons::skinImg(
                    $GLOBALS['BACK_PATH'],
                    'gfx/clip_cut.gif',
                    'width="13" height="12"'
                ),
                $recordUid
            );
        }

        return "<a href=\"#\" onclick=\"return jumpSelf('/typo3/db_list.php?id=". $currentPid .'&amp;CB[el][' . $editTable
                     . '%7C' . $recordUid . "]=1');\">" . $image . $label .'</a>';
    }

    /**
     * Erstellt einen Link zum Verschieben eines Datensatzes.
     *
     * @param string $table
     * @param int $uid
     * @param int $moveId die uid des elements vor welches das element aus $uid gesetzt werden soll
     * @param array $options
     */
    public function createMoveUpLink($table, $uid, $moveId, $options = array())
    {
        $jsCode = $this->getJavaScriptForLinkToDataHandlerAction('cmd['.$table.']['.$uid.'][move]=-' . $moveId . '&prErr=1&uPT=1', $options);
        $label = isset($options['label']) ? $options['label'] : 'Move up';
        $title = isset($options['title']) ? $options['title'] : $label;

        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $image = Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon('actions-move-up');
        } else {
            $image = sprintf(
                '<img %s title="%s" alt="">',
                Tx_Rnbase_Backend_Utility_Icons::skinImg(
                    $GLOBALS['BACK_PATH'],
                    'gfx/up.gif',
                    'width="13" height="12"'
                ),
                $title
            );
        }

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
     * @param int $uid
     * @param int $moveId die uid des elements nach welchem das element aus $uid gesetzt werden soll
     * @param array $options
     */
    public function createMoveDownLink($table, $uid, $moveId, $options = array())
    {
        $jsCode = $this->getJavaScriptForLinkToDataHandlerAction('cmd['.$table.']['.$uid.'][move]=-' . $moveId, $options);
        $label = isset($options['label']) ? $options['label'] : 'Move up';
        $title = isset($options['title']) ? $options['title'] : $label;

        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $image = Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon('actions-move-down');
        } else {
            $image = sprintf(
                '<img %s title="%s" alt="">',
                Tx_Rnbase_Backend_Utility_Icons::skinImg(
                    $GLOBALS['BACK_PATH'],
                    'gfx/up.gif',
                    'width="13" height="12"'
                ),
                $title
            );
        }

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
     * $this->getJavaScriptForLinkToDataHandlerAction('cmd[pages][123][delete]=1')
     *
     * @param string $urlParameters command for datahandler
     * @param array $options
     * @return string
     */
    protected function getJavaScriptForLinkToDataHandlerAction($urlParameters, array $options = array())
    {
        if (tx_rnbase_util_TYPO3::isTYPO87OrHigher()) {
            $jumpToUrl = Tx_Rnbase_Backend_Utility::getLinkToDataHandlerAction('&' . $urlParameters, -1);
            // the jumpUrl method is no longer global available since TYPO3 8.7
            $this->getDoc()->getPageRenderer()->addJsInlineCode(
                'rnBaseMethods',
                $this->getBaseJavaScriptCode()
            );
        } else {
            $currentLocation = $this->getLinkThisScript(true, $options);

            if (tx_rnbase_util_TYPO3::isTYPO76OrHigher()) {
                $dataHandlerEntryPoint = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('tce_db') .
                '&';
            } else {
                $dataHandlerEntryPoint = $GLOBALS['BACK_PATH'] . 'tce_db.php?';
            }
            $jumpToUrl = $dataHandlerEntryPoint. 'redirect=' . $currentLocation . '&amp;' . $urlParameters;

            // jetzt noch alles zur Formvalidierung einfügen damit
            // TYPO3 den Link akzeptiert und als valide einstuft
            // der Formularname ist immer tceAction
            $jumpToUrl .= '&amp;vC=' . $GLOBALS['BE_USER']->veriCode();
            $jumpToUrl .= Tx_Rnbase_Backend_Utility::getUrlToken('tceAction');
            $jumpToUrl = '\'' . $jumpToUrl . '\'';
        }
        return $this->getConfirmCode('return jumpToUrl(' . $jumpToUrl . ');', $options);
    }

    /**
     * @see t3lib_div::linkThisScript
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::linkThisScript
     *
     * @param bool $encode
     * @param array $options possible key "params" with array of url params
     * @return string
     */
    protected function getLinkThisScript($encode = true, array $options = array())
    {
        $params = [
            'CB' => '', 'SET' => '', 'cmd' => '', 'popViewId' => ''
        ];
        if (isset($options['params']) && is_array($options['params'])) {
            $params = array_merge($params, $options['params']);
        }
        $location = tx_rnbase_util_Link::linkThisScript($params);
        if ($encode) {
            $location = str_replace('%20', '', rawurlencode($location));
        }

        return $location;
    }

    /**
     * Erstellt einen Link zum Löschen eines Datensatzes
     *
     * @param string $table
     * @param int $iUid
     * @param string $sLabel
     * @param array $options
     */
    public function createDeleteLink($table, $uid, $label = 'Remove', $options = array())
    {
        $jsCode = $this->getJavaScriptForLinkToDataHandlerAction('cmd['.$table.']['.$uid.'][delete]=1', $options);
        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $image = Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon('actions-delete');
        } else {
            $image = '<img'.Tx_Rnbase_Backend_Utility_Icons::skinImg($GLOBALS['BACK_PATH'], 'gfx/deletedok.gif', 'width="16" height="16"').'  border="0" alt="" />';
        }

        return '<a onclick="'.$jsCode.'" href="#" title="Delete UID: '.$uid.'">'. $image . $label.'</a>';
    }

    /**
     * Fügt den JS Code für eine Confirm-Meldung hinzu, wenn in den Options gesetzt.
     * @param string $jsCode
     * @param array $options
     */
    protected function getConfirmCode($jsCode, $options)
    {
        if (isset($options['confirm']) && strlen($options['confirm']) > 0) {
            return 'if(confirm('.Tx_Rnbase_Utility_Strings::quoteJSvalue($options['confirm']).')) {' . $jsCode .'} else {return false;}';
        }

        return $jsCode;
    }

    public function createHidden($name, $value)
    {
        return '<input type="hidden" name="'. $name.'" value="' . htmlspecialchars($value) . '" />';
    }

    public function createRadio($name, $value, $checked = false, $onclick = '')
    {
        return '<input type="radio" name="'. $name.'" value="' . htmlspecialchars($value) . '" '. ($checked ? 'checked="checked"' : '') . (strlen($onclick) ? ' onclick="' . $onclick . '"' : '') . ' />';
    }

    public function createCheckbox($name, $value, $checked = false, $onclick = '')
    {
        return '<input type="checkbox" name="'. $name.'" value="' . htmlspecialchars($value) . '" '. ($checked ? 'checked="checked"' : '') . (strlen($onclick) ? ' onclick="' . $onclick . '"' : '') .' />';
    }

    /**
     * @deprecated alias for createModuleLink()
     * @see createModuleLink()
     */
    public function createLink($paramStr, $pid, $label, array $options = array())
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
     * @param array $params additional url parameters for current script
     * @param int $pid wird nicht mehr verwendet. nur für abwärtskompatibilität
     * @param string $label
     * @param array $options
     * @return string
     */
    public function createModuleLink(array $params, $pid, $label, array $options = array())
    {
        // $options['sprite'] für abwärtskompatibilität
        if ($options['icon'] || $options['sprite']) {
            $icon = isset($options['icon']) ? $options['icon'] : $options['sprite'];
            if (!tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
                $label =    '<img '.Tx_Rnbase_Backend_Utility_Icons::skinImg($GLOBALS['BACK_PATH'], 'gfx/' . $icon) .
                            ' title="' . $label . '\" alt="" >';
            } else {
                tx_rnbase::load('tx_rnbase_mod_Util');
                $label = tx_rnbase_mod_Util::getSpriteIcon($icon, $options);
            }
        }

        $location = $this->getLinkThisScript(false, ['params'=>$params]);

        $jsCode = "window.location.href='".$location. "'; return false;";

        $title = '';
        if ($options['hover']) {
            $title = 'title="' . $options['hover'] . '"';
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = 'class="' . $class .'"';

        return '<a href="#" ' . $class . ' onclick="'.htmlspecialchars($jsCode).'" '. $title.'>'. $label .'</a>';
    }

    /**
     * Submit button for BE form.
     * If you set an icon in options, the output will like this:
     * <button><img></button>
     * @param string $name
     * @param string $value
     * @param string $confirmMsg
     * @param array $options
     */
    public function createSubmit($name, $value, $confirmMsg = '', $options = array())
    {
        $icon = '';
        if ($options['icon'] && !tx_rnbase_util_TYPO3::isTYPO80OrHigher()) {
            $icon = Tx_Rnbase_Backend_Utility_Icons::skinImg($GLOBALS['BACK_PATH'], 'gfx/'.$options['icon']);
        }
        $onClick = '';
        if (strlen($confirmMsg)) {
            $onClick = 'onclick="return confirm('.Tx_Rnbase_Utility_Strings::quoteJSvalue($confirmMsg).')"';
        }

        $class = array_key_exists('class', $options) ? htmlspecialchars($options['class']) : self::CSS_CLASS_BTN;
        $class = ' class="' . $class .'"';

        if ($icon) {
            $btn = '<button type="submit" ' . $class . ' name="'. $name.'" value="' . htmlspecialchars($value) . '">' .
                    '<img '.$icon.' alt="SomeAlternateText"></button>';
        } else {
            $btn = '<input type="submit" ' . $class . ' name="'. $name.'" value="' . htmlspecialchars($value) . '" ';
            $btn .= $onClick;
            $btn .= '/>';
        }

        return $btn;
    }

    /**
     * Erstellt ein Textarea
     */
    public function createTextArea($name, $value, $cols = '30', $rows = '5', $options = 0)
    {
        $options = is_array($options) ? $options : array();
        $onChangeStr = $options['onchange'] ? ' onchange=" ' . $options['onchange'] . '" ' : '';

        return '<textarea name="' . $name . '" style="width:288px;" class="formField1"'. $onChangeStr .
            ' cols="'.$cols.'" rows="'.$rows.'" wrap="virtual">' . $value . '</textarea>';
    }

    /**
     * Erstellt ein einfaches Textfield
     */
    public function createTxtInput($name, $value, $width, $options = array())
    {
        $class = array_key_exists('class', $options) ? ' class="' . $options['class'].'"' : '';
        $onChange = array_key_exists('onchange', $options) ? ' onchange="' . $options['onchange'].'"' : '';
        $ret = '<input type="text" name="'. $name.'"'.$this->doc->formWidth($width).
            $onChange .
            $class .
            ' value="' . htmlspecialchars($value) . '" />';

        return $ret;
    }

    /**
     * Erstellt ein Eingabefeld für Integers
     */
    public function createIntInput($name, $value, $width, $maxlength = 10)
    {
        if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            /* @var $inputField Tx_Rnbase_Backend_Form_Element_InputText */
            $inputField = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_Element_InputText', $this->getTCEForm()->getNodeFactory(), array());
            $out = $inputField->renderHtml($name, $value, [
                'width' => $width,
                'maxlength' => $maxlength,
                'eval' => 'int',
            ]);
        } else {
            $out = '
            <input type="text" name="' . $name . '_hr"'.$GLOBALS['TBE_TEMPLATE']->formWidth($width).
                        ' onchange="typo3FormFieldGet(\'' . $name . '\', \'int\', \'\', 0,0);"'.
                        $GLOBALS['TBE_TEMPLATE']->formWidth(12). ' maxlength="' . $maxlength . '"/>'.'
            <input type="hidden" value="'.htmlspecialchars($value).'" name="' . $name . '" />';

            // JS-Code für die Initialisierung im TCEform eintragen
            $this->form->extJSCODE .= 'typo3FormFieldSet("' . $name . '", "int", "", 0,0);';
        }

        return $out;
    }

    /**
     * Erstellt ein Eingabefeld für DateTime
     * @todo fix prefilling of field in TYPO3 8.7
     */
    public function createDateInput($name, $value)
    {
        // Take care of current time zone. Thanks to Thomas Maroschik!
        if (tx_rnbase_util_Math::isInteger($value)) {
            $value += date('Z', $value);
        }
        if(tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
            $this->getDoc()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/DateTimePicker');
            /* @var $inputField Tx_Rnbase_Backend_Form_Element_InputText */
            $inputField = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Form_Element_InputText', $this->getTCEForm()->getNodeFactory(), array());
            return $inputField->renderHtml($name, $value, [
                'width' => 20,
                'maxlength' => 20,
                'eval' => 'datetime',
            ]);
        }
        else {
            return $this->createDateInput62($name, $value);
        }
    }
    /**
     * DateTime-Field rendering up to 6.2
     * @param string $name
     * @param int $value
     * @return string
     */
    private function createDateInput62($name, $value)
    {
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
    public function createSelectSingle($name, $value, $table, $column, $options = 0)
    {
        global $TCA, $LANG;
        $options = is_array($options) ? $options : array();

        $out = '<select  name="' . $name . '" class="select" ';
        if ($options['onchange']) {
            $out .= 'onChange="' . $options['onchange'] .'" ';
        }
        $out .= '>';

        // Die TCA laden
        tx_rnbase_util_TCA::loadTCA($table);

        // Die Options ermitteln
        foreach ($TCA[$table]['columns'][$column]['config']['items'] as $item) {
            $sel = '';
            if ($value == $item[1]) {
                $sel = 'selected="selected"';
            }
            $out .= '<option value="' . $item[1] . '" ' . $sel . '>' . $LANG->sL($item[0]) . '</option>';
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
     * ob es eine mehrfach Auswahl geben soll
     *
     * @param string $name
     * @param string $currentValues comma separated
     * @param array $selectOptions
     * @param array $options
     * @return string
     */
    public function createSelectByArray($name, $currentValues, array $selectOptions, $options = array())
    {
        $options = is_array($options) ? $options : array();

        $onChangeStr = $options['reload'] ? ' this.form.submit(); ' : '';
        if ($options['onchange']) {
            $onChangeStr .= $options['onchange'];
        }
        if ($onChangeStr) {
            $onChangeStr = ' onchange="'.$onChangeStr.'" ';
        }

        $multiple = $options['multiple'] ? ' multiple="multiple"' : '';
        $name .= $options['multiple'] ? '[]' : '';

        $size = $options['size'] ? ' size="' . $options['size'] . '"' : '';

        $out = '<select name="' . $name . '" class="select"' . $onChangeStr . $multiple . $size . '>';

        $currentValues = Tx_Rnbase_Utility_Strings::trimExplode(',', $currentValues);

        // Die Options ermitteln
        foreach ($selectOptions as $value => $label) {
            $selected = '';
            if (in_array($value, $currentValues)) {
                $selected = 'selected="selected"';
            }
            $out .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
        }
        $out .= '</select>';

        return $out;
    }

    /**
     * Liefert einen Sortierungslink für das gegebene Feld
     * @param string $sSortField
     * @return string
     */
    public function createSortLink($sSortField, $sLabel)
    {
        //das ist aktuell gesetzt
        $sCurrentSortField = tx_rnbase_parameters::getPostOrGetParameter('sortField');
        $sCurrentSortRev = tx_rnbase_parameters::getPostOrGetParameter('sortRev');
        //wir verweisen immer auf die aktuelle Seite
        //es kann aber schon ein sort parameter gesetzt sein
        //weshalb wir alte entfernen
        $sUrl = preg_replace('/&sortField=.*&sortRev=[^&]*/', '', tx_rnbase_util_Misc::getIndpEnv('TYPO3_REQUEST_URL'));

        //sort richtung rausfinden
        //beim initialen Aufruf (spalte noch nicht geklickt) wird immer aufsteigend sortiert
        if ($sCurrentSortField != $sSortField) {
            $sSortRev = 'asc';
        } else {//sonst das gegenteil vom aktuellen
            $sSortRev = ($sCurrentSortRev == 'desc') ? 'asc' : 'desc';
        }

        //prüfen ob Parameter mit ? oder & angehängt werden müssen
        $sAddParamsWith = (strstr($sUrl, '?')) ? '&' : '?';
        //jetzt setzen wir den aktuellen Sort parameter zusammen
        $sSortUrl = $sUrl . $sAddParamsWith.'sortField=' . $sSortField . '&sortRev=' . $sSortRev;
        //noch den Pfeil für die aktuelle Sortierungsrichtung ggf. einblenden
        $sSortArrow = '';
        if ($sCurrentSortField == $sSortField) {
            if (tx_rnbase_util_TYPO3::isTYPO70OrHigher()) {
                $sSortArrow = Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon(
                    $sSortRev == 'asc' ? 'actions-move-up' : 'actions-move-down'
                );
            } else {
                $sSortArrow = sprintf(
                    '<img %s alt="">',
                    Tx_Rnbase_Backend_Utility_Icons::skinImg(
                        $GLOBALS['BACK_PATH'],
                        'gfx/red' . ($sSortRev == 'asc' ? 'up' : 'down') . '.gif',
                        'width="7" height="4"'
                    )
                );
            }
        }

        return '<a href="'.htmlspecialchars($sSortUrl).'">'.$sLabel.$sSortArrow.'</a>';
    }

    public function addTCEfield2Stack($table, $row, $fieldname, $pre = '', $post = '')
    {
        $this->tceStack[] = $pre . $this->form->getSoloField($table, $row, $fieldname) . $post;
    }
    /**
     * @return TYPO3\CMS\Backend\Form\FormEngine
     */
    public function getTCEForm()
    {
        return $this->form;
    }

    public function getTCEfields($formname)
    {
        $ret = array();
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
    public function getJSCode($pid, $location = '')
    {
        return $this->doc->wrapScriptTags($this->getBaseJavaScriptCode($location));
    }

    /**
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
            var T3_RETURN_URL = ' .
                Tx_Rnbase_Utility_Strings::quoteJSvalue(
                    str_replace('%20', '', rawurlencode(tx_rnbase_parameters::getPostOrGetParameter('returnUrl')))
                ) . ';
            var T3_THIS_LOCATION=' .
                Tx_Rnbase_Utility_Strings::quoteJSvalue(str_replace('%20', '', rawurlencode($location)));

        return $javaScriptCode;
    }

    /**
     * Zeigt ein TabMenu
     *
     * @param int $pid
     * @param string $name
     * @param array $entries
     * @return array with keys 'menu' and 'value'
     */
    public function showTabMenu($pid, $name, $modName, $entries)
    {
        $MENU = array(
            $name => $entries
        );
        $SETTINGS = Tx_Rnbase_Backend_Utility::getModuleData(
            $MENU,
            tx_rnbase_parameters::getPostOrGetParameter('SET'),
            $modName
        );
        $menuItems = array();
        foreach ($entries as $key => $value) {
            if (strcmp($value, '') === 0) {
                // skip empty entries!
                continue;
            }
            $menuItems[] = array(
                'isActive' => $SETTINGS[$name] == $key,
                'label' => $value,
                // jumpUrl ist ab TYPO3 6.2 nicht mehr nötig
                // @TODO jumpUrl entfernen wenn kein Support mehr für 4.5
                'url' => '#',
                'addParams' =>    'onclick="jumpToUrl(\'' .
                                $this->buildScriptURI(array('id' => $pid, 'SET['.$name.']' => $key)) .
                                '\',this);"'
            );
        }

        // In TYPO3 6 the getTabMenuRaw produces a division by zero error if there are no entries.
        if (!empty($menuItems)) {
            $out = '<div class="typo3-dyntabmenu-tabs">' . $this->getDoc()->getTabMenuRaw($menuItems) . '</div>';
            // durch den Kommentar <!-- Tab menu --> wird das Menü 2-mal eingefügt vor TYPO3 7.6
            // also entfernen wir den Kommentar
            $out = str_replace('<!-- Tab menu -->', '', $out);
        }

        $ret = array(
            'menu' => $out,
            'value' => $SETTINGS[$name],
        );

        return $ret;
    }
    protected function buildScriptURI($urlParams)
    {
        if (!Tx_Rnbase_Backend_Utility::isDispatchMode()) {
            return 'index.php?'. http_build_query($urlParams);
//            'index.php?&amp;id='.$pid.'&amp;SET['.$name.']='. $key;
        } else {
            // In dem Fall die URI über den DISPATCH-Modus bauen
            return Tx_Rnbase_Backend_Utility::getModuleUrl($this->getModule()->getName(), $urlParams, '');
        }
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
    public static function showMenu($pid, $name, $modName, $entries, $script = '', $addparams = '')
    {
        $MENU = array(
            $name => $entries
        );
        $SETTINGS = Tx_Rnbase_Backend_Utility::getModuleData(
            $MENU,
            tx_rnbase_parameters::getPostOrGetParameter('SET'),
            $modName
        );

        $ret = array();
        if ((tx_rnbase_util_TYPO3::isTYPO62OrHigher() && is_array($MENU[$name]) && count($MENU[$name]) == 1)) {
            $ret['menu'] = self::buildDummyMenu('SET['.$name.']', $MENU[$name]);
        } else {
            $funcMenu = tx_rnbase_util_TYPO3::isTYPO76OrHigher() ? 'getDropdownMenu' : 'getFuncMenu';
            $ret['menu'] = Tx_Rnbase_Backend_Utility::$funcMenu(
                $pid, 'SET['.$name.']', $SETTINGS[$name],
                $MENU[$name], $script, $addparams
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
        $options = array();

        foreach ($menuItems as $value => $label) {
            $options[] = sprintf(
                '<option value="%1$s" selected="selected">%2$s</option>',
                htmlspecialchars($value),
                htmlspecialchars($label, ENT_COMPAT, 'UTF-8', false)
            );
        }

        return '<!-- Function Menu of module -->' . sprintf(
            '<select class="form-control" name="%1$s" >%2$s</select>',
            $elementName,
            implode(LF, $options)
        );
    }

    /**
     * Submit-Button like this: name="mykey[123]" value="label"
     * You will get 123 as long as no other submit changes this value.
     * @param string $key
     * @param string $modName
     * @return mixed
     */
    public function getStoredRequestData($key, $changed = array(), $modName = 'DEFRNBASEMOD')
    {
        $data = tx_rnbase_parameters::getPostOrGetParameter($key);
        if (is_array($data)) {
            list($itemid, ) = each($data);
            $changed[$key] = $itemid;
        }
        $ret = Tx_Rnbase_Backend_Utility::getModuleData(array($key => ''), $changed, $modName);

        return $ret[$key];
    }
    /**
     * Load a fullfilled TCE data array for a database record.
     * @param string $table
     * @param int $theUid
     * @param bool $isNew
     */
    public function getTCEFormArray($table, $theUid, $isNew = false)
    {
        $transferDataClass = tx_rnbase_util_TYPO3::isTYPO62OrHigher() ?
            'TYPO3\\CMS\\Backend\\Form\\DataPreprocessor' : 't3lib_transferData';
        $trData = tx_rnbase::makeInstance($transferDataClass);
        $trData->addRawData = true;
        $trData->fetchRecord($table, $theUid, $isNew ? 'new' : '');    // 'new'
        reset($trData->regTableItems_data);

        return $trData->regTableItems_data;
    }
}
