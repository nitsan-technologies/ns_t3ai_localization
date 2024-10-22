<?php

namespace NITSAN\NsT3AiLocalization\Controller;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Page\PageRenderer;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

abstract class AbstractAIController extends ActionController
{

    protected UriBuilder $backendUriBuilder;

    protected array $typo3VersionArray = [];

    protected ModuleTemplateFactory $moduleTemplateFactory;

    protected PageRenderer $pageRenderer;

    public function __construct(
    ) {
        $this->typo3VersionArray = $this->getVersionData();
        $this->backendUriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
    }

    protected function getViewAndTemplate(ServerRequestInterface $request, array $assign, string $dir, string $template): ResponseInterface
    {
        $assign['version'] = $this->typo3VersionArray['version_main'];
        $assign['t3version'] = $this->typo3VersionArray['version_main'];
        if ($this->typo3VersionArray['version_main'] === 11) {
            $view = GeneralUtility::makeInstance(StandaloneView::class);
            $view->setLayoutRootPaths([
                GeneralUtility::getFileAbsFileName('EXT:ns_t3ai_localization/Resources/Private/Layouts/'),
                GeneralUtility::getFileAbsFileName('EXT:ns_t3ai_localization/Resources/Private/Layouts/'),
            ]);
            $view->setTemplateRootPaths([
                GeneralUtility::getFileAbsFileName('EXT:ns_t3ai_localization/Resources/Private/Templates/'),
                GeneralUtility::getFileAbsFileName('EXT:ns_t3ai_localization/Resources/Private/Templates/')
            ]);
            $view->setPartialRootPaths([
                GeneralUtility::getFileAbsFileName('EXT:ns_t3ai_localization/Resources/Private/Partials/'),
                GeneralUtility::getFileAbsFileName('EXT:ns_t3ai_localization/Resources/Private/Partials/')
            ]);

            $view->setTemplatePathAndFilename('EXT:ns_t3ai_localization/Resources/Private/' . $dir.$template. '.html');
            $view->assignMultiple($assign);
            $response = GeneralUtility::makeInstance(Response::class);
            $response->getBody()->write($view->render());
            return $response;

        } else {
            $view = $this->initializeModuleTemplate($request);
            $view->assignMultiple($assign);
            return $view->renderResponse($template);
        }
    }

    public function getCurrentPagePathData(int $pageId): array
    {
        $pageRecord  = BackendUtility::readPageAccess($pageId, '1=1');
        $pagePath = $iconHtml = '';
        $dokType = 1;
        if (is_array($pageRecord) && isset($pageRecord['uid'])) {
            // Is this a real page
            $pagePath = substr($pageRecord['_thePathFull'] ?? '', 0, -1);
            // Remove current page title
            $pos = strrpos($pagePath, $pageRecord['title']);
            if ($pos !== false) {
                $pagePath = substr($pagePath, 0, $pos);
            }
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $iconIdentifier = $iconFactory->mapRecordTypeToIconIdentifier('pages', $pageRecord);
            $dokType = $pageRecord['doktype'];
            $iconHtml = $iconFactory->getIcon($iconIdentifier, Icon::SIZE_SMALL)->render();
        }
        return [
            'pagePath' => htmlspecialchars($pagePath),
            'pageTitle' => $pageRecord['title'] ?? '',
            'iconHtml' => $iconHtml,
            'doktype' => $dokType,
        ];
    }



    protected function loadAssets(): void
    {
        if (version_compare($this->typo3VersionArray['version_main'], 11, '<=')) {
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/NsT3aiLocalization/module-functionality');
        } else {
            $this->pageRenderer->loadJavaScriptModule('@nitsan/ns-t3ai-localization//module-functionality,js');
        }
        $this->pageRenderer->addInlineSetting('', 't3version', $this->typo3VersionArray['version_main']);
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:ns_t3ai_localization/Resources/Private/Language/locallang.xlf');

    }


    protected function addFlashNotification(array $response = [])
    {
        if ($response && (isset($response['message']) || isset($response['title']))) {
            $message = isset($response['message']) ? $response['message'] : '';
            $title = isset($response['title']) ? $response['title'] : '';
            $severity = isset($response['severity']) ? $response['severity'] : 0;
            if ($this->typo3VersionArray['version_main'] === 11) {
                $notificationInstruction = JavaScriptModuleInstruction::forRequireJS('TYPO3/CMS/Backend/Notification');
            } else {
                $notificationInstruction = JavaScriptModuleInstruction::create('@typo3/backend/notification.js');
            }
            $notificationInstruction->invoke('showMessage', $title, $message, (int)$severity);
            $this->pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction($notificationInstruction);
        }
    }

    protected function generateBackendUrl(string $route, array $parameters = [], string $referenceType = 'absolute'): string
    {
        return (string)$this->backendUriBuilder->buildUriFromRoute($route, $parameters, $referenceType);
    }

    public function getVersionData(): array
    {
        return VersionNumberUtility::convertVersionStringToArray(
            VersionNumberUtility::getCurrentTypo3Version()
        );
    }

    protected function initializeModuleTemplate(ServerRequestInterface $request): ModuleTemplate
    {
        return $this->moduleTemplateFactory->create($request);
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
