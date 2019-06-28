<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {
    $extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY);
    //require_once $extensionPath . '/Classes/vendor/autoload.php';
    \OAuth2\Autoloader::register();

    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Controller/Server.php';
};

$boot($_EXTKEY);
unset($boot);
