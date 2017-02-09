<?php
namespace CommerceTeam\Commerce\Controller\Backend;

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

use TYPO3\CMS\Core\Http\AjaxRequestHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class \CommerceTeam\Commerce\ViewHelpers\Navigation\AjaxProductTreeController
 *
 * @author Anselm Ruby <a.ruby@connetation.at>
 */
class AccessAjaxController {

    /**
     * Makes the AJAX call to expand or collapse the categorytree.
     * Called by typo3/ajax.php.
     *
     * @param array $params Additional parameters (not used here)
     * @param AjaxRequestHandler $ajaxObj Ajax object
     *
     * @return void
     */
    public function ajaxGetAccessData(array $paramsRaw, AjaxRequestHandler &$ajaxObj) {
    	if (!$GLOBALS['BE_USER']->isAdmin()) {
    		$ajaxObj->setError('Access Denied!');
			return;
    	}

		$categoryTree = GeneralUtility::makeInstance('CommerceTeam\\Commerce\\Tree\\CategoryTree');

		$params = $paramsRaw['request']->getQueryParams();

		if (isset($params['treeId'])) {
			$preExpandedState = unserialize($GLOBALS['BE_USER']->uc[$params['treeId']]);
			if (is_array($preExpandedState)) {
				$categoryTree->setPreExpanded($preExpandedState);
			}
		}

		if (isset($params['treeConf'])) {
			$categoryTree->setTreeConf($params['treeConf']);
		}

		$treeData = $categoryTree
			->setShowProducts(false)
			->setShowArticles(false)
			->addNodeModifier(function(&$node, &$tableName, &$nodeData) {
				if ($tableName === 'tx_commerce_categories') {
					$node['access'] = array(
						'perms_userid'    => (int) $nodeData['perms_userid'],
						'perms_groupid'   => (int) $nodeData['perms_groupid'],
						'perms_user'      => (int) $nodeData['perms_user'],
						'perms_group'     => (int) $nodeData['perms_group'],
						'perms_everybody' => (int) $nodeData['perms_everybody'],
						'editlock'        => (bool) $nodeData['editlock'],
					);
				}
				return $node;
			})
			->getTree();


		$treeData['beUsers']  = $this->getBeUsers();
		$treeData['beGroups'] = $this->getBeGroups();


		$ajaxObj->setContentFormat('json');
        $ajaxObj->setContent($treeData);
    }


	public function ajaxSetAccess(array $paramsRaw, AjaxRequestHandler &$ajaxObj) {
    	if (!$GLOBALS['BE_USER']->isAdmin()) {
    		$ajaxObj->setError('Access Denied!');
			return;
    	}

		$params = $paramsRaw['request']->getParsedBody();

		$catUid = (int)explode('|', $params['refKey'])[1];

		$recursive = filter_var($params['recursive'], FILTER_VALIDATE_BOOLEAN);

		// Make sure no other fields are set...
        $perms = [
		    'perms_userid'    => (int)$params['access']['perms_userid'],
		    'perms_groupid'   => (int)$params['access']['perms_groupid'],
		    'perms_user'      => (int)$params['access']['perms_user'],
		    'perms_group'     => (int)$params['access']['perms_group'],
		    'perms_everybody' => (int)$params['access']['perms_everybody'],
		    'editlock'        => (int)($params['access']['editlock'] ? 1 : 0)
		];

		$this->updateCategory($catUid, $recursive, $perms);

		$ajaxObj->setContentFormat('json');
		//$ajaxObj->addContent('params', $params);
	}


	private function getBeUsers() {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, username, disable',
			'be_users',
			'deleted = 0',
			'', 'username', '',
			'uid'
		);
	}


	private function getBeGroups() {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, title, hidden',
			'be_groups',
			'deleted = 0',
			'', 'title', '',
			'uid'
		);
	}

	private function updateCategory($catUid, $recursive, $perms) {
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
        	'tx_commerce_categories',
        	'deleted = 0 AND (uid = ' . ((int)$catUid) . ' OR l18n_parent = ' . ((int)$catUid) . ')',
        	$perms
		);

		if ($recursive) {
	        $subCategories = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
	            'c.uid as uid',
	            'tx_commerce_categories_parent_category_mm mm JOIN tx_commerce_categories c ON mm.uid_local = c.uid',
	            'mm.uid_foreign = ' . ((int)$catUid) . ' AND c.deleted = 0'
	        );
			foreach ($subCategories as $subCategory) {
				$this->updateCategory($subCategory['uid'], $recursive, $perms);
			}
		}
	}

}
