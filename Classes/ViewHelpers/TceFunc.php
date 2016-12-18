<?php
namespace CommerceTeam\Commerce\ViewHelpers;

use TYPO3\CMS\Core\Page\PageRenderer;


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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Holds the TCE Functions.
 *
 * Class \CommerceTeam\Commerce\ViewHelpers\TceFunc
 *
 * @author 2008-2011 Erik Frister <typo3@marketing-factory.de>
 */
class TceFunc {


	/**
	 * categoryTree
	 *
	 * @var \CommerceTeam\Commerce\Tree\CategoryTree
	 */
	protected $categoryTree;



	public function __construct() {
		$this->categoryTree = GeneralUtility::makeInstance('CommerceTeam\\Commerce\\Tree\\CategoryTree');
	}



	/**
	 * This will render a selector box element for selecting elements
	 * of (category) trees.
	 * Depending on the tree it display full trees or root elements only.
	 *
	 * @param array $parameter An array with additional configuration options.
	 * @param UserElement $fObj TCEForms object reference
	 *
	 * @return string The HTML code for the TCEform field
	 */
	public function getSingleField_selectCategories(array $parameter, &$tmpfObj) {
		return $this->categoryTree->setShowProducts(false)->setShowArticles(false)->getRenderedTCACategoryChooser($parameter);
	}

}
