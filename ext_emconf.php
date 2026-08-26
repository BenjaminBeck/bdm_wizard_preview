<?php

$EM_CONF['bdm_wizard_preview'] = [
    'title' => 'Wizard Preview',
    'description' => '',
    'category' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.3.99',
            'backend' => '13.4.0-14.3.99'
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
    'author' => 'Benjamin Beck',
    'author_email' => 'beck@beck-digitale-medien.de',
    'version' => '14.3.1',
];
