<?php

/**
 * Site configuration override for bdm_wizard_preview extension
 * Adds custom settings to the "New Content Wizard" tab in site configuration
 */

defined('TYPO3') or die();

$GLOBALS['SiteConfiguration']['site']['columns']['bdmWizardPreviewImagePath'] = [
    'label' => 'Preview Image Path',
    'description' => 'Path to the folder containing preview images for the new content wizard. Example: EXT:bdm_content_su/Resources/Public/Backend/Images/WizardPreview/',
    'config' => [
        'type' => 'input',
        'size' => 50,
        'max' => 255,
        'default' => '',
        'placeholder' => 'EXT:your_extension/Resources/Public/Backend/Images/WizardPreview/',
        'eval' => 'trim',
    ],
];

// Ensure showitem exists before appending
if (!isset($GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'])) {
    $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = '';
}

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ',
    --div--;New Content Wizard,
        bdmWizardPreviewImagePath
';
