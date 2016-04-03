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

namespace Causal\CslOauth2\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Server {

    /**
     * @var string
     */
    protected $extKey = 'csl_oauth2';

    /**
     * @var string
     */
    protected $extPath;

    /**
     * @var \OAuth2\Server
     */
    protected $oauth2Server;

    /**
     * Server constructor.
     */
    public function __construct()
    {
        $this->extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extKey);

        $storage = new \Causal\CslOauth2\Storage\Typo3Pdo();
        $this->oauth2Server = new \OAuth2\Server($storage, [
            'allow_implicit' => true,
        ]);

        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        //$this->oauth2Server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storage));

        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $this->oauth2Server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($storage));

        // Trick when using fcgi, requires this in .htaccess:
        //
        //     RewriteEngine On
        //     RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
        //
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        }
    }

    /**
     * Handles an authorize request.
     *
     * @return void
     */
    public function handleAuthorizeRequest()
    {
        $request = \OAuth2\Request::createFromGlobals();
        $response = new \OAuth2\Response();

        // Validate the authorize request. if it is invalid, redirect back to the client with the errors in tow
        if (!$this->oauth2Server->validateAuthorizeRequest($request, $response)) {
            $response->send();
            return;
        }

        $clientId = GeneralUtility::_GET('client_id');
        $storage = $this->oauth2Server->getStorage('client');
        $clientData = $storage->getClientDetails($clientId);
        $actionParameters = GeneralUtility::_GET();
        $username = '';
        $messages = [];
        $doLogin = GeneralUtility::_POST('login');

        session_start();

        if ($doLogin) {
            $username = GeneralUtility::_POST('username');
            $password = GeneralUtility::_POST('password');
            if (!(empty($username) || empty($password))) {
                $this->doLogin($clientData['typo3_context'], $username, $password);
            }
        }

        if ($this->isAuthenticated($clientData['client_id'])) {
            $template = 'Authorize.html';
            $actionParameters['mode'] = 'authorizeFormSubmit';
        } else {
            $template = 'Login.html';
            if ($doLogin) {
                // Authentication failed
                $messages[] = [
                    'type' => 'danger',
                    'title' => $this->translate('login.error.title'),
                    'message' => $this->translate('login.error.message'),
                ];
            }
        }

        $actionUrl = GeneralUtility::getIndpEnv('SCRIPT_NAME') . '?' . http_build_query($actionParameters);

        // Generate a form to authorize the request
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setLayoutRootPaths([$this->extPath . 'Resources/Private/Layouts/']);
        $view->setPartialRootPaths([$this->extPath . 'Resources/Private/Partials/']);
        $view->setTemplatePathAndFilename($this->extPath . 'Resources/Private/Templates/' . $template);

        // Initialize localization
        $view->getRequest()->setControllerExtensionName($this->extKey);

        $view->assignMultiple([
            'siteName' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
            'client' => $clientData,
            'actionUrl' => $actionUrl,
            'username' => $username,
            'messages' => $messages,
        ]);

        $html = $view->render();
        echo $html;
    }

    /**
     * This method is called once the user decides to authorize or cancel the client
     * app's authorization request.
     *
     * @param bool $isAuthorized
     * @param int $userId [Optional] user id
     * @return void
     */
    public function handleAuthorizeFormSubmitRequest($isAuthorized, $userId = null)
    {
        $request = \OAuth2\Request::createFromGlobals();
        $response = new \OAuth2\Response();

        $this->oauth2Server->handleAuthorizeRequest($request, $response, $isAuthorized, $userId)->send();
    }

    /**
     * Handles a request for an OAuth2.0 Access Token and sends
     * the response to the client.
     */
    public function handleTokenRequest()
    {
        $request = \OAuth2\Request::createFromGlobals();
        $server->handleTokenRequest($request)->send();
    }

    /**
     * Translates a label.
     *
     * @param string $id
     * @param array $arguments
     * @return null|string
     */
    protected function translate($id, array $arguments = null)
    {
        $value = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($id, $this->extKey, $arguments);
        return $value !== null ? $value : $id;
    }

    /**
     * Performs a TYPO3 login with given credentials.
     *
     * @param string $context
     * @param string $username
     * @param string $password
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function doLogin($context, $username, $password)
    {
        // TODO: rely on TYPO3 itself to authenticate

        switch ($context) {
            case 'BE':
                $table = 'be_users';
                break;
            case 'FE':
                $table = 'fe_users';
                break;
            default:
                throw new \InvalidArgumentException('Context "' . $context . '" is not yet implemented', 1459697724);
        }

        $database = $this->getDatabaseConnection();
        $user = $database->exec_SELECTgetSingleRow(
            'uid, password',
            $table,
            'username=' . $database->fullQuoteStr($username, $table) . ' AND disable=0 AND deleted=0'
        );
        if (!empty($user)) {
            $hashedPassword = $user['password'];
            $objInstanceSaltedPW = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($hashedPassword);
            if (is_object($objInstanceSaltedPW)) {
                $validPasswd = $objInstanceSaltedPW->checkPassword($password, $hashedPassword);
                if ($validPasswd) {
                    $_SESSION['client_id'] = GeneralUtility::_GET('client_id');
                    $_SESSION['user_id'] = (int)$user['uid'];
                }
            }
        }
    }

    /**
     * @param string $clientId
     * @return bool
     */
    protected function isAuthenticated($clientId)
    {
        $isAuthenticated = false;

        if ($_SESSION['client_id'] === $clientId) {
            $isAuthenticated = (int)$_SESSION['user_id'] > 0;
        }

        return $isAuthenticated;
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}

$server = new Server();

$mode = GeneralUtility::_GET('mode');
switch ($mode) {
    case 'authorize':
        $server->handleAuthorizeRequest();
        break;
    case 'authorizeFormSubmit':
        $isAuthorized = (bool)GeneralUtility::_POST('authorize');
        $userId = 1234; // A value on your server that identifies the user
        $server->handleAuthorizeFormSubmitRequest($isAuthorized, $userId);
        break;
    case 'token':
        $server->handleTokenRequest();
        break;
    default:
        throw new \Exception('Invalid mode provided: "' . $mode . '"', 1457023604);
}
