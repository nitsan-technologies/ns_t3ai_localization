<?php

namespace NITSAN\NsT3AiLocalization\Controller;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use NITSAN\NsT3AiLocalization\Utility\XliffUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use NITSAN\NsT3AiLocalization\Service\TranslationService;
use NITSAN\NsT3AiLocalization\Domain\Repository\LocalizationLogRepository;



class T3AiLocalizationController extends AbstractAIController
{   

    protected int $limit = 10;

    protected Locales $locales;

    protected TranslationService $translationService;

    protected LocalizationLogRepository $localizationlogRepository;


    public function __construct(
        LocalizationLogRepository $localizationlogRepository,
        TranslationService $translationService
    )
    {
        parent::__construct();
        $this->loadAssets();
        $this->translationService = $translationService;
        $this->localizationlogRepository = $localizationlogRepository;
        $this->locales = GeneralUtility::makeInstance(Locales::class);
    }

    public function translationAction()
    {

        $pageId = (int)isset($this->request->getQueryParams()['id']) ?
            $this->request->getQueryParams()['id'] : 0;
        $pagePathData = $this->getCurrentPagePathData($pageId);

        $assign = [
            'opentab' => 'Translation',
            'id' => $pageId,
            't3version' => $this->typo3VersionArray['version_main'],
            'pagePathData' => $pagePathData,
        ];
        return $this->getViewAndTemplate($this->request, $assign, 'Templates/', 'T3AiLocalization/Dashboard');

    }

    public function indexAction(ServerRequestInterface $request)
    {
        $listUtility = GeneralUtility::makeInstance(ListUtility::class);
        $availableExtensions = $listUtility->getAvailableExtensions('');
            
        $languages = $this->locales->getLanguages();
        
        if (array_key_exists('default', $languages)) {
            unset($languages['default']);
        }
        $assign = [
            't3version' => $this->typo3VersionArray['version_main'],
            'allLanguagesList' => $languages,
            'extensions' => $availableExtensions,
            'templateLayout' => 'T3AiLocalization/Index',
            'validateDataUrl' => $this->generateBackendUrl('ajax_file_translate'),
            'extSettings' => GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('ns_t3ai_localization')
        ];
        
        $responseData = $request->getQueryParams() ?? [];

        $this->addFlashNotification($responseData);

        $requestData = $request->getParsedBody();
        $extension = $requestData['extensionKey'] ?? $responseData['additionalParams'] ?? null;

        if($availableExtensions){
            foreach($availableExtensions as $extkey => $value){
                $xlfUtility = GeneralUtility::makeInstance(XliffUtility::class);
                $fileOptions[$extkey] = array_values($xlfUtility->getFileList($availableExtensions[$extkey]['packagePath']));
            }
            
            $this->pageRenderer->addInlineSetting('ExtensionFiles', 'fileOptions', $fileOptions);
        }
            
        if ($extension) {
            $assign['extkey'] = $extension;
            $xlfUtility = GeneralUtility::makeInstance(XliffUtility::class);
            $assign['files'] = $xlfUtility->getFileList($availableExtensions[$assign['extkey']]['packagePath']);
        }
        
        return $this->getViewAndTemplate($request, $assign, 'Templates/', 'T3AiLocalization/Index');
    }


    public function translateAction(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();
            
        if (!$data['targetLanguage']) {
            return new RedirectResponse($this->generateBackendUrl('ajax_file_localization', $data));
        }
        if ($data['translationMode'] === '1') {
            try {
                $xlfUtility = GeneralUtility::makeInstance(XliffUtility::class);
                foreach ($data['filename'] as $fileName) {
                    $xlfUtility->readXliff($data['extensionKey'], $data['targetLanguage'] . '.' . $fileName, false);
                }
                $message = '';
                if (isset($data['extensionKey'])) {
                    $message .= 'Extension: ' . $data['extensionKey'] . 'Translated to: ' . $data['targetLanguage'];
                }
              
            } catch (\Exception $exception) {
                $data['severity'] = 2;
                $data['message'] = $exception->getMessage();
                $data['title'] = $this->getLocallangTranslation('traslation.file_missing.error');
                return new RedirectResponse($this->generateBackendUrl('ajax_file_localization', $data));
            }
        }

        $dataValues =  $data;
        unset($dataValues['filename']);
        $writeableData = [];
        $result = [];
        try {

            foreach ($data['filename'] as $fileName) {
                $dataValues['filename'] = $fileName;
                $xlfUtility = GeneralUtility::makeInstance(XliffUtility::class);
                $translations = $xlfUtility->getTranslateValues($dataValues);
                
                if (count($translations) === 0) {
                    $data['severity'] = 1;
                    $data['message'] = $this->getLocallangTranslation('translation.noTranslationsNeeded.message');
                    $data['title'] = $this->getLocallangTranslation('translation.noTranslationsNeeded.title');
                    continue;
                }   

                $responseData = $this->translationService->request($data['apiModel'], $data['targetLanguage'], $translations);
                $response = json_decode($responseData['content'], true);
                $dataValues['translations'][] = $response;
                $xlfUtility = GeneralUtility::makeInstance(XliffUtility::class);
                $originalValues = $xlfUtility->readXliff($data['extensionKey'], $dataValues['filename']);
                
                foreach ($originalValues as $origKey => $origValue) {
                    if (is_array($response)) {
                        if (array_key_exists($origKey, $response)) {
                            $originalValues[$origKey]['translated'] = $response[$origKey];
                        } else {
                            unset($originalValues[$origKey]);
                        }
                    }
                }
                $writeableData[$dataValues['filename']] = $originalValues;
            }
            $message = '';
            if (isset($data['extensionKey'])) {
                $message .= 'Extension: ' . $data['extensionKey'] . ' Translated to: ' . $data['targetLanguage'];
            }
          
            if ($writeableData) {
                $assign = [
                    't3version' => $this->typo3VersionArray['version_main'],
                    'allLanguagesList' => $this->locales->getLanguages(),
                    'input' => $dataValues,
                    'originalValues' => $writeableData,
                    'templateLayout' => 'T3AiLocalization/Validate',
                    'mainPageUrl' => $this->generateBackendUrl(
                        'ajax_file_localization',
                        ['extensionKey' => $dataValues['extensionKey']]
                    ),
                ];

                $result['message'] = $this->getLocallangTranslation('translation.fetchingDataSuccessful.message');
                $result['title'] = $this->getLocallangTranslation('translation.fetchingDataSuccessful.title');

                $this->addFlashNotification($result);
                return $this->getViewAndTemplate($request, $assign, 'Templates/', 'T3AiLocalization/Validate');
            } else {
                return new RedirectResponse($this->generateBackendUrl('ajax_file_localization', $data));
            }
        } catch (\Exception $exception) {
            
            $data['severity'] = 2;
            $data['message'] = $exception->getMessage();
            $data['title'] = $this->getLocallangTranslation('translation.fetchingDataSuccessful.title');
            return new RedirectResponse($this->generateBackendUrl('ajax_file_localization', $data));
        }
    }

