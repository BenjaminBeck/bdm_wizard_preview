<?php

use BDM\BdmWizardPreview\Hook\Backend\PageRendererPreProcessHook;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;

defined('TYPO3') or die('Access denied.');


call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['bdm_wizard_preview'] = PageRendererPreProcessHook::class. '->__invoke';;
});
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][NewContentElementController::class] = [
    'className' => \BDM\BdmWizardPreview\XCLASS\NewContentElementController::class,
];

// Load site configuration overrides
$siteConfigurationOverride = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:bdm_wizard_preview/Configuration/SiteConfiguration/Overrides/site.php');
if (file_exists($siteConfigurationOverride)) {
    require_once $siteConfigurationOverride;
}

