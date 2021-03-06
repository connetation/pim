<?php
namespace CommerceTeam\Commerce\Domain\Repository;

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
 * @author 2005-2012 Ingo Schmitt <is@marketing-factory.de>
 */
class CategoryRepository extends Repository
{
    /**
     * Database table.
     *
     * @var string
     */
    public $databaseTable = 'tx_commerce_categories';

    /**
     * Database parent category relation table.
     *
     * @var string
     */
    protected $databaseParentCategoryRelationTable = 'tx_commerce_categories_parent_category_mm';

    /**
     * Database attribute relation table.
     *
     * @var string Attribute rel table
     */
    protected $databaseAttributeRelationTable = 'tx_commerce_categories_attributes_mm';

    /**
     * Category sorting field.
     *
     * @var string
     */
    protected $categoryOrderField = 'tx_commerce_categories.sorting';

    /**
     * Product sorting field.
     *
     * @var string
     */
    protected $productOrderField = 'tx_commerce_products.sorting';

    /**
     * Uid of current Category.
     *
     * @var int
     */
    protected $uid;

    /**
     * Language Uid.
     *
     * @var int
     */
    protected $lang_uid;

    /**
     * Gets the "master" category from this category.
     *
     * @param int $uid Category uid
     *
     * @return int Category uid
     */
    public function getParentCategory($uid)
    {
        $database = $this->getDatabaseConnection();

        $result = 0;
        if ($uid && \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($uid)) {
            $this->uid = $uid;
            $row = (array) $database->exec_SELECTgetSingleRow(
                'uid_foreign',
                $this->databaseParentCategoryRelationTable,
                'uid_local = ' . $uid . ' AND is_reference = 0'
            );
            if (!empty($row)) {
                $result = $row['uid_foreign'];
            }
        }

        return $result;
    }

    /**
     * Returns the permissions information for the category with the uid.
     *
     * @param int $uid Category UID
     *
     * @return array with permission information
     */
    public function getPermissionsRecord($uid)
    {
        $database = $this->getDatabaseConnection();

        $result = array();
        if (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($uid) && $uid) {
            $result = (array) $database->exec_SELECTgetSingleRow(
                'perms_everybody, perms_user, perms_group, perms_userid, perms_groupid, editlock',
                $this->databaseTable,
                'uid = ' . $uid
            );
        }

        return $result;
    }

    /**
     * Gets the parent categories from this category.
     *
     * @param int $uid Category uid
     *
     * @return array Parent categories Uids
     */
    public function getParentCategories($uid)
    {
        if (empty($uid) || !\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($uid)) {
            return array();
        }

        $database = $this->getDatabaseConnection();
        $frontend = $this->getFrontendController();
        $this->uid = $uid;
        if (is_object($frontend->sys_page)) {
            $additionalWhere = $frontend->sys_page->enableFields(
                $this->databaseTable,
                $frontend->showHiddenRecords
            );
        } else {
            $additionalWhere = ' AND ' . $this->databaseTable . '.deleted = 0';
        }

        $result = $database->exec_SELECT_mm_query(
            'uid_foreign',
            $this->databaseTable,
            $this->databaseParentCategoryRelationTable,
            $this->databaseTable,
            ' AND ' . $this->databaseParentCategoryRelationTable . '.uid_local = ' . $uid . ' ' . $additionalWhere
        );

        if ($result) {
            $data = array();
            while (($row = $database->sql_fetch_assoc($result))) {
                // @todo access_check for data sets
                $data[] = $row['uid_foreign'];
            }
            $database->sql_free_result($result);

            return $data;
        }

        return array();
    }

    /**
     * Returns an array of sys_language_uids of the i18n categories
     * Only use in BE.
     *
     * @param int $uid Uid of the category we want to get the i18n languages from
     *
     * @return array Array of UIDs
     */
    public function getL18nCategories($uid)
    {
        if (empty($uid) || !\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($uid)) {
            return array();
        }

        $database = $this->getDatabaseConnection();
        $this->uid = $uid;
        $res = $database->exec_SELECTquery(
            't1.title, t1.uid, t2.flag, t2.uid as sys_language',
            $this->databaseTable . ' AS t1 LEFT JOIN sys_language AS t2 ON t1.sys_language_uid = t2.uid',
            'l18n_parent = ' . $uid . ' AND deleted = 0'
        );

        $uids = array();
        while (($row = $database->sql_fetch_assoc($res))) {
            $uids[] = $row;
        }

        return $uids;
    }

