<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Frontend
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] = 'EXT:onm_less/Classes/Hook/PageRendererHook.php:ONM\Less\Hook\PageRendererHook->renderPreProcess';

// Backend (htmlarea RTE)
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Rtehtmlarea\\RteHtmlAreaBase'] = array(
    'className' => 'ONM\\Less\\XClass\\RteHtmlAreaBase',
);
?>