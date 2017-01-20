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

$MCONF['script'] = '_DISPATCH';
$MLANG['default']['tabs_images']['tab'] = '../../Resources/Public/Icons/mod_category.svg';
$MLANG['default']['ll_ref'] = 'LLL:EXT:commerce/Resources/Private/Language/locallang_mod_category.xml';

$MCONF['access'] = 'user,group';
$MCONF['name'] = 'commerce_category';
// $MCONF['navigationComponentId'] = 'category-navframe';
$MCONF['navFrameScript'] = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('commerce_Tree');
//$MCONF['navFrameScript'] = '/typo3/index.php?M=commerce_Tree&moduleToken=a8151b71e67a899e39e08f275c85bda62bda9c4d';
