<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2016 Rene Nitzsche (rene@system25.de)
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

// Die Datenbank-Klasse
tx_rnbase::load('tx_rnbase_model_base');

class tx_rnbase_model_media extends tx_rnbase_model_base
{
    public $uid;
    public $record;

    public function __construct($rowOrUid)
    {
        if (is_object($rowOrUid)) {
            // Das Media-Objekt auslesen
            $this->initMedia($rowOrUid);
        } else {
            parent::__construct($rowOrUid);
        }
        $this->initAdditionalData();
    }

    private function initMedia($media)
    {
        // Bei FAL steckt in Media eine Referenz
        if ($media instanceof TYPO3\CMS\Core\Resource\FileReference) {
            $this->initFalReference($media);
        } else {
            $this->initFalFile($media);
        }
    }

    /**
     * @param TYPO3\CMS\Core\Resource\File $media
     */
    private function initFalFile($media)
    {
        $this->record = $media->getProperties();
        $this->uid = $media->getUid();
        $this->record['fal_file'] = '1'; // Das wird per TS ausgewertet. Die UID ist KEINE Referenz
        $this->record['uid_local'] = $media->getUid();
        $this->record['file_path'] = $media->getPublicUrl();
        $this->record['file_abs_url'] = tx_rnbase_util_Misc::getIndpEnv('TYPO3_SITE_URL').$this->record['file_path'];
    }

    /**
     * @param TYPO3\CMS\Core\Resource\FileReference $media
     */
    private function initFalReference($media)
    {
        $this->record = $media->getProperties();
        // Wir verwenden hier die UID der Referenz
        $this->uid = $media->getUid();
        $this->record['uid'] = $media->getUid();
        $this->record['file_path'] = $media->getPublicUrl();
        $this->record['file_abs_url'] = tx_rnbase_util_Misc::getIndpEnv('TYPO3_SITE_URL').$this->record['file_path'];
    }

    private function initAdditionalData()
    {
        $this->record['file'] = urldecode($this->record['file_path'].$this->record['file_name']);
        // Some more file fields are useful
        $this->record['file1'] = $this->record['file'];
        $this->record['thumbnail'] = $this->record['file'];
    }

    /**
     * Kindklassen müssen diese Methode überschreiben und den Namen der gemappten Tabelle liefern!
     *
     * @return string Tabellenname
     */
    public function getTableName()
    {
        return 'tx_dam';
    }
}
