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
class CategoryNavigationFrameAjaxController extends \TYPO3\CMS\Backend\Module\BaseScriptClass
{
    /**
     * Category tree.
     *
     * @var \CommerceTeam\Commerce\Tree\CategoryTree
     */
    protected $categoryTree;

    /**
     * Current sub script.
     *
     * @var string
     */
    protected $currentSubScript;

    /**
     * Do highlight.
     *
     * @var bool
     */
    protected $doHighlight;

    /**
     * Has filter box.
     *
     * @var bool
     */
    protected $hasFilterBox;

    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
        $GLOBALS['SOBE'] = $this;
        $this->init();
    }

    /**
     * Setter for currentSubScript.
     *
     * @param string $currentSubScript Current sub script
     *
     * @return void
     */
    public function setCurrentSubScript($currentSubScript)
    {
        $this->currentSubScript = $currentSubScript;
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

        // Get the Category Tree
        $this->categoryTree = GeneralUtility::makeInstance('CommerceTeam\\Commerce\\Tree\\CategoryTree');
        $this->categoryTree->setBare($bare);
        $this->categoryTree->setSimpleMode((int) SettingsFactory::getInstance()->getExtConf('simpleMode'));
        $this->categoryTree->setNavigationFrame(true);
        $this->categoryTree->init();
    }



    /**
     * Main method.
     *
     * @return void
     */
    public function main()
    {
        // Check if commerce needs to be updated.
        if ($this->isUpdateNecessary()) {
            $tree = $this->getLanguageService()->getLL('ext.update');
        } else {
            // Get the browseable Tree
            $tree = $this->categoryTree->getBrowseableTree();
        }
        // Outputting page tree:
        $this->content .= $tree;

        $docHeaderButtons = $this->getButtons();
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
     * @param array $params Additional parameters (not used here)
     * @param AjaxRequestHandler $ajaxObj Ajax object
     *
     * @return void
     */
    public function ajaxExpandCollapse(array $params, AjaxRequestHandler &$ajaxObj)
    {
        // Get the Category Tree
        $this->init();
        $tree = $this->categoryTree->getBrowseableAjaxTree($this->getParameter());

        $ajaxObj->addContent('tree', $tree);
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
    public function ajaxExpandCollapseWithoutProduct(array $params, AjaxRequestHandler &$ajaxObj)
    {
        // Get the category tree without the products and the articles
        $this->init(true);
        $tree = $this->categoryTree->getBrowseableAjaxTree($this->getParameter());

        $ajaxObj->addContent('tree', $tree);
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
