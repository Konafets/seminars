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
 * This class represents a mapper for front-end user groups.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Mapper_FrontEndUserGroup extends tx_oelib_Mapper_FrontEndUserGroup {
	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = 'Tx_Seminars_Model_FrontEndUserGroup';

	/**
	 * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
	 */
	protected $relations = array(
		'tx_seminars_reviewer' => 'tx_oelib_Mapper_BackEndUser',
		'tx_seminars_default_categories' => 'Tx_Seminars_Mapper_Category',
		'tx_seminars_default_organizer' => 'Tx_Seminars_Mapper_Organizer',
	);
}