    /**
     * Gets the child categories from this category.
     *
     * @param int $uid Product UID
     * @param int $languageUid Language UID
     *
     * @return array Array of child categories UID
     */
    public function getChildCategories($uid, $languageUid = -1)
    {
        if (empty($uid) || !\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($uid)) {
            return array();
        }

        if ($languageUid == -1) {
            $languageUid = 0;
        }
        $this->uid = $uid;
        $frontend = $this->getFrontendController();
        if ($languageUid == 0 && $frontend->sys_language_uid) {
            $languageUid = $frontend->sys_language_uid;
        }
        $this->lang_uid = $languageUid;

        // @todo Sorting should be by database
        // 'tx_commerce_categories_parent_category_mm.sorting'
        // as TYPO3 isn't currently able to sort by MM tables
        // We are using $this->databaseTable.sorting

        $localOrderField = $this->categoryOrderField;
        $hookObject = \CommerceTeam\Commerce\Factory\HookFactory::getHook(
            'Domain/Repository/CategoryRepository',
            'getChildCategories'
        );
        if (is_object($hookObject) && method_exists($hookObject, 'categoryOrder')) {
            $localOrderField = $hookObject->categoryOrder($this->categoryOrderField, $this);
        }

        $additionalWhere = $this->enableFields($this->databaseTable, $frontend->showHiddenRecords);

        $database = $this->getDatabaseConnection();

        $result = $database->exec_SELECT_mm_query(
            'uid_local',
            $this->databaseTable,
            $this->databaseParentCategoryRelationTable,
            $this->databaseTable,
            ' AND ' . $this->databaseParentCategoryRelationTable . '.uid_foreign = ' . $uid . ' ' . $additionalWhere,
            '',
            $localOrderField
        );

        $return = array();
        if ($result) {
            $data = array();
            while (($row = $database->sql_fetch_assoc($result))) {
                // @todo access_check for datasets
                if ($languageUid == 0) {
                    $data[] = (int) $row['uid_local'];
                } else {
                    // Check if a localised product is availiabe for this product
                    // @todo Check if this is correct in Multi Tree Sites
                    $lresult = $database->exec_SELECTquery(
                        'uid',
                        $this->databaseTable,
                        'l18n_parent = ' . (int) $row['uid_local'] . ' AND sys_language_uid = ' . $this->lang_uid .
                        $this->enableFields($this->databaseTable, $frontend->showHiddenRecords)
                    );

                    if ($database->sql_num_rows($lresult)) {
                        $data[] = (int) $row['uid_local'];
                    }
                }
            }

            if (is_object($hookObject) && method_exists($hookObject, 'categoryQueryPostHook')) {
                $data = $hookObject->categoryQueryPostHook($data, $this);
            }

            $database->sql_free_result($result);
            $return = $data;
        }

        return $return;
    }

