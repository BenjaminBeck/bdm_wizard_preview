<?php

$EM_CONF['bdm_wizard_preview'] = [
    'title' => 'Wizard Preview',
    'description' => '',
    'category' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99'
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'BDM\\BdmWizardPreview' => 'Classes',
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Benjamin Beck',
    'author_email' => 'beck@beck-digitale-medien.de',
    'version' => '1.0.0',
];
