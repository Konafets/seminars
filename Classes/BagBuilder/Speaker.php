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
 * This builder class creates customized speaker bag objects.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_BagBuilder_Speaker extends Tx_Seminars_BagBuilder_Abstract {
	/**
	 * @var string class name of the bag class that will be built
	 */
	protected $bagClassName = 'Tx_Seminars_Bag_Speaker';

	/**
	 * @var string the table name of the bag to build
	 */
	protected $tableName = 'tx_seminars_speakers';
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BagBuilder/Speaker.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BagBuilder/Speaker.php']);
}