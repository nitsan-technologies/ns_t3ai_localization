<?php

declare(strict_types=1);

namespace NITSAN\NsT3AiLocalization\Service;

use TYPO3\CMS\Core\Http\RequestFactory;
use GuzzleHttp\Exception\ClientException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

class OpenAiService
{
    public array $extConf;

    protected RequestFactory $requestFactory;

    private const OPENAI_ENDPOINT = "https://api.openai.com/v1/chat/completions";

    protected $prompt = 'Translate the following phrases to ([languageIsoCode]) [language] and fill in the \"target\" values in the JSON array structure provided. [Content] Translate and fill in the \'target\' values accordingly. Keep the structure intact. Return the modified JSON array. Consider this NOTE while translating: \"The result should only have translated text. Do not write anything else, and do not write an explanation\"';

    public function __construct()
    {
        $this->requestFactory =  GeneralUtility::makeInstance(RequestFactory::class);
        $this->extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('ns_t3ai_localization');
    }


    public function translateRequest(string $isoCode, string $langTitle, array $requestData): array
    {

        $response = [];
        $translationSubParts = array_chunk($requestData, 20, true);
        foreach ($translationSubParts as $subpart) {
            $responseJson = $this->translate(
                'Input JSON:\n ' . json_encode($subpart),
                strtolower($isoCode),
                $langTitle
            );

            $response = $response ? array_merge($response, json_decode($responseJson, true)) : json_decode($responseJson, true);
        }
        
        return $response;

    }

    public function translate($request, $isoCode, $langTitle): string
    {
        try {

            $search = ['[languageIsoCode]', '[language]', '[Content]'];
            $replace = [$isoCode, $langTitle, $request];
            $modifiedPrompt = str_replace($search, $replace, $this->prompt);
            
            $jsonContent = [
                "model" => $this->extConf['openaiApiModel'],
                "messages" => [
                    ["role" => "user", "content" => "Gpt4"],
                    ["role" => "assistant", "content" => $modifiedPrompt],
                ],
                "max_tokens" => 1300,
            ];

            $response=  $this->requestFactory->request(
                self::OPENAI_ENDPOINT,
                "POST",
                [
                    "headers" => [
                        "Content-Type" => "application/json",
                        "Authorization" => "Bearer " . $this->extConf['openaiApiKey'],
                    ],
                    "json" => $jsonContent,
                ]
            );
           
            $content = json_decode($response->getBody()->getContents(), true);
            $result['content']  = $content['choices'][0]['message']['content'];
            $result['status']  = 'true';
            return json_encode($result);

        } catch (ClientException $e) {
            
            $result['status']  = 'false';
            $result['message'] = $e->getMessage();
            $result = json_encode($result);
            return $result;
          
        }

    }
}
