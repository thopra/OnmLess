<?php

namespace ONM\Less\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 *
 *
 * @package onm_less
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */

abstract class ParseLessAbstract {
    
    protected $file;
    
    protected $cacheFileExt = 'cache';
    
    protected $path;
    
    protected $cachePath;

    protected $compiled;

    protected $updated = false;

    protected $outFileName;

    protected $force = false;

    protected $hash = '';

    protected $conf = array();

    protected $variables = array();

    protected $registerFunctions = array();

    /**
     * Construct
     * 
     * @param string $file                  the LESS filename
     * @param string $path                  path to the LESS file
     * @param string $cachePath             path to cache files
     * @param array $parserConf             Compiler specific config
     * @param boolean $addHash              add hash to filename
     * @param array $variables              variables to pass to lessphp
     * @param array $registerFunctions      functions to pass to lessphp
     */
    public function __construct($file, $path, $cachePath, $parserConf = array(), $addHash = true, $variables = array(), $registerFunctions = array())
    {
        $this->path = $path.'/';
        $this->cachePath = $cachePath .'/';
        $this->hash = ($addHash) ? '-'.md5($this->path) : '';
        $this->variables = $variables;
        $this->registerFunctions = $registerFunctions;

        if (!is_dir( PATH_site . $this->cachePath)) {
        	GeneralUtility::mkdir_deep(PATH_site . $this->cachePath);
        }

        $this->file = $file;
        $this->init($parserConf);

        $filename = str_replace(".less", "", $this->file).$this->hash;
        $this->outFileName = $filename . '.css';
    }

    /**
     * compile 
     * 
     * writes the compiled CSS file to the spoecified destination
     *
     * @param string $outPath       output path (without tailing slash)
     * @return string/bool          relative path to the created CSS file or false on error
     */
    public function compile($outPath)
    {
        
    }
    

    /**
     * Initialize compiling
     * 
     */
    protected function init($conf)
    {

    }
    
}
