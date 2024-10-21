<?php
return [
    'dependencies' => ['backend'],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@nitsan/ns-t3ai-localization/translation-wizard-manipulation.js' => 'EXT:ns_t3ai_localization/Resources/Public/JavaScript/translation-wizard-manipulation.js',
        '@nitsan/ns-t3ai-localization/global-action-button.js' => 'EXT:ns_t3ai_localization/Resources/Public/JavaScript/global-action-button.js',
    ]
];
