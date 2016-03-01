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
