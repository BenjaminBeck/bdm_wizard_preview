<?php
defined('TYPO3') or die();

call_user_func(function()
{
    $extensionKey = 'bdm_wizard_preview';

    /**
     * Default TypoScript
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript',
        'BE Wizard Preview'
    );
});
