<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2014 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_util_FormTool');

/**
 * Pager für BE-Module.
 */
class tx_rnbase_util_BEPager
{
    public $id;

    public $pid;

    public $listSize;

    public $settings;

    public $init = false;

    public function __construct($id, $modName, $pid, $listSize = 0, $conf = [])
    {
        $this->id = strlen(trim($id)) ? trim($id) : 'pager';
        $this->pid = $pid;
        $this->modName = $modName;
        $this->conf = $conf;
        $this->setListSize($listSize);
    }

    public function setListSize($listSize)
    {
        $this->listSize = $listSize;
    }

    public function getListSize()
    {
        return $this->listSize;
    }

    /**
     * Setzt die Daten für den SQL-Select.
     *
     * @param array $options
     */
    public function setOptions(&$options)
    {
        $this->setState();
        $options['limit'] = $this->getSetting('limit');
        $options['offset'] = $this->getSetting('offset');
    }

    public function setSetting($name, $value)
    {
        $this->settings[$this->getDataName().'_'.$name] = $value;
    }

    public function getSetting($name)
    {
        return $this->settings[$this->getDataName().'_'.$name];
    }

    /**
     * Returns the array with page size limit.
     *
     * @return array
     */
    public function getLimits()
    {
        return is_array($this->conf['limits']) ? $this->conf['limits'] :
                ['10' => '10 Einträge', '25' => '25 Einträge', '50' => '50 Einträge', '100' => '100 Einträge'];
    }

    public function setState()
    {
        if ($this->init) {
            return;
        }
        $sizes = $this->getLimits();
        $menu = tx_rnbase_util_FormTool::showMenu($this->pid, $this->getDataName().'_limit', $this->modName, $sizes);
        $this->setSetting('limit', $menu['value']);
        $this->setSetting('limitMenu', $menu['menu']);

        $count = $this->listSize;
        $results_at_a_time = $this->getSetting('limit');
        $totalPages = ceil($count / $results_at_a_time);
        // Wir zeigen erstmal maximal 200 Einträge in diesem Menu. Bei sehr großen Listen
        // kommt es sonst zu Speicher-Problemen
        $pages = [];
        for ($i = 0; $i < $totalPages; ++$i) {
            if ($i > 200) {
                break;
            }
            $pages[$i * $results_at_a_time] = 'Seite '.$i;
        }
        $menu = tx_rnbase_util_FormTool::showMenu($this->pid, $this->getDataName().'_offset', $this->modName, $pages);
        $this->setSetting('offset', $menu['value']);
        $this->setSetting('offsetMenu', $menu['menu']);
        $this->init = true;
    }

    /**
     * Liefert die Eingabeelemente des Pagers. Das sind die Auswahlbox der Seitengrösse
     * und die Seitenauswahl. Das Rückgabearray hat zwei Keys: pager und options.
     *
     * @return array
     */
    public function render()
    {
        $this->setState();
        $ret = [];
        $ret['limits'] = $this->getSetting('limitMenu');
        $ret['pages'] = $this->getSetting('offsetMenu');

        return $ret;
    }

    public function getDataName()
    {
        return $this->id.'data';
    }
}
