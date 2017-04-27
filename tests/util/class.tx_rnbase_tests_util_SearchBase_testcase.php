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

tx_rnbase::load('tx_rnbase_util_DB');

class tx_rnbase_tests_util_SearchBase_testcase extends Tx_Phpunit_TestCase {
	function test_searchFieldJoinedWithoutValue() {
		$searcher = $this->getGenericSearcher();
		$options = $this->createOptions();

		// Test 1: Fehlerhaften Code in t3users korrigieren. Ein falsch gesetztes SEARCH_FIELD_JOINED muss ignoriert werden
		$fields = array();
		$fields[SEARCH_FIELD_JOINED][0]['cols'][] = 'FEUSER.UID';
		$ret = $searcher->search($fields, $options);

		$this->assertTrue(strpos($ret, 'AND  AND') === FALSE, 'SQL is wrong');
	}

	/**
	 */
	public function testSearchForCount() {
		$searcher = $this->getGenericSearcher();
		$fields = array('FEUSER.uid' => array(OP_EQ_INT => 54));
		$options = $this->createOptions();
		$options['count'] = TRUE;

		// SELECT FEUSER.* FROM fe_users AS FEUSER WHERE 1=1 AND FEUSER.uid = 54;
		$query = $searcher->search($fields, $options);


		// the count should be at the first
		$this->assertSame(0, strpos($query, 'SELECT count(*) as cnt FROM'));
		// check the uid where
		$this->assertContains(' FEUSER.uid = 54 ', $query);


		// SELECT FEUSER.* FROM fe_users AS FEUSER WHERE 1=1 AND FEUSER.uid = 54 GROUP BY FEUSER.usergroup
		$options['groupby'] = 'FEUSER.usergroup';
		$query = $searcher->search($fields, $options);#


		// the count with the subselect should be at the first
		$this->assertSame(0, strpos($query, 'SELECT COUNT(*) AS cnt FROM'));
		// check the uid where
		$this->assertContains(' FEUSER.uid = 54 ', $query);
		$this->assertContains(' GROUP BY FEUSER.usergroup', $query);
		// the closing subselect should be at the last
		$this->assertEquals(
			strlen($query) - strlen(') AS COUNTWRAP WHERE 1=1'),
			strpos($query, ') AS COUNTWRAP WHERE 1=1')
		);


		// SELECT COUNT(FEUSER.uid) FROM fe_users AS FEUSER WHERE 1=1 AND FEUSER.uid = 54 GROUP BY FEUSER.usergroup
		$options['what'] = 'FEUSER.*, COUNT(FEUSER.uid) as usercount';
		$query = $searcher->search($fields, $options);#

		// the count with the subselect should be at the first
		$this->assertSame(0, strpos($query, 'SELECT COUNT(*) AS cnt FROM'));
		// check the uid where
		$this->assertContains(' FEUSER.*, COUNT(FEUSER.uid) as usercount ', $query);
		$this->assertContains(' FEUSER.uid = 54 ', $query);
		$this->assertContains(' GROUP BY FEUSER.usergroup', $query);
		// the closing subselect should be at the last
		$this->assertEquals(
			strlen($query) - strlen(') AS COUNTWRAP WHERE 1=1'),
			strpos($query, ') AS COUNTWRAP WHERE 1=1')
		);


		// SELECT FEUSER.*, COUNT(FEUSER.uid) as usercount FROM fe_users AS FEUSER WHERE 1=1 AND FEUSER.uid = 54 GROUP BY FEUSER.usergroup HAVING usercount > 20
		$options['having'] = 'usercount > 20';
		$query = $searcher->search($fields, $options);

		// the count with the subselect should be at the first
		$this->assertSame(0, strpos($query, 'SELECT COUNT(*) AS cnt FROM'));
		// check the uid where
		$this->assertContains(' FEUSER.*, COUNT(FEUSER.uid) as usercount ', $query);
		$this->assertContains(' FEUSER.uid = 54 ', $query);
		$this->assertContains(' GROUP BY FEUSER.usergroup ', $query);
		$this->assertContains(' HAVING usercount > 20', $query);
		// the closing subselect should be at the last
		$this->assertEquals(
			strlen($query) - strlen(') AS COUNTWRAP WHERE 1=1'),
			strpos($query, ') AS COUNTWRAP WHERE 1=1')
		);

		// SELECT FEUSER.* FROM fe_users AS FEUSER WHERE 1=1 AND FEUSER.uid = 54 GROUP BY FEUSER.usergroup
		$options = $this->createOptions();
		$options['count'] = TRUE;
		$options['disableCountWrap'] = TRUE;
		$options['groupby'] = 'FEUSER.usergroup';
		$query = $searcher->search($fields, $options);

		// the count with the subselect should be at the first
		$this->assertSame(0, strpos($query, 'SELECT count(*) as cnt FROM'));
		// check the uid where
		$this->assertContains(' FEUSER.uid = 54 ', $query);
		$this->assertContains(' GROUP BY FEUSER.usergroup', $query);
		// the closing subselect should be at the last
		$this->assertEquals(
			strlen($query) - strlen('BY FEUSER.usergroup'),
			strpos($query, 'BY FEUSER.usergroup')
		);
		$this->assertNotContains('COUNTWRAP', $query, 'COUNTWRAP doch enthalten');
	}

