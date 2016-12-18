<?php
namespace CommerceTeam\Commerce\Hooks;

/***************************************************************
 *  Copyright notice
 *  (c) 2016 Anselm Ruby, Connetation GmbH <a.ruby@connetation.at>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;



/**
 * Class/Function which adds the necessary ExtJS and pure JS stuff for commerce.
 *
 * @author Anselm Ruby, Connetation GmbH <a.ruby@connetation.at>
 * @package TYPO3
 * @subpackage tx_commerce
 */
class PageRenderer implements SingletonInterface {

    /**
     * wrapper function called by hook
     * See: \TYPO3\CMS\Core\Page\PageRenderer->render-preProcess
     *
     * @param array $parameters - An array of available parameters
     * @param \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer - PageRenderer that triggered this hook
     *
     * @return void
     */
    public function addJSCSS($parameters, \TYPO3\CMS\Core\Page\PageRenderer & $pageRenderer) {
        $jsPath = ExtensionManagementUtility::extRelPath('commerce') . 'Resources/Public/JavaScript/';

        $pageRenderer->addCssFile(
            $jsPath . 'Libs/fancytree-2.20.0/skin-conn-pim/ui.fancytree.css',
            'stylesheet',
            'screen'
        );

        $pageRenderer->addJsFile(
            $GLOBALS['BACK_PATH'] . $jsPath . 'categoryTree.js',
            $type = 'text/javascript'
        );
    }

}
