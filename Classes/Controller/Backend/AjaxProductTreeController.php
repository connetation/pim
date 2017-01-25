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

		if (isset($params['action']) && isset($params['table']) && isset($params['uid']) && isset($params['treeId'])) {
			$treeId = $params['treeId'];
			$treeState = unserialize($GLOBALS['BE_USER']->uc[$treeId]);
			if ($params['action'] == 'collapse') {
				if (isset($treeState[$params['table']][$params['uid']])) {
					unset($treeState[$params['table']][$params['uid']]);
				}
			} else {
				$treeState[$params['table']][$params['uid']] = $params['uid'];
			}

			$GLOBALS['BE_USER']->uc[$treeId] = serialize($treeState);
			$GLOBALS['BE_USER']->writeUC();
		}

        $ajaxObj->addContent('tree', var_export($treeState, true));
    }

    /**
     * Makes the AJAX call to expand or collapse the categorytree.
     * Called by typo3/ajax.php.
     *
     * @param array $params Additional parameters (not used here)
     * @param AjaxRequestHandler $ajaxObj Ajax object
     *
     * @return void
     */
    public function ajaxGetCategoryTreeData(array $paramsRaw, AjaxRequestHandler &$ajaxObj) {
		$params = $paramsRaw['request']->getQueryParams();

		$treeId = null;
		if (isset($params['treeId'])) {
			$treeId = $params['treeId'];
		}

		$treeConf = [];
		if (isset($params['treeConf'])) {
			$treeConf = $params['treeConf'];
		}

        $categoryTree = GeneralUtility::makeInstance('CommerceTeam\\Commerce\\Tree\\CategoryTree', $treeConf);

        $ajaxObj->addContent('tree', $categoryTree->getTreeJSON($treeId));
    }

}
