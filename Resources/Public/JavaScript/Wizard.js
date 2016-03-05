/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with DocumentHeader source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: Causal/CslOauth2/Wizard
 */
define(['jquery'], function ($) {

    $.fn.exists = function () {
        return this.length !== 0;
    }

    Wizard = {
        resetClientSecret: function (e) {
            var field = $('#' + e);

            // Will actually trigger the reset upon saving the record
            field.val(1);

            var secretField;
            if (field.closest('div.form-group').exists()) {
                // TYPO3 >= 7
                secretField = field.closest('div.form-group').prev().find('input[type="text"]');
                secretField.val('');
            } else {
                // TYPO3 6.2
                secretField = field.closest('span.t3-form-palette-field-container').prev()
                    .find('div.t3-tceforms-fieldReadOnly').find('span.nobr');
                secretField.html('&nbsp;');
            }
        }
    };

    //$(document).ready(function() {
    //    Wizard.initialize();
    //});

    return Wizard;
});
