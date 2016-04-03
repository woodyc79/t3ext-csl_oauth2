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

================  ======================================================
Parameter         Value
================  ======================================================
mode              ``authorize``
response_type     ``code``
client_id         Client ID as seen in your record's configuration
redirect_uri      *optional* if you defined it in your record's configuration
state             arbitrary value to be used in your client application
================  ======================================================

This will show an authorization form where the user will be able to authenticate and confirm she wants to authorize your
client application to impersonate her on the TYPO3 website.

At the end of the process, your client application will be called back (using ``redirect_uri``) with a JSON response.
