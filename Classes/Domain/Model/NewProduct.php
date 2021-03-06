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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Main script class for the handling of categories. Categories contains
 * categories (Reverse data structure) and products.
 *
 * Class NewProduct
 * @packafe CommerceTeam\Commerce\Domain\Model
 *
 * @author Anselm Ruby <a.ruby@connetation.at>
 */
class NewProduct extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

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
     * Returns the product description.
     *
     * @return string Description;
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the title of the product.
     *
     * @return \CommerceTeam\Commerce\Domain\Model\NewProduct
     */
    public function setDescription($description) {
    	$this->description = $description;
        return $this;
    }


    /**
     * Returns the product keywords.
     *
     * @return string Keywords;
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Returns the title of the product.
     *
     * @return \CommerceTeam\Commerce\Domain\Model\NewProduct
     */
    public function setKeywords($keywords) {
    	$this->keywords = $keywords;
        return $this;
    }


    /**
     * Returns the product navigationtitle.
     *
     * @return string Navigationtitle;
     */
    public function getNavtitle()
    {
        return $this->navtitle;
    }

    /**
     * Returns the title of the product.
     *
     * @return \CommerceTeam\Commerce\Domain\Model\NewProduct
     */
    public function setNavtitle($navtitle) {
    	$this->navtitle = $navtitle;
        return $this;
    }


    /**
     * Returns the subtitle of the product.
     *
     * @return string Subtitle;
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Returns the title of the product.
     *
     * @return \CommerceTeam\Commerce\Domain\Model\NewProduct
     */
    public function setSubtitle($subtitle) {
    	$this->subtitle = $subtitle;
        return $this;
    }



    /**
     * Returns the title of the product.
     *
     * @return string Title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the title of the product.
     *
     * @return \CommerceTeam\Commerce\Domain\Model\NewProduct
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
     * @return \CommerceTeam\Commerce\Domain\Model\NewProduct
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
     * @return \CommerceTeam\Commerce\Domain\Model\NewProduct
     */
    public function setDeleted($deleted) {
    	$this->deleted = $deleted;
        return $this;
    }

}
