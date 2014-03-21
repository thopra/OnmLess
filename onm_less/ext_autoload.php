<?php

$extensionPath = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('onm_less');

return array(	
	'lessc' => $extensionPath . 'Classes/Contrib/lessphp/lessc.inc.php',
    'ONM\\Less\\Utility\\ParseLESS' => $extensionPath . 'Classes/Utility/ParseLESS.php',
    'ONM\\Less\\XClass\\RteHtmlAreaBase' => $extensionPath . 'Classes/XClass/RteHtmlAreaBase.php',
);