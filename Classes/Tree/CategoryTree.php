<?php
namespace CommerceTeam\Commerce\Tree;

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

use TYPO3\CMS\Core\Type\Bitmask\Permission;

/**
* Implements a Categorytree
* A tree can have n leafs, and leafs can in itself contain other leafs.
*
* Class \CommerceTeam\Commerce\Tree\CategoryTree
*
* @author 2008-2011 Erik Frister <typo3@marketing-factory.de>
*/
class CategoryTree {

	/**
	 * pageRepository
	 *
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	protected $pageRepository;


	/**
	 * Backend utility.
	 *
	 * @var \CommerceTeam\Commerce\Utility\BackendUserUtility $backendUserUtility
	 */
	protected $backendUserUtility;



	protected $tree = [];

	protected $nodeDataCollection = [];



	protected $nodeModifiers = [];



	protected $preSelectTable2Uids = [];

	protected $preExpandedTable2Uids = [];


	protected $mountPoints = [];


	protected $showProducts = true;

	protected $showArticles = true;


	protected $expanded = false;




	public function __construct() {
		$this->pageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$this->backendUserUtility = GeneralUtility::makeInstance('CommerceTeam\\Commerce\\Utility\\BackendUserUtility');

		$this->mountPoints = [0];
	}


	public function setTreeConf(array $treeConf) {
		foreach ($treeConf as $confName => $confValue) {
			$methodName = 'set' . ucfirst($confName);
			if (method_exists($this, $methodName)) {
				$this->{$methodName}($confValue);
			}
		}
	}


	/**
	 * Initializes the Categorytree.
	 *
	 * @return void
	 */
	public function generateTree() {

		$rootNode = [
			'refKey'       => '0',
			'type'         => 'root',
			'extraClasses' => 'root',
			'title'        => 'Category',
			'folder'       => 'true',
			'uid'          => 0,
			'children'     => [],
		];

		if (isset($this->preSelectTable2Uids['tx_commerce_categories'][0]) && $this->preSelectTable2Uids['tx_commerce_categories'][0]) {
			$rootNode['selected'] = true;
		}
		if ($this->expanded || (isset($this->preExpandedTable2Uids['tx_commerce_categories'][0]) && $this->preExpandedTable2Uids['tx_commerce_categories'][0])) {
			$rootNode['expanded'] = true;
		}


		$this->nodeDataCollection['tx_commerce_categories'][0] = $rootNode;


		$this->addTreeData('tx_commerce_categories', 'tx_commerce_categories', 'tx_commerce_categories_parent_category_mm');

		if ($this->showProducts) {
			$this->addTreeData('tx_commerce_products', 'tx_commerce_categories', 'tx_commerce_products_categories_mm');

			if ($this->showArticles) {
				$this->addArticles();
			}
		}

		$this->tree = [
			'children'    => [],
			'mountPoints' => $this->mountPoints,
			//'beuser'      => $GLOBALS['BE_USER'],
			//'begroups'    => $GLOBALS['BE_USER']->userGroups,
			//'isAdmin'     => $GLOBALS['BE_USER']->isAdmin(),
		];

		foreach ($this->mountPoints as $mountPoint) {
			if (isset($this->nodeDataCollection['tx_commerce_categories'][$mountPoint])) {
				$this->tree['children'][] = $this->nodeDataCollection['tx_commerce_categories'][$mountPoint];
			}
		}
	}



	protected function addTreeData($tableName, $parentTableName, $mmTableName) {
		$select = 'uid, title, hidden';
		if ($tableName === 'tx_commerce_categories') {
			$select .= ', perms_userid, perms_groupid, perms_user, perms_group, perms_everybody, editlock';
		}
		$nodeDataRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$select,
			$tableName,
			'sys_language_uid = 0' . $this->pageRepository->deleteClause($tableName)
		);


		while ($nodeData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($nodeDataRes)) {
			$this->addNode($tableName, $nodeData);
		}


		$resMM = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_local, uid_foreign, sorting', $mmTableName, '', '', 'uid_foreign, sorting');

