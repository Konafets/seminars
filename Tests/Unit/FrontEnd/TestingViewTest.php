<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_TestingViewTest extends tx_phpunit_testcase {
	/**
	 * the fixture to test
	 *
	 * @var Tx_Seminars_Tests_Fixtures_FrontEnd_TestingView
	 */
	private $fixture;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();
		$this->fixture = new Tx_Seminars_Tests_Fixtures_FrontEnd_TestingView(
			array('templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'),
			$GLOBALS['TSFE']->cObj
		);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}

	public function testRenderCanReturnAViewsContent() {
		self::assertEquals(
			'Hi, I am the testingFrontEndView!',
			$this->fixture->render()
		);
	}
}