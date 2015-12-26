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
 * This class represents a mapper for registrations.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Mapper_Registration extends Tx_Oelib_DataMapper {
	/**
	 * @var string the name of the database table for this mapper
	 */
	protected $tableName = 'tx_seminars_attendances';

	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = 'Tx_Seminars_Model_Registration';

	/**
	 * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
	 */
	protected $relations = array(
		'seminar' => 'Tx_Seminars_Mapper_Event',
		'user' => 'Tx_Seminars_Mapper_FrontEndUser',
		'currency' => 'tx_oelib_Mapper_Currency',
		'method_of_payment' => 'Tx_Seminars_Mapper_PaymentMethod',
		'lodgings' => 'Tx_Seminars_Mapper_Lodging',
		'foods' => 'Tx_Seminars_Mapper_Food',
		'checkboxes' => 'Tx_Seminars_Mapper_Checkbox',
		'additional_persons' => 'Tx_Seminars_Mapper_FrontEndUser',
	);
}