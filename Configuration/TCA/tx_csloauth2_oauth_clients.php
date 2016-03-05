<?php
return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:csl_oauth2/Resources/Private/Language/locallang_db.xlf:tx_csloauth2_oauth_clients',
        'label' => 'name',
        'default_sortby' => 'name',
        'adminOnly' => 1,
        'rootLevel' => 1,
        'dividers2tabs' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'iconfile' => 'EXT:csl_oauth2/Resources/Public/Icons/tx_csloauth2_oauth_clients.png',
    ),
    'interface' => array(
        'showRecordFieldList' => 'hidden, name, client_id, client_secret, reset_client_secret, redirect_uri',
    ),
    'types' => array(
        '1' => array(
            'showitem' => '
                        hidden, name, client_id, map,
                        --palette--;LLL:EXT:csl_oauth2/Resources/Private/Language/locallang_db.xlf:palette.client_secret;client_secret,
                        --palette--;LLL:EXT:csl_oauth2/Resources/Private/Language/locallang_db.xlf:palette.restrictions;restrictions'
        ),
    ),
    'palettes' => array(
        'client_secret' => array(
            'showitem' => 'client_secret, reset_client_secret',
            'canNotCollapse' => 1,
        ),
        'restrictions' => array(
            'showitem' => 'redirect_uri',
            'canNotCollapse' => 1,
        ),
    ),
    'columns' => array(
        'hidden' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'name' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:csl_oauth2/Resources/Private/Language/locallang_db.xlf:tx_csloauth2_oauth_clients.name',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'eval' => 'required,trim',
            )
        ),
        'client_id' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:csl_oauth2/Resources/Private/Language/locallang_db.xlf:tx_csloauth2_oauth_clients.client_id',
            'config' => array(
                'type' => 'input',
                'size' => '40',
                'readOnly' => true,
            )
        ),
        'client_secret' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:csl_oauth2/Resources/Private/Language/locallang_db.xlf:tx_csloauth2_oauth_clients.client_secret',
            'config' => array(
                'type' => 'input',
                'size' => '40',
                'readOnly' => true,
            )
        ),
        'reset_client_secret' => array(
            'exclude' => 1,
            'label' => '',
            'config' => array(
                'type' => 'user',
                'userFunc' => \Causal\CslOauth2\Tca\ClientsWizard::class . '->resetClientSecret',
            ),
        ),
        'redirect_uri' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:csl_oauth2/Resources/Private/Language/locallang_db.xlf:tx_csloauth2_oauth_clients.redirect_uri',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
                'placeholder' => 'https://www.example.com/oauth2callback',
                'wizards' => array(
                    'specialWizards' => array(
                        'type' => 'userFunc',
                        'userFunc' => \Causal\CslOauth2\Tca\ClientsWizard::class . '->enhance',
                    ),
                ),
            ),
        ),
    ),
);
