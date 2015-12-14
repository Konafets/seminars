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
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_registrationchild extends tx_seminars_registration {
	/**
	 * The constructor.
	 *
	 * @param int $registrationUid UID of the registration record, must be > 0
	 */
	public function __construct($registrationUid = 0) {
		if ($registrationUid > 0) {
			$dbResult = tx_oelib_db::select(
				'*',
				$this->tableName,
				'uid = ' . $registrationUid
			);
		} else {
			$dbResult = FALSE;
		}

		$contentObject = new \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer();
		$contentObject->start(array());

		parent::__construct($contentObject, $dbResult);
	}

	/**
	 * Sets the "registration_queue" field of the registration record.
	 *
	 * @param bool $isOnRegistrationQueueValue TRUE if the registration should be on the waiting list, FALSE otherwise
	 *
	 * @return void
	 */
	public function setIsOnRegistrationQueue($isOnRegistrationQueueValue) {
		$this->setRecordPropertyInteger(
			'registration_queue',
			(int)$isOnRegistrationQueueValue
		);
	}

	/**
	 * Sets the payment method of this registration.
	 *
	 * @param int $uid the UID of the payment method to set
	 *
	 * @return void
	 */
	public function setPaymentMethod($uid) {
		if ($uid <= 0) {
			throw new InvalidArgumentException('Invalid payment method UID.', 1333293343);
		}

		$this->setRecordPropertyInteger(
			'method_of_payment',
			$uid
		);
	}

	/**
	 * Sets the data of the FE user of this registration.
	 *
	 * @param array $userData data of the front-end user, may be empty
	 *
	 * @return void
	 */
	public function setUserData(array $userData) {
		parent::setUserData($userData);
	}

	/**
	 * Returns the content of the member variable foods.
	 *
	 * @return int[] the content of the member variable foods, will be empty if foods is empty
	 */
	public function getFoodsData() {
		return $this->foods;
	}

	/**
	 * Returns the content of the member variable lodgings.
	 *
	 * @return int[] the content of the member variable lodgings, will be empty if lodgings is empty
	 */
	public function getLodgingsData() {
		return $this->lodgings;
	}

	/**
	 * Returns the content of the member variable checkboxes.
	 *
	 * @return int[] the content of the member variable checkboxes, will be empty if checkboxes is empty
	 */
	public function getCheckboxesData() {
		return $this->checkboxes;
	}

	/**
	 * Sets the value for 'registered_themselves'.
	 *
	 * @param bool $registeredThemselves the value for the registered_themselves property
	 *
	 * @return void
	 */
	public function setRegisteredThemselves($registeredThemselves) {
		$this->setRecordPropertyBoolean('registered_themselves', $registeredThemselves);
	}
}