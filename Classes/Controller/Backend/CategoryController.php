<?php
namespace CommerceTeam\Commerce\Controller\Backend;

use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\View\BackendTemplateView;


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
	 * Backend utility.
	 *
	 * @var \CommerceTeam\Commerce\Utility\BackendUserUtility
	 * @inject
	 */
	protected $backendUserUtility;





    /**
     *
	 * @param integer $parent
     * @return void
     */
    public function indexAction($parent = NULL) {
    	$parent = $this->newCategoryRepository->findByUid($parent);

    	if ($this->backendUserUtility->isInCommerceMount($parent)) {
    		if ($this->backendUserUtility->canAccess($parent, Permission::PAGE_SHOW)) {
				$this->view->assign('parent', $parent);
			}

			$products = $this->newProductRepository->findProductsOfCategory($parent);
			if (!empty($products)) {
				$this->view->assign('products', $products);
			}

			$categories = $this->newCategoryRepository->findSubCategories($parent, Permission::PAGE_SHOW);
			if (!empty($categories)) {
				$this->view->assign('categories', $categories);
			}
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

		if ($this->backendUserUtility->isInCommerceMount($category) && $this->backendUserUtility->canAccess($category, Permission::PAGE_EDIT)) {
			$this->newCategoryRepository->update($category->setHidden(true));
		}

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

		if ($this->backendUserUtility->isInCommerceMount($category) && $this->backendUserUtility->canAccess($category, Permission::PAGE_EDIT)) {
			$this->newCategoryRepository->update($category->setHidden(false));
		}

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

		if ($this->backendUserUtility->isInCommerceMount($category) && $this->backendUserUtility->canAccess($category, Permission::PAGE_DELETE)) {
			$this->newCategoryRepository->remove($category);
		}

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

		if ($this->backendUserUtility->isInCommerceMount($category) && $this->backendUserUtility->canAccess($category, Permission::PAGE_EDIT)) {
			$this->newCategoryRepository->sortUpDown($category, true, $parent);
		}

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

		if ($this->backendUserUtility->isInCommerceMount($category) && $this->backendUserUtility->canAccess($category, Permission::PAGE_EDIT)) {
			$this->newCategoryRepository->sortUpDown($category, false, $parent);
		}

		$this->forwardIndex($parent);
    }










	/**
	 *
	 * @param integer $product
	 * @param integer $parent
	 * @return void
	 */
	public function hideProductAction($product, $parent = NULL) {
		$parent = $this->newCategoryRepository->findByUid($parent);
		$product = $this->newProductRepository->findByUid($product);

		if ($this->backendUserUtility->isInCommerceMount($parent) && $this->backendUserUtility->canAccess($parent, Permission::CONTENT_EDIT)) {
			$this->newProductRepository->update($product->setHidden(true));
		}

		$this->forwardIndex($parent);
	}


	/**
	 *
	 * @param integer $product
	 * @param integer $parent
	 * @return void
	 */
	public function unhideProductAction($product, $parent = NULL) {
		$parent = $this->newCategoryRepository->findByUid($parent);
		$product = $this->newProductRepository->findByUid($product);

		if ($this->backendUserUtility->isInCommerceMount($parent) && $this->backendUserUtility->canAccess($parent, Permission::CONTENT_EDIT)) {
			$this->newProductRepository->update($product->setHidden(false));
		}

		$this->forwardIndex($parent);
	}


	/**
	 * @param integer $product
	 * @param integer $parent
	 * @return void
	 */
	public function deleteProductAction($product, $parent = NULL) {
		$parent = $this->newCategoryRepository->findByUid($parent);
		$product = $this->newProductRepository->findByUid($product);

		if ($this->backendUserUtility->isInCommerceMount($parent) && $this->backendUserUtility->canAccess($parent, Permission::CONTENT_EDIT)) {
			$this->newProductRepository->remove($product);
		}

		$this->forwardIndex($parent);
	}


	/**
	 * @param integer $product
	 * @param integer $parent
	 * @return void
	 */
	public function upProductAction($product, $parent = NULL) {
		$parent = $this->newCategoryRepository->findByUid($parent);
		$product = $this->newProductRepository->findByUid($product);

		if ($this->backendUserUtility->isInCommerceMount($parent) && $this->backendUserUtility->canAccess($parent, Permission::CONTENT_EDIT)) {
			$this->newProductRepository->sortUpDown($product, true, $parent);
		}

		$this->forwardIndex($parent);
	}


	/**
	 * @param integer $product
	 * @param integer $parent
	 * @return void
	 */
	public function downProductAction($product, $parent = NULL) {
		$parent = $this->newCategoryRepository->findByUid($parent);
		$product = $this->newProductRepository->findByUid($product);

		if ($this->backendUserUtility->isInCommerceMount($parent) && $this->backendUserUtility->canAccess($parent, Permission::CONTENT_EDIT)) {
			$this->newProductRepository->sortUpDown($product, false, $parent);
		}

		$this->forwardIndex($parent);
	}

}
