<?php

$extensionPath = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('onm_less');

return array(	
//	'lessc' => $extensionPath . 'Classes/Contrib/lessphp/lessc.inc.php',
    'ONM\Less\Utility\ParseLessPhp' => $extensionPath . 'Classes/Utility/ParseLessPhp.php',
    'ONM\Less\Utility\ParseLessRecess' => $extensionPath . 'Classes/Utility/ParseLessRecess.php',
);