<?php
namespace ONM\Less\XClass;
use ONM\Less\Utility\ParseLESS;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

class RteHtmlAreaBase extends \TYPO3\CMS\Rtehtmlarea\RteHtmlAreaBase {

	/**
	 * Get the name of the contentCSS file to use
	 *
	 * @return 	the full file name of the content css file to use
	 */
	protected function getContentCssFileName() {

		// Get stylesheet file name from Page TSConfig if any
		$fileName = trim($this->thisConfig['contentCSS']);

		if ($fileName) {
			$fileName = $this->getFullFileName($fileName);
		}

		$absolutePath = $fileName ? \TYPO3\CMS\Core\Utility\GeneralUtility::resolveBackPath(PATH_site . ($this->is_FE() || $this->isFrontendEditActive() ? '' : TYPO3_mainDir) . $fileName) : '';

		// --- onm_less hook
		$fileName = $this->getCompiledLessFile($fileName, $absolutePath);
		// --- onm_less hook end

		// Fallback to default content css file if configured file does not exists or is of zero size
		if (!$fileName || !file_exists($absolutePath) || !filesize($absolutePath)) {
			$fileName = $this->getFullFileName('EXT:' . $this->ID . '/res/contentcss/default.css');
		}

		return $fileName;
	}

	/**
	 * getCompiledLessFile
	 * Returns the relative path to the compiled css file.
	 * If the passed file is not a valid .less file, the original source will be returned.
	 *
	 * @param string $fileName relative path to the less file
	 * @return string relative path to the compiled css file
	 */
	protected function getCompiledLessFile($fileName, $absolutePath)
	{

		/*$templateParserObj = GeneralUtility::makeInstance('t3lib_tsparser_ext');
		$templateParserObj->tt_track = 0;
		$templateParserObj->init();

		$pageSelectObj = GeneralUtility::makeInstance ('t3lib_pageSelect');
		$rootLine = $pageSelectObj->getRootLine($this->getUid());
		$templateParserObj->runThroughTemplates($rootLine);
		$templateParserObj->generateConfig();
		$foreignTs = $templateParserObj->setup;

		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');
        $conf =  $configurationManager->getConfiguration( Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'OnmLess', '' );
		var_dump($conf);*/

		if (!file_exists( $absolutePath)) {
			return $fileName;
		}

		$fileInfo = pathinfo( $absolutePath );

		if ($fileInfo['extension'] != 'less') {
			return $fileName;
		}
	
		$lessFile = new ParseLESS(
				$fileInfo['basename'],
			 	$fileInfo['dirname'],
				'typo3temp/compressor'
			);

		$outFileName = $lessFile->compile('typo3temp/compressor');
		if ( $outFileName ) {
			return '../'.$outFileName;
		}

		return $fileName;
	}

}

