<?php

namespace CommerceTeam\Commerce\ViewHelpers\Be;

/**
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
 * ViewHelper to generate a Backend-Link to edit a record
 *
 * @package TYPO3
 * @subpackage tx_commerce
 */
class IfPermissionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * Backend utility.
	 *
	 * @var \CommerceTeam\Commerce\Utility\BackendUserUtility
	 * @inject
	 */
	protected $backendUserUtility;

	protected $perms = [
		'show'        =>  1,
		'edit'        =>  2,
		'delete'      =>  4,
		'new'         =>  8,
		'editcontent' => 16,
		'all'         => 31,
	];



	public function __construct() {
		parent::__construct();
		$this->registerArgument('category',   'NewCategory', 'The category you want ask for permission.', true);
		$this->registerArgument('permission', 'string',      'The permission you need.', true);
	}


	/**
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function render() {
		//return $this->renderThenChild();
		$permsAnd = explode('||', $this->arguments['permission']);
		foreach ($permsAnd as $permAnd) {
			$perms = explode('&&', $permAnd);
			$permNum = 0;
			foreach ($perms as $perm) {
				$permLower = trim(strtolower($perm));
				if (isset($this->perms[$permLower])) {
					$permNum |= $this->perms[$permLower];
				} else {
					throw new \Exception('Unknown Permission: "' . $perm . '"');
				}
			}
			if ($this->backendUserUtility->canAccess($this->arguments['category'], $permNum)) {
				return $this->renderThenChild();
			}
		}

		return $this->renderElseChild();
	}

}