    /**
     * Gets child products from this category.
     *
     * @param int $uid Product uid
     * @param int $languageUid Language uid
     *
     * @return array Array of child products UIDs
     */
    public function getChildProducts($uid, $languageUid = -1)
    {
        if (empty($uid) || !\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($uid)) {
            return array();
        }

        if ($languageUid == -1) {
            $languageUid = 0;
        }
        $this->uid = $uid;
        $frontend = $this->getFrontendController();
        if ($languageUid == 0 && $frontend->sys_language_uid) {
            $languageUid = $frontend->sys_language_uid;
        }
        $this->lang_uid = $languageUid;

        $localOrderField = $this->productOrderField;

        $hookObject = \CommerceTeam\Commerce\Factory\HookFactory::getHook(
            'Domain/Repository/CategoryRepository',
            'getChildProducts'
        );
        if (is_object($hookObject) && method_exists($hookObject, 'productOrder')) {
            $localOrderField = $hookObject->productOrder($localOrderField, $this);
        }

        $whereClause = 'mm.uid_foreign = ' . (int) $uid;
        if (is_object($GLOBALS['TSFE']->sys_page)) {
            $whereClause .= $this->enableFields('tx_commerce_products', $GLOBALS['TSFE']->showHiddenRecords);
            $whereClause .= $this->enableFields('tx_commerce_articles', $GLOBALS['TSFE']->showHiddenRecords, 'a');
            $whereClause .= $this->enableFields('tx_commerce_article_prices', $GLOBALS['TSFE']->showHiddenRecords, 'ap');
        }

        // Versioning - no deleted or versioned records, nor live placeholders
        $whereClause .= ' AND tx_commerce_products.sys_language_uid = 0 AND tx_commerce_products.deleted = 0 AND tx_commerce_products.pid != -1 AND tx_commerce_products.t3ver_state != 1';
        $queryArray = array(
            'SELECT' => 'DISTINCT(tx_commerce_products.uid)',
            'FROM' => 'tx_commerce_products
             INNER JOIN tx_commerce_products_categories_mm AS mm ON tx_commerce_products.uid = mm.uid_local
             INNER JOIN tx_commerce_articles AS a ON tx_commerce_products.uid = a.uid_product
             INNER JOIN tx_commerce_article_prices AS ap ON a.uid = ap.uid_article',
            'WHERE' => $whereClause,
            'GROUPBY' => '',
            'ORDERBY' => $localOrderField,
            'LIMIT' => ''
        );

        if (is_object($hookObject) && method_exists($hookObject, 'productQueryPreHook')) {
            $queryArray = $hookObject->productQueryPreHook($queryArray, $this);
        }

        $database = $this->getDatabaseConnection();

        $return = array();
        $result = $database->exec_SELECT_queryArray($queryArray);
        if ($result !== false) {
            while (($row = $database->sql_fetch_assoc($result))) {
                if ($languageUid == 0) {
                    $return[] = (int) $row['uid'];
                } else {
                    // Check if a localized product is available
                    // @todo Check if this is correct in multi tree sites
                    $lresult = $database->exec_SELECTquery(
                        'uid',
                        'tx_commerce_products',
                        'l18n_parent = ' . (int) $row['uid'] . ' AND sys_language_uid = ' . $this->lang_uid .
                        $this->enableFields('tx_commerce_products', $frontend->showHiddenRecords)
                    );
                    if ($database->sql_num_rows($lresult)) {
                        $return[] = (int) $row['uid'];
                    }
                }
            }
            $database->sql_free_result($result);

            if (is_object($hookObject) && method_exists($hookObject, 'productQueryPostHook')) {
                $return = $hookObject->productQueryPostHook($return, $this);
            }
        }

        return $return;
    }

    /**
     * Returns an array of array for the TS rootline
     * Recursive Call to build rootline.
     *
     * @param int $categoryUid Category uid
     * @param string $clause Where clause
     * @param array $result Result
     *
     * @return array
     */
    public function getCategoryRootline($categoryUid, $clause = '', array $result = array())
    {
        if (!empty($categoryUid) && \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($categoryUid)) {
            $row = (array) $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                'tx_commerce_categories.uid, mm.uid_foreign AS parent',
                'tx_commerce_categories
                    INNER JOIN tx_commerce_categories_parent_category_mm AS mm
                        ON tx_commerce_categories.uid = mm.uid_local',
                'tx_commerce_categories.uid = ' . $categoryUid .
                $this->enableFields('tx_commerce_categories', $this->getFrontendController()->showHiddenRecords)
            );

            if (!empty($row) && $row['parent'] != $categoryUid) {
                $result = $this->getCategoryRootline((int) $row['parent'], $clause, $result);
            }

            $result[] = array(
                'uid' => $row['uid'],
            );
        }

        return $result;
    }

    /**
     * Get relation.
     *
     * @param int $foreignUid Foreign uid
     *
     * @return array
     */
    public function findRelationByForeignUid($foreignUid)
    {
        return (array) $this->getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            $this->databaseParentCategoryRelationTable,
            'uid_foreign = ' . (int) $foreignUid
        );
    }

    /**
     * Find by uid.
     *
     * @param int $uid Product uid
     *
     * @return array
     */
    public function findByUid($uid)
    {
        return (array) $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
            '*',
            $this->databaseTable,
            'uid = ' . (int) $uid . $this->enableFields($this->databaseTable)
        );
    }
}
