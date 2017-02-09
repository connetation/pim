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
class AjaxProductTreeController {


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
     * Makes the AJAX call to expand or collapse the categorytree.
     * Called by typo3/ajax.php.
     *
     * @param array $paramsRaw Additional parameters
     * @param AjaxRequestHandler $ajaxObj Ajax object
     *
     * @return void
     */
    public function ajaxExpandCollapse(array $paramsRaw, AjaxRequestHandler &$ajaxObj) {
        $params = $paramsRaw['request']->getQueryParams();

        $treeState = null;
        if (isset($params['action'], $params['table'], $params['uid'], $params['treeId'])) {
            $treeId = $params['treeId'];
            $treeState = unserialize($GLOBALS['BE_USER']->uc[$treeId]);
            if ($params['action'] === 'collapse') {
                if (isset($treeState[$params['table']][$params['uid']])) {
                    unset($treeState[$params['table']][$params['uid']]);
                }
            } else {
                $treeState[$params['table']][$params['uid']] = $params['uid'];
            }

            $GLOBALS['BE_USER']->uc[$treeId] = serialize($treeState);
            $GLOBALS['BE_USER']->writeUC();
        }

        //$ajaxObj->addContent('tree', var_export($treeState, true));
    }

    /**
     * Makes the AJAX call to expand or collapse the categorytree.
     * Called by typo3/ajax.php.
     *
     * @param array $paramsRaw Additional parameters (not used here)
     * @param AjaxRequestHandler $ajaxObj Ajax object
     *
     * @return void
     */
    public function ajaxGetCategoryTreeData(array $paramsRaw, AjaxRequestHandler $ajaxObj) {
		$params = $paramsRaw['request']->getQueryParams();

		if (isset($params['treeConf'])) {
			$this->categoryTree->setTreeConf($params['treeConf']);
		}

		if (isset($params['treeId'])) {
			$preExpandedState = unserialize($GLOBALS['BE_USER']->uc[$params['treeId']]);
			if (is_array($preExpandedState)) {
				$this->categoryTree->setPreExpanded($preExpandedState);
			}
		}

		$this->categoryTree->setMountPoints(
			$this->backendUserUtility->getCommerceMounts()
		);

		$treeData = $this->categoryTree->getTree();
        $treeData['recordEdit'] = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('record_edit');
        $treeData['productPID'] = \CommerceTeam\Commerce\Utility\BackendUtility::getProductFolderUid();

        $ajaxObj->addContent(
        	'tree',
        	json_encode($treeData)
        );
    }

}
