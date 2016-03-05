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

namespace Causal\CslOauth2\Tca;

use Causal\FalDriverDropbox\Service\DropboxService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA wizard for tx_csloauth2_oauth_clients.
 *
 * @category    Tca
 * @package     csl_oauth2
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ClientsWizard
{

    protected $extKey = 'csl_oauth2';

    /**
     * Wizard to authorize/link the storage folder with Dropbox.
     *
     * @param array $PA
     * @param object $pObj
     * @return void
     */
    public function enhance(array $PA, $pObj)
    {
        $button = null;
        $csh = null;

        switch ($PA['field']) {
            case 'client_secret':
                $button = $this->generateResetClientSecretButton();
                break;
            case 'redirect_uri':
                $csh = 'tx_csloauth2_oauth_clients.redirect_uri.description';
                break;
        }

        if (!empty($button)) {
            $clearHtml = '<div style="clear:both;"></div>';
            if (strpos($PA['item'], $clearHtml) !== false) {
                $PA['item'] = str_replace($clearHtml, $button . $clearHtml, $PA['item']);
            } else {
                $PA['item'] .= $button;
            }
        }
        if (!empty($csh)) {
            $PA['item'] .= '<div style="white-space:normal;font-size:80%">' . $this->translate($csh) . '</div>';
        }
    }

    /**
     * form-group
     *
     * @param array $PA
     * @param \TYPO3\CMS\Backend\Form\FormEngine|\TYPO3\CMS\Backend\Form\Element\UserElement $pObj
     * @return string
     */
    public function resetClientSecret(array $PA, $pObj)
    {
        $this->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/CslOauth2/Wizard');
        //$jsPath = ExtensionManagementUtility::extRelPath($this->extKey) . 'Resources/Public/JavaScript/';
        //$this->getPageRenderer()->addJsFile($jsPath . 'Main.js');

        $onClick = 'Wizard.resetClientSecret(\'' . $PA['itemFormElID'] . '\');';
        $onClick .= $PA['fieldChangeFunc']['TBE_EDITOR_fieldChanged'];
        $onClick .= 'return false;';

        $html = [];
        $html[] = '<button onclick="' . $onClick . '">' . $this->translate('action.reset') . '</button>';
        $html[] = '<input type="hidden" id="' . $PA['itemFormElID'] . '" name="' . $PA['itemFormElName'] . '" value="" />';

        return implode(LF, $html);
    }

    /**
     * Returns current PageRenderer.
     *
     * @return \TYPO3\CMS\Core\Page\PageRenderer
     */
    protected function getPageRenderer()
    {
        if (version_compare(TYPO3_version, '7.4', '>=')) {
            $pageRenderer = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        } else {
            /** @var \TYPO3\CMS\Backend\Template\DocumentTemplate $documentTemplate */
            $documentTemplate = $GLOBALS['TBE_TEMPLATE'];
            $pageRenderer = $documentTemplate->getPageRenderer();
        }
        return $pageRenderer;
    }

    /**
     * Generates a reset button for the client secret.
     *
     * @return string
     */
    protected function generateResetClientSecretButton()
    {
        $button = '<button onclick="javascript:alert(1);return false;">reset</button>';
        return $button;
    }

    /**
     * Returns a translated label.
     *
     * @param string $input Label key/reference
     * @param array $arguments Optional arguments for replacing placeholders in the label
     * @param bool $hsc If set, the return value is htmlspecialchar'ed
     * @param bool $hscArguments If unset, the arguments will NOT be htmlspecialchar'ed
     * @return string
     */
    protected function translate($input, array $arguments = [], $hsc = true, $hscArguments = true)
    {
        if (!GeneralUtility::isFirstPartOfStr($id, 'LLL:EXT:')) {
            $reference = 'LLL:EXT:' . $this->extKey . '/Resources/Private/Language/locallang_db.xlf:' . $input;
        } else {
            $reference = $input;
        }
        $value = $this->getLanguageService()->sL($reference, false);
        $value = empty($value) ? $input : $value;

        if ($hsc && !$hscArguments) {
            $value = htmlspecialchars($value);
        }
        if (!empty($arguments)) {
            $value = vsprintf($value, $arguments);
        }
        if ($hsc && $hscArguments) {
            $value = htmlspecialchars($value);
        }

        return $value;
    }

    /**
     * Returns the language service.
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}