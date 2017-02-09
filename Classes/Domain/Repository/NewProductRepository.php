<?php
namespace CommerceTeam\Commerce\Domain\Repository;

use \TYPO3\CMS\Extbase\Persistence\Repository;
use \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

use CommerceTeam\Commerce\Domain\Model\NewCategory;
use CommerceTeam\Commerce\Domain\Model\NewProduct;

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
 * Database Class for tx_commerce_products. All database calls should
 * be made by this class. In most cases you should use the methodes
 * provided by tx_commerce_category to get informations for articles.
 * Class \CommerceTeam\Commerce\Domain\Repository\ProductRepository
 *
 * @author Anselm Ruby <a.ruby@connetation.at>
 */
class NewProductRepository extends Repository {

	public function initializeObject() {
		$querySettings = $this->objectManager->get(Typo3QuerySettings::class);
		$querySettings->setRespectStoragePage(false);
		$querySettings->setIgnoreEnableFields(true);
		$this->setDefaultQuerySettings($querySettings);
	}

	/**
	 * We overwrite this default method because we want to find hidden records
	 *
	 * @param mixed $uid
	 * @return \CommerceTeam\Commerce\Domain\Model\NewProduct|null
	 */
	public function findByIdentifier($uid) {
		$query = $this->createQuery();
		return $query->matching($query->equals('uid', (int)$uid))->execute()->getFirst();
	}

	/**
	 * @param \CommerceTeam\Commerce\Domain\Model\NewCategory $category
	 */
	public function findProductsOfCategory(NewCategory $category = null) {
		$parentUid = $category === null ? 0 : (int)$category->getUid();

		$query = $this->createQuery();
		$st = $query->statement(
			'SELECT p.uid, p.title FROM tx_commerce_products p'
			. ' JOIN tx_commerce_products_categories_mm mm'
			. ' ON p.uid = mm.uid_local'
			. ' WHERE mm.uid_foreign = ' . $parentUid . ' AND p.sys_language_uid = 0 AND p.deleted = 0'
			. ' ORDER BY mm.sorting'
		);
		return $st->execute();
	}

	/**
	 * @param \CommerceTeam\Commerce\Domain\Model\NewProduct $toSortUp
	 * @param bool $up True to move the product up, fals to move it down
	 * @param \CommerceTeam\Commerce\Domain\Model\NewCategory $parent
	 * @return void
	 */
	public function sortUpDown(NewProduct $toSortUp, $up = true, NewCategory $parent = null) {
		$meUid = $toSortUp->getUid();
		$parentUid = $parent === null ? 0 : (int)$parent->getUid();

		$mms = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'mm.*',
			'tx_commerce_products_categories_mm mm JOIN tx_commerce_products p ON mm.uid_local = p.uid',
			'uid_foreign = ' . $parentUid . ' AND p.deleted = 0',
			'', 'mm.sorting ' . ($up ? 'DESC' : 'ASC')
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
					$tmp = $up ? ($sorting - 8) : ($sorting + 8);
				} else {
					if ($replaceNext) {
						$tmp = $replaceNext;
						$replaceNext = false;
					}
				}

				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					'tx_commerce_products_categories_mm',
					'uid_foreign = ' . $mm['uid_foreign'] . ' AND uid_local = ' . $mm['uid_local'],
					array('sorting' => $tmp)
				);

				$sorting = $up ? ($sorting - 8) : ($sorting + 8);
			}
		}
	}

}
