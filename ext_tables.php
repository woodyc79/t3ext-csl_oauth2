<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    // Register hooks for \TYPO3\CMS\Core\DataHandling\DataHandler
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \Causal\CslOauth2\Hooks\DataHandler::class;
};

$boot($_EXTKEY);
unset($boot);
