<?php

namespace BDM\BdmWizardPreview\Backend;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class PreRender
{
    public function addRequireJsConfiguration($a, PageRenderer $pageRenderer): void{


        $isValidRequest = ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface;
        if(!$isValidRequest) return;

        $isBackendApplicationType = ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();
        if(!$isBackendApplicationType) return;

        $requestHasGetId = isset($_GET['id']);
        if(!$requestHasGetId) {
            $pageRenderer->addCssFile('EXT:bdm_wizard_preview/Resources/Public/Styles/backend-wizard.css');
            return;
        }

        $pageUid = (int)$_GET['id'];
        $pageTsConfig = BackendUtility::getPagesTSconfig($pageUid);
        $previewImagePath = $pageTsConfig['bdm_wizard_preview.']['previewImagePath'] ?? '';
        $hasPreviewImagePathFromPageTs = !empty($previewImagePath);
        $hasPreviewImagePathFromExtensionConfiguration = false;
        if(!$hasPreviewImagePathFromPageTs) {
            $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
            $previewImagePath = $extensionConfiguration->get('bdm_wizard_preview', 'previewImagePath');
            $hasPreviewImagePathFromExtensionConfiguration = !empty($previewImagePath);
        }
        if(!$hasPreviewImagePathFromPageTs && !$hasPreviewImagePathFromExtensionConfiguration) {
            $pageRenderer->addCssFile('EXT:bdm_wizard_preview/Resources/Public/Styles/backend-wizard.css');
            $pageRenderer->addJsInlineCode('bdm_wizard_preview_extension_config', 'console.warn("bdm_wizard_preview: No previewImagePath found in pageTsConfig or extension configuration");');
            return;
        }

        $absoluteFilesystemImagePath = GeneralUtility::getFileAbsFileName($previewImagePath);
        $allPreviewImages = GeneralUtility::getAllFilesAndFoldersInPath([], $absoluteFilesystemImagePath, 'png', false, 2, '');
        $absoluteUrlImagePath = \TYPO3\CMS\Core\Utility\PathUtility::getPublicResourceWebPath ($previewImagePath);
        $hasAnyPreviewImages = !empty($allPreviewImages);
        if(!$hasAnyPreviewImages) {
//            $pageRenderer->addCssFile('EXT:bdm_wizard_preview/Resources/Public/Styles/backend-wizard.css');
            $pageRenderer->addJsInlineCode('bdm_wizard_preview_extension_config_alert', 'console.warn("bdm_wizard_preview: No preview images found in ' . $absoluteFilesystemImagePath . '");');
//            return;
        }
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/BdmWizardPreview/Backend');
        $extensionKey = 'bdm_wizard_preview';
//        $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get($extensionKey);
        $configuration = [];
        $configuration['previewImagePath'] = $absoluteUrlImagePath;
        $configuration['allPreviewImagePaths'] = $allPreviewImages;
        $applicationContext = Environment::getContext();
        $isDevelepmentContext = $applicationContext->isDevelopment();
        $configuration['isDevelepmentContext'] = $isDevelepmentContext;
        $pageRenderer->addJsInlineCode(
            'bdm_wizard_preview_extension_config',
            'alert("hello"); var bdm_wizard_preview_extension_config = ' . json_encode($configuration) . ';'
        );
        $pageRenderer->addCssFile('EXT:bdm_wizard_preview/Resources/Public/Styles/backend-wizard.css');



//        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
//             && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()
//         ) {
//
//            if(isset($_GET['id'])){
//                $pageUid = $_GET['id'];
//                $pageTsConfig = BackendUtility::getPagesTSconfig($pageUid);
//
//
//
//
//
//
//                $pageRenderer->loadRequireJsModule('TYPO3/CMS/BdmWizardPreview/Backend');
//                $extensionKey = 'bdm_wizard_preview';
//                $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get($extensionKey);
//            }else{
////                $pageRenderer->addCssFile('EXT:bdm_wizard_preview/Resources/Public/Styles/backend-wizard.css');
////                return;
//            }
//            $absoluteImagePath = GeneralUtility::getFileAbsFileName($previewImagePath);
//            $allPreviewImages = GeneralUtility::getAllFilesAndFoldersInPath([], $absoluteImagePath, 'png', false, 2, '');
//            $absoluteImagePath = \TYPO3\CMS\Core\Utility\PathUtility::getPublicResourceWebPath ($previewImagePath);
//            $configuration['previewImagePath'] = $absoluteImagePath;
//            $configuration['allPreviewImagePaths'] = $allPreviewImages;
//            $applicationContext = Environment::getContext();
//            $isDevelepmentContext = $applicationContext->isDevelopment();
//            $configuration['isDevelepmentContext'] = $isDevelepmentContext;
//            $pageRenderer->addJsInlineCode(
//                'bdm_wizard_preview_extension_config',
//                'var bdm_wizard_preview_extension_config = ' . json_encode($configuration) . ';'
//            );
//            $pageRenderer->addCssFile('EXT:bdm_wizard_preview/Resources/Public/Styles/backend-wizard.css');
//        }
    }
}
