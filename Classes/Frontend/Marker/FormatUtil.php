<?php

namespace Sys25\RnBase\Frontend\Marker;

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Utility\Debug;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2021 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Contains utility functions for formatting
 * TODO: Die Verwendung der Klasse \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer sollte überarbeitet werden.
 */
class FormatUtil
{
    public $configurations;

    public $cObj;

    // Wie kommen diese Werte in die Config??
    // Sind es nicht auch eher Konfigwerte??
    public $dateFormatKey = 'dateFormat';

    public $floatFormatKey = 'floatFormat';

    public $parseFuncTextKey = 'parseFuncText';

    public $parseFuncRteKey = 'parseFuncRte';

    public $timeFormatKey = 'timeFormat';

    private static $time = 0;

    private static $mem = 0;

    /**
     * Konstruktor.
     *
     * @param ConfigurationInterface $configurations
     */
    public function __construct($configurations)
    {
        $this->configurations = $configurations;
        $this->cObj = $configurations->getCObj(self::class);
    }

    /**
     * Returns configuration instance.
     *
     * @return ConfigurationInterface
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Get human readability and localized date for a timestamp out of the internal data array.
     *
     * If no format parameter is provided, the function tries to find one in the configurations
     * by using the pathKey $this->dateFormatKey.
     *
     * @param     mixed       key of internal data array
     * @param     string      format string
     *
     * @return string human readable date string
     *
     * @see       http://php.net/strftime
     */
    public function asDate($value, $format = null)
    {
        $format = $format ? $format : $this->configurations->get($this->dateFormatKey);

        return $format ? strftime($format, $value) : $value;
    }

    /**
     * Für die CASE Funktion aus. Das übergebene Daten-Array wird nur verwendet, wenn
     * nicht das Standard-cObj verwendet wird ($cObjId != 0).
     */
    public function casefunc($conf, $dataArr, $cObjId = 0)
    {
        $cObj = &$this->configurations->getCObj($cObjId);
        if ($cObjId) {
            $data = $cObj->data;
            $cObj->data = $dataArr;
        }
        $value = $cObj->CASEFUNC($conf);
        if ($cObjId) {
            $cObj->data = $data;
        }

        return $value;
    }

    /**
     * Wrap the $content.
     */
    public function wrap($content, $confId, $dataArr = 0)
    {
        return $this->stdWrap($content, $this->configurations->get($confId), $dataArr);
    }

    /**
     * Call of \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::stdWrap().
     */
    public function stdWrap($content, $conf, $dataArr = 0)
    {
        $tmpArr = $this->cObj->data;
        if (is_array($dataArr)) {
            $this->cObj->data = $dataArr;
        }
        $ret = $this->cObj->stdWrap($content, $conf);
        // Data zurücksetzen
        $this->cObj->data = $tmpArr;

        return $ret;
    }

    /**
     * Führt einen Standardwrap durch und setzt vorher das data-Array des cObject
     * auf die übergebenen Daten.
     *
     * @param array  $dataArr data für cObject
     * @param string $content
     * @param string $confId
     */
    public function dataStdWrap($dataArr, $content, $confId)
    {
        $tmpArr = $this->cObj->data;
        if (is_array($dataArr)) {
            $this->cObj->data = $dataArr;
        }
        $conf = $this->configurations->get($confId);
        $ret = $this->cObj->stdWrap($content, $conf);

        // Data zurücksetzen
        $this->cObj->data = $tmpArr;

        return $ret;
    }

    /**
     * Liefert den Wert als Image. Intern wird \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::IMAGE verwendet.
     */
    public function getImage($image, $confId, $extensionKey = 0)
    {
        if (0 == strlen($image)) {
            return '';
        }
        $confArr = $this->configurations->get($confId);
        $confArr['file'] = 'uploads/tx_'.str_replace(
            '_',
            '',
            ($extensionKey) ? $extensionKey : $this->configurations->getExtensionKey()
        ).'/'.($image);

        // Bei einfachen Bildern sollen die Einstellungen aus cObj->data nicht verwendet
        // werden, um zu verhindern, daß z.B. eine Gallery angezeigt wird
        $tmp = $this->cObj->data;
        $this->cObj->data = [];
        $theImgCode = $this->cObj->IMAGE($confArr);

        $this->cObj->data = $tmp;

        return $theImgCode;
    }

