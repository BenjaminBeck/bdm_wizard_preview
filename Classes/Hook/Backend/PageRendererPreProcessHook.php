<?php
namespace BDM\BdmWizardPreview\Hook\Backend;

use BDM\BdmWizardPreview\Service\Configuration;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;

class PageRendererPreProcessHook
{
    public function __invoke($params, PageRenderer $pageRenderer)
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
        $configuration['isDevelepmentContext'] = $isDevelepmentContext;
        $pageRenderer->addJsInlineCode(
            'bdm_wizard_preview_extension_config',
            'var bdm_wizard_preview_extension_config = ' . json_encode($configuration) . ';'
        );
        $pageRenderer->addCssFile('EXT:bdm_wizard_preview/Resources/Public/Styles/backend-wizard.css');
    }
}
