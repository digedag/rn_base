<?php

namespace Sys25\RnBase\Utility;

use Sys25\RnBase\Backend\Utility\Icons;
use tx_rnbase;

/***************************************************************
 * Copyright notice
 *
 *  (c) 2017-2021 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * TCA Util and wrapper methods.
 *
 * @author René Nitzsche
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class WizIcon
{
    /**
     * @param string $clazz
     * @param string $clazzFile
     */
    public static function addWizicon($clazz, $clazzFile)
    {
        $id = 'rn_base';
        if (!TYPO3::isTYPO80OrHigher()) {
            $GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses'][$clazz] = $clazzFile;
        } else {
            $wizard = tx_rnbase::makeInstance($clazz);
            // for TYPO3 8 we need an other way
            // Geht das? Die Methode ist protected...
            $pluginData = $wizard->getPluginData();
            foreach ($pluginData as $id => $data) {
                if (!isset($data['tsconfig'])) {
                    // Noch nicht für 8.x vorbereitet
                    continue;
                }
                Icons::getIconRegistry()->registerIcon(
                    $id.'-icon',
                    'TYPO3\\CMS\Core\\Imaging\\IconProvider\\BitmapIconProvider',
                    ['source' => $data['icon']]
                );
                $configFile = $data['tsconfig'];
                // Wizardkonfiguration hinzufügen
                Extensions::addPageTSConfig(
                    '<INCLUDE_TYPOSCRIPT: source="'.$configFile.'">'
                );
            }
        }
    }

    /**
     * Adds plugin wizard icon.
     *
     * @param array Input array with wizard items for plugins
     *
     * @return array modified input array, having the items for plugins added
     */
    public function proc($wizardItems)
    {
        $lang = $this->includeLocalLang();
        $plugins = $this->getPluginData();
        foreach ($plugins as $id => $plugin) {
            $wizardItems['plugins_'.$id] = [
                'icon' => $plugin['icon'],
                'title' => $lang->getLL($plugin['title']),
                'description' => $lang->getLL($plugin['description']),
                'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]='.$id,
            ];
        }

        return $wizardItems;
    }

    abstract protected function getPluginData();

    abstract protected function getLLFile();

    /**
     * @return Language
     */
    private function includeLocalLang()
    {
        $llFile = $this->getLLFile();
        /** @var Language $lang */
        $lang = tx_rnbase::makeInstance(Language::class);
        $lang->loadLLFile($llFile);

        return $lang;
    }
}
