<?php
namespace CommerceTeam\Commerce\Controller\Backend;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\View\BackendTemplateView;


/**
 * Class \CommerceTeam\Commerce\Controller\BackendAccessController.
 *
 * @author Anselm Ruby <a.ruby@connetation.at>
 */
class AccessController extends ActionController {
	/**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;


    /**
     * @return void
     */
    public function indexAction() {

        $categoryTree = GeneralUtility::makeInstance('CommerceTeam\\Commerce\\Tree\\CategoryTree', [
        	'showProducts' => false
        ]);

        $this->view->assign('tree', $categoryTree->getTree());

    }


}
