<?php
namespace CommerceTeam\Commerce\Domain\Repository;

use CommerceTeam\Commerce\Domain\Model\NewCategory;

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
 * Database Class for tx_commerce_categories. All database calls should
 * be made by this class. In most cases you should use the methodes
 * provided by tx_commerce_category to get informations for articles.
 *
 * Class \CommerceTeam\Commerce\Domain\Repository\CategoryRepository
 *
 * @author Anselm Ruby <a.ruby@connetation.at>
 */
class NewCategoryRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	public function initializeObject() {
		$querySettings = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class);
		$querySettings->setRespectStoragePage(false);
		$querySettings->setIgnoreEnableFields(true);
		$this->setDefaultQuerySettings($querySettings);
	}


	/**
	 * We overwrit this default method becaus we want to find hidden records
	 *
	 * @param integer $uid
	 */
	public function findByIdentifier($uid) {
		$query = $this->createQuery();
		return $query->matching($query->equals('uid', (int)$uid))->execute()->getFirst();
	}


    /**
	 * @param \CommerceTeam\Commerce\Domain\Model\NewCategory $parent
	 * @return integer
     */
	protected function uidOrZero(NewCategory $parent = NULL) {
		if ($parent !== NULL) {
			return (int)$parent->getUid();
		}
		return 0;
	}



    /**
	 * @param \CommerceTeam\Commerce\Domain\Model\NewCategory $parent
     */
	public function findSubCategories(NewCategory $parent = NULL, $bePermissions = 0) {
		$permWhere = ' WHERE mm.uid_foreign = ' . $this->uidOrZero($parent) . ' AND c.sys_language_uid = 0 AND c.deleted = 0';
		if ($bePermissions > 0) {
			$permWhere .= ' AND (' . $this->beUserPermsWhere($bePermissions) . ')';
		}

		$query = $this->createQuery();
		return $query->statement(
			'SELECT c.* FROM tx_commerce_categories c'
			. ' JOIN tx_commerce_categories_parent_category_mm mm'
			. ' ON c.uid = mm.uid_local'
			. $permWhere
			. ' ORDER BY mm.sorting'
		)->execute();
	}



	/**
	 * @param \CommerceTeam\Commerce\Domain\Model\NewCategory $child
	 */
	public function findParentCategories(NewCategory $child, $bePermissions = 0) {
		$permWhere = ' WHERE mm.uid_local = ' . ((int)$child->getUid()) . ' AND c.sys_language_uid = 0 AND c.deleted = 0';
		if ($bePermissions > 0) {
			$permWhere .= ' AND (' . $this->beUserPermsWhere($bePermissions) . ')';
		}

		$query = $this->createQuery();
		return $query->statement(
			'SELECT c.* FROM tx_commerce_categories c'
			. ' JOIN tx_commerce_categories_parent_category_mm mm'
			. ' ON c.uid = mm.uid_foreign'
			. $permWhere
			. ' ORDER BY mm.sorting'
		)->execute();
	}



    /**
     * @param \CommerceTeam\Commerce\Domain\Model\NewCategory $toSortUp
	 * @param \CommerceTeam\Commerce\Domain\Model\NewCategory $parent
     * @return void
     */
    public function sortUpDown(NewCategory & $toSortUp, $up = true, NewCategory $parent = NULL) {
    	$meUid = $toSortUp->getUid();

    	$mms = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
    		'mm.*',
    		'tx_commerce_categories_parent_category_mm mm JOIN tx_commerce_categories c ON mm.uid_local = c.uid',
    		'uid_foreign = ' . $this->uidOrZero($parent) . ' AND c.deleted = 0',
    		'', 'mm.sorting ' . ($up?'DESC':'ASC')
		);

		$lastMM = end($mms);
		if ($lastMM['uid_local'] != $meUid) {
			// Only re-sort, if we are not already on the edge
			$sorting = $up ? (count($mms) * 8) : 8;
			$replaceNext = false;
			foreach ($mms as $mm) {
				$tmp = $sorting;

				if ($mm['uid_local'] == $meUid) {
					$replaceNext = $tmp;
					$tmp = $up ? ($sorting-8) : ($sorting+8);
				} else if ($replaceNext) {
					$tmp = $replaceNext;
					$replaceNext = false;
				}

				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					'tx_commerce_categories_parent_category_mm',
					'uid_foreign = ' . $mm['uid_foreign'] . ' AND uid_local = ' . $mm['uid_local'],
					array('sorting' => $tmp)
				);

				$sorting = $up ? ($sorting-8) : ($sorting+8);
			}
		}
    }


	public function beUserPermsWhere($perms) {
		if (is_array($GLOBALS['BE_USER']->user)) {
			if ($GLOBALS['BE_USER']->isAdmin()) {
				return '1=1';
			}

			$perms = (int) $perms;
			$where = 'c.perms_everybody & ' . $perms . ' = ' . $perms
				. ' OR (c.perms_userid = ' . $GLOBALS['BE_USER']->user['uid'] . ' AND c.perms_user & ' . $perms . ' = ' . $perms . ')';

			if ($GLOBALS['BE_USER']->groupList) {
				$where .= ' OR (c.perms_groupid in (' . $GLOBALS['BE_USER']->groupList . ') AND c.perms_group & ' . $perms . ' = ' . $perms . ')';
			}

			return $where;
		}
		return '1=0';
	}

}
