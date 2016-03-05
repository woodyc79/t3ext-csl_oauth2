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
            $incomingFieldArray['client_secret'] = '';
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
