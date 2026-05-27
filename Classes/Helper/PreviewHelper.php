<?php

namespace BDM\BdmWizardPreview\Helper;

use BDM\BdmWizardPreview\Service\BackendFrontendTypoScriptHackService;
use BDM\BdmWizardPreview\Service\Configuration;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;

class PreviewHelper{

	private BackendFrontendTypoScriptHackService $tsBuilder;
	private BackendConfigurationManager $bcm;

	public function __construct(
		BackendFrontendTypoScriptHackService $tsBuilder,
		BackendConfigurationManager $bcm
	) {
		$this->tsBuilder = $tsBuilder;
		$this->bcm = $bcm;
	}

    public function enrichtItemData($item, $wizardItem, $pageUid, $request): array
    {
        $item['ctype'] = $wizardItem['defaultValues']['CType'] ?? 'no-ctype';
        $ctype = $item['ctype'];
        $listType = $wizardItem['defaultValues']['list_type'] ?? null;
        $images = $this->getImages($ctype, $listType, $pageUid, $request);
        $item['images'] = $images;
        $fileBaseName = $ctype . ($listType ? '_' . $listType : ''). '.png';
        $item['filename'] = $fileBaseName;
        return $item;
    }
    public function getImages($ctype, $listType, $pageUid, $request): array
    {
        $result = [];
		$tsp = $this->bcm->getTypoScriptSetup($request);
//		$ts = $this->tsBuilder->build($pageUid, 0);
//			 $ts = $this->tsBuilder->build(123, 0);
//    $setup = $ts->getSetupArray();
//    $config = $ts->getConfigArray();
        $imagePath = Configuration::getTyposcriptPreviewPath($pageUid, $request,$tsp);

        // Get preview image path from site configuration.
        // Do not fall back to a project extension here: TYPO3 14 resolves EXT:
        // paths through SystemResourceFactory and fails hard when the package is
        // unknown. A stale fallback from another project would break the backend.
        $siteConfigPath = $this->getPreviewImagePathFromSiteConfig($pageUid);
        if (!empty($siteConfigPath)) {
            $imagePath = $siteConfigPath;
        }

	    $absoluteFilesystemImagePath = GeneralUtility::getFileAbsFileName($imagePath);
        if ($absoluteFilesystemImagePath === '' || !is_dir($absoluteFilesystemImagePath)) {
            return $result;
        }

        $fileBaseName = $ctype . ($listType ? '_' . $listType : '');
        $fileName = $fileBaseName . '.png';
//        $absoluteFilePath = GeneralUtility::getFileAbsFileName($imagePath . '/' . $fileName);
        $absoluteFilePath = $absoluteFilesystemImagePath . '/' . $fileName;

        if(empty($absoluteFilePath)){
//            self::flashMessageError('bdm_wizard_preview: File not found: ' . $imagePath . '/' . $fileName);
            return $result;
        }
        $absUrl = PathUtility::getAbsoluteWebPath($absoluteFilePath);
        if(file_exists($absoluteFilePath)){
			$imageSize = getimagesize($absoluteFilePath);
			$imageWidth = $imageSize[0];
			$imageHeight = $imageSize[1];
            $result[] = [
                'fileName' => $fileName,
                'imageWidth' => $imageWidth,
                'imageHeight' => $imageHeight,
                'fileUrl' => $absUrl
            ];
        }
        while($fileName = $fileBaseName . '-variant-' . count($result) . '.png'){
            $absoluteFilePath = GeneralUtility::getFileAbsFileName($imagePath . '/' . $fileName);
            $absUrl = PathUtility::getAbsoluteWebPath($absoluteFilePath);
            if(file_exists($absoluteFilePath)){
				$imageSize = getimagesize($absoluteFilePath);
				$imageWidth = $imageSize[0];
				$imageHeight = $imageSize[1];
                $result[] = [
                    'fileName' => $fileName,
                    'imageWidth' => $imageWidth,
                    'imageHeight' => $imageHeight,
                    'fileUrl' => $absUrl
                ];
            }else{
                break;
            }
        }
        return $result;
    }


    public static function flashMessageError($message): void
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            '',
            ContextualFeedbackSeverity::ERROR,
            true
        );
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
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
