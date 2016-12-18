<?php
namespace CommerceTeam\Commerce\Controller;

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

use CommerceTeam\Commerce\Factory\SettingsFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\AjaxRequestHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class \CommerceTeam\Commerce\ViewHelpers\Navigation\CategoryNavigationFrameController
 *
 * @author Sebastian Fischer <typo3@marketing-factory.de>
 */
class CategoryNavigationFrameController extends \TYPO3\CMS\Backend\Module\BaseScriptClass
{
    /**
     * Constructor
     *
     * @return self
     */
    public function __construct() {
		$GLOBALS['SOBE'] = $this;
		$this->init();
    }

    /**
     * Initializes the Tree.
     *
     * @param bool $bare If TRUE only categories get rendered
     *
     * @return void
     */
    public function init($bare = false)
    {
        $this->getLanguageService()->includeLLFile(
            'EXT:commerce/Resources/Private/Language/locallang_mod_category.xml'
        );
    }

    /**
     * Initializes the Page.
     *
     * @param bool $bare If TRUE only categories get rendered
     *
     * @return void
     */
    public function initPage($bare = false) {
        /**
         * Document template.
         *
         * @var \TYPO3\CMS\Backend\Template\DocumentTemplate $doc
         */
        $doc = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        $this->doc = $doc;
        $this->doc->backPath = $this->getBackPath();
        $this->doc->setModuleTemplate('EXT:commerce/Resources/Private/Backend/mod_navigation.html');
        $this->doc->showFlashMessages = false;


        $formStyle = (!$GLOBALS['CLIENT']['FORMSTYLE'] ? '' : 'if (linkObj) { linkObj.blur(); }');


        // Adding javascript code for AJAX (prototype), drag&drop and the
        // pagetree as well as the click menu code
        //$this->doc->getContextMenuCode();

        $this->doc->bodyTagId = 'typo3-fancytree';
    }

    /**
     * Main method.
     *
     * @return void
     */
    public function main()
    {
        $docHeaderButtons = $this->getButtons();

        $markers = array(
            'CONTENT' => $this->content,
        );

        $subparts = array();
        // Build the <body> for the module
        $this->content = $this->doc->startPage(
            $this->getLanguageService()->sl(
                'LLL:EXT:commerce/Resources/Private/Language/locallang_be.xml:mod_category.navigation_title'
            )
        );
        $this->content .= $this->doc->moduleBody('', $docHeaderButtons, $markers, $subparts);
        $this->content .= $this->doc->endPage();
        $this->content = $this->doc->insertStylesAndJS($this->content);
    }

    /**
     * Print content.
     *
     * @return void
     */
    public function printContent()
    {
        echo $this->content;
    }

    /**
     * Create the panel of buttons for submitting the
     * form or otherwise perform operations.
     *
     * @return array all available buttons as an assoc. array
     */
    protected function getButtons()
    {
        $buttons = array(
            'csh' => '',
            'refresh' => '',
        );

        // Refresh
        $buttons['refresh'] = '<a href="' . htmlspecialchars(GeneralUtility::getIndpEnv('REQUEST_URI')) . '">' .
            \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-system-refresh') . '</a>';

        // CSH
        $buttons['csh'] = str_replace(
            'typo3-csh-inline',
            'typo3-csh-inline show-right',
            BackendUtility::cshItem('xMOD_csh_commercebe', 'categorytree', $this->getBackPath())
        );

        return $buttons;
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
    public function ajaxExpandCollapse(array $paramsRaw, AjaxRequestHandler &$ajaxObj)
    {
    	$params = $paramsRaw['request']->getQueryParams();

		if (isset($params['action']) && isset($params['table']) && isset($params['uid'])) {
			$treeState = unserialize($GLOBALS['BE_USER']->uc['commerceNavTreeState']);
			if ($params['action'] == 'collapse') {
				if (isset($treeState[$params['table']][$params['uid']])) {
					unset($treeState[$params['table']][$params['uid']]);
				}
			} else {
				$treeState[$params['table']][$params['uid']] = $params['uid'];
			}

			$GLOBALS['BE_USER']->uc['commerceNavTreeState'] = serialize($treeState);
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
    public function ajaxGetCategoryTreeData(array $params, AjaxRequestHandler &$ajaxObj)
    {
        // Get the category tree without the products and the articles
        $this->init(true);


        // Get the Category Tree
        $categoryTree = GeneralUtility::makeInstance('CommerceTeam\\Commerce\\Tree\\CategoryTree');

        $ajaxObj->addContent('tree', $categoryTree->getTreeJSON());
    }

    /**
     * Parameter getter.
     *
     * @return array
     */
    protected function getParameter()
    {
        $parameter = GeneralUtility::_GP('PM');
        // IE takes anchor as parameter
        if (($parameterPosition = strpos($parameter, '#')) !== false) {
            $parameter = substr($parameter, 0, $parameterPosition);
        }

        return explode('_', $parameter);
    }

    /**
     * Checks if an update of the commerce extension is necessary.
     *
     * @return bool
     */
    protected function isUpdateNecessary()
    {
        /**
         * Updater.
         *
         * @var \CommerceTeam\Commerce\Utility\UpdateUtility $updater
         */
        $updater = GeneralUtility::makeInstance('CommerceTeam\\Commerce\\Utility\\UpdateUtility');

        return $updater->access();
    }


    /**
     * Get back path.
     *
     * @return string
     */
    protected function getBackPath()
    {
        return $GLOBALS['BACK_PATH'];
    }
}
