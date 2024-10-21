<?php


use NITSAN\NsT3AiLocalization\Controller\T3AiLocalizationController;

return [
    'file_localization' => [
        'path' => '/get/file-localization',
        'target' => T3AiLocalizationController::class . '::indexAction'
    ],
    'file_translate' => [
        'path' => '/get/file-validate',
        'target' => T3AiLocalizationController::class . '::translateAction'
    ],
    'file_write' => [
        'path' => '/get/file-write',
        'target' => T3AiLocalizationController::class . '::writeXlfAction'
    ],
];
