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
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Mapper_FrontEndUserTest extends tx_phpunit_testcase {
	/**
	 * @var Tx_Seminars_Mapper_FrontEndUser the object to test
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = tx_oelib_MapperRegistry::get(
			'Tx_Seminars_Mapper_FrontEndUser'
		);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	//////////////////////////////////////
	// Tests for the basic functionality
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function mapperForGhostReturnsSeminarsFrontEndUserInstance() {
		self::assertTrue(
			$this->fixture->getNewGhost()
				instanceof Tx_Seminars_Model_FrontEndUser
		);
	}


	///////////////////////////////////
	// Tests concerning the relations
	///////////////////////////////////

	/**
	 * @test
	 */
	public function relationToRegistrationIsReadFromRegistrationMapper() {
		$registration = tx_oelib_MapperRegistry
			::get('Tx_Seminars_Mapper_Registration')->getNewGhost();

		$model = $this->fixture->getLoadedTestingModel(
			array('Tx_Seminars_Registration' => $registration->getUid())
		);

		self::assertSame(
			$registration,
			$model->getRegistration()
		);
	}
}