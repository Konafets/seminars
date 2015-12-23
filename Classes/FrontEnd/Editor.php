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

require_once(PATH_formidableapi);

/**
 * This class is the base class for any kind of front-end editor, for example the event editor or the registration editor.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_FrontEnd_Editor extends Tx_Seminars_FrontEnd_AbstractView {
	/**
	 * @var tx_ameosformidable object that creates the form
	 */
	private $formCreator = NULL;

	/**
	 * UID of the currently edited object, zero if the object is going to be a new database record
	 *
	 * @var int
	 */
	private $objectUid = 0;

	/**
	 * @var array[] the FORMidable form configuration
	 */
	private $formConfiguration = array();

	/**
	 * @var bool whether the class ist used in test mode
	 */
	private $isTestMode = FALSE;

	/**
	 * @var array this is used to fake form values for testing
	 */
	private $fakedFormValues = array();

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->formCreator);
		parent::__destruct();
	}

	/**
	 * Sets the current UID.
	 *
	 * @param int $uid
	 *        UID of the currently edited object. For creating a new database record, $uid must be zero. $uid must not be < 0.
	 *
	 * @return void
	 */
	public function setObjectUid($uid) {
		$this->objectUid = $uid;
	}

	/**
	 * Gets the current object UID.
	 *
	 * @return int UID of the currently edited object, zero if a new object is being created
	 */
	public function getObjectUid() {
		return $this->objectUid;
	}

	/**
	 * Sets the FORMidable form configuration.
	 *
	 * @param array[] $formConfiguration the FORMidable form configuration, must not be empty
	 *
	 * @return void
	 */
	public function setFormConfiguration(array $formConfiguration) {
		$this->formConfiguration = $formConfiguration;
	}

	/**
	 * Returns the FORMidable instance.
	 *
	 * @return tx_ameosformidable FORMidable instance or NULL if the test mode
	 *                            is set
	 */
	public function getFormCreator() {
		if ($this->formCreator === NULL) {
			$this->formCreator = $this->makeFormCreator();
		}

		return $this->formCreator;
	}

	/**
	 * Enables the test mode. If this mode is activated, the FORMidable object
	 * will not be used at all, instead the faked form values will be taken.
	 *
	 * @return void
	 */
	public function setTestMode() {
		$this->isTestMode = TRUE;
	}

	/**
	 * Checks whether the test mode is set.
	 *
	 * @return bool TRUE if the test mode is set, FALSE otherwise
	 */
	public function isTestMode() {
		return $this->isTestMode;
	}

	/**
	 * Returns the FE editor in HTML.
	 *
	 * Note that render() requires the FORMidable object to be initializable.
	 * This means that the test mode must not be set when calling render().
	 *
	 * @return string HTML for the FE editor or an error view if the requested object is not editable for the current user
	 */
	public function render() {
		return $this->getFormCreator()->render();
	}

	/**
	 * Creates a FORMidable instance for the current UID and form configuration.
	 * The UID must be of an existing seminar object.
	 *
	 * This function does nothing if this instance is running in test mode.
	 *
	 * @return tx_ameosformidable FORMidable instance or NULL if the test mode is set
	 *
	 * @throws BadMethodCallException
	 */
	protected function makeFormCreator() {
		if ($this->isTestMode()) {
			return NULL;
		}

		if (empty($this->formConfiguration)) {
			throw new BadMethodCallException(
				'Please define the FORMidable form configuration to use via $this->setFormConfiguration().', 1333293139
			);
		}

		/** @var tx_ameosformidable $formCreator */
		$formCreator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_ameosformidable');
		$formCreator->initFromTs(
			$this, $this->formConfiguration, ($this->getObjectUid() > 0) ? $this->getObjectUid() : FALSE
		);

		return $formCreator;
	}

	/**
	 * Returns a form value from the FORMidable object.
	 *
	 * Note: In test mode, this function will return faked values.
	 *
	 * @param string $key column name of the 'tx_seminars_seminars' table as key, must not be empty
	 *
	 * @return string|array form value or an empty string if the value does not exist
	 */
	public function getFormValue($key) {
		$dataSource = ($this->isTestMode) ? $this->fakedFormValues : $this->getFormCreator()->oDataHandler->__aFormData;

		return isset($dataSource[$key]) ? $dataSource[$key] : '';
	}

	/**
	 * Fakes a form data value that is usually provided by the FORMidable object.
	 *
	 * This function is for testing purposes.
	 *
	 * @param string $key column name of the 'tx_seminars_seminars' table as key, must not be empty
	 * @param mixed $value faked value
	 *
	 * @return void
	 */
	public function setFakedFormValue($key, $value) {
		$this->fakedFormValues[$key] = $value;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/Editor.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/Editor.php']);
}