<?php

use NITSAN\NsT3AiLocalization\Controller\T3AiLocalizationController;

return [
    'translation_history' => [
        'path' => '/history',
        'target' => T3AiLocalizationController::class . '::historyAction',
    ],
];
