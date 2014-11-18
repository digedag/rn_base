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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');


tx_rnbase::load('tx_rnbase_util_Misc');


class tx_rnbase_tests_listbuilder_testcase extends tx_phpunit_testcase {

	function setup() {
		unset($GLOBALS['TSFE']);
		tx_rnbase_util_Misc::prepareTSFE();
	}
	function test_advList() {
		$items = array();
		$confArr = array();
		$configurations = $this->getConfig($confArr);
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$html = $listBuilder->render($items,
						FALSE, self::$advTemplate, 'tx_rnbase_util_MediaMarker',
						'media.pic.', 'PIC', $configurations->getFormatter());
		$this->assertEquals($html, self::$listAdvEmpty, 'Leere Liste ist falsch');

		$items = $this->getModels();
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$html = $listBuilder->render($items,
						FALSE, self::$advTemplate, 'tx_rnbase_util_MediaMarker',
						'media.pic.', 'PIC', $configurations->getFormatter());
		$this->assertEquals($html, self::$listAdvFilled, 'Liste ist falsch');
	}

	function test_multiSubpartList() {
		$items = array();
		$confArr = array();
		$configurations = $this->getConfig($confArr);
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$html = $listBuilder->render($items,
						FALSE, self::$multiSubpartTemplate, 'tx_rnbase_util_MediaMarker',
						'media.pic.', 'PIC', $configurations->getFormatter());

		$this->assertEquals($html, self::$listMultiSubpartEmpty, 'Leere Liste ist falsch');

		$items = $this->getModels();
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$html = $listBuilder->render($items,
						FALSE, self::$multiSubpartTemplate, 'tx_rnbase_util_MediaMarker',
						'media.pic.', 'PIC', $configurations->getFormatter());
		$this->assertEquals($html, self::$listMultiSubpartFilled, 'Liste ist falsch');
	}

	function test_simpleList() {
		$items = array();
		$confArr = array();
		$configurations = $this->getConfig($confArr);
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$html = $listBuilder->render($items,
						FALSE, self::$template, 'tx_rnbase_util_MediaMarker',
						'media.pic.', 'PIC', $configurations->getFormatter());

		$this->assertEquals($html, self::$listEmpty, 'Leere Liste ist falsch');

		$items = $this->getModels();
		$html = $listBuilder->render($items,
						FALSE, self::$template, 'tx_rnbase_util_MediaMarker',
						'media.pic.', 'PIC', $configurations->getFormatter());
		$this->assertEquals($html, self::$listSimple, 'Einfache Liste ist falsch');

	}

	private function getModels() {
		$models = array();
		tx_rnbase::load('tx_rnbase_model_media');
		$models[] = new tx_rnbase_model_media(array('uid'=>22, 'file_name'=>'file22.jpg'));
		$models[] = new tx_rnbase_model_media(array('uid'=>33, 'file_name'=>'file33.jpg'));
		return $models;
	}
	private function getConfig($confArr) {
    $cObj = t3lib_div::makeInstance('tslib_cObj');
		$configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
    $configurations->init($confArr, $cObj, 'tx_rnbase', 'rnbase');
		return $configurations;
	}

	static $template = '
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

	static $advTemplate = '
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

	static $multiSubpartTemplate = '
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

	static $listEmpty = '
<html>
<h1>Bilder</h1>

</html>
';

	static $listMultiSubpartEmpty = '
<html>
<h1>Bilder</h1>
No list pics found!

<h1>Bilder 2</h1>
No table pics found!
</html>
';

static $listMultiSubpartFilled = '
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

	static $listAdvEmpty = '
<html>
<h1>Bilder</h1>
No pics found!
</html>
';

	static $listAdvFilled = '
<html>
<h1>Bilder</h1>

<ul>

<li>22: file22.jpg</li>
<li>33: file33.jpg</li>
</ul>


</html>
';

	static $listSimple = '
<html>
<h1>Bilder</h1>

<ul>

<li>22: file22.jpg</li>
<li>33: file33.jpg</li>
</ul>

</html>
';
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_listbuilder_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_listbuilder_testcase.php']);
}

