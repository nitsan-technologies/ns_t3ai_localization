<?php

declare(strict_types=1);

namespace NITSAN\NsT3AiLocalization\Service;

use TYPO3\CMS\Core\Http\RequestFactory;
use GuzzleHttp\Exception\ClientException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

class CrowdinService
{
    public array $extConf;

    protected string $accessToken;


    protected RequestFactory $requestFactory;

    private const API_ENDPOINT = "https://api.crowdin.com/api/v2/";


    public function __construct()
    {
        $this->requestFactory =  GeneralUtility::makeInstance(RequestFactory::class);
        $this->extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('ns_t3ai_localization');
        $this->accessToken = 'a0ef6cde5cff914d9142c737625a0325ae9d66dfa84754353cc3a0d1929be64bc4bb308feb93d6ef';
    }


    public function fetchFile($projectId, $fileId): array
    {
        try {

            $url = self::API_ENDPOINT.'projects/'.$projectId.'/files/'.$fileId.'/download';

            $response =  $this->requestFactory->request(
                $url,
                "GET",
                [
                    "headers" => [
                        "Content-Type" => "application/json",
                        "Authorization" => "Bearer " . $this->accessToken,
                    ],
                ]
            );

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                $downloadUrl = $data['data']['url'];

                $fileResponse = $this->requestFactory->request($downloadUrl,'GET');

                if ($fileResponse->getStatusCode() === 200) {
                    $result['status'] = true;
                    $result['content'] = $fileResponse->getBody()->getContents();
                } else {
                    $result['status'] = false;
                    $result['message'] = "Failed to download the file. Status code: " . $fileResponse->getStatusCode();
                }

            } else {

                $result['status'] = false;
                $result['message'] = "Failed to get the download URL. Status code: " . $response->getStatusCode();

            }

        } catch (ClientException $e) {
            $result['status'] = false;
            $result['message'] = $e->getMessage();

        }

        return $result;

    }

    public function updateFile($projectId, $fileId, $content, $fileName)
    {
        $url = self::API_ENDPOINT.'projects/'.$projectId.'/files/'.$fileId;

        try {
         
            $data = [
                'data' => [
                    'content' => $content,
                    'name' => $fileName 
                ]
            ];

            // Send the request to update the file
            $response = $this->requestFactory->request(
                $url,
                'PUT',
                [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $data,
                ]
            );

            echo"</pre>";
            print_r($response);die();
            
            // // Handle the response
            // if ($response->getStatusCode() === 200) {
            //     // Success
            //     $responseBody = $response->getBody()->getContents();
            //     return json_decode($responseBody, true);
            // } else {
            //     // Handle errors
            //     return [
            //         'error' => 'Failed to update file',
            //         'status_code' => $response->getStatusCode(),
            //     ];
            // }
        } catch (\Exception $e) {

            echo"</pre>";
            print_r($e->getMessage());die();

            // Handle exception
            // return [
            //     'error' => $e->getMessage(),
            // ];
        }
    }
}
