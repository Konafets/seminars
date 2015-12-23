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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class that adds the wizard icon.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_FrontEnd_WizardIcon {
	/**
	 * Processes the wizard items array.
	 *
	 * @param array[] $wizardItems the wizard items, may be empty
	 *
	 * @return array[] modified array with wizard items
	 */
	public function proc(array $wizardItems) {
		$localLanguage = $this->includeLocalLang();

		$wizardItems['plugins_tx_seminars_pi1'] = array(
			'icon' => ExtensionManagementUtility::extRelPath('seminars') . 'Resources/Public/Icons/ContentWizard.gif',
			'title' => $GLOBALS['LANG']->getLLL('pi1_title', $localLanguage),
			'description' => $GLOBALS['LANG']->getLLL('pi1_description', $localLanguage),
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=seminars_pi1',
		);

		return $wizardItems;
	}

	/**
	 * Reads the [extDir]/locallang.xml and returns the $LOCAL_LANG array found in that file.
	 *
	 * @return array[] the found language labels
	 */
	public function includeLocalLang() {
		if (class_exists('TYPO3\\CMS\\Core\\Localization\\Parser\\LocallangXmlParser')) {
			/** @var \TYPO3\CMS\Core\Localization\Parser\LocallangXmlParser $xmlParser */
			$xmlParser = GeneralUtility::makeInstance('TYPO3\CMS\Core\Localization\Parser\LocallangXmlParser');
			$localLanguage = $xmlParser->getParsedData(
				ExtensionManagementUtility::extPath('seminars') . 'locallang.xml', $GLOBALS['LANG']->lang
			);
		} else {
			$localLanguage = GeneralUtility::readLLXMLfile(
				ExtensionManagementUtility::extPath('seminars') . 'locallang.xml', $GLOBALS['LANG']->lang
			);
		}

		return $localLanguage;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/WizardIcon.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/WizardIcon.php']);
}