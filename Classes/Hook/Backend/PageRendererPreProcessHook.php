<?php
namespace BDM\BdmWizardPreview\Hook\Backend;

use BDM\BdmWizardPreview\Service\Configuration;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class PageRendererPreProcessHook
{
    public function __invoke($params, PageRenderer $pageRenderer): void
    {
        $hasRequest = isset($GLOBALS['TYPO3_REQUEST']) && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface;
        $isBackend = $hasRequest && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();

        if(!$isBackend){
            return;
        }
        if(!Configuration::isEnabled()){
            return;
        }
        /** this needs to be executed on every request or else the files wont be loaded when the user
         * switches to page view - maybe because the new content wizard overlay is not inside the iframe but
         * on the outer page ...
         */
        $pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction(
            JavaScriptModuleInstruction::create('@bdm/bdm_wizard_preview/mod_new_content_wizard.js')
        );
        $applicationContext = Environment::getContext();
        $isDevelepmentContext = $applicationContext->isDevelopment();


	    $pageUid = (int)$_GET['id'];
	    $pageTsConfig = BackendUtility::getPagesTSconfig($pageUid);
	    $previewImagePath = $pageTsConfig['bdm_wizard_preview.']['previewImagePath'] ?? '';
	    $hasPreviewImagePathFromPageTs = !empty($previewImagePath);
	    $hasPreviewImagePathFromExtensionConfiguration = false;
//	    if(!$hasPreviewImagePathFromPageTs) {
//		    $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
//		    $previewImagePath = $extensionConfiguration->get('bdm_wizard_preview', 'previewImagePath');
//		    $hasPreviewImagePathFromExtensionConfiguration = !empty($previewImagePath);
//	    }

//	    $previewImagePath = "EXT:bdm_wizard_preview/Resources/Public/Images/wizard-preview-placeholder.png"
	    // Get preview image path from site configuration
	    // Example: In site configuration (Settings > Sites), add under "New Content Wizard" tab:
	    // bdmWizardPreviewImagePath: EXT:bdm_content_su/Resources/Public/Backend/Images/WizardPreview/
	    $previewImagePath = $this->getPreviewImagePathFromSiteConfig($pageUid);

	    // Fallback to hardcoded path if not configured
	    if (empty($previewImagePath)) {
	        $previewImagePath = "EXT:bdm_content_su/Resources/Public/Backend/Images/WizardPreview/";
	    }

	    $absoluteFilesystemImagePath = GeneralUtility::getFileAbsFileName($previewImagePath);
	    $allPreviewImages = GeneralUtility::getAllFilesAndFoldersInPath([], $absoluteFilesystemImagePath, 'png', false, 2, '');
	    $absoluteUrlImagePath = \TYPO3\CMS\Core\Utility\PathUtility::getPublicResourceWebPath ($previewImagePath);
	    $hasAnyPreviewImages = !empty($allPreviewImages);


//	    if(!$hasPreviewImagePathFromPageTs && !$hasPreviewImagePathFromExtensionConfiguration) {
//		    $pageRenderer->addCssFile('EXT:bdm_wizard_preview/Resources/Public/Styles/backend-wizard.css');
//		    $pageRenderer->addJsInlineCode('bdm_wizard_preview_extension_config', 'console.warn("bdm_wizard_preview: No previewImagePath found in pageTsConfig or extension configuration");');
//		    return;
//	    }



	    $configuration = [];
	    $configuration['previewImagePath'] = $absoluteUrlImagePath;
	    $configuration['allPreviewImagePaths'] = $allPreviewImages;
	    $applicationContext = Environment::getContext();
	    $isDevelepmentContext = $applicationContext->isDevelopment();
	    $configuration['isDevelepmentContext'] = $isDevelepmentContext;


//        $pageRenderer->addJsInlineCode(
//            'bdm_wizard_preview_extension_config',
//            'alert("hi"); debugger;var bdm_wizard_preview_extension_config = ' . json_encode($configuration) . ';'
//        );
		$pageRenderer->addBodyContent("<div data-identifier='bdm_wizard_preview' data-config='".json_encode($configuration)."'></div>");
        $pageRenderer->addCssFile('EXT:bdm_wizard_preview/Resources/Public/Styles/backend-wizard.css');
    }

    /**
     * Get preview image path from site configuration
     *
     * @param int $pageUid
     * @return string
     */
    private function getPreviewImagePathFromSiteConfig(int $pageUid): string
    {
        try {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByPageId($pageUid);
            $configuration = $site->getConfiguration();

            return $configuration['bdmWizardPreviewImagePath'] ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
