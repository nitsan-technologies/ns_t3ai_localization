<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:ns_t3ai/Resources/Private/Language/locallang_be.xlf:NsT3Ai.tx_nst3ai_domain_model_localizationlog',
        'label' => 'extension_name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'hideTable' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'extension_name',
        'iconfile' => 'EXT:ns_t3ai/Resources/Public/Icons/tx_nst3ai_domain_model_localizationlog.gif'
    ],
    'types' => [
        '1' => ['showitem' => 'extension_name,source_file,output_file, translation_mode, status,
             --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden,'],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ]
                ],
                'default' => -1,
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_nst3ai_domain_model_localizationlog',
                'foreign_table_where' => 'AND {#tx_nst3ai_domain_model_localizationlog}.{#pid}=###CURRENT_PID### AND {#tx_nst3ai_domain_model_localizationlog}.{#sys_language_uid} IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true
                    ]
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],
        'status' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ns_t3ai/Resources/Private/Language/locallang_be.xlf:tx_nst3ai_domain_model_localizationlog.status',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
                'default' => ''
            ],
        ],
        'translation_mode' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ns_t3ai/Resources/Private/Language/locallang_be.xlf:tx_nst3ai_domain_model_localizationlog.translation_mode',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
                'default' => ''
            ],
        ],
        'extension_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ns_t3ai/Resources/Private/Language/locallang_be.xlf:tx_nst3ai_domain_model_localizationlog.extension_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
                'default' => ''
            ],
        ],
        'source_file' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ns_t3ai/Resources/Private/Language/locallang_be.xlf:tx_nst3ai_domain_model_localizationlog.source_file',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
                'default' => ''
            ],
        ],
        'output_file' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ns_t3ai/Resources/Private/Language/locallang_be.xlf:tx_nst3ai_domain_model_localizationlog.output_file',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
                'default' => ''
            ],
        ],
        'content' => [
            'exclude' => true,
            'label' => 'LLL:EXT:ns_t3ai/Resources/Private/Language/locallang_be.xlf:tx_nst3ai_domain_model_localizationlog.content',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 5,
                'readOnly' => true,
            ],
        ],
    ],
];
