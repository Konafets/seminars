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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class holds functions used to validate submitted forms in the back end.
 *
 * These functions are called from \TYPO3\CMS\Core\DataHandling\DataHandler via hooks.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_TceMainProcDm {
	/**
	 * @var array[]
	 */
	private $tceMainFieldArrays = array();

	/**
	 * @var string the extension key
	 */
	public $extKey = 'seminars';

	/**
	 * Handles data after everything had been written to the database.
	 *
	 * @return void
	 */
	public function processDatamap_afterAllOperations() {
		$this->processTimeSlots();
		$this->processEvents();
	}

	/**
	 * Builds $this->tceMainFieldArrays if the right tables were modified.
	 *
	 * Some of the parameters of this function are not used in this function.
	 * But they are given by the hook in \TYPO3\CMS\Core\DataHandling\DataHandler.
	 *
	 * Note: When using the hook after INSERT operations, you will only get the
	 * temporary NEW... id passed to your hook as $id, but you can easily
	 * translate it to the real uid of the inserted record using the
	 * $pObj->substNEWwithIDs array.
	 *
	 * @param string $status the status of this record (new/update)
	 * @param string $table the affected table name
	 * @param int $uid the UID of the affected record (may be 0)
	 * @param string[] &$fieldArray an array of all fields that got changed (as reference)
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj reference to calling object
	 *
	 * @return void
	 */
	public function processDatamap_afterDatabaseOperations(
		$status, $table, $uid, array &$fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
	) {
		// Translates new UIDs.
		if ($status == 'new') {
			$uid = $pObj->substNEWwithIDs[$uid];
		}

		if (($table == 'tx_seminars_seminars')
			|| ($table == 'tx_seminars_timeslots')
		) {
			$this->tceMainFieldArrays[$table][$uid] = $fieldArray;
		}
	}

	/**
	 * Processes all time slots.
	 *
	 * @return void
	 */
	private function processTimeSlots() {
		$table = 'tx_seminars_timeslots';

		if (
			isset($this->tceMainFieldArrays[$table])
			&& is_array($this->tceMainFieldArrays[$table])
		) {
			foreach ($this->tceMainFieldArrays[$table] as $uid => $fieldArray) {
				$this->processSingleTimeSlot($uid);
			}
		}
	}

	/**
	 * Processes all events.
	 *
	 * @return void
	 */
	private function processEvents() {
		$table = 'tx_seminars_seminars';

		if (
			isset($this->tceMainFieldArrays[$table])
			&& is_array($this->tceMainFieldArrays[$table])
		) {
			foreach ($this->tceMainFieldArrays[$table] as $uid => $fieldArray) {
				$this->processSingleEvent($uid, $fieldArray);
			}
		}
	}

	/**
	 * Processes a single time slot.
	 *
	 * @param int $uid the UID of the affected record (may be 0)
	 *
	 * @return void
	 */
	private function processSingleTimeSlot($uid) {
		/** @var Tx_Seminars_TimeSlot $timeslot */
		$timeslot = GeneralUtility::makeInstance(
			'Tx_Seminars_TimeSlot', $uid, FALSE
		);

		if ($timeslot->isOk()) {
			// Gets an associative array of fields that need
			// to be updated in the database and update them.
			$timeslot->saveToDatabase(
				$timeslot->getUpdateArray()
			);
		}
	}

	/**
	 * Processes a single event.
	 *
	 * @param int $uid the UID of the affected record (may be 0)
	 * @param string[] $fieldArray an array of all fields that got changed
	 *
	 * @return void
	 */
	private function processSingleEvent($uid, array $fieldArray) {
		/** @var Tx_Seminars_Seminar $seminar */
		$seminar = GeneralUtility::makeInstance(
			'Tx_Seminars_Seminar', $uid, FALSE, TRUE
		);

		if ($seminar->isOk()) {
			// Gets an associative array of fields that need to be updated in
			// the database.
			$seminar->saveToDatabase(
				$seminar->getUpdateArray($fieldArray)
			);
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_tcemain.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_tcemain.php']);
}