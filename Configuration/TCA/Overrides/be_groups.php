<?php

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

// extend beusers/begroups for access control
$tempColumns = array(
    'tx_commerce_mountpoints' => array(
        'label' => 'LLL:EXT:commerce/Resources/Private/Language/locallang_db.xml:label.tx_commerce_mountpoints',
        'config' => array(
            'type'          => 'user',
            'allowed'       => 'tx_commerce_categories',
            'form_type'     => 'user',
            'userFunc'      => 'CommerceTeam\\Commerce\\ViewHelpers\\TceFunc->getSingleField_selectCategories',
            'treeView'      => 1,
            'treeClass'     => 'CommerceTeam\\Commerce\\ViewHelpers\\TceFunc\\CategoryTree',
			'mountRootOnly' => true,
            'size'          => 10,
            'autoSizeMax'   => 30,
            'minitems'      =>  0,
            'maxitems'      => 20,
        ),
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_groups', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'be_groups', 'tx_commerce_mountpoints', '', 'after:file_mountpoints'
);
