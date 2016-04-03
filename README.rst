.. _start:

=======================
OAuth2 Server for TYPO3
=======================

This extension transforms your TYPO3 website into an OAuth2 server for any application you like.

It is based on the terrific `OAuth2 PHP library written by Brent Shaffer <http://bshaffer.github.io/oauth2-server-php-docs>`__.


Installation
============


1. Fetch the extension
----------------------

Either install as usual from TER or manually by cloning the Git project:

::

    $ cd /path/to/website/typo3conf/ext/
    $ git clone https://github.com/xperseguers/t3ext-csl_oauth2.git csl_oauth2
    $ cd csl_oauth2/Classes/
    $ composer install


2. Create a client
------------------

Open TYPO3 module :menuselection:`Web --> List`, click on the root page of your TYPO3 install and create a new record of
type "OAuth2 Client".


3. OAuth2 endpoint
------------------

In your client application, redirect your user to::

    https://<www.typo3website-url.tld>/?eID=csl_oauth2&<parameters>

where ``<parameters>`` include:

================  =============================================================================================
Parameter         Value
================  =============================================================================================
mode              ``authorize``
response_type     *required* The grant type requested, either ``token`` or ```code``.
client_id         *required* The app's key, found in your record's configuration.
redirect_uri      Where to redirect the user after authorization has completed. This must be the exact URI
                  registered in your record's configuration. A redirect URI is required for a ``token`` flow,
                  but optional for ``code``. If the redirect URI is omitted, the ``code`` will be presented
                  directly to the user and they will be invited to enter the information in your app.
state             Up to 500 bytes of arbitrary data that will be passed back to your redirect URI. This
                  parameters should be used to protect against cross-site request forgery (CSRF). See sections
                  `4.4.1.8 <https://tools.ietf.org/html/rfc6819#section-4.4.1.8>`_ and
                  `4.4.2.5 <https://tools.ietf.org/html/rfc6819#section-4.4.2.5>`_  of the OAuth 2.0 threat
                  model spec.
================  =============================================================================================

This will show an authorization form where the user will be able to authenticate and confirm she wants to authorize your
client application to impersonate her on the TYPO3 website.

At the end of the process, your client application will be called back (using ``redirect_uri``) with a JSON response.
