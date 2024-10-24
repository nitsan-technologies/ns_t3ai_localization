<?php

declare(strict_types=1);

namespace NITSAN\NsT3AiLocalization\Service;

use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use NITSAN\NsT3AiLocalization\Service\OpenAiService;

class TranslationService
{

    public $openAiService;

    protected Locales $locales;


    public function __construct()
    {
        $this->openAiService = GeneralUtility::makeInstance(OpenAiService::class);
        $this->locales = GeneralUtility::makeInstance(Locales::class);
    }

    public function request(string $apiModel, string $isoCode, array $requestData): array
    {
        $allLanguages = $this->locales->getLanguages();
        $langTitle = $allLanguages[$isoCode];
        $responseData = [];
        switch ($apiModel) {
            case 'openai':
                $responseData = $this->openAiService->translateRequest($isoCode, $langTitle, $requestData);
                break;
        }
        return $responseData;

    }
}