	private function createOptions() {
		$options = array();
		$options['sqlonly'] = TRUE;
		$options['searchdef']['basetable'] = 'fe_users';
		$options['searchdef']['usealias'] = TRUE;
		$options['searchdef']['basetablealias'] = 'FEUSER';
		$options['orderby']['username'] = 'asc';

		return $options;
	}
	/**
	 *
	 * @return tx_rnbase_util_SearchGeneric
	 */
	private function getGenericSearcher() {
		return tx_rnbase::makeInstance('tx_rnbase_util_SearchGeneric');
	}

	/**
	 * @group unit
	 */
	public function testSearchSetsEnableFieldsForJoinedTablesIfConfigured() {
		$searcher = tx_rnbase::makeInstance('tx_rnbase_tests_fixtures_classes_Searcher');
		$fields = array(
			'PAGE.uid' => array(OP_GT_INT => 0),
			'CONTENT.uid' => array(OP_GT_INT => 0),
			'FEUSER.uid' => array(OP_GT_INT => 0)
		);
		$options['sqlonly'] = TRUE;
		$options['enableFieldsForAdditionalTableAliases'] = 'CONTENT';

		$query = $searcher->search($fields, $options);

		// typo3 generated sql
		if (tx_rnbase_util_TYPO3::isTYPO86OrHigher()) {
			// doctrine
			self::assertContains('`pages`.`deleted` = 0', $query, 'deleted for pages is missing');
			self::assertContains('`tt_content`.`deleted` = 0', $query, 'deleted for tt_content is missing');
			self::assertNotContains('`fe_users`.`deleted` = 0', $query, 'deleted for fe_users is present');
		} else {
			// old direct mysqli
			self::assertContains('pages.deleted=0', $query, 'deleted for pages is missing');
			self::assertContains('tt_content.deleted=0', $query, 'deleted for tt_content is missing');
			self::assertNotContains('fe_users.deleted=0', $query, 'deleted for fe_users is present');
		}
		// non typo3 generated sql
		self::assertContains('tt_content.pid >=0', $query, 'pid for tt_content is missing');
	}

	/**
	 * @group unit
	 */
	public function testSearchSetsEnableFieldsForJoinedTablesIfConfiguredAndUseAliasIsTrue() {
		$searcher = tx_rnbase::makeInstance('tx_rnbase_tests_fixtures_classes_Searcher');
		$searcher->setUseAlias(TRUE);
		$fields = array(
			'PAGE.uid' => array(OP_GT_INT => 0),
			'CONTENT.uid' => array(OP_GT_INT => 0),
			'FEUSER.uid' => array(OP_GT_INT => 0)
		);
		$options['sqlonly'] = TRUE;
		$options['enableFieldsForAdditionalTableAliases'] = 'CONTENT';

		$query = $searcher->search($fields, $options);

		// typo3 generated sql
		if (tx_rnbase_util_TYPO3::isTYPO86OrHigher()) {
			// doctrine
			self::assertContains('`PAGE`.`deleted` = 0', $query, 'deleted for PAGE is missing');
			self::assertContains('`CONTENT`.`deleted` = 0', $query, 'deleted for CONTENT is missing');
			self::assertNotContains('`FEUSER`.`deleted` = 0', $query, 'deleted for FEUSER is present');
		} else {
			// old direct mysqli
			self::assertContains('PAGE.deleted=0', $query, 'deleted for PAGE is missing');
			self::assertContains('CONTENT.deleted=0', $query, 'deleted for CONTENT is missing');
			self::assertNotContains('FEUSER.deleted=0', $query, 'deleted for FEUSER is present');
		}
		// non typo3 generated sql
		self::assertContains('CONTENT.pid >=0', $query, 'pid for CONTENT is missing');
	}

