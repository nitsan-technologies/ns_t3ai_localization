<?php


namespace NITSAN\NsT3AiLocalization\ViewHelpers;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;


class ValidatorViewHelper extends AbstractViewHelper
{

    public function render(): bool
    {
        $settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('ns_t3ai_localization');
        
        if($settings){
           if($settings['deeplApiKey'] || $settings['geminiApiKey'] || $settings['googleApiKey'] || $settings['openaiApiKey']){
                return true;
           }
        }
        return false;
        
    }

}