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

    protected $oauth2Server;

    /**
     * Server constructor.
     */
    public function __construct()
    {
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
     * @param bool $isAuthorized
     * @param int $userId [Optional] user id
     * @return void
     */
    public function handleAuthorizeRequest($isAuthorized, $userId = null)
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

}

$server = new Server();

$mode = GeneralUtility::_GP('mode');
switch ($mode) {
    case 'authorize':
        $isAuthorized = true;   // false if user finally denied access
        $userId = 1234; // A value on your server that identifies the user
        $server->handleAuthorizeRequest($isAuthorized, $userId);
        break;
    case 'token':
        $server->handleTokenRequest();
        break;
    default:
        throw new \Exception('Invalid mode provided: "' . $mode . '"', 1457023604);
}