		while ($mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resMM)) {
			if (isset($this->nodeDataCollection[$tableName][$mm['uid_local']], $this->nodeDataCollection[$parentTableName][$mm['uid_foreign']])) {
				$this->nodeDataCollection[$parentTableName][$mm['uid_foreign']]['children'][] = & $this->nodeDataCollection[$tableName][$mm['uid_local']];
			}
		}
	}


	protected function addArticles() {
		$tableName = 'tx_commerce_articles';
		$nodeDataRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, title, hidden, uid_product',
			$tableName,
			'sys_language_uid = 0' . $this->pageRepository->deleteClause($tableName)
		);

		while ($nodeData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($nodeDataRes)) {
			$this->addNode($tableName, $nodeData);

			if (isset($this->nodeDataCollection['tx_commerce_products'][$nodeData['uid_product']])) {
				$this->nodeDataCollection['tx_commerce_products'][$nodeData['uid_product']]['children'][] = & $this->nodeDataCollection[$tableName][$nodeData['uid']];
			} else {
				// TODO: throw \Exception ??!?
			}
		}
	}


	protected function addNode($tableName, $nodeData) {
		if($tableName === 'tx_commerce_categories') {
			$accessRights = $this->backendUserUtility->getAccessRights($nodeData);
			if (($accessRights & Permission::PAGE_SHOW) === Permission::PAGE_SHOW) {
				$this->nodeDataCollection[$tableName][$nodeData['uid']] = $this->createCategoryNode($tableName, $nodeData);
				$this->nodeDataCollection[$tableName][$nodeData['uid']]['currentUserAccess'] = $accessRights;
			}
		} else if ($tableName === 'tx_commerce_products') {
			$this->nodeDataCollection[$tableName][$nodeData['uid']] = $this->createProductNode($tableName, $nodeData);
		} else if ($tableName === 'tx_commerce_articles') {
			$this->nodeDataCollection[$tableName][$nodeData['uid']] = $this->createArticleNode($tableName, $nodeData);
		} else {
			throw new \Exception("Could not generate node from data. Unknown table name '$tableName'!", 1);
		}

		foreach ($this->nodeModifiers as $userFunc) {
			$this->nodeDataCollection[$tableName][$nodeData['uid']] = $userFunc(
				$this->nodeDataCollection[$tableName][$nodeData['uid']],
				$tableName,
				$nodeData
			);
		}

		return $this->nodeDataCollection[$tableName][$nodeData['uid']] ?: FALSE;
	}

	protected function createCategoryNode($tableName, $nodeData) {
		$node = $this->createNode($tableName, $nodeData);

		$node['folder'] = true;
		$node['extraClasses'] .= ' category';

		return $node;
	}

	protected function createProductNode($tableName, $nodeData) {
		$node = $this->createNode($tableName, $nodeData);

		$node['folder'] = false;
		$node['extraClasses'] .= ' product';

		return $node;
	}

	protected function createArticleNode($tableName, $nodeData) {
		$node = $this->createNode($tableName, $nodeData);

		$node['folder'] = false;
		$node['extraClasses'] .= ' article';

		return $node;
	}

	protected function createNode($tableName, $nodeData) {
		$node = [];
		$node['refKey'] = $tableName . '|' . $nodeData['uid'];
		$node['title']  = $nodeData['title'];
		$node['type']   = $tableName;
		$node['uid']    = $nodeData['uid'];

		if ($nodeData['hidden']) {
			$node['extraClasses'] = ' is-hidden';
		}

		if (isset($this->preSelectTable2Uids[$tableName][$node['uid']]) && $this->preSelectTable2Uids[$tableName][$node['uid']]) {
			$node['selected'] = true;
		}

		if ($this->expanded || (isset($this->preExpandedTable2Uids[$tableName][$node['uid']]) && $this->preExpandedTable2Uids[$tableName][$node['uid']])) {
			$node['expanded'] = true;
		}

		return $node;
	}



	public function addNodeModifier($function) {
		if (is_callable($function)) {
			$this->nodeModifiers[] = $function;
		}
		return $this;
	}



	public function getShowProducts() {
		return $this->showProducts;
	}

	public function setShowProducts($showProducts) {
		$this->showProducts = !!$showProducts;
		return $this;
	}



	public function getShowArticles() {
		return $this->showArticles;
	}

	public function setShowArticles($showArticles) {
		$this->showArticles = !!$showArticles;
		return $this;
	}



	public function getMountPoints() {
		return $this->mountPoints;
	}

	public function setMountPoints($mountPoints) {
		$this->mountPoints = $mountPoints;
		return $this;
	}




	public function getPreSelect() {
		return $this->preSelectTable2Uids;
	}

	public function setPreSelect($preSelectTable2Uids) {
		$this->preSelectTable2Uids = [];

		foreach ($preSelectTable2Uids as $tableName => $selUIDs) {
			foreach ($selUIDs as $selUID) {
				$this->preSelectTable2Uids[$tableName][$selUID] = true;
			}
		}
		return $this;
	}




	public function getPreExpanded() {
		return $this->preExpandedTable2Uids;
	}

	public function setPreExpanded($preExpandedTable2Uids) {
		foreach ($preExpandedTable2Uids as $tableName => $preExpUids) {
			foreach ($preExpUids as $preExpUid) {
				$this->preExpandedTable2Uids[$tableName][$preExpUid] = true;
			}
		}
		return $this;
	}




	public function isExpanded() {
		return $this->expanded;
	}

	public function setExpanded($expanded) {
		$this->expanded = (bool) $expanded;
		return $this;
	}





	public function getTree() {
		if (empty($this->tree)) {
			$this->generateTree();
		}
		return $this->tree;
	}
}
