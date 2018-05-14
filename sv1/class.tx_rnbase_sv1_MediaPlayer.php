<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Rene Nitzsche <rene@system25.de>
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
tx_rnbase::load('Tx_Rnbase_Service_Base');

/**
 * Service "MediaPlayer" for playing mp3 media files.
 *
 * @author  Rene Nitzsche <rene@system25.de>
 * @package     TYPO3
 * @subpackage  tx_cfcleaguefe
 */
class tx_rnbase_sv1_MediaPlayer extends Tx_Rnbase_Service_Base
{
    public $prefixId = 'tx_rnbase_sv1_MediaPlayer';  // Same as class name
    public $scriptRelPath = 'sv1/class.tx_rnbase_sv1_MediaPlayer.php'; // Path to this script relative to the extension dir.
    public $extKey = 'rn_base'; // The extension key.

  /**
   * [Put your description here]
   *
   * @return    [type]      ...
   */
    public function init()
    {
        $available = parent::init();
        // Here you can initialize your class.
        // The class have to do a strict check if the service is available.
        // The needed external programs are already checked in the parent class.
        // If there's no reason for initialization you can remove this function.
        return $available;
    }

    public function getPlayer($media, $conf)
    {
        $mediaName = $media['file_name'];
        $mediaPath = $media['file_path'];

    // Die genaue Konfig holen wir für den Dateityp
        $fileType = $media['file_type'];
        $playerConf = $conf[$fileType . '.']['player.'];

        $color = $playerConf['backgroundColor'];
        $autoStart = $playerConf['autoStart'];
        $autoReplay = $playerConf['autoReplay'];

        tx_rnbase::load('tx_rnbase_util_Extensions');
        $fePath = tx_rnbase_util_Extensions::siteRelPath('rn_base') . 'sv1/';

        $out = '<object type="application/x-shockwave-flash" data="/'.
            $fePath. 'dewplayer.swf?son='.
           $mediaPath . $mediaName .
           '&amp;autostart=' . $autoStart.
           '&amp;autoreplay=' . $autoReplay.
           '&amp;bgcolor='. $color .
           '" width="200" height="20"><param name="movie" value="/'.
            $fePath.'dewplayer.swf?son='.
            $mediaPath . $mediaName.
              '&amp;autostart='.$autoStart.
              '&amp;autoreplay='.$autoReplay.
              '&amp;bgcolor='.$color.
              '" />'. $media['title'] .'</object>';

        return $out;
    }
}

