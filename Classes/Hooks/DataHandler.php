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

namespace Causal\CslOauth2\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (version_compare(phpversion(), '7.0', '<')) {
    function random_bytes($length)
    {
        $randomBytes = GeneralUtility::generateRandomBytes($length);
        return $randomBytes;
    }
}

/**
 * Hook for \TYPO3\CMS\Core\DataHandling\DataHandler.
 *
 * @category    Hooks
 * @package     csl_oauth2
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class DataHandler
{

    /**
     * Hooks into \TYPO3\CMS\Core\DataHandling\DataHandler to pre-process the list of changed fields.
     * Required to process virtual columns.
     *
     * @param array $incomingFieldArray
     * @param string $table
     * @param integer|string $id
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     */
    public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, $table, $id, \TYPO3\CMS\Core\DataHandling\DataHandler $pObj)
    {
        if ($table !== 'tx_csloauth2_oauth_clients') {
            return;
        }

        if (!empty($incomingFieldArray['reset_client_secret'])) {
            // Actually reset the client secret
            $incomingFieldArray['client_secret'] = '';
        }

        if (!empty($incomingFieldArray['redirect_uri'])) {
            $isValidRedirectUri = GeneralUtility::isValidUrl($incomingFieldArray['redirect_uri']);
            $info = parse_url($incomingFieldArray['redirect_uri']);
            if (!in_array($info['scheme'], ['http', 'https'])) {
                // Redirect URI must be associated with a protocol
                $isValidRedirectUri = false;
            }
            if (!empty($info['query']) || strpos($info['path'], '../') !== false) {
                // Can not contain fragments of URLs or relative paths
                $isValidRedirectUri = false;
            }
            if (GeneralUtility::validIP($info['host'])) {
                $reservedIPv4Addresses = [
                    '10.0.0.0/8',       // Used for local communications within a private network as specified by RFC 1918
                    '127.0.0.0/8',      // Used for loopback addresses to the local host, as specified by RFC 990
                    '172.16.0.0/12',    // Used for local communications within a private network as specified by RFC 1918
                    '192.168.0.0/16',   // Used for local communications within a private network as specified by RFC 1918
                ];
                $reservedIPv6Addresses = [
                    'fc00::/7',         // Unique local address
                ];
                $isPrivate = false;
                if (GeneralUtility::validIPv4($info['host']) && GeneralUtility::cmpIPv4($info['host'], implode(',', $reservedIPv4Addresses))) {
                    $isPrivate = true;
                } elseif (GeneralUtility::validIPv6($info['host']) && GeneralUtility::cmpIPv6($info['host'], implode(',', $reservedIPv6Addresses))) {
                    $isPrivate = true;
                }
                if (!$isPrivate) {
                    // Can not be a public IP address
                    $isValidRedirectUri = false;
                }
            }
            if (!$isValidRedirectUri) {
                unset($incomingFieldArray['redirect_uri']);
            }
        }

        unset($incomingFieldArray['reset_client_secret']);
    }

    /**
     * Hooks into \TYPO3\CMS\Core\DataHandling\DataHandler after records have been saved to the database.
     *
     * @param string $operation
     * @param string $table
     * @param mixed $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     * @return void
     */
    public function processDatamap_afterDatabaseOperations($operation, $table, $id, array &$fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler $pObj)
    {
        if ($table !== 'tx_csloauth2_oauth_clients') {
            return;
        }

        switch ($operation) {
            case 'update':
                // Nothing to do
                break;
            case 'new':
                if (!is_numeric($id)) {
                    $id = $pObj->substNEWwithIDs[$id];
                }
                $fieldArray['client_id'] = 'this is my test';
                break;
        }

        $row = BackendUtility::getRecord($table, $id);
        $updatedData = [];
        if (empty($row['client_id'])) {
            $updatedData['client_id'] = $this->generateClientId();
        }
        if (empty($row['client_secret'])) {
            $updatedData['client_secret'] = $this->generateClientSecret();
        }
        if (!empty($updatedData)) {
            // Generate a random new client_id
            $payload = $row['name'] . '-' . $id;

            $this->getDatabaseConnection()->exec_UPDATEquery(
                $table,
                'uid=' . $id,
                $updatedData
            );
        }
    }

    /**
     * Generates a random client id.
     *
     * @return string
     */
    protected function generateClientId()
    {
        $clientId = $GLOBALS['EXEC_TIME'];
        $clientId .= '.' . $this->hmac(random_bytes(30), 'md5');
        $clientId .= '.' . GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');
        return $clientId;
    }

    /**
     * Generates a random client secret.
     *
     * @param array $row
     * @return string
     */
    protected function generateClientSecret()
    {
        $clientSecret = base64_encode($this->hmac(random_bytes(30), 'sha256', true));
        return $clientSecret;
    }

    /**
     * Generate a keyed hash value using the HMAC method using algorithm sha256.
     *
     * @param string $payload
     * @param string $algorithm
     * @param bool $rawOutput
     * @return string
     */
    protected function hmac($payload, $algorithm, $rawOutput = false)
    {
        return hash_hmac($algorithm, $payload, $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'], $rawOutput);
    }

    /**
     * Returns the database connection.
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

}
