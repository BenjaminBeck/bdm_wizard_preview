<?php
defined('TYPO3') or die('Access denied.');


call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['bdm_wizard_preview'] = \BDM\BdmWizardPreview\Backend\PreRender::class. '->addRequireJsConfiguration';;
});