    public function writeXlfAction(ServerRequestInterface $request)
    {
        $input = isset($request->getParsedBody()['input']) ? $request->getParsedBody()['input'] : [];
        $xlfUtility = GeneralUtility::makeInstance(XliffUtility::class);
        $inputArray = $input;
       
        $result['extensionKey'] = $input['extensionKey'];
        try {
            foreach ($input['filename'] as $file) {
                $inputArray['filename'] = $file;
                $inputArray['translations'] = $input[$file]['translations'];
                $result = json_decode($xlfUtility->writeXliff($inputArray), true);

                $logEntries = [
                    'translation_mode' => $input['translationMode'],
                    'extension_name' => $input['extensionKey'],
                    'source_file' => $file,
                    'output_file' => $input['targetLanguage'] . '.' . $file
                ];
                $inputArray['translations'] = $input[$file]['translations'];
                $logEntries['content'] = $result['content'];
                $logEntries['status'] = $result['status'];
                $this->localizationlogRepository->insertRecord($logEntries);
            }
            return new JsonResponse(['status'=> true]);

        } catch (\Exception $exception) {
            return new JsonResponse(['status'=> false]);
        }

    }

    public function historyAction(ServerRequestInterface $request)
    {
        $arguments = [];
        $request = $request->getParsedBody();
        $filter = isset($request['filter']) ? $request['filter'] : [];
        $currentPage = isset($arguments['currentPage']) ? $arguments['currentPage'] : 1;
        $translations = $this->localizationlogRepository->findRecords($filter);
        $paginator = new QueryResultPaginator($translations, $currentPage, $this->limit);
        $pagination = new SimplePagination($paginator);
        $listUtility = GeneralUtility::makeInstance(ListUtility::class);
        $availableExtensions = $listUtility->getAvailableExtensions('Local');
        $requestUri = $request->getAttribute('normalizedParams')->getRequestUri();
        $assign = [
            't3version' => $this->typo3VersionArray['version_main'],
            'activeTab' => 'Translation',
            'extensions' => $availableExtensions,
            'arguments' => $arguments,
            'filter' => $filter,
            'templateLayout' => 'T3AiLocalization/History',
            'returnUrl' => $requestUri,
            'pagination' => [
                'pagination' => $pagination,
                'paginator' => $paginator,
            ]
        ];
        $this->loadAssets();
        if ($this->typo3VersionArray['version_main'] === 11) {
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/ContextMenu');
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/Tooltip');
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/MultiRecordSelection');
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/MultiRecordSelectionAction');
        } else {
            $this->pageRenderer->loadJavaScriptModule('@typo3/backend/context-menu.js');
            $this->pageRenderer->loadJavaScriptModule('@typo3/backend/modal.js');
            $this->pageRenderer->loadJavaScriptModule('@typo3/backend/multi-record-selection.js');
            $this->pageRenderer->loadJavaScriptModule('@typo3/backend/multi-record-selection-delete-action.js');
        }
        return $this->getViewAndTemplate($this->request, $assign, 'Templates/', 'T3AiLocalization/History');
    }
    
    protected function getLocallangTranslation(string $id): string
    {
        return $this->getLanguageService()->sL('LLL:EXT:ns_t3ai_localization/Resources/Private/Language/locallang.xlf:' . $id);
    }
   

}
