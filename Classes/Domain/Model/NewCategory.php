<?php
namespace CommerceTeam\Commerce\Domain\Model;

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


/**
 * Main script class for the handling of categories. Categories contains
 * categories (Reverse data structure) and products.
 *
 * Class NewCategory
 * @packafe CommerceTeam\Commerce\Domain\Model
 *
 * @author Anselm Ruby <a.ruby@connetation.at>
 */
class NewCategory extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity implements \ArrayAccess {

	/**
	 * NewCategoryRepository
	 *
	 * @var \CommerceTeam\Commerce\Domain\Repository\NewCategoryRepository
	 * @inject
	 */
	protected $categoryRepository;





	/**
	 * Parent Categories.
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CommerceTeam\Commerce\Domain\Model\NewCategory>
	 */
	protected $parentCategories;


	/**
	 * Child Categories.
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CommerceTeam\Commerce\Domain\Model\NewCategory>
	 */
	protected $childCategories;




	/**
	 * BE Owner UID.
	 *
	 * @var integer
	 */
	protected $permsUserid = 0;

	/**
	 * BE-Permission: Owner.
	 *
	 * @var integer
	 */
	protected $permsUser = 0;

	/**
	 * BE Group UID.
	 *
	 * @var integer
	 */
	protected $permsGroupid = 0;

	/**
	 * BE-Permission: Group.
	 *
	 * @var integer
	 */
	protected $permsGroup = 0;

	/**
	 * BE-Permission: Everybody.
	 *
	 * @var integer
	 */
	protected $permsEverybody = 0;





    /**
     * Title.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Subtitle.
     *
     * @var string
     */
    protected $subtitle = '';

    /**
     * Description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Title for navigation an Menu Rendering.
     *
     * @var string
     */
    protected $navtitle = '';

    /**
     * Keywords for meta informations.
     *
     * @var string
     */
    protected $keywords = '';

    /**
     * is hidden.
     *
     * @var boolean
     */
    protected $hidden = '';

    /**
     * is deleted.
     *
     * @var boolean
     */
    protected $deleted = '';




	public function offsetSet($offset, $value) {
		$methodName = 'set' . $this->underscoreToCamelCase($offset);
		if (method_exists($this, $methodName)) {
			$this->{$methodName}($value);
		}
	}

	public function offsetExists($offset) {
		$methodName = 'set' . $this->underscoreToCamelCase($offset);
		return method_exists($this, $methodName);
	}

	public function offsetUnset($offset) {
	}

	public function offsetGet($offset) {
		$methodName = 'get' . $this->underscoreToCamelCase($offset);
		if (method_exists($this, $methodName)) {
			return $this->{$methodName}();
		}
	}

	private function underscoreToCamelCase($string) {
		$str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
		lcfirst($str);
		return $str;
	}


	/**
	 * Returns the Parent Categories of the category.
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CommerceTeam\Commerce\Domain\Model\NewCategory> ParentCategories
	 */
	public function getParentCategories()  {
		if ($this->parentCategories === NULL) {
			$this->parentCategories = $this->categoryRepository->findParentCategories($this);
		}
		return $this->parentCategories;
	}



	/**
	 * Returns the child categories of this category.
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\CommerceTeam\Commerce\Domain\Model\NewCategory> ParentCategories
	 */
	public function getChildCategories()  {
		if ($this->childCategories === NULL) {
			$this->childCategories = $this->categoryRepository->findSubCategories($this);
		}
		return $this->childCategories;
	}







	/**
	 * Returns the title of the category.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Returns the title of the category.
	 *
	 * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}



	/**
	 * Returns the hidden state.
	 *
	 * @return boolean Title
	 */
	public function getHidden() {
		return $this->hidden;
	}

	/**
	 * Returns the hidden state.
	 *
	 * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
		return $this;
	}



	/**
	 * Returns the deleted state.
	 *
	 * @return boolean Title
	 */
	public function getDeleted() {
		return $this->deleted;
	}

	/**
	 * Returns the deleted state.
	 *
	 * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
	 */
	public function setDeleted($deleted) {
		$this->deleted = $deleted;
		return $this;
	}



    /**
     * Returns the category description.
     *
     * @return string Description;
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the title of the category.
     *
     * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
     */
    public function setDescription($description) {
    	$this->description = $description;
        return $this;
    }



    /**
     * Returns the category keywords.
     *
     * @return string Keywords;
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Returns the title of the category.
     *
     * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
     */
    public function setKeywords($keywords) {
    	$this->keywords = $keywords;
        return $this;
    }



    /**
     * Returns the category navigationtitle.
     *
     * @return string Navigationtitle;
     */
    public function getNavtitle()
    {
        return $this->navtitle;
    }

    /**
     * Returns the title of the category.
     *
     * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
     */
    public function setNavtitle($navtitle) {
    	$this->navtitle = $navtitle;
        return $this;
    }



    /**
     * Returns the subtitle of the category.
     *
     * @return string Subtitle;
     */
    public function getSubtitle() {
        return $this->subtitle;
    }

    /**
     * Returns the title of the category.
     *
     * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
     */
    public function setSubtitle($subtitle) {
    	$this->subtitle = $subtitle;
        return $this;
    }




	/**
	 * @return int
	 */
	public function getPermsUserid(): int {
		return $this->permsUserid;
	}

	/**
	 * @param int $permsUserid
	 * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
	 */
	public function setPermsUserid(int $permsUserid): NewCategory {
		$this->permsUserid = $permsUserid;
		return $this;
	}



	/**
	 * @return int
	 */
	public function getPermsUser(): int {
		return $this->permsUser;
	}

	/**
	 * @param int $permsUser
	 * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
	 */
	public function setPermsUser(int $permsUser): NewCategory {
		$this->permsUser = $permsUser;
		return $this;
	}



	/**
	 * @return int
	 */
	public function getPermsGroupid(): int {
		return $this->permsGroupid;
	}

	/**
	 * @param int $permsGroupid
	 * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
	 */
	public function setPermsGroupid(int $permsGroupid): NewCategory {
		$this->permsGroupid = $permsGroupid;
		return $this;
	}



	/**
	 * @return int
	 */
	public function getPermsGroup(): int {
		return $this->permsGroup;
	}

	/**
	 * @param int $permsGroup
	 * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
	 */
	public function setPermsGroup(int $permsGroup): NewCategory {
		$this->permsGroup = $permsGroup;
		return $this;
	}



	/**
	 * @return int
	 */
	public function getPermsEverybody(): int {
		return $this->permsEverybody;
	}

	/**
	 * @param int $permsEverybody
	 * @return \CommerceTeam\Commerce\Domain\Model\NewCategory
	 */
	public function setPermsEverybody(int $permsEverybody): NewCategory {
		$this->permsEverybody = $permsEverybody;
		return $this;
	}

}
