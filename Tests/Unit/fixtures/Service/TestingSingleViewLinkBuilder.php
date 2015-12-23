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
 * This class just makes some functions public for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Fixtures_Service_TestingSingleViewLinkBuilder extends tx_seminars_Service_SingleViewLinkBuilder {
	/**
	 * Retrieves a content object to be used for creating typolinks.
	 *
	 * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer a content object for creating typolinks
	 */
	public function getContentObject() {
		return parent::getContentObject();
	}

	/**
	 * Creates a content object.
	 *
	 * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 *         a created content object (will always be the same instance)
	 */
	public function createContentObject() {
		return parent::createContentObject();
	}

	/**
	 * Gets the single view page UID/URL from $event (if any single view page is set for
	 * the event) or from the configuration.
	 *
	 * @param tx_seminars_Model_Event $event the event for which to get the single view page
	 *
	 * @return string
	 *         the single view page UID/URL for $event, will be empty if neither
	 *         the event nor the configuration has any single view page set
	 */
	public function getSingleViewPageForEvent(tx_seminars_Model_Event $event) {
		return parent::getSingleViewPageForEvent($event);
	}

	/**
	 * Checks whether there is a single view page set in the configuration.
	 *
	 * @return bool
	 *         TRUE if a single view page has been set in the configuration,
	 *         FALSE otherwise
	 */
	public function configurationHasSingleViewPage() {
		return parent::configurationHasSingleViewPage();
	}

	/**
	 * Retrieves the single view page UID from the flexforms/TS Setup
	 * configuration.
	 *
	 * @return int
	 *         the single view page UID from the configuration, will be 0 if no
	 *         page UID has been set
	 */
	public function getSingleViewPageFromConfiguration() {
		return parent::getSingleViewPageFromConfiguration();
	}
}