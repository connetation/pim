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

/**
 * Module 'Systemdata' for the 'commerce' extension.
 *
 * @var \CommerceTeam\Commerce\Controller\Backend\SystemdataModuleController $systemdataModuleController
 */
$systemdataModuleController = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    'CommerceTeam\\Commerce\\Controller\\Backend\\SystemdataModuleController'
);
$systemdataModuleController->main();
$systemdataModuleController->printContent();
