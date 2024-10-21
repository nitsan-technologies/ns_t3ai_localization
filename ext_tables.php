<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
defined('TYPO3') || die();

(static function() {

    if(ExtensionManagementUtility::isLoaded('ns_t3ai')){

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'NsT3aiLocalization',
            'web',
            'translation',
            '',
            [
                \NITSAN\NsT3AiLocalization\Controller\T3AiLocalizationController::class => 'translation,history',
            ],
            [
                'access' => 'user',
                'path' => '/nitsan/nst3ai/dashboard',
                'icon'   => 'EXT:ns_t3ai_localization/Resources/Public/Icons/user_mod_translation.svg',
                'labels' => 'LLL:EXT:ns_t3ai_localization/Resources/Private/Language/locallang.xlf:module.translation',
                'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
                'extensionName' => 'NsT3aiLocalization',
            ]
        );
      
    }

})();
