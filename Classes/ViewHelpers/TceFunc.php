<?php
namespace CommerceTeam\Commerce\ViewHelpers;


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


	/**
	 * Backend utility.
	 *
	 * @var \CommerceTeam\Commerce\Utility\BackendUserUtility $backendUserUtility
	 */
	protected $backendUserUtility;



	public function __construct() {
		$this->backendUserUtility = GeneralUtility::makeInstance('CommerceTeam\\Commerce\\Utility\\BackendUserUtility');
		$this->categoryTree = GeneralUtility::makeInstance('CommerceTeam\\Commerce\\Tree\\CategoryTree');
	}



	/**
	 * This will render a selector box element for selecting elements
	 * of (category) trees.
	 * Depending on the tree it display full trees or root elements only.
	 *
	 * @param array $parameter An array with additional configuration options.
	 * @param UserElement $tmpfObj TCEForms object reference
	 *
	 * @return string The HTML code for the TCEform field
	 */
	public function getSingleField_selectCategories(array $parameter, &$tmpfObj) {
		// TODO Find the ******* error in commerce. For now go with the dark side...
		$parentUids = explode(',', $parameter['itemFormElValue']);
		foreach ($parentUids as $key => $value) {
			if ($pos = strrpos($value, '_')) {
				$parentUids[$key] = (int) substr($value, $pos+1);
			}
		}

		if (empty($parameter['fieldConf']['config']['mountRootOnly'])) {
			$mountPoints = $this->backendUserUtility->getCommerceMounts();
		} else {
			$mountPoints = [0];
		}

		$treeData = $this->categoryTree
			->setShowProducts(false)
			->setShowArticles(false)
			->setPreSelect([
				'tx_commerce_categories' => $parentUids
			])
			->setMountPoints($mountPoints)
			->getTree();



		return '<input type="hidden" id="' . $parameter['itemFormElID'] . '" name="' . $parameter['itemFormElName'] . '" value="' . implode(',', $parentUids) . '" />'
			. '<div data-input-id="' . $parameter['itemFormElID'] . '" class="tca-fancy-tree">'
			. '<script type="application/json" class="fancy-tree-data">' . json_encode($treeData) . '</script>'
			. '</div>'
			//. '<pre>' . var_export($treeData, true) . '</pre>'
			//. '<pre>' . var_export($parameter, true) . '</pre>'
		;

	}

}
