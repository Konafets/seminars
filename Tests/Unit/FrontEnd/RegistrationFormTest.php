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
 * @author Philipp Kitzberger <philipp@cron-it.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_RegistrationFormTest extends tx_phpunit_testcase {
	/**
	 * @var Tx_Seminars_FrontEnd_RegistrationForm
	 */
	protected $fixture = NULL;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var Tx_Oelib_FakeSession a fake session
	 */
	protected $session = NULL;

	/**
	 * @var int the UID of the event the fixture relates to
	 */
	protected $seminarUid = 0;

	/**
	 * @var tx_seminars_seminars
	 */
	protected $seminar = NULL;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
		$frontEndPageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createFakeFrontEnd($frontEndPageUid);

		$this->session = new Tx_Oelib_FakeSession();
		tx_oelib_Session::setInstance(tx_oelib_Session::TYPE_USER, $this->session);

		$configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
		$configuration = new Tx_Oelib_Configuration();
		$configuration->setAsString('currency', 'EUR');
		$configurationRegistry->set('plugin.tx_seminars', $configuration);
		$configurationRegistry->set(
			'plugin.tx_staticinfotables_pi1', new Tx_Oelib_Configuration()
		);

		$this->seminar = new Tx_Seminars_Seminar($this->testingFramework->createRecord(
			'tx_seminars_seminars', array('payment_methods' => '1')
		));
		$this->seminarUid = $this->seminar->getUid();

		$this->fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'pageToShowAfterUnregistrationPID' => $frontEndPageUid,
				'sendParametersToThankYouAfterRegistrationPageUrl' => 1,
				'thankYouAfterRegistrationPID' => $frontEndPageUid,
				'sendParametersToPageToShowAfterUnregistrationUrl' => 1,
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'logOutOneTimeAccountsAfterRegistration' => 1,
				'showRegistrationFields' => 'registered_themselves,attendees_names',
				'showFeUserFieldsInRegistrationForm' => 'name,email',
				'showFeUserFieldsInRegistrationFormWithLabel' => 'email',
				'form.' => array(
					'unregistration.' => array(),
					'registration.'	=> array(
						'step1.' => array(),
						'step2.' => array(),
					),
				),
			),
			$GLOBALS['TSFE']->cObj
		);
		$this->fixture->setAction('register');
		$this->fixture->setSeminar($this->seminar);
		$this->fixture->setTestMode();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		Tx_Seminars_RegistrationManager::purgeInstance();
	}


	////////////////////////////////////////////////////////////////
	// Tests for getting the page-to-show-after-unregistration URL
	////////////////////////////////////////////////////////////////

	public function testGetPageToShowAfterUnregistrationUrlReturnsUrlStartingWithHttp() {
		self::assertRegExp(
			'/^http:\/\/./',
			$this->fixture->getPageToShowAfterUnregistrationUrl()
		);
	}

	public function testGetPageToShowAfterUnregistrationUrlReturnsUrlWithEncodedBrackets() {
		self::assertContains(
			'%5BshowUid%5D',
			$this->fixture->getPageToShowAfterUnregistrationUrl()
		);

		self::assertNotContains(
			'[showUid]',
			$this->fixture->getPageToShowAfterUnregistrationUrl()
		);
	}


	///////////////////////////////////////////////////////////
	// Tests for getting the thank-you-after-registration URL
	///////////////////////////////////////////////////////////

	public function testGetThankYouAfterRegistrationUrlReturnsUrlStartingWithHttp() {
		self::assertRegExp(
			'/^http:\/\/./',
			$this->fixture->getThankYouAfterRegistrationUrl()
		);
	}

	public function testGetThankYouAfterRegistrationUrlReturnsUrlWithEncodedBrackets() {
		self::assertContains(
			'%5BshowUid%5D',
			$this->fixture->getThankYouAfterRegistrationUrl()
		);

		self::assertNotContains(
			'[showUid]',
			$this->fixture->getThankYouAfterRegistrationUrl()
		);
	}

	public function testGetThankYouAfterRegistrationUrlLeavesUserLoggedInByDefault() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->getThankYouAfterRegistrationUrl();

		self::assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	public function testGetThankYouAfterRegistrationUrlWithOneTimeAccountMarkerInUserSessionLogsOutUser() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->session->setAsBoolean('onetimeaccount', TRUE);

		$this->fixture->getThankYouAfterRegistrationUrl();

		self::assertFalse(
			$this->testingFramework->isLoggedIn()
		);
	}


	/*
	 * Test concerning getAllFeUserData
	 */

	/**
	 * @test
	 */
	public function getAllFeUserContainsNonEmptyNameOfFrontEndUser() {
		$this->testingFramework->createAndLoginFrontEndUser('', array('name' => 'John Doe'));

		self::assertContains(
			'John Doe',
			$this->fixture->getAllFeUserData()
		);
	}

	/**
	 * @test
	 */
	public function getAllFeUserContainsLabelForNonEmptyEmailOfFrontEndUser() {
			$this->testingFramework->createAndLoginFrontEndUser('', array('email' => 'john@example.com'));

		self::assertContains(
			'mail',
			$this->fixture->getAllFeUserData()
		);
	}

	/**
	 * @test
	 */
	public function getAllFeUserDoesNotContainEmptyLinesForMissingCompanyName() {
		$this->testingFramework->createAndLoginFrontEndUser('', array('name' => 'John Doe'));

		self::assertNotRegExp(
			'/<br \/>\s*<br \/>/',
			$this->fixture->getAllFeUserData()
		);
	}


	///////////////////////////////////////
	// Tests concerning saveDataToSession
	///////////////////////////////////////

	public function testSaveDataToSessionCanWriteEmptyZipToUserSession() {
		$this->fixture->processRegistration(array('zip' => ''));

		self::assertEquals(
			'',
			$this->session->getAsString('tx_seminars_registration_editor_zip')
		);
	}

	public function testSaveDataToSessionCanWriteNonEmptyZipToUserSession() {
		$this->fixture->processRegistration(array('zip' => '12345'));

		self::assertEquals(
			'12345',
			$this->session->getAsString('tx_seminars_registration_editor_zip')
		);
	}

	public function testSaveDataToSessionCanOverwriteNonEmptyZipWithEmptyZipInUserSession() {
		$this->session->setAsString(
			'tx_seminars_registration_editor_zip', '12345'
		);
		$this->fixture->processRegistration(array('zip' => ''));

		self::assertEquals(
			'',
			$this->session->getAsString('tx_seminars_registration_editor_zip')
		);
	}

	/**
	 * @test
	 */
	public function saveDataToSessionCanStoreCompanyInSession() {
		$this->fixture->processRegistration(array('company' => 'foo inc.'));

		self::assertEquals(
			'foo inc.',
			$this->session->getAsString(
				'tx_seminars_registration_editor_company'
			)
		);
	}

	/**
	 * @test
	 */
	public function saveDataToSessionCanStoreNameInSession() {
		$this->fixture->processRegistration(array('name' => 'foo'));

		self::assertEquals(
			'foo',
			$this->session->getAsString(
				'tx_seminars_registration_editor_name'
			)
		);
	}


	/////////////////////////////////////////////
	// Tests concerning retrieveDataFromSession
	/////////////////////////////////////////////

	public function testRetrieveDataFromSessionWithUnusedKeyReturnsEmptyString() {
		self::assertEquals(
			'',
			$this->fixture->retrieveDataFromSession('', array('key' => 'foo'))
		);
	}

	public function testRetrieveDataFromSessionWithKeySetInUserSessionReturnsDataForThatKey() {
		$this->session->setAsString(
			'tx_seminars_registration_editor_zip', '12345'
		);

		self::assertEquals(
			'12345',
			$this->fixture->retrieveDataFromSession('', array('key' => 'zip'))
		);
	}


	////////////////////////////////////////////////
	// Tests concerning populateListPaymentMethods
	////////////////////////////////////////////////

	public function testPopulateListPaymentMethodsDoesNotCrash() {
		$this->fixture->populateListPaymentMethods(array());
	}

	public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsOneItem() {
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods'
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$paymentMethodUid,
			'payment_methods'
		);

		self::assertEquals(
			1,
			count($this->fixture->populateListPaymentMethods(array()))
		);
	}

	public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsThisMethodsTitle() {
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('title' => 'foo')
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$paymentMethodUid,
			'payment_methods'
		);

		$paymentMethods = $this->fixture->populateListPaymentMethods(array());

		self::assertContains(
			'foo',
			$paymentMethods[0]['caption']
		);
	}

	public function testPopulateListPaymentMethodsForEventWithOnePaymentMethodReturnsThisMethodsUid() {
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods'
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$paymentMethodUid,
			'payment_methods'
		);

		$paymentMethods = $this->fixture->populateListPaymentMethods(array());

		self::assertEquals(
			$paymentMethodUid,
			$paymentMethods[0]['value']
		);
	}

	public function testPopulateListPaymentMethodsForEventWithTwoPaymentMethodsReturnsBothPaymentMethods() {
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$this->testingFramework->createRecord('tx_seminars_payment_methods'),
			'payment_methods'
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$this->testingFramework->createRecord('tx_seminars_payment_methods'),
			'payment_methods'
		);

		self::assertEquals(
			2,
			count($this->fixture->populateListPaymentMethods(array()))
		);
	}


	////////////////////////////////////
	// Tests concerning getStepCounter
	////////////////////////////////////

	public function testGetStepCounterReturnsNumberOfCurrentPageIfCurrentPageNumberIsLowerThanNumberOfLastPage() {
		$this->fixture->setConfigurationValue(
			'numberOfFirstRegistrationPage',
			1
		);
		$this->fixture->setConfigurationValue(
			'numberOfLastRegistrationPage',
			2
		);

		$this->fixture->setPage(array('next_page' => 0));

		self::assertContains(
			'1',
			$this->fixture->getStepCounter()
		);
	}

	public function testGetStepCounterReturnsNumberOfLastRegistrationPage() {
		$this->fixture->setConfigurationValue(
			'numberOfFirstRegistrationPage',
			1
		);
		$this->fixture->setConfigurationValue(
			'numberOfLastRegistrationPage',
			2
		);
		$this->fixture->setPage(array('next_page' => 0));

		self::assertContains(
			'2',
			$this->fixture->getStepCounter()
		);
	}

	public function testGetStepCounterReturnsNumberOfLastRegistrationPageAsCurrentPageIfPageNumberIsAboveLastRegistrationPage() {
		$this->fixture->setConfigurationValue(
			'numberOfFirstRegistrationPage',
			1
		);
		$this->fixture->setConfigurationValue(
			'numberOfLastRegistrationPage',
			2
		);

		$this->fixture->setPage(array('next_page' => 5));

		self::assertEquals(
			sprintf($this->fixture->translate('label_step_counter'), 2, 2),
			$this->fixture->getStepCounter()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning populateListCountries().
	//////////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListCountriesWithLanguageSetToDefaultNotContainsEnglishCountryNameForGermany() {
		$backUpLanguage = $GLOBALS['LANG'];
		$GLOBALS['LANG'] = new language();
		$GLOBALS['LANG']->init('default');

		self::assertNotContains(
			array('caption' => 'Germany', 'value' => 'Germany'),
			$this->fixture->populateListCountries()
		);

		$GLOBALS['LANG'] = $backUpLanguage;
	}

	/**
	 * @test
	 */
	public function populateListCountriesContainsLocalCountryNameForGermany() {
		self::assertContains(
			array('caption' => 'Deutschland', 'value' => 'Deutschland'),
			$this->fixture->populateListCountries()
		);
	}


	//////////////////////////////////////
	// Tests concerning getFeUserData().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getFeUserDataWithKeyCountryAndNoCountrySetReturnsDefaultCountrySetViaTypoScriptSetup() {
		$this->testingFramework->createAndLoginFrontEndUser();

		Tx_Oelib_ConfigurationRegistry::get('plugin.tx_staticinfotables_pi1')->
			setAsString('countryCode', 'DEU');

		self::assertEquals(
			'Deutschland',
			$this->fixture->getFeUserData(NULL, array('key' => 'country'))
		);
	}

	/**
	 * @test
	 */
	public function getFeUserDataWithKeyCountryAndStaticInfoCountrySetReturnsStaticInfoCountry() {
		if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('sr_feuser_register')) {
			self::markTestSkipped('This test only is available is sr_feuser_register is installed.');
		}

		$this->testingFramework->createAndLoginFrontEndUser(
			'', array('static_info_country' => 'GBR')
		);

		self::assertEquals(
			'United Kingdom',
			$this->fixture->getFeUserData(NULL, array('key' => 'country'))
		);
	}

	/**
	 * @test
	 */
	public function getFeUserDataWithKeyCountryAndCountrySetReturnsCountry() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'', array('country' => 'Taka-Tuka-Land')
		);

		self::assertEquals(
			'Taka-Tuka-Land',
			$this->fixture->getFeUserData(NULL, array('key' => 'country'))
		);
	}


	////////////////////////////////////////
	// Tests concerning isFormFieldEnabled
	////////////////////////////////////////

	/**
	 * Data provider that returns the keys of all available form fields.
	 *
	 * @return array[] two-dimensional array with the inner array being:
	 *               [key] string: the form field key
	 *               [self-contained] boolean: whether the field is visible
	 *                                if no other fields are visible
	 *
	 * @see isFormFieldEnabledForNoFieldsEnabledReturnsFalseForEachField
	 * @see isFormFieldEnabledForNoFieldsEnabledReturnsTrueForSelfContainedFields
	 */
	public function formFieldsDataProvider() {
		return array(
			'step_counter' => array(
				'key' => 'step_counter', 'self-contained' => TRUE
			),
			'price' => array(
				'key' => 'price', 'self-contained' => TRUE
			),
			'method_of_payment' => array(
				'key' => 'method_of_payment', 'self-contained' => FALSE
			),
			'account_number' => array(
				'key' => 'account_number', 'self-contained' => FALSE
			),
			'bank_code' => array(
				'key' => 'bank_code', 'self-contained' => FALSE
			),
			'bank_name' => array(
				'key' => 'bank_name', 'self-contained' => FALSE
			),
			'account_owner' => array(
				'key' => 'account_owner', 'self-contained' => FALSE
			),
			'billing_address' => array(
				'key' => 'billing_address', 'self-contained' => FALSE
			),
			'company' => array(
				'key' => 'company', 'self-contained' => TRUE
			),
			'gender' => array(
				'key' => 'gender', 'self-contained' => TRUE
			),
			'name' => array(
				'key' => 'name', 'self-contained' => TRUE
			),
			'address' => array(
				'key' => 'address', 'self-contained' => TRUE
			),
			'zip' => array(
				'key' => 'zip', 'self-contained' => TRUE
			),
			'city' => array(
				'key' => 'city', 'self-contained' => TRUE
			),
			'country' => array(
				'key' => 'country', 'self-contained' => TRUE
			),
			'telephone' => array(
				'key' => 'telephone', 'self-contained' => TRUE
			),
			'email' => array(
				'key' => 'email', 'self-contained' => TRUE
			),
			'interests' => array(
				'key' => 'interests', 'self-contained' => TRUE
			),
			'expectations' => array(
				'key' => 'expectations', 'self-contained' => TRUE
			),
			'background_knowledge' => array(
				'key' => 'background_knowledge', 'self-contained' => TRUE
			),
			'accommodation' => array(
				'key' => 'accommodation', 'self-contained' => TRUE
			),
			'food' => array(
				'key' => 'food', 'self-contained' => TRUE
			),
			'known_from' => array(
				'key' => 'known_from', 'self-contained' => TRUE
			),
			'seats' => array(
				'key' => 'seats', 'self-contained' => TRUE
			),
			'registered_themselves' => array(
				'key' => 'registered_themselves', 'self-contained' => TRUE
			),
			'attendees_names' => array(
				'key' => 'attendees_names', 'self-contained' => TRUE
			),
			'kids' => array(
				'key' => 'kids', 'self-contained' => TRUE
			),
			'lodgings' => array(
				'key' => 'lodgings', 'self-contained' => FALSE
			),
			'foods' => array(
				'key' => 'foods', 'self-contained' => FALSE
			),
			'checkboxes' => array(
				'key' => 'checkboxes', 'self-contained' => FALSE
			),
			'notes' => array(
				'key' => 'notes', 'self-contained' => TRUE
			),
			'total_price' => array(
				'key' => 'total_price', 'self-contained' => TRUE
			),
			'feuser_data' => array(
				'key' => 'feuser_data', 'self-contained' => TRUE
			),
			'registration_data' => array(
				'key' => 'registration_data', 'self-contained' => TRUE
			),
			'terms' => array(
				'key' => 'terms', 'self-contained' => TRUE
			),
			'terms_2' => array(
				'key' => 'terms_2', 'self-contained' => FALSE
			),
		);
	}

	/**
	 * @test
	 *
	 * @param string $key the key of the field to check for, must not be empty
	 *
	 * @dataProvider formFieldsDataProvider
	 */
	public function isFormFieldEnabledForNoFieldsEnabledReturnsFalseForEachField(
		$key
	) {
		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array('showRegistrationFields' => ''),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setSeminar($this->getMock('Tx_Seminars_Seminar', array(), array(), '', FALSE));

		self::assertFalse(
			$fixture->isFormFieldEnabled($key)
		);
	}

	/**
	 * @test
	 *
	 * @param string $key the key of the field to check for, must not be empty
	 * @param bool $isSelfContained
	 *        whether the field will be visible if no other fields are enabled
	 *        and the event has no special features enabled
	 *
	 *
	 * @dataProvider formFieldsDataProvider
	 */
	public function isFormFieldEnabledForNoFieldsEnabledReturnsTrueForSelfContainedFields(
		$key, $isSelfContained
	) {
		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array('showRegistrationFields' => $key),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setSeminar($this->getMock('Tx_Seminars_Seminar', array(), array(), '', FALSE));

		self::assertEquals(
			$isSelfContained,
			$fixture->isFormFieldEnabled($key)
		);
	}

	/**
	 * @test
	 */
	public function isFormFieldEnabledForEnabledRegisteredThemselvesFieldOnlyReturnsFalseForMoreSeats() {
		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array('showRegistrationFields' => 'registered_themselves'),
			$GLOBALS['TSFE']->cObj
		);

		self::assertFalse(
			$fixture->isFormFieldEnabled('more_seats')
		);
	}

	/**
	 * @test
	 */
	public function isFormFieldEnabledForEnabledCompanyFieldReturnsTrueForBillingAddress() {
		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array('showRegistrationFields' => 'company, billing_address'),
			$GLOBALS['TSFE']->cObj
		);

		self::assertTrue(
			$fixture->isFormFieldEnabled('billing_address')
		);
	}


	////////////////////////////////////////////////////////
	// Tests concerning getAdditionalRegisteredPersonsData
	////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAdditionalRegisteredPersonsDataForNoDataReturnsEmptyArray() {
		self::assertEquals(
			array(),
			$this->fixture->getAdditionalRegisteredPersonsData()
		);
	}

	/**
	 * @test
	 */
	public function getAdditionalRegisteredPersonsDataForNoEmptyReturnsEmptyArray() {
		$this->fixture->setFakedFormValue('structured_attendees_names', '');

		self::assertEquals(
			array(),
			$this->fixture->getAdditionalRegisteredPersonsData()
		);
	}

	/**
	 * @test
	 */
	public function getAdditionalRegisteredPersonsDataCanReturnDataOfOnePerson() {
		$this->fixture->setFakedFormValue(
			'structured_attendees_names',
			'[["John", "Doe", "Key account", "john@example.com"]]'
		);

		self::assertEquals(
			array(
				array(
					0 => 'John',
					1 => 'Doe',
					2 => 'Key account',
					3 => 'john@example.com',
				),
			),
			$this->fixture->getAdditionalRegisteredPersonsData()
		);
	}

	/**
	 * @test
	 */
	public function getAdditionalRegisteredPersonsDataCanReturnDataOfTwoPersons() {
		$this->fixture->setFakedFormValue(
			'structured_attendees_names',
			'[["John", "Doe", "Key account", "john@example.com"],' .
				'["Jane", "Doe", "Sales", "jane@example.com"]]'
		);

		self::assertEquals(
			array(
				array(
					0 => 'John',
					1 => 'Doe',
					2 => 'Key account',
					3 => 'john@example.com',
				),
				array(
					0 => 'Jane',
					1 => 'Doe',
					2 => 'Sales',
					3 => 'jane@example.com',
				),
			),
			$this->fixture->getAdditionalRegisteredPersonsData()
		);
	}

	/**
	 * @test
	 */
	public function getAdditionalRegisteredPersonsDataForNonArrayDataReturnsEmptyArray() {
		$this->fixture->setFakedFormValue('structured_attendees_names', '"Foo"');

		self::assertEquals(
			array(),
			$this->fixture->getAdditionalRegisteredPersonsData()
		);
	}

	/**
	 * @test
	 */
	public function getAdditionalRegisteredPersonsDataForInvalidJsonReturnsEmptyArray() {
		$this->fixture->setFakedFormValue('structured_attendees_names', 'argh');

		self::assertEquals(
			array(),
			$this->fixture->getAdditionalRegisteredPersonsData()
		);
	}


	/////////////////////////////////////////////////////////////////////////
	// Tests concerning the validation of the number of persons to register
	/////////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForEmptyFormDataReturnsZero() {
		self::assertEquals(
			0,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForNoSelfRegistrationReturnsZero() {
		$this->fixture->setFakedFormValue('registered_themselves', 0);

		self::assertEquals(
			0,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForSelfRegistrationHiddenReturnsOne() {
		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'showRegistrationFields' => 'seats',
				'form.' => array(
					'registration.'	=> array(
						'step1.' => array('seats' => array()),
						'step2.' => array(),
					)
				),
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setAction('register');
		$fixture->setTestMode();

		self::assertEquals(
			1,
			$fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForSelfRegistrationReturnsOne() {
		$this->fixture->setFakedFormValue('registered_themselves', 1);

		self::assertEquals(
			1,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForOneAdditionalPersonReturnsOne() {
		$this->fixture->setFakedFormValue(
			'structured_attendees_names',
			'[["John", "Doe", "Key account", "john@example.com"]]'
		);

		self::assertEquals(
			1,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForTwoAdditionalPersonsReturnsTwo() {
		$this->fixture->setFakedFormValue(
			'structured_attendees_names',
			'[["John", "Doe", "Key account", "john@example.com"],' .
				'["Jane", "Doe", "Sales", "jane@example.com"]]'
		);

		self::assertEquals(
			2,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfEnteredPersonsForSelfRegistrationAndOneAdditionalPersonReturnsTwo() {
		$this->fixture->setFakedFormValue(
			'structured_attendees_names',
			'[["John", "Doe", "Key account", "john@example.com"]]'
		);
		$this->fixture->setFakedFormValue('registered_themselves', 1);

		self::assertEquals(
			2,
			$this->fixture->getNumberOfEnteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function validateNumberOfRegisteredPersonsForZeroSeatsReturnsFalse() {
		$this->fixture->setFakedFormValue('seats', 0);

		self::assertFalse(
			$this->fixture->validateNumberOfRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function validateNumberOfRegisteredPersonsForNegativeSeatsReturnsFalse() {
		$this->fixture->setFakedFormValue('seats', -1);

		self::assertFalse(
			$this->fixture->validateNumberOfRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function validateNumberOfRegisteredPersonsForOnePersonAndOneSeatReturnsTrue() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getNumberOfEnteredPersons', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getNumberOfEnteredPersons')
			->will(self::returnValue(1));
		$fixture->setTestMode();

		$fixture->setFakedFormValue('seats', 1);

		self::assertTrue(
			$fixture->validateNumberOfRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function validateNumberOfRegisteredPersonsForOnePersonAndTwoSeatsReturnsFalse() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getNumberOfEnteredPersons', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getNumberOfEnteredPersons')
			->will(self::returnValue(1));
		$fixture->setTestMode();

		$fixture->setFakedFormValue('seats', 2);

		self::assertFalse(
			$fixture->validateNumberOfRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function validateNumberOfRegisteredPersonsForTwoPersonsAndOneSeatReturnsFalse() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getNumberOfEnteredPersons', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getNumberOfEnteredPersons')
			->will(self::returnValue(2));
		$fixture->setTestMode();

		$fixture->setFakedFormValue('seats', 1);

		self::assertFalse(
			$fixture->validateNumberOfRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function validateNumberOfRegisteredPersonsForTwoPersonsAndTwoSeatsReturnsTrue() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getNumberOfEnteredPersons', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getNumberOfEnteredPersons')
			->will(self::returnValue(2));
		$fixture->setTestMode();

		$fixture->setFakedFormValue('seats', 2);

		self::assertTrue(
			$fixture->validateNumberOfRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getMessageForSeatsNotMatchingRegisteredPersonsForOnePersonAndOneSeatReturnsEmptyString() {
		$this->fixture->setFakedFormValue(
			'structured_attendees_names',
			'[["John", "Doe", "Key account", "john@example.com"]]'
		);
		$this->fixture->setFakedFormValue('seats', 1);

		self::assertEquals(
			'',
			$this->fixture->getMessageForSeatsNotMatchingRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getMessageForSeatsNotMatchingRegisteredPersonsForOnePersonAndTwoSeatsReturnsMessage() {
		$this->fixture->setFakedFormValue(
			'structured_attendees_names',
			'[["John", "Doe", "Key account", "john@example.com"]]'
		);
		$this->fixture->setFakedFormValue('seats', 2);

		self::assertEquals(
			$this->fixture->translate('message_lessAttendeesThanSeats'),
			$this->fixture->getMessageForSeatsNotMatchingRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function getMessageForSeatsNotMatchingRegisteredPersonsForTwoPersonsAndOneSeatReturnsMessage() {
		$this->fixture->setFakedFormValue(
			'structured_attendees_names',
			'[["John", "Doe", "Key account", "john@example.com"],' .
				'["Jane", "Doe", "Sales", "jane@example.com"]]'
		);
		$this->fixture->setFakedFormValue('seats', 1);

		self::assertEquals(
			$this->fixture->translate('message_moreAttendeesThanSeats'),
			$this->fixture->getMessageForSeatsNotMatchingRegisteredPersons()
		);
	}

	/**
	 * @test
	 */
	public function validateNumberOfRegisteredPersonsForAttendeesNamesHiddenAndManySeatsReturnsTrue() {
		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'showRegistrationFields' => 'seats',
				'form.' => array(
					'registration.'	=> array(
						'step1.' => array('seats' => array()),
						'step2.' => array(),
					)
				),
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setAction('register');
		$fixture->setTestMode();

		$fixture->setFakedFormValue('seats', 8);

		self::assertTrue(
			$fixture->validateNumberOfRegisteredPersons()
		);
	}


	/////////////////////////////////////////////////////////////
	// Tests concerning validateAdditionalPersonsEMailAddresses
	/////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function validateAdditionalPersonsEMailAddressesForDisabledFrontEndUserCreationReturnsTrue() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
			->will(self::returnValue(array()));
		$fixture->setTestMode();
		$fixture->setConfigurationValue(
			'createAdditionalAttendeesAsFrontEndUsers', FALSE
		);

		self::assertTrue(
			$fixture->validateAdditionalPersonsEMailAddresses()
		);
	}

	/**
	 * @test
	 */
	public function validateAdditionalPersonsEMailAddressesForDisabledFormFieldReturnsTrue() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(FALSE));
		$fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
			->will(self::returnValue(array()));
		$fixture->setTestMode();
		$fixture->setConfigurationValue(
			'createAdditionalAttendeesAsFrontEndUsers', TRUE
		);

		self::assertTrue(
			$fixture->validateAdditionalPersonsEMailAddresses()
		);
	}

	/**
	 * @test
	 */
	public function validateAdditionalPersonsEMailAddressesForNoPersonsReturnsTrue() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
			->will(self::returnValue(array()));
		$fixture->setTestMode();
		$fixture->setConfigurationValue(
			'createAdditionalAttendeesAsFrontEndUsers', TRUE
		);

		self::assertTrue(
			$fixture->validateAdditionalPersonsEMailAddresses()
		);
	}

	/**
	 * @test
	 */
	public function validateAdditionalPersonsEMailAddressesForOneValidEMailAddressReturnsTrue() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
			->will(self::returnValue(
				array(array('John', 'Doe', '', 'john@example.com'))
			));
		$fixture->setTestMode();
		$fixture->setConfigurationValue(
			'createAdditionalAttendeesAsFrontEndUsers', TRUE
		);

		self::assertTrue(
			$fixture->validateAdditionalPersonsEMailAddresses()
		);
	}

	/**
	 * @test
	 */
	public function validateAdditionalPersonsEMailAddressesForOneInvalidEMailAddressReturnsFalse() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
			->will(self::returnValue(
				array(array('John', 'Doe', '', 'potato salad!'))
			));
		$fixture->setTestMode();
		$fixture->setConfigurationValue(
			'createAdditionalAttendeesAsFrontEndUsers', TRUE
		);

		self::assertFalse(
			$fixture->validateAdditionalPersonsEMailAddresses()
		);
	}

	/**
	 * @test
	 */
	public function validateAdditionalPersonsEMailAddressesForOneEmptyAddressReturnsFalse() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
			->will(self::returnValue(
				array(array('John', 'Doe', '', ''))
			));
		$fixture->setTestMode();
		$fixture->setConfigurationValue(
			'createAdditionalAttendeesAsFrontEndUsers', TRUE
		);

		self::assertFalse(
			$fixture->validateAdditionalPersonsEMailAddresses()
		);
	}

	/**
	 * @test
	 */
	public function validateAdditionalPersonsEMailAddressesForOneMissingAddressReturnsFalse() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
			->will(self::returnValue(
				array(array('John', 'Doe', ''))
			));
		$fixture->setTestMode();
		$fixture->setConfigurationValue(
			'createAdditionalAttendeesAsFrontEndUsers', TRUE
		);

		self::assertFalse(
			$fixture->validateAdditionalPersonsEMailAddresses()
		);
	}

	/**
	 * @test
	 */
	public function validateAdditionalPersonsEMailAddressesForOneValidAndOneInvalidEMailAddressReturnsFalse() {
		$fixture = $this->getMock(
			'Tx_Seminars_FrontEnd_RegistrationForm',
			array('getAdditionalRegisteredPersonsData', 'isFormFieldEnabled'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('isFormFieldEnabled')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('getAdditionalRegisteredPersonsData')
			->will(self::returnValue(
				array(
					array('John', 'Doe', '', 'john@example.com'),
					array('Jane', 'Doe', '', 'tomato salad!'),
				)
			));
		$fixture->setTestMode();
		$fixture->setConfigurationValue(
			'createAdditionalAttendeesAsFrontEndUsers', TRUE
		);

		self::assertFalse(
			$fixture->validateAdditionalPersonsEMailAddresses()
		);
	}


	/////////////////////////////////////////////////
	// Tests concerning getPreselectedPaymentMethod
	/////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPreselectedPaymentMethodForOnePaymentMethodReturnsItsUid() {
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('title' => 'foo')
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$paymentMethodUid,
			'payment_methods'
		);

		self::assertEquals(
			$paymentMethodUid,
			$this->fixture->getPreselectedPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function getPreselectedPaymentMethodForTwoNotSelectedPaymentMethodsReturnsZero() {
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$this->testingFramework->createRecord('tx_seminars_payment_methods'),
			'payment_methods'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$this->testingFramework->createRecord('tx_seminars_payment_methods'),
			'payment_methods'
		);

		self::assertEquals(
			0,
			$this->fixture->getPreselectedPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function getPreselectedPaymentMethodForTwoPaymentMethodsOneSelectedOneNotReturnsUidOfSelectedRecord() {
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$this->testingFramework->createRecord('tx_seminars_payment_methods'),
			'payment_methods'
		);
		$selectedPaymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$selectedPaymentMethodUid,
			'payment_methods'
		);

		$this->session->setAsInteger(
			'tx_seminars_registration_editor_method_of_payment', $selectedPaymentMethodUid
		);

		self::assertEquals(
			$selectedPaymentMethodUid,
			$this->fixture->getPreselectedPaymentMethod()
		);
	}


	/////////////////////////////////////////
	// Tests concerning getRegistrationData
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegistrationDataForDisabledPaymentMethodFieldReturnsEmptyString() {
		$selectedPaymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('title' => 'payment foo')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm',
			$this->seminarUid,
			$selectedPaymentMethodUid,
			'payment_methods'
		);
		$this->fixture->setFakedFormValue(
			'method_of_payment', $selectedPaymentMethodUid
		);

		self::assertEquals(
			'',
			$this->fixture->getAllRegistrationDataForConfirmation()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForEnabledPriceFieldReturnsSelectedPriceValue() {
		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'price',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars',
			$this->seminarUid,
			array('price_regular' => 42)
		);
		$event = new Tx_Seminars_Seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('price', 42);

		self::assertContains(
			'42',
			$fixture->getAllRegistrationDataForConfirmation()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataHtmlspecialcharsInterestsField() {
		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'interests',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new Tx_Seminars_Seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('interests', 'A, B & C');

		self::assertContains(
			'A, B &amp; C',
			$fixture->getAllRegistrationDataForConfirmation()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataReplacesCarriageReturnInInterestsFieldWithBr() {
		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'interests',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new Tx_Seminars_Seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('interests', 'Love' . CR . 'Peace');

		self::assertContains(
			'Love<br />Peace',
			$fixture->getAllRegistrationDataForConfirmation()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataCanContainAttendeesNames() {
		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'attendees_names',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new Tx_Seminars_Seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('attendees_names', 'John Doe');

		self::assertContains(
			'John Doe',
			$fixture->getAllRegistrationDataForConfirmation()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForAttendeesNamesAndThemselvesSelectedContainsUserName() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'', array('name' => 'Jane Doe')
		);

		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'attendees_names,registered_themselves',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new Tx_Seminars_Seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('attendees_names', 'John Doe');
		$fixture->setFakedFormValue('registered_themselves', '1');

		self::assertContains(
			'Jane Doe',
			$fixture->getAllRegistrationDataForConfirmation()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForAttendeesNamesEnabledAndThemselvesNotSelectedNotContainsUserName() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'', array('name' => 'Jane Doe')
		);

		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'attendees_names,registered_themselves',
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new Tx_Seminars_Seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('attendees_names', 'John Doe');
		$fixture->setFakedFormValue('registered_themselves', '');

		self::assertNotContains(
			'Jane Doe',
			$fixture->getAllRegistrationDataForConfirmation()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForThemselvesSelectedAndSeparateAttendeesRecordsDisabledNotContainsTitle() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'',
			array('name' => 'Jane Doe', 'title' => 'facility manager')
		);

		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'attendees_names,registered_themselves',
				'createAdditionalAttendeesAsFrontEndUsers' => FALSE,
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new Tx_Seminars_Seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('registered_themselves', '1');

		self::assertNotContains(
			'facility manager',
			$fixture->getAllRegistrationDataForConfirmation()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForThemselvesSelectedAndSeparateAttendeesRecordsEnabledContainsTitle() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'',
			array('name' => 'Jane Doe', 'title' => 'facility manager')
		);

		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'attendees_names,registered_themselves',
				'createAdditionalAttendeesAsFrontEndUsers' => TRUE,
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new Tx_Seminars_Seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('registered_themselves', '1');

		self::assertContains(
			'facility manager',
			$fixture->getAllRegistrationDataForConfirmation()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForThemselvesSelectedAndSeparateAttendeesRecordsDisabledNotContainsEMailAddress() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'',
			array('name' => 'Jane Doe', 'email' => 'jane@example.com')
		);

		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'attendees_names,registered_themselves',
				'createAdditionalAttendeesAsFrontEndUsers' => FALSE,
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new Tx_Seminars_Seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('registered_themselves', '1');

		self::assertNotContains(
			'jane@example.com',
			$fixture->getAllRegistrationDataForConfirmation()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDataForThemselvesSelectedAndSeparateAttendeesRecordsEnabledContainsEMailAddress() {
		$this->testingFramework->createAndLoginFrontEndUser(
			'',
			array('name' => 'Jane Doe', 'email' => 'jane@example.com')
		);

		$fixture = new Tx_Seminars_FrontEnd_RegistrationForm(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'showRegistrationFields' => 'attendees_names,registered_themselves',
				'createAdditionalAttendeesAsFrontEndUsers' => TRUE,
			),
			$GLOBALS['TSFE']->cObj
		);
		$fixture->setTestMode();

		$event = new Tx_Seminars_Seminar($this->seminarUid);
		$fixture->setSeminar($event);
		$fixture->setFakedFormValue('registered_themselves', '1');

		self::assertContains(
			'jane@example.com',
			$fixture->getAllRegistrationDataForConfirmation()
		);
	}


	/*
	 * Tests concerning getSeminar and getEvent
	 */

	/**
	 * @test
	 */
	public function getSeminarReturnsSeminarFromSetSeminar() {
		self::assertSame(
			$this->seminar,
			$this->fixture->getSeminar()
		);
	}


	/**
	 * @test
	 */
	public function getEventReturnsEventWithSeminarUid() {
		$event = $this->fixture->getEvent();
		self::assertInstanceOf(
			'Tx_Seminars_Model_Event',
			$event
		);

		self::assertSame(
			$this->seminarUid,
			$event->getUid()
		);
	}
}