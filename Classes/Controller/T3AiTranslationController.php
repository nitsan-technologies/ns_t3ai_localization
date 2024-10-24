<?php

namespace NITSAN\NsT3AiLocalization\Controller;


use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use NITSAN\NsT3AiLocalization\Utility\XliffUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use NITSAN\NsT3AiLocalization\Service\CrowdinService;


class T3AiTranslationController extends AbstractAIController
{   

    protected Locales $locales;

    protected CrowdinService $crowdinService;



    public function __construct(
        CrowdinService $crowdinService
    )
    {
        parent::__construct();
        $this->loadAssets();
        $this->crowdinService = $crowdinService;
        $this->locales = GeneralUtility::makeInstance(Locales::class);
    }


    public function indexAction(ServerRequestInterface $request)
    {
        $languages = $this->locales->getLanguages();
        if (array_key_exists('default', $languages)) {
            unset($languages['default']);
        }
        $assign = [
            't3version' => $this->typo3VersionArray['version_main'],
            'allLanguagesList' =>  $this->locales->getLanguages(),
            'requestUrl' => $this->generateBackendUrl('ajax_file_read')
        ];
        
        $extension = $request->getQueryParams() ? $request->getQueryParams()['additionalParams'] : '';
        $listUtility = GeneralUtility::makeInstance(ListUtility::class);
        $extensionDetails = $listUtility->getExtension($extension);
       
        $packagePath = $extensionDetails->getPackagePath();
        $assign['extensionType'] = $extensionDetails->getPackageMetaData()->getPackageType();

        if ($extension) {
            $assign['extkey'] = $extension;
            $xlfUtility = GeneralUtility::makeInstance(XliffUtility::class);
            $assign['files'] = $xlfUtility->getFileList($packagePath);
        }

        if($request->getParsedBody()){

            $requestData = $request->getParsedBody();
            // $fileName = $requestData['filename'] ?? '';
            // $targetLanguage = $requestData['targetLanguage'] ?? '';
            $fileLocation = $requestData['fileLocation'] ?? '';
            $editingMode = $requestData['editingMode'] ?? '';

            if($fileLocation === 'crowdin'){

                $projectId = (int)$requestData['projectId'] ?? 730787;
                $fileId = (int)$requestData['fileId'] ?? 1;

                $response = $this->crowdinService->fetchFile($projectId,$fileId);
                if($response['status'] && $response['content']){

                    if($editingMode === 'ui'){
                        $xlfUtility = GeneralUtility::makeInstance(XliffUtility::class);
                        $assign['fileDataArray'] = $xlfUtility->stringToArray($response['content']);
                    }else{
                        $assign['fileData'] = $response['content'];
                    }
                    
                }else{
                    $result['message'] = $response['message'];
                    $result['title'] = '';
                    $result['severity'] = 2;
                    $this->addFlashNotification($result);
                }
            }

            $result['fileWriteUrl'] = $this->generateBackendUrl('ajax_write_manual_translation');
            $assign['requestData'] = $requestData;

        }

        return $this->getViewAndTemplate($request, $assign, 'Templates/', 'ManualTranslation');

    }

    public function updateFileAction(ServerRequestInterface $request)
    {
       
     
        $requestData = $request->getParsedBody()['input'];
        $location = $requestData['fileLocation'] ?? '';

        if($location === 'crowdin'){
            $projectId = $requestData['projectId'] ?? '';
            $fileId = $requestData['fileId'] ?? '';
            $content = $requestData['content'] ?? '';
            $fileName = $requestData['filename'] ?? '';
            
            $data = $this->crowdinService->updateFile($projectId,$fileId,$content,$fileName);
        \TYPO3\CMS\Core\Utility\DebugUtility::debug($request);die();

          
        }
        \TYPO3\CMS\Core\Utility\DebugUtility::debug($request);die();
        
    }


}
