<?php

namespace Sys25\RnBase\Frontend\Marker;

/***************************************************************
*  Copyright notice
*
*  (c) 2013-2025 Rene Nitzsche (rene@system25.de)
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

use Sys25\RnBase\Configuration\Processor;
use Sys25\RnBase\Domain\Model\BaseModel;
use Sys25\RnBase\Testing\TestUtility;
use Sys25\RnBase\Utility\TYPO3;
use Sys25\RnBase\Utility\TypoScript;
use tx_rnbase;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @author Michael Wagner <mihcael.wagner@das-medienkombinat.de>
 */
class SimpleMarkerTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        // $testbase = new \TYPO3\TestingFramework\Core\Testbase();
        // $testbase->defineOriginalRootPath();

        parent::setUp();

        // Minimalen ServerRequest erzeugen
        $request = new ServerRequest('https://example.com/');

        // Optional: Dummy Site + Language (verhindert weitere Exceptions)
        $site = new NullSite();
        $language = new SiteLanguage(
            0,                                      // languageId
            'en_US.UTF-8',                          // locale
            new Uri('/'),                           // base (als URI)
            [                                       // configuration-Array
                'title' => 'English',
                'navigationTitle' => 'English',
                'flag' => 'gb',
                'languageId' => 0,
                'locale' => 'en_US.UTF-8',
                'base' => '/',
                'iso-639-1' => 'en',
                'hreflang' => 'en-US',
            ]
        );
        $request = $request->withAttribute('site', $site);
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('language', $language);

        $GLOBALS['TYPO3_REQUEST'] = $request;
    }

    /**
     * @group functional
     */
    public function testPrepareSubparts()
    {
        if (!TYPO3::isTYPO121OrHigher()) {
            $this->markTestSkipped('This test is only for TYPO3 12.1 or higher');
        }

        $formatter = $this->buildFormatter();
        $item = tx_rnbase::makeInstance(BaseModel::class, [
            'uid' => 0,
            'fcol' => 'foo',
            'bcol' => 'bar',
        ]);
        // die marker müssen im template vorhanden sein, da diese sonnst nicht gerendert werden
        $template = <<<'HTML'
###ITEM_FCOL_IS_HIDDEN### ITEM_FCOL_IS_HIDDEN ###ITEM_FCOL_IS_HIDDEN###
###ITEM_FCOL_IS_VISIBLE### ITEM_FCOL_IS_VISIBLE ###ITEM_FCOL_IS_VISIBLE###
###ITEM_BCOL_IS_VERSTECKT### ITEM_BCOL_IS_VERSTECKT ###ITEM_BCOL_IS_VERSTECKT###
###ITEM_BCOL_IS_SICHTBAR### ITEM_BCOL_IS_SICHTBAR ###ITEM_BCOL_IS_SICHTBAR###
###ITEM_UNUSED_VISIBLE### ITEM_UNUSED_VISIBLE ###ITEM_UNUSED_VISIBLE###
###ITEM_UNUSED_HIDDEN### ITEM_UNUSED_HIDDEN ###ITEM_UNUSED_HIDDEN###
HTML;
        $marker = tx_rnbase::makeInstance(SimpleMarkerTests::class);
        $wrappedSubpartArray = $subpartArray = [];
        $marker->prepareSubparts(
            $wrappedSubpartArray,
            $subpartArray,
            $template,
            $item,
            $formatter,
            'action.item.',
            'ITEM'
        );

        // auszugebende subparts
        self::assertTrue(array_key_exists('###ITEM_FCOL_IS_HIDDEN###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
        self::assertTrue(is_array($wrappedSubpartArray['###ITEM_FCOL_IS_HIDDEN###']), 'FailedOn:'.__LINE__);
        self::assertEquals('', $wrappedSubpartArray['###ITEM_FCOL_IS_HIDDEN###'][0], 'FailedOn:'.__LINE__);
        self::assertEquals('', $wrappedSubpartArray['###ITEM_FCOL_IS_HIDDEN###'][1], 'FailedOn:'.__LINE__);
        self::assertTrue(array_key_exists('###ITEM_BCOL_IS_VERSTECKT###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
        self::assertTrue(array_key_exists('###ITEM_UNUSED_VISIBLE###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
        self::assertFalse(array_key_exists('###ITEM_FCOL_IS_VISIBLE###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
        self::assertFalse(array_key_exists('###ITEM_BCOL_IS_SICHTBAR###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
        self::assertFalse(array_key_exists('###ITEM_UNUSED_HIDDEN###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);
        self::assertFalse(array_key_exists('###ITEM_NOT_IN_TEMPLATE_HIDDEN###', $wrappedSubpartArray), 'FailedOn:'.__LINE__);

        // subparts, die nicht ausgegeben werden sollen
        self::assertFalse(array_key_exists('###ITEM_FCOL_IS_HIDDEN###', $subpartArray), 'FailedOn:'.__LINE__);
        self::assertFalse(array_key_exists('###ITEM_BCOL_IS_VERSTECKT###', $subpartArray), 'FailedOn:'.__LINE__);
        self::assertFalse(array_key_exists('###ITEM_UNUSED_VISIBLE###', $subpartArray), 'FailedOn:'.__LINE__);
        self::assertTrue(array_key_exists('###ITEM_FCOL_IS_VISIBLE###', $subpartArray), 'FailedOn:'.__LINE__);
        self::assertTrue(is_string('###ITEM_FCOL_IS_VISIBLE###'), 'FailedOn:'.__LINE__);
        self::assertEquals('', $subpartArray['###ITEM_FCOL_IS_VISIBLE###'], 'FailedOn:'.__LINE__);
        self::assertTrue(array_key_exists('###ITEM_BCOL_IS_SICHTBAR###', $subpartArray), 'FailedOn:'.__LINE__);
        self::assertTrue(array_key_exists('###ITEM_UNUSED_HIDDEN###', $subpartArray), 'FailedOn:'.__LINE__);
        self::assertFalse(array_key_exists('###ITEM_NOT_IN_TEMPLATE_HIDDEN###', $subpartArray), 'FailedOn:'.__LINE__);
    }

    /**
     * @group functional
     */
    public function testPrepareItem()
    {
        /** @var SimpleMarker $marker */
        $marker = tx_rnbase::makeInstance(SimpleMarker::class);

        $model = tx_rnbase::makeInstance(
            BaseModel::class,
            [
                'uid' => 1,
                'field' => 'name',
                'field.name' => 'fieldname',
                'fieldname' => 'field.name',
                'dot.name' => 'dotname',
                'dotname' => 'dot.name',
            ]
        );

        $confId = 'hit.';
        $configurations = TestUtility::createConfigurations(
            [
                $confId => [
                    'dataMap.' => [
                        'dotFieldFields' => 'dot.name',
                        'dotValueFields' => 'dotname,unknown',
                    ],
                ],
            ],
            'rn_base'
        );

        $template = <<<'HTML'
HIT_FIELD: ###HIT_FIELD###
HIT_FIELD_NAME: ###HIT_FIELD_NAME###
HIT_FIELDNAME: ###HIT_FIELDNAME###
HIT__UNKNOWN: ###HIT__UNKNOWN###
HTML;

        Templates::disableSubstCache();
        $result = $marker->parseTemplate($template, $model, $configurations->getFormatter(), $confId, 'HIT');
        $array = $model->getRecord();

        self::assertArrayHasKey('field', $array);
        self::assertEquals($array['field'], 'name');

        self::assertArrayHasKey('field.name', $array);
        self::assertEquals($array['field.name'], 'fieldname');

        self::assertArrayHasKey('fieldname', $array);
        self::assertEquals($array['fieldname'], 'field.name');

        self::assertArrayNotHasKey('_field_name', $array);
        self::assertArrayNotHasKey('_fieldname', $array);

        self::assertArrayHasKey('_dot_name', $array);
        self::assertEquals($array['_dot_name'], 'dotname');

        self::assertArrayHasKey('dotname', $array);
        self::assertEquals($array['_dotname'], 'dot_name');

        // auch wenn das feld im record nicht existiert, er muss angelegt werden!
        self::assertArrayHasKey('_unknown', $array);
    }

    /**
     * liefert einen formatter inklusive typoscript.
     *
     * @return FormatUtil
     */
    protected function buildFormatter()
    {
        $typoScript = <<<'TS'
action.item.subparts {
	fcol_is {
		visible = TEXT
		visible.value = 1
		visible.if {
		value = tt_content
			equals.data = field:baz
		}
	}
	bcol_is {
		marker {
			visible = SICHTBAR
			hidden = VERSTECKT
		}
		visible = TEXT
		visible.value = 1
		visible.if {
		value = tt_content
			equals.data = field:bar
		}
	}
	unused {
		visible = 1
	}
	not_in_template {
		visible = 1
	}
}
TS;
        /** @var TypoScript $parser */
        $parser = tx_rnbase::makeInstance(TypoScript::class);
        $configurationArray = $parser->parseTsConfig($typoScript, 'test');
        $configurations = tx_rnbase::makeInstance(Processor::class);
        $configurations->init($configurationArray, null, 'extkey_text', 'rntest');
        $formatter = tx_rnbase::makeInstance(FormatUtil::class, $configurations);

        return $formatter;
    }
}
class SimpleMarkerTests extends SimpleMarker
{
    // die methode public machen.
    // mit einer reflaction funktioniert es nicht, da die parameter als referenzen angelnommen werden müssen!
    public function prepareSubparts(array &$wrappedSubpartArray, array &$subpartArray, $template, $item, $formatter, $confId, $marker)
    {
        parent::prepareSubparts($wrappedSubpartArray, $subpartArray, $template, $item, $formatter, $confId, $marker);
    }
}
