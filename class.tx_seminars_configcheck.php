<?php
/***************************************************************
* Copyright notice
*
* (c) 2006 Oliver Klee (typo3-coding@oliverklee.de)
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

/**
 * Class 'tx_seminars_confcheck' for the 'seminars' extension.
 *
 * This class checks the Seminar Manager configuration for basic sanity.
 *
 * The correct functioning of this class does not rely on any HTML templates or
 * language files so it works even under the worst of circumstances.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_oe_configcheck.php');

class tx_seminars_configcheck extends tx_seminars_oe_configcheck {
	/**
	 * Checks the configuration for: tx_seminars_registrationmanager/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_registrationmanager() {
		// The registration manager needs to be able to create registration
		// objects. So we check whether the prerequisites for registrations
		// are fullfilled as well.
		$this->check_tx_seminars_registration();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_seminar/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_seminar() {
		$this->checkStaticIncluded();
		$this->checkSalutationMode();
		$this->checkTimeAndDate();
		$this->checkShowTimeOfRegistrationDeadline();
		$this->checkShowTimeOfEarlyBirdDeadline();
		$this->checkShowVacanciesThreshold();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_registration/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_registration() {
		$this->checkStaticIncluded();
		$this->checkTemplateFile();
		$this->checkSalutationMode();

		$this->checkRegistrationFlag();

		$this->checkThankYouMail();
		$this->checkGeneralPriceInMail();
		$this->checkNotificationMail();
		if ($this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$this->checkAttendancesPid();
		}

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_seminarbag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_seminarbag() {
		$this->checkStaticIncluded();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_registrationbag/.
	 *
	 * @access	private
	 */
	function check_tx_seminars_registrationbag() {
		$this->checkStaticIncluded();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/seminar_registration.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_seminar_registration() {
		$this->checkCommonFrontEndSettings();

		$this->checkRegistrationFlag();
		if (!$this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$message = 'You are using the registration page although online '
				.'registration is disabled. This will break the registration '
				.'page and the automatic configuration check. '
				.'Please either enable online registration by setting the TS '
				.'setup variable <strong>'.$this->getTSSetupPath()
				.'enableRegistration</strong> to <strong>1</strong> or remove '
				.'the registration page.';
			$this->setErrorMessage($message);
		}

		$this->checkBaseUrl();

		$this->checkGeneralPriceInSingle();
		$this->checkEventFieldsOnRegistrationPage();
		$this->checkShowRegistrationFields();
		$this->checkListPid();
		$this->checkLoginPid();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/single_view.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_single_view() {
		$this->checkCommonFrontEndSettings();

		$this->checkRegistrationFlag();

		$this->checkHideFields();
		$this->checkGeneralPriceInSingle();
		$this->checkShowSpeakerDetails();
		$this->checkShowSiteDetails();
		if ($this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$this->checkRegisterPid();
			$this->checkLoginPid();
		}
		$this->checkRegistrationsListPidOptional();
		$this->checkRegistrationsVipListPidOptional();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/seminar_list.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_seminar_list() {
		$this->checkCommonFrontEndSettings();

		$this->checkRegistrationFlag();

		$this->checkPages();
		$this->checkRecursive();
		$this->checkListView(array_keys($this->objectToCheck->orderByList));

		$this->checkHideColumns();
		$this->checkTimeframeInList();
		$this->checkHideSearchForm();
		$this->checkHidePageBrowser();
		$this->checkHideCanceledEvents();
		$this->checkGeneralPriceInList();
		$this->checkOmitDateIfSameAsPrevious();
		$this->checkListPid();
		if ($this->objectToCheck->getConfValueBoolean('enableRegistration')) {
			$this->checkRegisterPid();
		}
		$this->checkRegistrationsListPidOptional();
		$this->checkRegistrationsVipListPidOptional();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/my_vip_events.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_my_vip_events() {
		$this->check_tx_seminars_pi1_seminar_list();
		$this->checkRegistrationsVipListPid();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/my_events.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_my_events() {
		$this->check_tx_seminars_pi1_seminar_list();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/list_registrations.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_list_registrations() {
		$this->checkCommonFrontEndSettings();

		$this->checkShowFeUserFieldsInRegistrationsList();
		$this->checkListPid();

		return;
	}

	/**
	 * Checks the configuration for: tx_seminars_pi1/list_vip_registrations.
	 *
	 * @access	private
	 */
	function check_tx_seminars_pi1_list_vip_registrations() {
		$this->check_tx_seminars_pi1_list_registrations();

		return;
	}

	/**
	 * Checks the configuration related to thank-you e-mails.
	 *
	 * @access	private
	 */
	function checkThankYouMail() {
		$this->checkCharsetForEMails();
		$this->checkHideFieldsInThankYouMail();

		return;
	}

	/**
	 * Checks the configuration related to notification e-mails.
	 *
	 * @access	private
	 */
	function checkNotificationMail() {
		$this->checkCharsetForEMails();
		$this->checkHideFieldsInNotificationMail();
		$this->checkShowSeminarFieldsInNotificationMail();
		$this->checkShowFeUserFieldsInNotificationMail();
		$this->checkShowAttendanceFieldsInNotificationMail();
		$this->checkSendAdditionalNotificationEmails();

		return;
	}

	/**
	 * Checks the settings for time and date format.
	 *
	 * @access	private
	 */
	function checkTimeAndDate() {
		$explanation = 'This determines the way dates and times are '
			.'displayed. If this is not set correctly, dates and times might '
			.'be mangled or not get displayed at all.';
		$configVariables = array(
			'timeFormat',
			'dateFormatY',
			'dateFormatM',
			'dateFormatD',
			'dateFormatYMD',
			'dateFormatMD'
		);
		foreach ($configVariables as $configVariableToCheck) {
			$this->checkForNonEmptyString(
				$configVariableToCheck,
				false,
				'',
				$explanation
			);
		}

		$this->checkAbbreviateDateRanges();

		return;
	}

	/**
	 * Checks the setting of the configuration value baseUrl.
	 *
	 * @see		http://www.ietf.org/rfc/rfc2396.txt
	 *
	 * @access	private
	 */
	function checkBaseUrl() {
		// The regular expression used for the host name mostly conforms with
		// http://www.ietf.org/rfc/rfc2396.txt.
		$this->checkRegExpNotEmpty(
			'baseURL',
			true,
			's_template_special',
			'This value specifies the base URL that will be used to create '
				.'links in e-mails. The base URL must include the protocol '
				.'(http:// or https://) and the trailing slash. '
				.'If this value is incorrect, invalid URLs will be created '
				.'in e-mails to the participants.',
			// the protocol
			'/^http(s?):\/\/'
				.'('
					// either a domain name ...
					.'(([a-z\d]|[a-z\d][a-z\d\-]*[a-z\d])\.)*'
						// ... with a top level domain (or a host at the local
						// network) at the end
						.'([a-z][a-z\d\-]*[a-z\d])'
					// or an IPv4 address
					.'|\d+\.\d+\.\d+\.\d+'
				.')'
				// a port (optional)
				.'(:\d+)?'
				.'\/'
				// any number of path segments (including none)
				.'([a-zA-Z\d_\-\.]+\/)'
				.'*$/'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value baseUrl.
	 *
	 * @access	private
	 */
	function checkRegistrationFlag() {
		$this->checkIfBoolean(
			'enableRegistration',
			false,
			'',
			'This value specifies whether the extension will use provide online'
				.'registration. If this value is incorrect, the online registration '
				.'will not be enabled or disabled correctly.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value what_to_display.
	 *
	 * @access	private
	 */
	function checkWhatToDisplay() {
		$this->checkIfSingleInSetNotEmpty(
			'what_to_display',
			true,
			'sDEF',
			'This value specifies the type of seminar manager plug-in to '
				.'display. If this value is not set correctly, the wrong '
				.'type of plug-in will be displayed.',
			array(
				'seminar_list',
				'my_events',
				'my_vip_events',
				'seminar_registration',
				'list_registrations',
				'list_vip_registrations'
			)
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value showTimeOfRegistrationDeadline.
	 *
	 * @access	private
	 */
	function checkShowTimeOfRegistrationDeadline() {
		$this->checkIfBoolean(
			'showTimeOfRegistrationDeadline',
			false,
			'',
			'This value specifies whether to also show the time of '
				.'registration deadlines. If this value is incorrect, the '
				.'time might get shown although this is not intended '
				.'(or vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value showTimeOfEarlyBirdDeadline.
	 *
	 * @access	private
	 */
	function checkShowTimeOfEarlyBirdDeadline() {
		$this->checkIfBoolean(
			'showTimeOfEarlyBirdDeadline',
			false,
			'',
			'This value specifies whether to also show the time of '
				.'early bird deadlines. If this value is incorrect, the '
				.'time might get shown although this is not intended '
				.'(or vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value showVacanciesThreshold.
	 *
	 * @access	private
	 */
	function checkShowVacanciesThreshold() {
		$this->checkIfInteger(
			'showVacanciesThreshold',
			false,
			'',
			'This value specifies down from which threshold the exact number '
				.'of vancancies will be displayed. If this value is incorrect, '
				.'the number might get shown although this is not intended '
				.'(or vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value generalPriceInMail.
	 *
	 * @access	private
	 */
	function checkGeneralPriceInMail() {
		$this->checkIfBoolean(
			'generalPriceInMail',
			false,
			'',
			'This value specifies which wording to use for the standard price '
				.'in e-mails. If this value is incorrect, the wrong wording '
				.'might get used.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value attendancesPID.
	 *
	 * @access	private
	 */
	function checkAttendancesPid() {
		$this->checkIfSingleSysFolderNotEmpty(
			'attendancesPID',
			false,
			'',
			'This value specifies the page on which registrations will be '
				.'stored. If this value is not set correctly, registration '
				.'records will be dumped in the TYPO3 root page. If you '
				.'explicitely do not wish to use the online registration '
				.'feature, you can disable these checks by setting '
				.'<strong>plugin.tx_seminars.enableRegistration</strong> and '
				.'<strong>plugin.tx_seminars_pi1.enableRegistration</strong> '
				.'to 0.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value hideFields.
	 *
	 * @access	private
	 */
	function checkHideFields() {
		$this->checkIfMultiInSetOrEmpty(
			'hideFields',
			true,
			's_template_special',
			'This value specifies which section to remove from the list view. '
				.'Incorrect values will cause the sections to still be displayed.',
			array(
				'title',
				'subtitle',
				'description',
				'accreditation_number',
				'credit_points',
				'date',
				'uid',
				'time',
				'place',
				'room',
				'speakers',
				'price_regular',
				'price_special',
				'paymentmethods',
				'organizers',
				'vacancies',
				'deadline_registration',
				'registration',
				'back'
			)
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value hideColumns.
	 *
	 * @access	private
	 */
	function checkHideColumns() {
		$this->checkIfMultiInSetOrEmpty(
			'hideColumns',
			true,
			's_template_special',
			'This value specifies which columns to remove from the list view. '
				.'Incorrect values will cause the colums to still be displayed.',
			array(
				'title',
				'uid',
				'event_type',
				'accreditation_number',
				'credit_points',
				'speakers',
				'date',
				'time',
				'place',
				'price_regular',
				'price_special',
				'organizers',
				'vacancies',
				'registration'
			)
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value timeframeInList.
	 *
	 * @access	private
	 */
	function checkTimeframeInList() {
		$this->checkIfSingleInSetNotEmpty(
			'timeframeInList',
			true,
			's_template_special',
			'This value specifies the time-frame from which events should be '
				.'displayed in the list view. An incorrect value will events '
				.'from a different time-frame cause to be displayed and other '
				.'events to not get displayed.',
			array(
				'all',
				'past',
				'pastAndCurrent',
				'current',
				'currentAndUpcoming',
				'upcoming',
				'deadlineNotOver'
			)
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value hideSearchForm.
	 *
	 * @access	private
	 */
	function checkHideSearchForm() {
		$this->checkIfBoolean(
			'hideSearchForm',
			true,
			's_template_special',
			'This value specifies whether the search form in the list view '
				.'will be displayed. If this value is incorrect, the search '
				.'form might get displayed when this is not intended (or '
				.'vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value hidePageBrowser.
	 *
	 * @access	private
	 */
	function checkHidePageBrowser() {
		$this->checkIfBoolean(
			'hidePageBrowser',
			true,
			's_template_special',
			'This value specifies whether the page browser in the list view '
				.'will be displayed. If this value is incorrect, the page '
				.'browser might get displayed when this is not intended (or '
				.'vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value hideCanceledEvents.
	 *
	 * @access	private
	 */
	function checkHideCanceledEvents() {
		$this->checkIfBoolean(
			'hideCanceledEvents',
			true,
			's_template_special',
			'This value specifies whether canceled events will be removed '
				.'from the list view. If this value is incorrect, canceled '
				.'events might get displayed when this is not intended (or '
				.'vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value hideFieldsInThankYouMail.
	 *
	 * @access	private
	 */
	function checkHideFieldsInThankYouMail() {
		$this->checkIfMultiInSetOrEmpty(
			'hideFieldsInThankYouMail',
			false,
			'',
			'These values specify the sections to hide in e-mails to '
				.'participants. A mistyped field name will cause the field to '
				.'be included nonetheless.',
			array(
				'hello',
				'title',
				'uid',
				'seats',
				'accreditation_number',
				'credit_points',
				'date',
				'time',
				'place',
				'room',
				'price_regular',
				'price_special',
				'paymentmethods',
				'url',
				'footer'
			)
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value hideFieldsInNotificationMail.
	 *
	 * @access	private
	 */
	function checkHideFieldsInNotificationMail() {
		$this->checkIfMultiInSetOrEmpty(
			'hideFieldsInNotificationMail',
			false,
			'',
			'These values specify the sections to hide in e-mails to '
				.'organizers. A mistyped field name will cause the field to '
				.'be included nonetheless.',
			array(
				'hello',
				'summary',
				'seminardata',
				'feuserdata',
				'attendancedata'
			)
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value showSeminarFieldsInNotificationMail.
	 *
	 * @access	private
	 */
	function checkShowSeminarFieldsInNotificationMail() {
		$this->checkIfMultiInSetOrEmpty(
			'showSeminarFieldsInNotificationMail',
			false,
			'',
			'These values specify the event fields to show in e-mails to '
				.'organizers. A mistyped field name will cause the field to '
				.'not get included.',
			array(
				'uid',
				'event_type',
				'title',
				'subtitle',
				'titleanddate',
				'date',
				'time',
				'accreditation_number',
				'credit_points',
				'room',
				'place',
				'speakers',
				'price_regular',
				'price_regular_early',
				'price_special',
				'price_special_early',
				'attendees',
				'attendees_min',
				'attendees_max',
				'vacancies',
				'enough_attendees',
				'is_full',
				'notes'
			)
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value showFeUserFieldsInNotificationMail.
	 *
	 * @access	private
	 */
	function checkShowFeUserFieldsInNotificationMail() {
		$this->checkIfMultiInTableOrEmpty(
			'showFeUserFieldsInNotificationMail',
			false,
			'',
			'These values specify the FE user fields to show in e-mails to '
				.'organizers. A mistyped field name will cause the field to '
				.'not get included.',
			'fe_users'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value showAttendanceFieldsInNotificationMail.
	 *
	 * @access	private
	 */
	function checkShowAttendanceFieldsInNotificationMail() {
		$this->checkIfMultiInSetOrEmpty(
			'showAttendanceFieldsInNotificationMail',
			false,
			'',
			'These values specify the registration fields to show in e-mails '
				.'to organizers. A mistyped field name will cause the field '
				.'to not get included.',
			array(
				'interests',
				'expectations',
				'background_knowledge',
				'accommodation',
				'food',
				'known_from',
				'notes',
				'seats',
				'price'
			)
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value sendAdditionalNotificationEmails.
	 *
	 * @access	private
	 */
	function checkSendAdditionalNotificationEmails() {
		$this->checkIfBoolean(
			'sendAdditionalNotificationEmails',
			false,
			'',
			'This value specifies whether organizers receive additional '
				.'notification e-mails. If this value is incorrect, e-mails '
				.'might get sent when this is not intended (or vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value abbreviateDateRanges.
	 *
	 * @access	private
	 */
	function checkAbbreviateDateRanges() {
		$this->checkIfBoolean(
			'abbreviateDateRanges',
			false,
			'',
			'This value specifies whether date ranges will be abbreviated. '
				.'If this value is incorrect, the values might be abbreviated '
				.'although this is not intended (or vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value generalPriceInList.
	 *
	 * @access	private
	 */
	function checkGeneralPriceInList() {
		$this->checkIfBoolean(
			'generalPriceInList',
			true,
			's_template_special',
			'This value specifies whether the column header for the standard '
				.'price in the list view will be just <em>Price</em> instead '
				.'of <em>Standard price</em>. '
				.'If this value is incorrect, the wrong label might be used.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value generalPriceInSingle.
	 *
	 * @access	private
	 */
	function checkGeneralPriceInSingle() {
		$this->checkIfBoolean(
			'generalPriceInSingle',
			true,
			's_template_special',
			'This value specifies whether the heading for the standard price '
				.'in the detailed view and on the registration page will be '
				.'just <em>Price</em> instead of <em>Standard price</em>. '
				.'If this value is incorrect, the wrong label might be used.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value omitDateIfSameAsPrevious.
	 *
	 * @access	private
	 */
	function checkOmitDateIfSameAsPrevious() {
		$this->checkIfBoolean(
			'omitDateIfSameAsPrevious',
			true,
			's_template_special',
			'This value specifies whether whether to omit the date in the '
				.'list view if it is the same as the previous item\'s. '
				.'If this value is incorrect, the date might be ommited '
				.'although this is not intended (or vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value eventFieldsOnRegistrationPage.
	 *
	 * @access	private
	 */
	function checkEventFieldsOnRegistrationPage() {
		$this->checkIfMultiInSetNotEmpty(
			'eventFieldsOnRegistrationPage',
			true,
			's_template_special',
			'This value specifies which data fields of the selected event '
				.'will be displayed on the registration page. '
				.'Incorrect values will cause those fields to not get displayed.',
			array(
				'uid',
				'title',
				'accreditation_number',
				'price_regular',
				'price_special',
				'vacancies'
			)
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value showRegistrationFields.
	 *
	 * @access	private
	 */
	function checkShowRegistrationFields() {
		$this->checkIfMultiInSetNotEmpty(
			'showRegistrationFields',
			true,
			's_template_special',
			'This value specifies which registration fields '
				.'will be displayed on the registration page. '
				.'Incorrect values will cause those fields to not get displayed.',
			array(
				'interests',
				'expectations',
				'background_knowledge',
				'accommodation',
				'food',
				'known_from',
				'seats',
				'notes'
			)
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value showSpeakerDetails.
	 *
	 * @access	private
	 */
	function checkShowSpeakerDetails() {
		$this->checkIfBoolean(
			'showSpeakerDetails',
			true,
			's_template_special',
			'This value specifies whether to show detailed information of '
				.'the speakers in the single view. '
				.'If this value is incorrect, the detailed information might '
				.'be shown although this is not intended (or vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value showSiteDetails.
	 *
	 * @access	private
	 */
	function checkShowSiteDetails() {
		$this->checkIfBoolean(
			'showSiteDetails',
			true,
			's_template_special',
			'This value specifies whether to show detailed information of '
				.'the locations in the single view. '
				.'If this value is incorrect, the detailed information might '
				.'be shown although this is not intended (or vice versa).'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value showFeUserFieldsInRegistrationsList.
	 *
	 * @access	private
	 */
	function checkShowFeUserFieldsInRegistrationsList() {
		$this->checkIfMultiInTableOrEmpty(
			'showFeUserFieldsInRegistrationsList',
			true,
			's_template_special',
			'These values specify the FE user fields to show in the list of '
				.'registrations for an event. A mistyped field name will '
				.'cause the contents of the field to not get displayed.',
			'fe_users'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value listPID.
	 *
	 * @access	private
	 */
	function checkListPid() {
		$this->checkIfSingleFePageNotEmpty(
			'listPID',
			true,
			'sDEF',
			'This value specifies the page that contains the list of events. '
				.'If this value is not set correctly, the links in the list '
				.'view and the back link on the list of registrations will '
				.'not work.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value registerPID.
	 *
	 * @access	private
	 */
	function checkRegisterPid() {
		$this->checkIfSingleFePageNotEmpty(
			'registerPID',
			true,
			'sDEF',
			'This value specifies the page that contains the registration '
				.'form. If this value is not set correctly, the link to the '
				.'registration page will not work. If you explicitely do not '
				.'wish to use the online registration feature, you can '
				.'disable these checks by setting '
				.'<strong>plugin.tx_seminars.enableRegistration</strong> and '
				.'<strong>plugin.tx_seminars_pi1.enableRegistration</strong> '
				.'to 0.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value loginPID.
	 *
	 * @access	private
	 */
	function checkLoginPid() {
		$this->checkIfSingleFePageNotEmpty(
			'loginPID',
			true,
			'sDEF',
			'This value specifies the page that contains the login form. '
				.'If this value is not set correctly, the link to the '
				.'login page will not work. If you explicitely do not '
				.'wish to use the online registration feature, you can '
				.'disable these checks by setting '
				.'<strong>plugin.tx_seminars.enableRegistration</strong> and '
				.'<strong>plugin.tx_seminars_pi1.enableRegistration</strong> '
				.'to 0.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value registrationsListPID.
	 *
	 * @access	private
	 */
	function checkRegistrationsListPidOptional() {
		$this->checkIfSingleFePageOrEmpty(
			'registrationsListPID',
			true,
			'sDEF',
			'This value specifies the page that contains the list of '
				.'registrations for an event. If this value is not set '
				.'correctly, the link to that page will not work.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value registrationsVipListPID.
	 *
	 * @access	private
	 */
	function checkRegistrationsVipListPid() {
		$this->checkIfSingleFePageNotEmpty(
			'registrationsVipListPID',
			true,
			'sDEF',
			'This value specifies the page that contains the list of '
				.'registrations for an event. If this value is not set '
				.'correctly, the link to that page will not work.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value registrationsVipListPID,
	 * but also allows empty values.
	 *
	 * @access	private
	 */
	function checkRegistrationsVipListPidOptional() {
		$this->checkIfSingleFePageOrEmpty(
			'registrationsVipListPID',
			true,
			'sDEF',
			'This value specifies the page that contains the list of '
				.'registrations for an event. If this value is not set '
				.'correctly, the link to that page will not work.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value pages.
	 *
	 * @access	private
	 */
	function checkPages() {
		$this->checkIfSysFoldersNotEmpty(
			'pages',
			true,
			'sDEF',
			'This value specifies the system folders that contain the '
			.'event records for the list view. If this value is not set '
			.'correctly, some events might not get displayed in the list '
			.'view.'
		);

		return;
	}

	/**
	 * Checks the setting of the configuration value recursive,
	 * but also allows empty values.
	 *
	 * @access	private
	 */
	function checkRecursive() {
		$this->checkIfInteger(
			'recursive',
			true,
			'sDEF',
			'This value specifies the how deep the recursion will be for '
				.'selecting the pages that contain the event records for the '
				.'list view. If this value is not set correctly, some events '
				.'might not get displayed in the list view.'
		);

		return;
	}

	/**
	 * Checks the settings that are common to all FE plug-in variations of this
	 * extension: CSS styled content, static TypoScript template included,
	 * template file, css file, salutation mode, CSS class names, and what to
	 * display.
	 *
	 * @access	protected
	 */
	function checkCommonFrontEndSettings() {
		$this->checkCssStyledContent();
		$this->checkStaticIncluded();
		$this->checkTemplateFile(true);
		$this->checkCssFile(true);
		$this->checkSalutationMode(true);
		$this->checkCssClassNames();
		$this->checkWhatToDisplay();

		return;
	}

	/**
	 * Checks the setting of the configuration value
	 * charsetForEMails.
	 *
	 * @access	private
	 */
	function checkCharsetForEMails() {
		$this->checkForNonEmptyString(
			'charsetForEMails',
			false,
			'',
			'This value specifies the charset that will be used in e-mails to '
				.'the organizers and the attendees. '
				.'If this value is empty, special characters in these e-mails '
				.'will appear garbled.'
		);

		return;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_configcheck.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_configcheck.php']);
}

?>