	/**
	 * @group unit
	 */
	public function testSearchSetsEnableFieldsForJoinedTablesIfConfiguredWithMoreThanOne() {
		$searcher = tx_rnbase::makeInstance('tx_rnbase_tests_fixtures_classes_Searcher');
		$searcher->setUseAlias(TRUE);
		$fields = array(
			'PAGE.uid' => array(OP_GT_INT => 0),
			'CONTENT.uid' => array(OP_GT_INT => 0),
			'FEUSER.uid' => array(OP_GT_INT => 0)
		);
		$options['sqlonly'] = TRUE;
		$options['enableFieldsForAdditionalTableAliases'] = 'CONTENT,FEUSER';

		$query = $searcher->search($fields, $options);

		// typo3 generated sql
		if (tx_rnbase_util_TYPO3::isTYPO86OrHigher()) {
			// doctrine
			self::assertContains('`PAGE`.`deleted` = 0', $query, 'deleted for PAGE is missing');
			self::assertContains('`CONTENT`.`deleted` = 0', $query, 'deleted for CONTENT is missing');
			self::assertContains('`FEUSER`.`deleted` = 0', $query, 'deleted for FEUSER is not present');
		} else {
			// old direct mysqli
			self::assertContains('PAGE.deleted=0', $query, 'deleted for PAGE is missing');
			self::assertContains('CONTENT.deleted=0', $query, 'deleted for CONTENT is missing');
			self::assertContains('FEUSER.deleted=0', $query, 'deleted for FEUSER is not present');
		}
		// non typo3 generated sql
		self::assertContains('CONTENT.pid >=0', $query, 'pid for CONTENT is missing');
	}

	/**
	 * @group unit
	 */
	public function testSearchSetsEnableFieldsForJoinedTablesNotIfNotConfigured() {
		$searcher = tx_rnbase::makeInstance('tx_rnbase_tests_fixtures_classes_Searcher');
		$searcher->setUseAlias(TRUE);
		$fields = array(
			'PAGE.uid' => array(OP_GT_INT => 0),
			'CONTENT.uid' => array(OP_GT_INT => 0),
			'FEUSER.uid' => array(OP_GT_INT => 0)
		);
		$options['sqlonly'] = TRUE;

		$query = $searcher->search($fields, $options);

		// typo3 generated sql
		if (tx_rnbase_util_TYPO3::isTYPO86OrHigher()) {
			// doctrine
			self::assertContains('`PAGE`.`deleted` = 0', $query, 'deleted for PAGE is missing');
			self::assertNotContains('`CONTENT`.`deleted` = 0', $query, 'deleted for CONTENT is present');
			self::assertNotContains('`FEUSER`.`deleted` = 0', $query, 'deleted for FEUSER is present');
		} else {
			// old direct mysqli
			self::assertContains('PAGE.deleted=0', $query, 'deleted for PAGE is missing');
			self::assertNotContains('CONTENT.deleted=0', $query, 'deleted for CONTENT is present');
			self::assertNotContains('FEUSER.deleted=0', $query, 'deleted for FEUSER is present');
		}
		// non typo3 generated sql

		self::assertNotContains('CONTENT.pid >=0', $query, 'pid for CONTENT is present');

	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_utilSearchBase_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/tests/class.tx_rnbase_tests_utilSearchBase_testcase.php']);
}
