<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_rnbase_util_Typo3Classes');

class tx_rnbase_tests_listbuilder_testcase extends tx_rnbase_tests_BaseTestCase
{
    public function setup()
    {
        unset($GLOBALS['TSFE']);
        tx_rnbase_util_Misc::prepareTSFE();
    }

    public function test_advList()
    {
        $items = [];
        $confArr = [];
        $configurations = $this->getConfig($confArr);
        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $html = $listBuilder->render(
            $items,
            false,
            self::$advTemplate,
            'tx_rnbase_util_MediaMarker',
            'media.pic.',
            'PIC',
            $configurations->getFormatter()
        );
        $this->assertEquals($html, self::$listAdvEmpty, 'Leere Liste ist falsch');

        $items = $this->getModels();
        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $html = $listBuilder->render(
            $items,
            false,
            self::$advTemplate,
            'tx_rnbase_util_MediaMarker',
            'media.pic.',
            'PIC',
            $configurations->getFormatter()
        );
        $this->assertEquals($html, self::$listAdvFilled, 'Liste ist falsch');
    }

    public function test_multiSubpartList()
    {
        $items = [];
        $confArr = [];
        $configurations = $this->getConfig($confArr);
        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $html = $listBuilder->render(
            $items,
            false,
            self::$multiSubpartTemplate,
            'tx_rnbase_util_MediaMarker',
            'media.pic.',
            'PIC',
            $configurations->getFormatter()
        );

        $this->assertEquals($html, self::$listMultiSubpartEmpty, 'Leere Liste ist falsch');

        $items = $this->getModels();
        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $html = $listBuilder->render(
            $items,
            false,
            self::$multiSubpartTemplate,
            'tx_rnbase_util_MediaMarker',
            'media.pic.',
            'PIC',
            $configurations->getFormatter()
        );
        $this->assertEquals($html, self::$listMultiSubpartFilled, 'Liste ist falsch');
    }

    public function test_simpleList()
    {
        $items = [];
        $confArr = [];
        $configurations = $this->getConfig($confArr);
        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $html = $listBuilder->render(
            $items,
            false,
            self::$template,
            'tx_rnbase_util_MediaMarker',
            'media.pic.',
            'PIC',
            $configurations->getFormatter()
        );

        $this->assertEquals($html, self::$listEmpty, 'Leere Liste ist falsch');

        $items = $this->getModels();
        $html = $listBuilder->render(
            $items,
            false,
            self::$template,
            'tx_rnbase_util_MediaMarker',
            'media.pic.',
            'PIC',
            $configurations->getFormatter()
        );
        $this->assertEquals($html, self::$listSimple, 'Einfache Liste ist falsch');
    }

    private function getModels()
    {
        $models = [];
        tx_rnbase::load('tx_rnbase_model_media');
        $models[] = new tx_rnbase_model_media(['uid' => 22, 'file_name' => 'file22.jpg']);
        $models[] = new tx_rnbase_model_media(['uid' => 33, 'file_name' => 'file33.jpg']);

        return $models;
    }

    private function getConfig($confArr)
    {
        $cObj = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getContentObjectRendererClass());
        $configurations = tx_rnbase::makeInstance('Tx_Rnbase_Configuration_Processor');
        $configurations->init($confArr, $cObj, 'tx_rnbase', 'rnbase');

        return $configurations;
    }

    public static $template = '
<html>
<h1>Bilder</h1>
###PICS###
<ul>
###PIC###
<li>###PIC_UID###: ###PIC_FILE###</li>###PIC###
</ul>
###PICS###
</html>
';

    public static $advTemplate = '
<html>
<h1>Bilder</h1>
###PICS###
<ul>
###PIC###
<li>###PIC_UID###: ###PIC_FILE###</li>###PIC###
</ul>
###PICEMPTYLIST###No pics found!###PICEMPTYLIST###
###PICS###
</html>
';

    public static $multiSubpartTemplate = '
<html>
<h1>Bilder</h1>
###PICS###
<ul>
###PIC###
<li>###PIC_UID###: ###PIC_FILE###</li>###PIC###
</ul>
###PICEMPTYLIST###No list pics found!###PICEMPTYLIST###
###PICS###

<h1>Bilder 2</h1>
###PICS###
<table><tr>
###PIC###
<td>###PIC_UID###</td><td>###PIC_FILE###</td>###PIC###
</tr></table>
###PICEMPTYLIST###No table pics found!###PICEMPTYLIST###
###PICS###
</html>
';

    public static $listEmpty = '
<html>
<h1>Bilder</h1>

</html>
';

    public static $listMultiSubpartEmpty = '
<html>
<h1>Bilder</h1>
No list pics found!

<h1>Bilder 2</h1>
No table pics found!
</html>
';

    public static $listMultiSubpartFilled = '
<html>
<h1>Bilder</h1>

<ul>

<li>22: file22.jpg</li>
<li>33: file33.jpg</li>
</ul>



<h1>Bilder 2</h1>

<table><tr>

<td>22</td><td>file22.jpg</td>
<td>33</td><td>file33.jpg</td>
</tr></table>


</html>
';

    public static $listAdvEmpty = '
<html>
<h1>Bilder</h1>
No pics found!
</html>
';

    public static $listAdvFilled = '
<html>
<h1>Bilder</h1>

<ul>

<li>22: file22.jpg</li>
<li>33: file33.jpg</li>
</ul>


</html>
';

    public static $listSimple = '
<html>
<h1>Bilder</h1>

<ul>

<li>22: file22.jpg</li>
<li>33: file33.jpg</li>
</ul>

</html>
';
}
