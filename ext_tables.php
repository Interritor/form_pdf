<?php
defined('TYPO3') || die('Access denied.');

if(!class_exists('\Mpdf\Mpdf')){
    $composerAutoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('form_pdf')
        . 'Resources/Private/PHP/autoload.php';
    require_once($composerAutoloadFile);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_formpdf_domain_model_pdftemplate');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_formpdf_domain_model_htmltemplate');