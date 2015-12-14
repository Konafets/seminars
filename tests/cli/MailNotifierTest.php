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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Cli_MailNotifierTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_seminars_cli_MailNotifier
	 */
	protected $fixture = NULL;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var Tx_Oelib_Configuration
	 */
	protected $configuration = NULL;

	/**
	 * @var Tx_Oelib_EmailCollector
	 */
	protected $mailer = NULL;

	/**
	 * @var language
	 */
	private $languageBackup = NULL;

	protected function setUp() {
		$this->languageBackup = $GLOBALS['LANG'];
		$GLOBALS['LANG'] = new \language();

		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
		/** @var Tx_Oelib_MailerFactory $mailerFactory */
		$mailerFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Oelib_MailerFactory');
		$mailerFactory->enableTestMode();
		$this->mailer = $mailerFactory->getMailer();

		define('TYPO3_cliKey', 'seminars');
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'][TYPO3_cliKey][1] = '_cli_seminars_test';
		$this->testingFramework->createBackEndUser(array('username' => '_cli_seminars_test'));

		tx_oelib_ConfigurationRegistry::getInstance()->set('plugin', new Tx_Oelib_Configuration());
		$this->configuration = new Tx_Oelib_Configuration();
		$this->configuration->setData(array(
			'sendEventTakesPlaceReminderDaysBeforeBeginDate' => 2,
			'sendCancelationDeadlineReminder' => TRUE,
			'filenameForRegistrationsCsv' => 'registrations.csv',
			'dateFormatYMD' => '%d.%m.%Y',
			'fieldsFromAttendanceForEmailCsv' => 'title',
			'showAttendancesOnRegistrationQueueInEmailCsv' => TRUE
		));
		tx_oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

		$this->fixture = new tx_seminars_cli_MailNotifier();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		$GLOBALS['LANG'] = $this->languageBackup;
		$this->languageBackup = NULL;
	}


	/*
	 * Utility functions
	 */

	/**
	 * Creates a seminar record and an organizer record and the relation
	 * between them.
	 *
	 * @param array $additionalSeminarData additional data for the seminar record, may be empty
	 *
	 * @return int UID of the added event, will be > 0
	 */
	private function createSeminarWithOrganizer(array $additionalSeminarData = array()) {
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array('title' => 'Mr. Test', 'email' => 'MrTest@example.com')
		);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array_merge($additionalSeminarData, array('organizers' => 1))
		);

		$this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);

		return $eventUid;
	}

	/**
	 * Adds a speaker relation to an existing seminar record.
	 *
	 * Note: This function must only be called once per test.
	 *
	 * @param int $eventUid event UID, must be > 0
	 *
	 * @return void
	 */
	private function addSpeaker($eventUid) {
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array('cancelation_period' => 2)
		);

		$this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, array('speakers' => 1));
		$this->testingFramework->createRelation('tx_seminars_seminars_speakers_mm', $eventUid, $speakerUid);
	}

	/**
	 * Returns the first e-mail attachment (if there is any).
	 *
	 * @return Swift_Mime_Attachment
	 */
	protected function getFirstEmailAttachment() {
		$children = $this->mailer->getFirstSentEmail()->getChildren();
		return $children[0];
	}


	/*
	 * Tests for the utility functions
	 */

	/**
	 * @test
	 */
	public function createSeminarWithOrganizerCreatesSeminarRecord() {
		$this->createSeminarWithOrganizer();

		self::assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars', '1=1')
		);
	}

	/**
	 * @test
	 */
	public function createSeminarWithOrganizerCreatesSeminarRecordWithAdditionalData() {
		$this->createSeminarWithOrganizer(array('title' => 'foo'));

		self::assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars', 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function createSeminarWithOrganizerCreatesOrganizerRecord() {
		$this->createSeminarWithOrganizer();

		self::assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_organizers', '1=1')
		);
	}

	/**
	 * @test
	 */
	public function createSeminarWithOrganizerCreatesRealtionBetweenSeminarAndOrganizer() {
		$this->createSeminarWithOrganizer();

		self::assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars_organizers_mm', '1=1')
		);
	}

	/**
	 * @test
	 */
	public function addSpeakerCreatesSpeakerRecord() {
		$this->addSpeaker($this->createSeminarWithOrganizer());

		self::assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_speakers', '1=1')
		);
	}

	/**
	 * @test
	 */
	public function addSpeakerCreatesSpeakerRelation() {
		$this->addSpeaker($this->createSeminarWithOrganizer());

		self::assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars_speakers_mm', '1=1')
		);
	}

	/**
	 * @test
	 */
	public function addSpeakerSetsNumberOfSpeakersToOneForTheSeminarWithTheProvidedUid() {
		$this->addSpeaker($this->createSeminarWithOrganizer());

		self::assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars', 'speakers = 1')
		);
	}


	/*
	 * Tests for setConfigurationPage
	 */

	/**
	 * @test
	 */
	public function setConfigurationPageThrowsExceptionIfNoPidIsProvided() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'Please provide the UID for the page with the configuration ' .
				'for the CLI module.'
		);

		unset($_SERVER['argv'][1]);

		$this->fixture->setConfigurationPage();
	}

	/**
	 * @test
	 */
	public function setConfigurationPageThrowsExceptionIfZeroIsProvidedAsPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The provided UID for the page with the configuration was 0, which was not found to be a UID of an existing page. ' .
				'Please provide the UID of an existing page.'
		);

		$_SERVER['argv'][1] = 0;

		$this->fixture->setConfigurationPage();
	}

	/**
	 * @test
	 */
	public function setConfigurationPageThrowsExceptionIfNonExistingPidIsProvided() {
		$invalidPid = $this->testingFramework->getAutoIncrement('pages');
		$this->setExpectedException(
			'InvalidArgumentException',
			'The provided UID for the page with the configuration was ' . $invalidPid .
				', which was not found to be a UID of an existing page. Please provide the UID of an existing page.'
		);

		$_SERVER['argv'][1] = $invalidPid;

		$this->fixture->setConfigurationPage();
	}

	/**
	 * @test
	 */
	public function setConfigurationPageThrowsNoExceptionIfAnExistingPidIsProvided() {
		$_SERVER['argv'][1] = $this->testingFramework->createFrontEndPage();

		$this->fixture->setConfigurationPage();
	}

	/**
	 * @test
	 */
	public function setConfigurationPageSetsTheExistingPidIsProvidedForThePageFinder() {
		$pageUid = $this->testingFramework->createFrontEndPage();

		$_SERVER['argv'][1] = $pageUid;

		$this->fixture->setConfigurationPage();

		self::assertSame(
			$pageUid,
			tx_oelib_PageFinder::getInstance()->getPageUid()
		);
	}


	/*
	 * Tests concerning sendEventTakesPlaceReminders
	 */

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventWithinConfiguredTimeFrameSendsReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertSame(
			1,
			$this->mailer->getNumberOfSentEmails()
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersSendsReminderWithEventTakesPlaceSubject() {
		/** @var tx_seminars_Model_BackEndUser $user */
		$user = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->findByCliKey();
		$GLOBALS['LANG']->lang = $user->getLanguage();
		$GLOBALS['LANG']->includeLLFile(ExtensionManagementUtility::extPath('seminars') . 'locallang.xml');
		$subject = $GLOBALS['LANG']->getLL('email_eventTakesPlaceReminderSubject');
		$subject = str_replace('%event', '', $subject);
		$subject = str_replace('%days', 2, $subject);

		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			$subject,
			$this->mailer->getFirstSentEmail()->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersSendsReminderWithEventTakesPlaceMessage() {
		/** @var tx_seminars_Model_BackEndUser $user */
		$user = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->findByCliKey();
		$GLOBALS['LANG']->lang = $user->getLanguage();
		$GLOBALS['LANG']->includeLLFile(ExtensionManagementUtility::extPath('seminars') . 'locallang.xml');
		$message = $GLOBALS['LANG']->getLL('email_eventTakesPlaceReminder');
		$message = str_replace('%event', '', $message);
		$message = str_replace('%organizer', 'Mr. Test', $message);

		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			substr($message, 0, strpos($message, '%') - 1),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForTwoConfirmedEventsWithinConfiguredTimeFrameSendsTwoReminders() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertSame(
			2,
			$this->mailer->getNumberOfSentEmails()
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventWithTwoOrganizersAndWithinConfiguredTimeFrameSendsTwoReminders() {
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array('title' => 'foo', 'email' => 'foo@example.com')
		);
		$this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);
		$this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, array('organizers' => 2));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertSame(
			2,
			$this->mailer->getNumberOfSentEmails()
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersSetsSentFlagInTheDatabaseWhenReminderWasSent() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_seminars', 'event_takes_place_reminder_sent = 1'
			)
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventWithinConfiguredTimeFrameAndReminderSentFlagTrueSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
			'event_takes_place_reminder_sent' => 1,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventWithPassedBeginDateSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventBeginningLaterThanConfiguredTimeFrameSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (3 * tx_oelib_Time::SECONDS_PER_DAY),
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventAndNoTimeFrameConfiguredSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->configuration->setAsInteger('sendEventTakesPlaceReminderDaysBeforeBeginDate', 0);

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForCanceledEventWithinConfiguredTimeFrameSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForPlannedEventWithinConfiguredTimeFrameSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}


	/*
	 * Tests concerning sendCancellationDeadlineReminders
	 */

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventAndOptionEnabledSendsReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertSame(
			1,
			$this->mailer->getNumberOfSentEmails()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersSendsReminderWithCancelationDeadlineSubject() {
		/** @var tx_seminars_Model_BackEndUser $user */
		$user = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->findByCliKey();
		$GLOBALS['LANG']->lang = $user->getLanguage();
		$GLOBALS['LANG']->includeLLFile(ExtensionManagementUtility::extPath('seminars') . 'locallang.xml');
		$subject = $GLOBALS['LANG']->getLL('email_cancelationDeadlineReminderSubject');
		$subject = str_replace('%event', '', $subject);

		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertContains(
			$subject,
			$this->mailer->getFirstSentEmail()->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersSendsReminderWithCancelationDeadlineMessage() {
		/** @var tx_seminars_Model_BackEndUser $user */
		$user = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->findByCliKey();
		$GLOBALS['LANG']->lang = $user->getLanguage();
		$GLOBALS['LANG']->includeLLFile(ExtensionManagementUtility::extPath('seminars') . 'locallang.xml');
		$message = $GLOBALS['LANG']->getLL('email_cancelationDeadlineReminder');
		$message = str_replace('%event', '', $message);
		$message = str_replace('%organizer', 'Mr. Test', $message);

		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertContains(
			substr($message, 0, strpos($message, '%') - 1),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForTwoPlannedEventsAndOptionEnabledSendsTwoReminders() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertSame(
			2,
			$this->mailer->getNumberOfSentEmails()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventWithTwoOrganizersAndOptionEnabledSendsTwoReminders() {
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		));
		$this->addSpeaker($eventUid);
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array('title' => 'foo', 'email' => 'foo@example.com')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $eventUid, array('organizers' => 2)
		);

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertSame(
			2,
			$this->mailer->getNumberOfSentEmails()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersSetsFlagInTheDatabaseWhenReminderWasSent() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars', 'cancelation_deadline_reminder_sent = 1')
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventAndOptionEnabledAndReminderSentFlagTrueSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
			'cancelation_deadline_reminder_sent' => 1,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventWithPassedBeginDateSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventWithSpeakersDeadlineNotYetReachedSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (3 * tx_oelib_Time::SECONDS_PER_DAY),
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventAndOptionDisabledSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));
		$this->configuration->setAsBoolean('sendCancelationDeadlineReminder', FALSE);

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForCanceledEventAndOptionEnabledSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForConfirmedEventAndOptionEnabledSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}


	/*
	 * Tests concerning the reminders content
	 *
	 * * sender and recipients
	 */

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsEmailWithOrganizerAsRecipient() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertArrayHasKey(
			'MrTest@example.com',
			$this->mailer->getFirstSentEmail()->getTo()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsEmailWithOrganizerAsSender() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertArrayHasKey(
			'MrTest@example.com',
			$this->mailer->getFirstSentEmail()->getFrom()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithTwoOrganizersSendsEmailWithFirstOrganizerAsSender() {
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array('title' => 'foo', 'email' => 'foo@example.com')
		);
		$this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);
		$this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, array('organizers' => 2));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertArrayHasKey(
			'MrTest@example.com',
			$this->mailer->getFirstSentEmail()->getFrom()
		);
	}


	/*
	 * * attached CSV
	 */

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithNoAttendancesAndAttachCsvFileTrueNotAttachesRegistrationsCsv() {
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);

		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertSame(
			array(),
			$this->mailer->getFirstSentEmail()->getChildren()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithAttendancesAndAttachCsvFileTrueAttachesRegistrationsCsv() {
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);

		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertSame(
			'registrations.csv',
			$this->getFirstEmailAttachment()->getFilename()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithAttendancesAndAttachCsvFileFalseNotAttachesRegistrationsCsv() {
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', FALSE);
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertSame(
			array(),
			$this->mailer->getFirstSentEmail()->getChildren()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsEmailWithCsvFileWhichContainsRegistration() {
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			'test registration' . CRLF,
			$this->getFirstEmailAttachment()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsEmailWithCsvFileWithOfFrontEndUserData() {
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);
		$this->configuration->setAsString('fieldsFromFeUserForEmailCsv', 'email');

		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser('', array('email' => 'foo@bar.com')),
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			'foo@bar.com',
			$this->getFirstEmailAttachment()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForShowAttendancesOnQueueInEmailCsvSendsEmailWithCsvWithRegistrationsOnQueue() {
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'real registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'on queue',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'registration_queue' => 1
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			'on queue',
			$this->getFirstEmailAttachment()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForShowAttendancesOnQueueInEmailCsvFalseSendsEmailWithCsvFileWhichDoesNotContainDataOfAttendanceOnQueue() {
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);
		$this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', FALSE);

		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'real registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'on queue',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'registration_queue' => 1
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertNotContains(
			'on queue',
			$this->getFirstEmailAttachment()->getBody()
		);
	}


	/*
	 * * customized subject
	 */

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithSubjectWithEventTitle() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
			'title' => 'test event'
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			'test event',
			$this->mailer->getFirstSentEmail()->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithSubjectWithDaysUntilBeginDate() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			'2',
			$this->mailer->getFirstSentEmail()->getSubject()
		);
	}


	/*
	 * * customized message
	 */

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithMessageWithOrganizerName() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			'Mr. Test',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithMessageWithEventTitle() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
			'title' => 'test event'
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			'test event',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithMessageWithEventUid() {
		$uid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			(string) $uid,
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithMessageWithDaysUntilBeginDate() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			'2',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithMessageWithEventsBeginDate() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		self::assertContains(
			strftime(
				$this->configuration->getAsString('dateFormatYMD'),
				$GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
			),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithNoRegistrationSendsReminderWithMessageWithNumberOfRegistrations() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			'0',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithOneRegistrationsSendsReminderWithMessageWithNumberOfRegistrations() {
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		self::assertContains(
			'1',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}
}