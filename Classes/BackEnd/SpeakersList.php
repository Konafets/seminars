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
 * This class creates a speaker list in the back end.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_BackEnd_SpeakersList extends Tx_Seminars_BackEnd_AbstractList {
	/**
	 * @var string the name of the table we're working on
	 */
	protected $tableName = 'tx_seminars_speakers';

	/**
	 * @var tx_seminars_speaker the speaker which we want to list
	 */
	private $speaker = NULL;

	/**
	 * @var string the path to the template file of this list
	 */
	protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/SpeakersList.html';

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->speaker);

		parent::__destruct();
	}

	/**
	 * Generates and prints out a speakers list.
	 *
	 * @return string the HTML source code to display
	 */
	public function show() {
		$content = '';

		$pageData = $this->page->getPageData();

		$this->template->setMarker(
			'new_record_button', $this->getNewIcon($pageData['uid'])
		);

		$this->template->setMarker(
			'label_full_name', $GLOBALS['LANG']->getLL('speakerlist.title')
		);
		$this->template->setMarker(
			'label_skills', $GLOBALS['LANG']->getLL('speakerlist.skills')
		);

		/** @var tx_seminars_BagBuilder_Speaker $builder */
		$builder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_seminars_BagBuilder_Speaker');
		$builder->showHiddenRecords();

		$builder->setSourcePages($pageData['uid'], self::RECURSION_DEPTH);

		$speakerBag = $builder->build();

		$tableRows = '';

		/** @var tx_seminars_speaker $speakerBag */
		foreach ($speakerBag as $this->speaker) {
			$this->template->setMarker(
				'icon', $this->speaker->getRecordIcon()
			);
			$this->template->setMarker(
				'full_name', htmlspecialchars($this->speaker->getTitle())
			);
			$this->template->setMarker(
				'edit_button',
				$this->getEditIcon(
					$this->speaker->getUid(), $this->speaker->getPageUid()
				)
			);
			$this->template->setMarker(
				'delete_button',
				$this->getDeleteIcon(
					$this->speaker->getUid(), $this->speaker->getPageUid()
				)
			);
			$this->template->setMarker(
				'hide_unhide_button',
				$this->getHideUnhideIcon(
					$this->speaker->getUid(),
					$this->speaker->getPageUid(),
					$this->speaker->isHidden()
				)
			);
			$this->template->setMarker(
				'skills', htmlspecialchars($this->speaker->getSkillsShort())
			);

			$tableRows .= $this->template->getSubpart('SPEAKER_ROW');
		}

		$this->template->setSubpart('SPEAKER_ROW', $tableRows);
		$this->template->setMarker(
			'label_print_button', $GLOBALS['LANG']->getLL('print')
		);
		$content .= $this->template->getSubpart('SEMINARS_SPEAKER_LIST');

		$content .= $speakerBag->checkConfiguration();

		return $content;
	}

	/**
	 * Returns the storage folder for new speaker records.
	 *
	 * This will be determined by the auxiliary folder storage setting of the
	 * currently logged-in BE-user.
	 *
	 * @return int the PID for new speaker records, will be >= 0
	 */
	protected function getNewRecordPid() {
		return $this->getLoggedInUser()->getAuxiliaryRecordsFolder();
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/SpeakersList.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/SpeakersList.php']);
}