<?php


use NITSAN\NsT3AiLocalization\Controller\T3AiTranslationController;
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
    'manual_translation' => [
        'path' => '/get/file-translation',
        'target' => T3AiTranslationController::class . '::indexAction'
    ],
    'file_read' => [
        'path' => '/get/file-read',
        'target' => T3AiTranslationController::class . '::indexAction'
    ],
    'write_manual_translation' => [
        'path' => '/get/write-file-translation',
        'target' => T3AiTranslationController::class . '::updateFileAction'
    ],
];
