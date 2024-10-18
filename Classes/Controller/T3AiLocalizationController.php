<?php

namespace NITSAN\NsT3AiLocalization\Controller;

use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use NITSAN\NsT3AiLocalization\Utility\XliffUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;



class T3AiLocalizationController extends AbstractAIController
{   
    protected Locales $locales;


    public function __construct(
    )
    {
        parent::__construct();
        $this->loadAssets();
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
        $availableExtensions = $listUtility->getAvailableExtensions('Local');
        $languages = $this->locales->getLanguages();

        if (array_key_exists('default', $languages)) {
            unset($languages['default']);
        }
        $assign = [
            't3version' => $this->typo3VersionArray['version_main'],
            'allLanguagesList' => $languages,
            'extensions' => $availableExtensions,
            'templateLayout' => 'T3AiLocalization/Index',
            'validateDataUrl' => $this->generateBackendUrl('ajax_file_validate'),
        ];
        
        $responseData = $request->getQueryParams() ?? [];
        $this->addFlashNotification($responseData);

        $requestData = $request->getParsedBody();
        $extension = $requestData['extensionKey'] ?? $responseData['extensionKey'] ?? null;

        if($availableExtensions){
            foreach($availableExtensions as $extkey => $value){
                $xlfUtility = GeneralUtility::makeInstance(XliffUtility::class);
                $fileOptions[$extkey] = array_values($xlfUtility->getFileList($availableExtensions[$extkey]));
            }
            
            $this->pageRenderer->addInlineSetting('ExtensionFiles', 'fileOptions', $fileOptions);
        }
        
        if ($extension) {
            $assign['extkey'] = $extension;
            $xlfUtility = GeneralUtility::makeInstance(XliffUtility::class);
            $assign['files'] = $xlfUtility->getFileList($availableExtensions[$assign['extkey']]);
        }

        return $this->getViewAndTemplate($request, $assign, 'Templates/', 'T3AiLocalization/Index');
    }

}
