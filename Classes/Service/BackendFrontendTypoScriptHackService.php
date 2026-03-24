<?php
declare(strict_types=1);

namespace BDM\BdmWizardPreview\Service;


use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScriptFactory;
use TYPO3\CMS\Core\TypoScript\IncludeTree\SysTemplateRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;



class BackendFrontendTypoScriptHackService
{
	public function __construct(
		private readonly FrontendTypoScriptFactory $factory,
		private readonly SiteFinder $siteFinder,
		private readonly SysTemplateRepository $sysTemplateRepository,
	) {}

	public function build(int $pageId, int $languageId = 0, string $typeNum = '0'): FrontendTypoScript
	{
		$site = $this->siteFinder->getSiteByPageId($pageId);
		$language = $site->getLanguageById($languageId);

		$request = (new ServerRequest($site->getBase(), 'GET'))
			->withAttribute('site', $site)
			->withAttribute('language', $language);

		$rootline = (new RootlineUtility($pageId))->get();
		$sysTemplateRows = $this->sysTemplateRepository->getSysTemplateRowsByRootline($rootline, $request);

		$vars = [
			'request' => $request,
			'site' => $site,
			'language' => $language,
			'pageId' => $pageId,
		];

//		$typoscriptCache =

		$typoScript = $this->factory->createSettingsAndSetupConditions(
			$site,
			$sysTemplateRows,
			$vars,
			null
		);

		return $this->factory->createSetupConfigOrFullSetup(
			true,
			$typoScript,
			$site,
			$sysTemplateRows,
			$vars,
			$typeNum,
			null,
			$request
		);
	}

//	public static function forPage(
//		int $pageId,
//		int $languageId = 0,
//		string $typeNum = '0'
//	): FrontendTypoScript {
//		$site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
//
//		// Minimaler Request (hilft u.a. bei Conditions, die request-basiert sind)
//		$uri = $site->getBase()->withPath('/')->withQuery(http_build_query([
//			'id' => $pageId,
//			'L' => $languageId,
//			'type' => $typeNum,
//		]));
//		$request = (new ServerRequest($uri, 'GET'))
//			->withAttribute('site', $site)
//			->withAttribute('language', $site->getLanguageById($languageId));
//
//		// Rootline + sys_template Rows einsammeln
//		$rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pageId)->get();
//		$sysTemplateRows = GeneralUtility::makeInstance(SysTemplateRepository::class)
//			->getSysTemplateRowsByRootline($rootline, $request);
//
////		 TypoScript rechnen
////		$factory = GeneralUtility::makeInstance(FrontendTypoScriptFactory::class);
//		/** @var FrontendTypoScriptFactory $factory */
//		$factory = GeneralUtility::getContainer()->get(FrontendTypoScriptFactory::class);
//
//
//		// Für quick&dirty: Ausdrucksvariablen minimal halten (Request ist wichtiger als Perfektion)
//		$expressionMatcherVariables = [
//			'request' => $request,
//			'site' => $site,
//			'language' => $site->getLanguageById($languageId),
//			'pageId' => $pageId,
//		];
//
//		$frontendTypoScript = $factory->createSettingsAndSetupConditions(
//			$site,
//			$sysTemplateRows,
//			$expressionMatcherVariables,
//			null // kein Cache-Frontend
//		);
//
//		// "true" = Full setup (du willst i.d.R. setup + config)
//		return $factory->createSetupConfigOrFullSetup(
//			true,
//			$frontendTypoScript,
//			$site,
//			$sysTemplateRows,
//			$expressionMatcherVariables,
//			$typeNum,
//			null,     // kein Cache-Frontend
//			$request  // optional, aber hilfreich
//		);
//	}
}
