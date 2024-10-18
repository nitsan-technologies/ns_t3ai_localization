<?php


use NITSAN\NsT3AiLocalization\Controller\T3AiLocalizationController;

return [
    'file_localization' => [
        'path' => '/get/file-localization',
        'target' => T3AiLocalizationController::class . '::indexAction'
    ],
    'file_validate' => [
        'path' => '/get/file-validate',
        'target' => T3AiLocalizationController::class . '::validateAction'
    ],
];
