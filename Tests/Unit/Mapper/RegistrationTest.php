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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Mapper_RegistrationTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var Tx_Seminars_Mapper_Registration
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new Tx_Seminars_Mapper_Registration();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	/**
	 * @test
	 */
	public function findWithUidReturnsRegistrationInstance() {
		self::assertTrue(
			$this->fixture->find(1) instanceof Tx_Seminars_Model_Registration
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsRecordAsModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('title' => 'registration for event')
		);

		/** @var Tx_Seminars_Model_Registration $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			'registration for event',
			$model->getTitle()
		);
	}


	////////////////////////////////
	// Tests concerning the event.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getEventWithEventReturnsEventInstance() {
		$event = tx_oelib_MapperRegistry::get('Tx_Seminars_Mapper_Event')
			->getNewGhost();

		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('seminar' => $event->getUid())
			)->getEvent() instanceof
				Tx_Seminars_Model_Event
		);
	}

	/**
	 * @test
	 */
	public function getSeminarWithEventReturnsEventInstance() {
		$event = tx_oelib_MapperRegistry::get('Tx_Seminars_Mapper_Event')
			->getNewGhost();

		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('seminar' => $event->getUid())
			)->getSeminar() instanceof
				Tx_Seminars_Model_Event
		);
	}


	/////////////////////////////////////////
	// Tests concerning the front-end user.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getFrontEndUserWithFrontEndUserReturnsFrontEndUserInstance() {
		$frontEndUser = tx_oelib_MapperRegistry::
			get('Tx_Seminars_Mapper_FrontEndUser')->getNewGhost();

		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('user' => $frontEndUser->getUid())
			)->getFrontEndUser() instanceof
				Tx_Seminars_Model_FrontEndUser
		);
	}


	/////////////////////////////////////////
	// Tests concerning the payment method.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPaymentMethodWithoutPaymentMethodReturnsNull() {
		self::assertNull(
			$this->fixture->getLoadedTestingModel(array())->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodWithPaymentMethodReturnsPaymentMethodInstance() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('Tx_Seminars_Mapper_PaymentMethod')->getNewGhost();

		self::assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('method_of_payment' => $paymentMethod->getUid())
			)->getPaymentMethod() instanceof
				Tx_Seminars_Model_PaymentMethod
		);
	}


	///////////////////////////////////
	// Tests concerning the lodgings.
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getLodgingsReturnsListInstance() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getLodgings()
				instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getLodgingsWithOneLodgingReturnsListOfLodgings() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$lodging = tx_oelib_MapperRegistry::get('Tx_Seminars_Mapper_Lodging')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $lodging->getUid(), 'lodgings'
		);

		/** @var Tx_Seminars_Model_Registration $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getLodgings()->first() instanceof Tx_Seminars_Model_Lodging
		);
	}

	/**
	 * @test
	 */
	public function getLodgingsWithOneLodgingReturnsOneLodging() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$lodging = tx_oelib_MapperRegistry::get('Tx_Seminars_Mapper_Lodging')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $lodging->getUid(), 'lodgings'
		);

		/** @var Tx_Seminars_Model_Registration $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$lodging->getUid(),
			$model->getLodgings()->first()->getUid()
		);
	}


	////////////////////////////////
	// Tests concerning the foods.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getFoodsReturnsListInstance() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getFoods()
				instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getFoodsWithOneFoodReturnsListOfFoods() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$food = tx_oelib_MapperRegistry::get('Tx_Seminars_Mapper_Food')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $food->getUid(), 'foods'
		);

		/** @var Tx_Seminars_Model_Registration $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getFoods()->first() instanceof Tx_Seminars_Model_Food
		);

	}

	/**
	 * @test
	 */
	public function getFoodsWithOneFoodReturnsOneFood() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$food = tx_oelib_MapperRegistry::get('Tx_Seminars_Mapper_Food')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $food->getUid(), 'foods'
		);

		/** @var Tx_Seminars_Model_Registration $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$food->getUid(),
			$model->getFoods()->first()->getUid()
		);
	}


	/////////////////////////////////////
	// Tests concerning the checkboxes.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getCheckboxesReturnsListInstance() {
		self::assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getCheckboxes()
				instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesWithOneCheckboxReturnsListOfCheckboxes() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$checkbox = tx_oelib_MapperRegistry::get('Tx_Seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $checkbox->getUid(), 'checkboxes'
		);

		/** @var Tx_Seminars_Model_Registration $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$checkbox->getUid(),
			$model->getCheckboxes()->first()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesWithOneCheckboxReturnsOneCheckbox() {
		$uid = $this->testingFramework->createRecord('tx_seminars_attendances');
		$checkbox = tx_oelib_MapperRegistry::get('Tx_Seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_attendances', $uid, $checkbox->getUid(), 'checkboxes'
		);

		/** @var Tx_Seminars_Model_Registration $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$checkbox->getUid(),
			$model->getCheckboxes()->first()->getUid()
		);
	}


	///////////////////////////////////////////////////////////////////////
	// Tests concerning the relation to the additional registered persons
	///////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function relationToAdditionalPersonsReturnsPersonsFromDatabase() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('additional_persons' => 1)
		);
		$personUid = $this->testingFramework->createFrontEndUser(
			'', array('tx_seminars_registration' => $registrationUid)
		);

		/** @var Tx_Seminars_Model_Registration $model */
		$model = $this->fixture->find($registrationUid);
		self::assertEquals(
			(string) $personUid,
			$model->getAdditionalPersons()->getUids()
		);
	}
}