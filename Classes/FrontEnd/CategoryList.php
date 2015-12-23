<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Niels Pardon (mail@niels-pardon.de)
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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a category list.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_FrontEnd_CategoryList extends Tx_Seminars_FrontEnd_AbstractView {
	/**
	 * Creates a HTML list of categories.
	 *
	 * This list is limited to categories for which there are events in the
	 * selected time-frame and in the selected sysfolders. Categories for which
	 * all events are canceled will always be ignored.
	 *
	 * @return string HTML code of the category list or a formatted message if
	 *                there are no categories to display
	 */
	public function render() {
		/** @var Tx_Seminars_BagBuilder_Event $seminarBagBuilder */
		$seminarBagBuilder = GeneralUtility::makeInstance('Tx_Seminars_BagBuilder_Event');
		$seminarBagBuilder->setSourcePages(
			$this->getConfValueString('pages'),
			$this->getConfValueInteger('recursive')
		);

		$seminarBagBuilder->ignoreCanceledEvents();
		try {
			$seminarBagBuilder->setTimeFrame(
				$this->getConfValueString(
					'timeframeInList', 's_template_special'
				)
			);
		} catch (Exception $exception) {
			// Ignores the exception because the user will be warned of the
			// problem by the configuration check.
		}

		$bag = $seminarBagBuilder->build();
		$eventUids = $bag->getUids();

		/** @var Tx_Seminars_BagBuilder_Category $categoryBagBuilder */
		$categoryBagBuilder = GeneralUtility::makeInstance('Tx_Seminars_BagBuilder_Category');
		$categoryBagBuilder->limitToEvents($eventUids);
		$categoryBag = $categoryBagBuilder->build();

		// Only lists categories for which there are events.
		if (($eventUids != '') && !$categoryBag->isEmpty()) {
			$allCategories = '';

			/** @var tx_seminars_OldModel_Category $category */
			foreach ($categoryBag as $category) {
				$link = $this->createLinkToListViewLimitedByCategory(
					$category->getUid(),
					htmlspecialchars($category->getTitle())
				);
				$this->setMarker('category_title', $link);

				$allCategories .= $this->getSubpart('SINGLE_CATEGORY_ITEM');
			}

			$this->setMarker('all_category_items', $allCategories);
			$result = $this->getSubpart('VIEW_CATEGORIES');
		} else {
			$result = $this->getSubpart('VIEW_NO_CATEGORIES');
		}

		$this->checkConfiguration();
		$result .= $this->getWrappedConfigCheckMessage();

		return $result;
	}

	/**
	 * Creates a hyperlink with the title $title to the current list view,
	 * limited to the category provided by the parameter $categoryUid.
	 *
	 * @param int $categoryUid UID of the category to which the list view should be limited, must be > 0
	 * @param string $title title of the link, must not be empty
	 *
	 * @return string link to the list view limited to the given category or an
	 *                empty string if there is an error
	 */
	public function createLinkToListViewLimitedByCategory(
		$categoryUid, $title
	) {
		if ($categoryUid <= 0) {
			throw new InvalidArgumentException('$categoryUid must be > 0.', 1333293037);
		}
		if ($title == '') {
			throw new InvalidArgumentException('$title must not be empty.', 1333293044);
		}

		return $this->cObj->getTypoLink(
			$title,
			$this->getConfValueInteger('listPID'),
			array('tx_seminars_pi1[category]' => $categoryUid)
		);
	}

	/**
	 * Creates the list of categories for the event list view.
	 *
	 * Depending on the configuration value, categoriesInListView returns
	 * either only the titles as comma-separated list, only the icons with the
	 * title as title attribute or both.
	 *
	 * @param array[] $categoriesToDisplay
	 *        the categories in an associative array, with the UID as key and "title", and "icon" as second level keys
	 *
	 * @return string the HTML output, will be empty if $categoriesToDisplay
	 *                is empty
	 */
	public function createCategoryList(
		array $categoriesToDisplay
	) {
		if (empty($categoriesToDisplay)) {
			return '';
		}

		$categories
			= $this->getConfValueString('categoriesInListView', 's_listView');
		$allCategoryLinks = array();
		$categorySeparator = ($categories != 'icon') ? ', ' : ' ';

		foreach ($categoriesToDisplay as $uid => $value) {
			$linkValue = '';
			switch ($categories) {
				case 'both':
					if ($value['icon'] != '') {
						$linkValue = $this->createCategoryIcon($value) .
							'&nbsp;';
					}
					$linkValue .= htmlspecialchars($value['title']);
					break;
				case 'icon':
					$linkValue = $this->createCategoryIcon($value);
					if ($linkValue == '') {
						$linkValue = htmlspecialchars($value['title']);
						$categorySeparator = ', ';
					}
					break;
				default:
					$linkValue = htmlspecialchars($value['title']);
			}
			$allCategoryLinks[]
				= $this->createLinkToListViewLimitedByCategory($uid, $linkValue);
		}

		return implode($categorySeparator, $allCategoryLinks);
	}

	/**
	 * Creates the category icon with the icon title as alt text.
	 *
	 * @param string[] $iconData
	 *        the filename and title of the icon in an associative array with "icon" as key for the filename and "title" as key
	 *        for the icon title, the values for "title" and "icon" may be empty
	 *
	 * @return string the icon tag with the given icon, will be empty if no
	 *                icon was given
	 */
	private function createCategoryIcon(array $iconData) {
		if ($iconData['icon'] == '') {
			return '';
		}

		$imageWithoutClass = $this->cObj->IMAGE(
			array(
				'file' => Tx_Seminars_FrontEnd_AbstractView::UPLOAD_PATH .
					$iconData['icon'],
				'titleText' => $iconData['title'],
			)
		);

		return str_replace(
			'<img ', '<img class="category_image" ', $imageWithoutClass
		);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/CategoryList.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/FrontEnd/CategoryList.php']);
}