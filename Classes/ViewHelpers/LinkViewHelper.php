<?php


namespace NITSAN\NsT3AiLocalization\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface as ExtbaseRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder as ExtbaseUriBuilder;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;


class LinkViewHelper extends AbstractTagBasedViewHelper
{
    /**
    * @var string
    */
    protected $tagName = 'a';

    /**
     * Initialize arguments
     *
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();

        $this->registerArgument('extensionKey', 'string', 'id', true);
    }

    public function render(): string
    {
        $extensionKey = (string) $this->arguments['extensionKey'];
        
        $arguments = [];
        if ($extensionKey) {
            $arguments['extensionKey'] = $extensionKey;
        }

        /** @var RenderingContext $renderingContext */
        $renderingContext = $this->renderingContext;
        $request = $renderingContext->getRequest();

        if ($request instanceof ExtbaseRequestInterface) {
            $uriBuilder = GeneralUtility::makeInstance(ExtbaseUriBuilder::class);
            $uriBuilder->reset()
                ->setRequest($request)
                ->setArguments($arguments);
            return $this->renderLink($uriBuilder->build());
        }
    }

    private function renderLink(string $uri): string
    {
        $content = strval($this->renderChildren());
        if (trim($uri) === '') {
            return $content;
        }

        $this->tag->addAttribute('href', $uri);
        $this->tag->setContent($content);
        $this->tag->forceClosingTag(true);
        return $this->tag->render();
    }
}