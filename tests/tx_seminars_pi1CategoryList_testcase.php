<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_pi1CategoryList.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the 'pi1CategoryList' class in the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 * @author		Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_pi1CategoryList_testcase extends tx_phpunit_testcase {
	/**
	 * @var	tx_seminars_pi1
	 */
	private $fixture;
	/**
	 * @var	tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var	integer		the UID of a seminar to which the fixture relates
	 */
	private $seminarUid;

	/**
	 * @var	integer		PID of a dummy system folder
	 */
	private $systemFolderPid = 0;

	public function setUp() {
		$this->testingFramework	= new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->systemFolderPid = $this->testingFramework->createSystemFolder();
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Test event',
			)
		);

		$this->fixture = new tx_seminars_pi1CategoryList(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
				'pages' => $this->systemFolderPid,
				'pidList' => $this->systemFolderPid,
				'recursive' => 1,
			),
			$GLOBALS['TSFE']->cObj
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////
	// Tests for createCategoryList()
	///////////////////////////////////

	public function testCreateCategoryListCreatesEmptyCategoryList() {
		$otherSystemFolderUid = $this->testingFramework->createSystemFolder();
		$this->fixture->setConfigurationValue('pages', $otherSystemFolderUid);

		$output = $this->fixture->createCategoryList();

		$this->assertNotContains(
			'<table',
			$output
		);
		$this->assertContains(
			$this->fixture->translate('label_no_categories'),
			$output
		);
	}

	public function testCreateCategoryListCreatesCategoryListContainingOneCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCreateCategoryListCreatesCategoryListContainingTwoCategoryTitles() {
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'first category')
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'second category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 2
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid2
		);

		$output = $this->fixture->createCategoryList();
		$this->assertContains(
			'first category',
			$output
		);
		$this->assertContains(
			'second category',
			$output
		);
	}

	public function testCreateCategoryListCreatesCategoryListWhichIsSortedAlphabetically() {
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'category B')
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'category A')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 2
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid2
		);

		$output = $this->fixture->createCategoryList();
		$this->assertTrue(
			strpos($output, 'category A') < strpos($output, 'category B')
		);
	}

	public function testCreateCategoryListCreatesCategoryListByUsingRecursion() {
		$systemSubFolderUid = $this->testingFramework->createSystemFolder(
			$this->systemFolderPid
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $systemSubFolderUid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCreateCategoryListIgnoresOtherSysFolders() {
		$otherSystemFolderUid = $this->testingFramework->createSystemFolder();
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $otherSystemFolderUid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertNotContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCreateCategoryListCanReadFromAllSystemFolders() {
		$this->fixture->setConfigurationValue('pages', '');

		$otherSystemFolderUid = $this->testingFramework->createSystemFolder();
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $otherSystemFolderUid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCreateCategoryListIgnoresCanceledEvents() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1,
				'cancelled' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertNotContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCreateCategoryListCreatesCategoryListOfEventsFromSelectedTimeFrames() {
		$this->fixture->setConfigurationValue(
			'timeframeInList', 'currentAndUpcoming'
		);

		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'end_date' => mktime() + 2000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCreateCategoryListIgnoresEventsFromDeselectedTimeFrames() {
		$this->fixture->setConfigurationValue(
			'timeframeInList', 'currentAndUpcoming'
		);

		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() - 2000,
				'end_date' => mktime() - 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertNotContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCreateCategoryListCreatesCategoryListContainingLinksToListPageLimitedToCategory() {
		$this->fixture->setConfigurationValue(
			'listPID', $this->testingFramework->createFrontEndPage()
		);

		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertContains(
			'tx_seminars_pi1[category]='.$categoryUid,
			$this->fixture->createCategoryList()
		);
	}
}
?>