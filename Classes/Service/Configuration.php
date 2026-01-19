<?php

namespace BDM\BdmWizardPreview\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class Configuration
{
	public function __construct(
		private readonly BackendFrontendTypoScriptHackService $tsBuilder
	) {}

    static public function isEnabled(): bool
    {
        $config = self::getExtensionConfiguration();
        return (bool)$config['enabled'];
    }

    static private function getExtensionConfiguration(): array
    {
        $backendConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('bdm_wizard_preview');
        return $backendConfiguration;
    }

    public static function getTyposcriptPreviewPath($pageUid, $request, $setupArray): string
    {
//	    $ts = BackendFrontendTypoScriptHackService::forPage($pageUid, 0, '0');
//	    $config = $ts->getConfigArray();   // statt TSFE->config['config']  (v13+)
//	    $setup  = $ts->getSetupArray();    // falls Full-Setup berechnet wurde
//	    $flatSettings = $ts->getFlatSettings();
//	    $ts = $this->tsBuilder->build(123, 0);
//	    $setup = $ts->getSetupArray();
//	    $config = $ts->getConfigArray();

//        $setupArray = self::getTyposcriptModuleSettings($pageUid, $request);
        if(count($setupArray) === 0){
            return "";
        }
		$path = $setupArray["module."]["tx_bdmwizardpreview."]["settings."]["previewFolder"];
//        $path = $setupArray["previewFolder"];
        $path = trim($path, '/');
        return $path;
    }

    private static $runtimeCachedTyposcriptSetupArray = [];
    private static function getTyposcriptModuleSettings($pageUid, $request): array
    {
        if(isset(self::$runtimeCachedTyposcriptSetupArray[$pageUid])){
            $setupArray = self::$runtimeCachedTyposcriptSetupArray[$pageUid];
        }else{
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByPageId($pageUid);
			/** @var TypoScriptFrontendController $controller */
            $controller = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                GeneralUtility::makeInstance(Context::class),
                $site,
                $site->getDefaultLanguage(),
                new PageArguments($site->getRootPageId(), '0', []),
                GeneralUtility::makeInstance(FrontendUserAuthentication::class)
            );
            // @extensionScannerIgnoreLine
            $controller->id = $pageUid;
			$ts = $request->getAttribute('frontend.typoscript');
//            $controller->determineId($request);
//            $setupArray = $controller->getFromCache($request)->getAttribute('frontend.typoscript')->getSetupArray();
//            self::$runtimeCachedTyposcriptSetupArray[$pageUid] = $setupArray;
        }
        if(isset($setupArray["module."]["tx_bdmwizardpreview."]["settings."])){
            $result = $setupArray["module."]["tx_bdmwizardpreview."]["settings."];
        }else{
            $result = [];
        }
        return $result;
    }

}
