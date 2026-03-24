<?php

$EM_CONF['bdm_wizard_preview'] = [
    'title' => 'Wizard Preview',
    'description' => '',
    'category' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'cms-backend' => '12.4.0-12.4.99'
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
    'version' => '1.0.0',
];
