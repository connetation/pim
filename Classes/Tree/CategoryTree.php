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



	protected $tree = array();

	protected $nodeDataCollection = array();



	protected $showProducts = true;

	protected $showArticles = true;





    public function __construct() {
        $this->pageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
    }



	/**
	 * Initializes the Categorytree.
	 *
	 * @return void
	 */
	public function generateTree() {

		$this->tree = [
			'refKey'       => '0',
			'type'         => 'root',
			'extraClasses' => 'root',
			'title'        => 'Category',
			'folder'       => 'true',
			'children'     => [],
		];

		$this->addTreeData('tx_commerce_categories', 'tx_commerce_categories', 'tx_commerce_categories_parent_category_mm');

		if ($this->showProducts) {
			$this->addTreeData('tx_commerce_products', 'tx_commerce_categories', 'tx_commerce_products_categories_mm');

			if ($this->showArticles) {
				$this->addArticles();
			}
		}

	}


	public function preSelect($tableName2UIDs) {
		foreach ($tableName2UIDs as $tableName => $selUIDs) {
			foreach ($selUIDs as $selUID) {
				if ($selUID != 0) {
					if (isset($this->nodeDataCollection[$tableName][$selUID])) {
						$this->nodeDataCollection[$tableName][$selUID]['selected'] = true;
					} else {
						throw new \Exception("Could not pre-select '$tableName' with UID '$selUID'. Not found!", 1);
					}
				}
			}
		}
	}


	public function preExpanded($tableName2UIDs) {
	  if(is_array($tableName2UIDs)) {
  		foreach ($tableName2UIDs as $tableName => $selUIDs) {
  		  if(is_array($selUIDs)) {
    			foreach ($selUIDs as $selUID) {
    				if (isset($this->nodeDataCollection[$tableName][$selUID])) {
    					$this->nodeDataCollection[$tableName][$selUID]['expanded'] = true;
    				} else {
    					throw new \Exception("Could not pre-select '$tableName' with UID '$selUID'. Not found!", 1);
    				}
    			}
    		}
  		}
    }
	}


	protected function addTreeData($tableName, $parentTableName, $mmTableName, $nmWhere = '') {
        $nodeDataRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'uid, title, hidden',
            $tableName,
            'sys_language_uid = 0' . $this->pageRepository->deleteClause($tableName)
        );

		while ($nodeData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($nodeDataRes)) {
			$this->nodeDataCollection[$tableName][$nodeData['uid']] = $this->getNodeFromData($tableName, $nodeData);
		}


        $resMM = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_local, uid_foreign, sorting', $mmTableName, $nmWhere, '', 'uid_foreign, sorting');

        while ($mm = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resMM)) {
            if (isset($this->nodeDataCollection[$tableName][$mm['uid_local']])) {
                if ($mm['uid_foreign'] == 0) {
                    $this->tree['children'][] = & $this->nodeDataCollection[$tableName][$mm['uid_local']];
                } else {
                    $this->nodeDataCollection[$parentTableName][$mm['uid_foreign']]['children'][] = & $this->nodeDataCollection[$tableName][$mm['uid_local']];
                }
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
			$this->nodeDataCollection[$tableName][$nodeData['uid']] = $this->getNodeFromData($tableName, $nodeData);

            if (isset($this->nodeDataCollection['tx_commerce_products'][$nodeData['uid_product']])) {
            	$this->nodeDataCollection['tx_commerce_products'][$nodeData['uid_product']]['children'][] = & $this->nodeDataCollection[$tableName][$nodeData['uid']];
            } else {
                // TODO: throw \Exception ??!?
            }
		}
	}


	protected function getNodeFromData($tableName, $data) {
		$node = array();
		$node['refKey'] = $tableName . '|' . $data['uid'];
		$node['title']  = $data['title'];
		$node['type']   = $tableName;
		$node['uid']    = $data['uid'];

		if($tableName == 'tx_commerce_categories') {
			$node['folder'] = true;
			$node['extraClasses'] = 'category';
		} else if ($tableName == 'tx_commerce_products') {
			$node['folder'] = false;
			$node['extraClasses'] = 'product';
		} else if ($tableName == 'tx_commerce_articles') {
			$node['folder'] = false;
			$node['extraClasses'] = 'article';

		} else {
			throw new \Exception("Could not generate node from data. Unknown table name '$tableName'!", 1);
		}


		if ($data['hidden']) {
			$node['extraClasses'] .= ' is-hidden';
		}

		return $node;
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




	public function getRenderedTCACategoryChooser($parameter) {
		if (empty($this->tree)) {
			$this->generateTree();
		}
		$this->preSelect([
			'tx_commerce_categories' => explode(',', $parameter['itemFormElValue'])
		]);
        return '<input type="text" id="' . $parameter['itemFormElID'] . '" name="' . $parameter['itemFormElName'] . '" value="' . $parameter['itemFormElValue'] . '" />'
        	. '<div data-input-id="' . $parameter['itemFormElID'] . '" class="tca-fancy-tree">'
        		. '<script type="application/json" class="fancy-tree-data">' . json_encode($this->tree) . '</script>'
        	. '</div>'
            //. '<pre>' . var_export($this->tree, true) . '</pre><pre>' . var_export($parameter, true) . '</pre>'
		;
	}




	public function getTreeJSON() {
		if (empty($this->tree)) {
			$this->generateTree();
		}

		$treeState = unserialize($GLOBALS['BE_USER']->uc['commerceNavTreeState']);
		$this->preExpanded($treeState);

		return json_encode([
			'recordEdit' => \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('record_edit'),
			'productPID' => \CommerceTeam\Commerce\Utility\BackendUtility::getProductFolderUid(),
			'children'   => [$this->tree]
		]);
	}
}
