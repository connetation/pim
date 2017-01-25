<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    COMMERCE_EXTKEY,
    'Configuration/TypoScript/',
    'COMMERCE'
);

if (TYPO3_MODE == 'BE') {
    /*
     * WIZICON
     * Default PageTS
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . COMMERCE_EXTKEY . '/Configuration/PageTS/ModWizards.ts">'
    );

    if (!isset($TBE_MODULES['commerce'])) {
        $tbeModules = array();
        foreach ($TBE_MODULES as $key => $val) {
            $tbeModules[$key] = $val;
            if ($key == 'file') {
                $tbeModules['commerce'] = 'category';
            }
        }
        $TBE_MODULES = $tbeModules;
    }

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('t3skin')) {
        $presetSkinImgs = is_array($GLOBALS['TBE_STYLES']['skinImg']) ? $GLOBALS['TBE_STYLES']['skinImg'] : array();

        $GLOBALS['TBE_STYLES']['skinImg'] = array_merge($presetSkinImgs, array(
            'MOD:commerce_permission/../../../Resources/Public/Icons/mod_access.gif' => array(
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('t3skin') . 'icons/module_web_perms.png',
                'width="24" height="24"',
            ),
        ));
    }




	// Add Category Navigation Component
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addNavigationComponent('commerce', 'typo3-commercecategory');




    // add main module
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'commerce',
        '',
        'after:file',
        PATH_TXCOMMERCE . 'Modules/Main/'
    );

    // add category module
	if (TYPO3_MODE === 'BE' && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
		// Module Commerce->Category
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
			'CommerceTeam.' . $_EXTKEY,
			'commerce',
			'category',
			'', //'after:layout',
			[
				'BackendCategory' => 'index, hide, unhide, delete, up, down'
			],
			[
				'icon'        => 'EXT:commerce/Resources/Public/Icons/mod_category.svg',
				//'labels' => 'LLL:EXT:viewpage/Resources/Private/Language/locallang_mod.xlf',
				'labels'      => 'LLL:EXT:commerce/Resources/Private/Language/locallang_mod_category.xml',
				'access'      => 'user,group',
				//'routeTarget' => 'CommerceTeam\Commerce\Modules\ListModule::mainAction',
				'navigationComponentId' => 'typo3-commercecategory'
			]
		);
	}

    // Access Module
    /* * /
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'commerce',
        'permission',
        '',
        PATH_TXCOMMERCE . 'Modules/Permission/'
    );
	/* */

    // Orders module
    /* * /
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'commerce',
        'orders',
        '',
        PATH_TXCOMMERCE . 'Modules/Orders/'
    );
	/**/


    // Statistic Module
    /* * /
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'commerce',
        'statistic',
        '',
        PATH_TXCOMMERCE . 'Modules/Statistic/'
    );
	/* */

    // Systemdata module
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'commerce',
        'systemdata',
        '',
        PATH_TXCOMMERCE . 'Modules/Systemdata/'
    );

    // Category-Tree module
    /* * /
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'commerce',
        'Tree',
        '',
        PATH_TXCOMMERCE . 'Modules/CategoryNavigationFrame/'
    );
	/* */

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
        'CommerceTeam_Commerce_CategoryViewHelper::ajaxExpandCollapse',
        'CommerceTeam\\Commerce\\Controller\\CategoryNavigationFrameController->ajaxExpandCollapse'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
        'CommerceTeam_Commerce_CategoryViewHelper::ajaxGetCategoryTreeData',
        'CommerceTeam\\Commerce\\Controller\\CategoryNavigationFrameController->ajaxGetCategoryTreeData'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
        'CommerceTeam_Commerce_PermissionAjaxController::dispatch',
        'CommerceTeam\\Commerce\\Controller\\PermissionAjaxController->dispatch'
    );

if (version_compare(TYPO3_version, '7.6', '>=')) {
  /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
  $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
  $iconRegistry->registerIcon(
    'tcarecords-pages-contains-commerce',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:commerce/Resources/Public/Icons/Table/commerce_folder.gif']
  );
  $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-commerce'] = 'tcarecords-pages-contains-commerce';
  unset($iconRegistry);
} else {
   // commerce icon
  \TYPO3\CMS\Backend\Sprite\SpriteManager::addTcaTypeIcon(
      'pages',
      'contains-commerce',
      PATH_TXCOMMERCE_REL . 'Resources/Public/Icons/Table/commerce_folder.gif'
  );
}
   

    // Add default User TS config
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
        options.saveDocNew {
            tx_commerce_products = 1
            tx_commerce_article_types = 1
            tx_commerce_attributes = 1
            tx_commerce_attribute_values = 1
            tx_commerce_categories = 1
            tx_commerce_trackingcodes = 1
            tx_commerce_moveordermails = 1
        }
    ');

    // Add default page TS config
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
        # CONFIGURATION of RTE in table "tx_commerce_products", field "description"
        RTE.config.tx_commerce_products.description {
            hidePStyleItems = H1, H4, H5, H6
            proc.exitHTMLparser_db = 1
            proc.exitHTMLparser_db {
                keepNonMatchedTags = 1
                tags.font.allowedAttribs = color
                tags.font.rmTagIfNoAttrib = 1
                tags.font.nesting = global
            }
        }

        # CONFIGURATION of RTE in table "tx_commerce_articles", field "description_extra"
        RTE.config.tx_commerce_articles.description_extra < RTE.config.tx_commerce_products.description
    ');
}

// Add context menu for category trees in BE
$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
    'name' => 'CommerceTeam\\Commerce\\Utility\\ClickmenuUtility',
    'path' => PATH_TXCOMMERCE . 'Classes/Utility/ClickmenuUtility.php',
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_commerce_categories');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_commerce_products');
