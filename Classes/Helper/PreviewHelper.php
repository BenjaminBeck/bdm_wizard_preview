<?php

namespace BDM\BdmWizardPreview\Helper;

use BDM\BdmWizardPreview\Service\Configuration;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class PreviewHelper{

    public function enrichtItemData($item, $wizardItem, $pageUid, $request): array
    {
        $item['ctype'] = $wizardItem['tt_content_defValues']['CType'] ?? 'no-ctype';
        $ctype = $item['ctype'];
        $listType = $wizardItem['tt_content_defValues']['list_type'] ?? null;
        $images = $this->getImages($ctype, $listType, $pageUid, $request);
        $item['images'] = $images;
        $fileBaseName = $ctype . ($listType ? '_' . $listType : ''). '.png';
        $item['filename'] = $fileBaseName;
        return $item;
    }
    public function getImages($ctype, $listType, $pageUid, $request): array
    {
        $result = [];
        $imagePath = Configuration::getTyposcriptPreviewPath($pageUid, $request);
        $fileBaseName = $ctype . ($listType ? '_' . $listType : '');
        $fileName = $fileBaseName . '.png';
        $absoluteFilePath = GeneralUtility::getFileAbsFileName($imagePath . '/' . $fileName);
        if(empty($absoluteFilePath)){
            self::flashMessageError('bdm_wizard_preview: File not found: ' . $imagePath . '/' . $fileName);
            return $result;
        }
        $absUrl = PathUtility::getAbsoluteWebPath($absoluteFilePath);
        if(file_exists($absoluteFilePath)){
            $result[] = [
                'fileName' => $fileName,
                'fileUrl' => $absUrl
            ];
        }
        while($fileName = $fileBaseName . '-variant-' . count($result) . '.png'){
            $absoluteFilePath = GeneralUtility::getFileAbsFileName($imagePath . '/' . $fileName);
            $absUrl = PathUtility::getAbsoluteWebPath($absoluteFilePath);
            if(file_exists($absoluteFilePath)){
                $result[] = [
                    'fileName' => $fileName,
                    'fileUrl' => $absUrl
                ];
            }else{
                break;
            }
        }
        return $result;
    }


    public static function flashMessageError($message)
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


}
