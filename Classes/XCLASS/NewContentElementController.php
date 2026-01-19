<?php
/**
 * NewContentElementController.php
 *
 * This file is part of the "bdm_wizard_preview" Extension for TYPO3 CMS.
 *
 * It provides additional variables to the javascript LIT element
 */


namespace BDM\BdmWizardPreview\XCLASS;

use BDM\BdmWizardPreview\Helper\PreviewHelper;
use BDM\BdmWizardPreview\Service\Configuration;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\Event\ModifyNewContentElementWizardItemsEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class NewContentElementController extends \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController{

    protected PreviewHelper $previewHelper;

    // Extend the parent constructor to include PreviewHelper
    public function __construct(
        UriBuilder $uriBuilder,
        BackendViewFactory $backendViewFactory,
        EventDispatcherInterface $eventDispatcher,
        DependencyOrderingService $dependencyOrderingService
    ) {
        // Call the parent constructor to maintain the original behavior
        parent::__construct($uriBuilder, $backendViewFactory, $eventDispatcher, $dependencyOrderingService);
        $this->previewHelper = GeneralUtility::makeInstance(PreviewHelper::class);
    }

    public function enrichtItemData($item, $wizardItem, $pageUid, $request){
        $item = $this->previewHelper->enrichtItemData($item, $wizardItem, $pageUid, $request);
        return $item;
    }

    protected function wizardAction(ServerRequestInterface $request): ResponseInterface
    {
	    if(!Configuration::isEnabled()){
		    return parent::wizardAction($request);
		}
	    $pageUid = $this->pageInfo['uid'];

        if (!$this->id || $this->pageInfo === []) {
            // No pageId or no access.
            return new HtmlResponse('No Access');
        }
	    // Whether position selection must be performed (no colPos was yet defined)
	    $positionSelection = $this->colPos === null;

        // Get processed and modified wizard items
        $wizardItems = $this->eventDispatcher->dispatch(
            new ModifyNewContentElementWizardItemsEvent(
                $this->getWizards(),
                $this->pageInfo,
                $this->colPos,
                $this->sys_language,
                $this->uid_pid,
	            $request
            )
        )->getWizardItems();

        $key = 'common';
        $categories = [];
        foreach ($wizardItems as $wizardKey => $wizardItem) {
            // An item is either a header or an item rendered with title/description and icon:
            if (isset($wizardItem['header'])) {
                $key = $wizardKey;
                $categories[$key] = [
                    'identifier' => $key,
                    'label' => $wizardItem['header'] ?: '-',
                    'items' => [],
                ];
            } else {
	            // Get default values for the wizard item
	            $defaultValues = (array)($wizardItem['defaultValues'] ?? []);

                // Initialize the view variables for the item
                $item = [
                    'identifier' => $wizardKey,
                    'icon' => $wizardItem['iconIdentifier'] ?? '',
                    'iconOverlay' => $wizardItem['iconOverlay'] ?? '',
                    'label' => $wizardItem['title'] ?? '',
                    'description' => $wizardItem['description'] ?? '',
                    'defaultValues' => $defaultValues,
                ];
                // {"iconIdentifier":"content-header","title":"Nur \u00dcberschrift","description":"Eine \u00dcberschrift.","saveAndClose":false,"tt_content_defValues":{"CType":"header"}}
                $item = $this->enrichtItemData($item, $wizardItem, $pageUid, $request);

	            // If the URL was already created (e.g. via the PSR-14 event) this needs to be
	            // kept and not overwritten
	            if (isset($wizardItem['url'])) {
		            $item['url'] = $wizardItem['url'];
		            if ($positionSelection) {
			            $item['requestType'] = 'ajax';
			            $item['saveAndClose'] = (bool)($wizardItem['saveAndClose'] ?? false);
		            }
	            } elseif ($positionSelection) {
		            $item['url'] = (string)$this->uriBuilder
			            ->buildUriFromRoute(
				            'new_content_element_wizard',
				            [
					            'action' => 'positionMap',
					            'id' => $this->id,
					            'sys_language_uid' => $this->sys_language,
					            'returnUrl' => $this->returnUrl,
				            ]
			            );
		            $item['requestType'] = 'ajax';
		            $item['saveAndClose'] = (bool)($wizardItem['saveAndClose'] ?? false);
	            } else {
		            // In case no position has to be selected, we can just add the target
		            if (($wizardItem['saveAndClose'] ?? false)) {
			            // Go to DataHandler directly instead of FormEngine
			            $item['url'] = (string)$this->uriBuilder->buildUriFromRoute('tce_db', [
				            'data' => [
					            'tt_content' => [
						            StringUtility::getUniqueId('NEW') => array_replace($defaultValues, [
							            'colPos' => $this->colPos,
							            'pid' => $this->uid_pid,
							            'sys_language_uid' => $this->sys_language,
						            ]),
					            ],
				            ],
				            'redirect' => $this->returnUrl,
			            ]);
		            } else {
			            $item['url'] = (string)$this->uriBuilder->buildUriFromRoute('record_edit', [
				            'edit' => [
					            'tt_content' => [
						            $this->uid_pid => 'new',
					            ],
				            ],
				            'returnUrl' => $this->returnUrl,
				            'defVals' => [
					            'tt_content' => array_replace($defaultValues, [
						            'colPos' => $this->colPos,
						            'sys_language_uid' => $this->sys_language,
					            ]),
				            ],
			            ]);
		            }
	            }
	            $categories[$key]['items'][] = $item;
            }
        }

        // Unset empty categories
        foreach ($categories as $key => $category) {
            if ($category['items'] === []) {
                unset($categories[$key]);
            }
        }

        $view = $this->backendViewFactory->create($request);
        $view->assignMultiple([
            'positionSelection' => $positionSelection,
            'categoriesJson' => GeneralUtility::jsonEncodeForHtmlAttribute($categories, false),
        ]);
        return new HtmlResponse($view->render('NewContentElement/Wizard'));
    }






}
