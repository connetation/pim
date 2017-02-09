<?php
namespace CommerceTeam\Commerce\Controller\Backend;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\View\BackendTemplateView;

use CommerceTeam\Commerce\Domain\Repository\NewCategoryRepository;
use CommerceTeam\Commerce\Domain\Repository\NewProductRepository;


/**
 * Class \CommerceTeam\Commerce\Modules\ListModule.
 *
 * @author Anselm Ruby <a.ruby@connetation.at>
 */
class CategoryController extends ActionController {
	/**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

	/**
	 * persistenceManager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * NewCategoryRepository
	 *
	 * @var \CommerceTeam\Commerce\Domain\Repository\NewCategoryRepository
	 * @inject
	 */
	protected $newCategoryRepository;

	/**
	 * NewProductRepository
	 *
	 * @var \CommerceTeam\Commerce\Domain\Repository\NewProductRepository
	 * @inject
	 */
	protected $newProductRepository;

	/**
	 * ObjectManager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

    /* *
     * BackendTemplateContainer
     *
     * @var BackendTemplateView
     */
    // protected $view;




    /**
     *
	 * @param integer $parent
     * @return void
     */
    public function indexAction($parent = NULL) {
    	$parent = $this->newCategoryRepository->findByUid($parent);

		$this->view->assign('parent', $parent);

		$products = $this->newProductRepository->findProductsOfCategory($parent);
		if (!empty($products)) {
			$this->view->assign('products', $products);
		}

		$categories = $this->newCategoryRepository->findSubCategories($parent);
		if (!empty($categories)) {
        	$this->view->assign('categories', $categories);
		}
    }




	protected function forwardIndex($parent = NULL) {
		$this->persistenceManager->persistAll();
		$this->forward('index', null, null, ['parent' => $parent ? $parent->getuid() : null]);
	}




    /**
     *
	 * @param integer $category
	 * @param integer $parent
     * @return void
     */
    public function hideAction($category, $parent = NULL) {
    	$parent = $this->newCategoryRepository->findByUid($parent);
		$category = $this->newCategoryRepository->findByUid($category);

    	$this->newCategoryRepository->update($category->setHidden(true));

		$this->forwardIndex($parent);
    }


    /**
     * @param integer $category
	 * @param integer $parent
     * @return void
     */
    public function unhideAction($category, $parent = NULL) {
    	$parent = $this->newCategoryRepository->findByUid($parent);
		$category = $this->newCategoryRepository->findByUid($category);

		$this->newCategoryRepository->update($category->setHidden(false));

		$this->forwardIndex($parent);
    }


    /**
     * @param integer $category
	 * @param integer $parent
     * @return void
     */
    public function deleteAction($category, $parent = NULL) {
    	$parent = $this->newCategoryRepository->findByUid($parent);
		$category = $this->newCategoryRepository->findByUid($category);

		$this->newCategoryRepository->remove($category);

		$this->forwardIndex($parent);
    }


    /**
     * @param integer $category
	 * @param integer $parent
     * @return void
     */
    public function upAction($category, $parent = NULL) {
    	$parent = $this->newCategoryRepository->findByUid($parent);
		$category = $this->newCategoryRepository->findByUid($category);

    	$this->newCategoryRepository->sortUpDown($category, true, $parent);

		$this->forwardIndex($parent);
    }

    /**
     * @param integer $category
	 * @param integer $parent
     * @return void
     */
    public function downAction($category, $parent = NULL) {
    	$parent = $this->newCategoryRepository->findByUid($parent);
		$category = $this->newCategoryRepository->findByUid($category);
		$this->newCategoryRepository->sortUpDown($category, false, $parent);

		$this->forwardIndex($parent);
    }



}
