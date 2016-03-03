<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\CslOauth2\Storage;

/**
 * Simple PDO storage for TYPO3.
 *
 * @category    Storage
 * @package     csl_oauth2
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Typo3Pdo extends \OAuth2\Storage\Pdo {

    /**
     * Typo3Pdo constructor.
     */
    public function __construct()
    {
        $dsn = 'mysql:dbname=' . TYPO3_db . ';';
        if (isset($GLOBALS['TYPO3_CONF_VARS']['DB']['socket'])) {
            $dsn .= 'unix_socket=' . $GLOBALS['TYPO3_CONF_VARS']['DB']['socket'];
        } else {
            $dsn .= 'host=' . TYPO3_db_host;
            if (isset($GLOBALS['TYPO3_CONF_VARS']['DB']['port'])) {
                $dsn .= ';port=' . (int)$GLOBALS['TYPO3_CONF_VARS']['DB']['port'];
            }
        }

        parent::__construct([
            'dsn' => $dsn,
            'username' => TYPO3_db_username,
            'password' => TYPO3_db_password,
        ], [
            'client_table' => 'tx_csloauth2_oauth_clients',
            'access_token_table' => 'tx_csloauth2_oauth_access_tokens',
            'refresh_token_table' => 'tx_csloauth2_oauth_refresh_tokens',
            'code_table' => 'tx_csloauth2_oauth_authorization_codes',
            'user_table' => 'tx_csloauth2_oauth_users',
            'jwt_table'  => 'tx_csloauth2_oauth_jwt',
            //'jti_table'  => 'oauth_jti',
            'scope_table'  => 'tx_csloauth2_oauth_scopes',
            //'public_key_table'  => 'oauth_public_keys',
        ]);
    }
        
}
