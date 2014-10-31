<?php

namespace ONM\Less\Hook;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Thomas Prangenberg <tpb@onm.de>, Open New Media GmbH
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * PageRendererHook
 * Hook for Class TYPO3\CMS\Core\Page\PageRenderer
 *
 * @package onm_less
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class PageRendererHook {

	protected $conf = array();

	/**
	 * renderPreProcess
	 * Execute the 'render-preProcess' hook: Analyse all included css files and replace them with the compiled less files
	 * 
	 * @param array $params
	 * @param TYPO3\CMS\Core\Page\PageRenderer $pageRenderer
	 */
	public function renderPreProcess($params, $pageRenderer)
	{
		//get Configuration
		$this->conf = $this->getConfig();

		//if disabled or static template not included, stop here
		if ( !isset($this->conf['enable']) || !(bool)$this->conf['enable'] ) {
			return;
		}

		//store configured css/less files
		$cssFiles = $params['cssFiles'];

		//clear cssFiles property
		$params['cssFiles'] = array();

		//replace the less files with compild css files
		foreach ( $cssFiles as $fileName => $fileProperties ) {

			$pageRenderer->addCssFile(
					$this->getCompiledLessFile($fileProperties['file']), 
					$fileProperties['rel'], 
					$fileProperties['media'], 
					$fileProperties['title'], 
					$fileProperties['compress'], 
					$fileProperties['forceOnTop'], 
					$fileProperties['allWrap'], 
					$fileProperties['excludeFromConcatenation']
			);

		}
	}

	/**
	 * getConfig
	 * gets the typoscript configuration of the plugin
	 *
	 * @return array typoscript config
	 */
	protected function getConfig()
	{
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');

        $config = $configurationManager->getConfiguration( Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'OnmLess', '' );
	
        $typoScriptService = $objectManager->get('TYPO3\CMS\Extbase\Service\TypoScriptService');
		$settingsAsTypoScriptArray = $typoScriptService->convertPlainArrayToTypoScriptArray($config);
		$cObj =  GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');

		if (is_array($config['variables'])) {
			foreach ( $config['variables'] as $key => $value ) {
				$config['variables'][$key] = $cObj->stdWrap($settingsAsTypoScriptArray['variables.'][$key], $settingsAsTypoScriptArray['variables.'][$key.'.']);
			}
		}
		
		if (is_array($config['registerFunction'])) {
			foreach ( $config['registerFunction'] as $key => $value ) {
				$config['registerFunction'][$key] = $cObj->stdWrap($settingsAsTypoScriptArray['variables.'][$key], $settingsAsTypoScriptArray['variables.'][$key.'.']);
			}
		}

		return $config;
	}

	/**
	 * getCompiledLessFile
	 * Returns the relative path to the compiled css file.
	 * If the passed file is not a valid .less file, the original source will be returned.
	 *
	 * @param string $fileName relative path to the less file
	 * @return string relative path to the compiled css file
	 */
	protected function getCompiledLessFile($fileName)
	{
		if (!file_exists( PATH_site . $fileName )) {
			return $fileName;
		}

		$fileInfo = pathinfo( PATH_site . $fileName );

		if ($fileInfo['extension'] != 'less') {
			return $fileName;
		}

		$parser = $this->getParser();

		$lessFile = new $parser(
				$fileInfo['basename'],
			 	$fileInfo['dirname'],
				$this->conf['path']['cache'],
				$this->conf[$this->conf['compiler']],
				(bool)$this->conf['addHash'],
				$this->conf['variables'],
				$this->conf['registerFunction']
			);

		$outFileName = $lessFile->compile( $this->conf['path']['output'] );
		if ( $outFileName ) {
			return $outFileName;
		}

		return $fileName;
	}

	private function getParser()
	{
		switch ( $this->conf['compiler'] ) {

			case 'recess':
				return '\\ONM\\Less\\Utility\\ParseLessRecess';

			case 'lessphp':
			default: 
				return '\\ONM\\Less\\Utility\\ParseLessPhp';

		}
	}

}