    /**
     * Puts all columns in $record to a Marker-Array. Each column is wrapped according to it's name.
     * So if your confId is 'profile.' and your column is 'date' you can define a TS setup like
     * <pre>profile.date.strftime = %Y</pre>.
     *
     * @return array
     */
    public function getItemMarkerArrayWrapped($record, $confId, $noMap = 0, $markerPrefix = '', $initMarkers = 0)
    {
        if (!is_array($record)) {
            return [];
        }

        $start = microtime(true);
        $mem = memory_get_usage();

        $tmpArr = $this->cObj->data;
        // Ensure the initMarkers are part of the record
        if (is_array($initMarkers)) {
            if (is_array($noMap)) {
                // It makes no sense to init markers that are not part of template
                $initMarkers = array_diff($initMarkers, $noMap);
            }

            foreach ($initMarkers as $initMarker) {
                if (!array_key_exists($initMarker, $record)) {
                    $record[$initMarker] = '';
                }
            }
        }

        // extend the record by dc columns
        $conf = $this->getConfigurations()->get($confId);
        if (is_array($conf)) {
            // Add dynamic columns
            $keys = $this->getConfigurations()->getUniqueKeysNames($conf);
            foreach ($keys as $key) {
                if ('d' === $key[0] && 'c' === $key[1] && !isset($record[$key])) {
                    $record[$key] = $conf[$key];
                }
            }
        }

        if (array_key_exists('__MINFO', $record)) {
            // Die TS-Config in die Ausgabe integrieren
            $record['__MINFO'] .= Debug::viewArray(['TS-Path' => $confId]);
            $record['__MINFO'] .= Debug::viewArray([$conf]);
        }

        $this->cObj->data = $record;

        // Alle Metadaten auslesen und wrappen
        $data = [];
        foreach ($record as $colname => $value) {
            if (is_array($noMap) && in_array($colname, $noMap)) {
                continue;
            }

            // Für DATETIME gibt es eine Sonderbehandlung, um leere Werte zu behandeln
            if ('DATETIME' == $conf[$colname] && $conf[$colname.'.']['ifEmpty'] && !$value) {
                $data[$colname] = $conf[$colname.'.']['ifEmpty'];
            } elseif ($conf[$colname]) {
                // Get value using cObjGetSingle
                $this->cObj->setCurrentVal($value);
                $data[$colname] = $this->cObj->cObjGetSingle($conf[$colname], $conf[$colname.'.']);
                $this->cObj->setCurrentVal(false);
            } elseif ('CASE' == $conf[$colname]) {
                $data[$colname] = $this->cObj->CASEFUNC($conf[$colname.'.']);
            } else {
                // Es wird ein normaler Wrap gestartet
                // Zuerst Numberformat durchführen
                $value = $this->numberFormat($value, $conf[$colname.'.']);
                $data[$colname] = $this->stdWrap($value, $conf[$colname.'.']);
            }
        }
        reset($record);
        $markerArray = self::getItemMarkerArray($data, $noMap, $markerPrefix, $initMarkers);
        unset($data); // 400 kB
        $this->cObj->data = $tmpArr;
        self::$time += (microtime(true) - $start);
        self::$mem += (memory_get_usage() - $mem);

        return $markerArray;
    }

    /**
     * Adds number_format functionality to stdWrap-Conf.
     * This method is taken from extension am_stdwrap_numberformat.
     * TS-Syntax: number_format.
     *
     * @author Artem Matevosyan
     *
     * @param string $content
     * @param array  $conf
     *
     * @return string
     */
    public function numberFormat($content, &$conf)
    {
        if (!is_array($conf) || !array_key_exists('number_format.', $conf) || !is_float($content)) {
            return $content;
        }
        if ($conf['number_format.']['dontCheckFloat'] || number_format(floatval($content), 0, '.', '') != $content) {
            if ($conf['number_format'] || $conf['number_format.']) {
                // default
                $decimal = 2;
                $dec_point = '.';
                $thousands_sep = '';
                if (isset($conf['number_format.']['decimal'])) {
                    $decimal = $conf['number_format.']['decimal'];
                }
                if (isset($conf['number_format.']['dec_point'])) {
                    $dec_point = $conf['number_format.']['dec_point'];
                }
                if (isset($conf['number_format.']['thousands_sep'])) {
                    if ('[space]' == $conf['number_format.']['thousands_sep']) {
                        $thousands_sep = ' ';
                    } else {
                        $thousands_sep = $conf['number_format.']['thousands_sep'];
                    }
                }
                $content = number_format($content, $decimal, $dec_point, $thousands_sep);
            }
        }

        return $content;
    }

    public function fillEmptyMarkers(&$markerArray, $markers, $markerPrefix = '')
    {
        foreach ($markers as $marker) {
            $marker = (string) strtoupper($marker);
            $markerArray["###${markerPrefix}${marker}###"] = '';
        }
    }

    /**
     * Puts all columns in $record to a Marker-Array. This method can be used static.
     *
     * @param array  $record       : Record to display
     * @param array  $noMap        : Array of column names to ignore
     * @param string $markerPrefix : An optional prefix for each marker, maybe 'PICTURE_'
     * @param array  $initMarkers  : Markers that should be initialized as empty strings
     */
    public function getItemMarkerArray(&$record, $noMap = 0, $markerPrefix = '', $initMarkers = 0)
    {
        $markerArray = [];

        $noMap = is_array($noMap) ? array_flip($noMap) : $noMap;
        // Marker vordefinieren
        if (is_array($initMarkers)) {
            self::fillEmptyMarkers($markerArray, $initMarkers, $markerPrefix);
        }
        if (is_array($record)) {
            foreach ($record as $colname => $value) {
                // Skip some values
                if (is_array($noMap) && array_key_exists($colname, $noMap)) {
                    continue;
                }
                $colname = (string) strtoupper($colname);
                $markerArray["###${markerPrefix}${colname}###"] = $value;
            }
        }

        return $markerArray;
    }
}
