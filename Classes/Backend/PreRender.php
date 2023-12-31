<?php

namespace BDM\BdmWizardPreview\Backend;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class PreRender
{
    public function addRequireJsConfiguration($a, PageRenderer $pageRenderer){
        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
             && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()
         ) {
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/BdmWizardPreview/Backend');
            $extensionKey = 'bdm_wizard_preview';
            $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get($extensionKey);
            $previewImagePath = $configuration['previewImagePath'];
            $absoluteImagePath = GeneralUtility::getFileAbsFileName($previewImagePath);
            $allPreviewImages = GeneralUtility::getAllFilesAndFoldersInPath([], $absoluteImagePath, 'png', false, 2, '');
            $absoluteImagePath = \TYPO3\CMS\Core\Utility\PathUtility::getPublicResourceWebPath ($previewImagePath);
            $configuration['previewImagePath'] = $absoluteImagePath;
            $configuration['allPreviewImagePaths'] = $allPreviewImages;
            $applicationContext = Environment::getContext();
            $isDevelepmentContext = $applicationContext->isDevelopment();
            $configuration['isDevelepmentContext'] = $isDevelepmentContext;
            $pageRenderer->addJsInlineCode(
                'bdm_wizard_preview_extension_config',
                'var bdm_wizard_preview_extension_config = ' . json_encode($configuration) . ';'
            );
            $pageRenderer->addCssFile('EXT:bdm_wizard_preview/Resources/Public/Styles/backend-wizard.css');
        }
    }
}
