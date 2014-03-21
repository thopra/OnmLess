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

class ParseLESS {
    
    private $file;
    
    private $cacheFileExt = 'cache';
    
    private $path;
    
    private $cachePath;

    private $compiled;

    private $updated = false;

    private $outFileName;

    private $lessc;

    private $force = false;

    private $hash = '';

    /**
     * Construct
     * 
     * @param string $file                  the LESS filename
     * @param string $path                  path to the LESS file
     * @param string $cachePath             path to cache files
     * @param array $conf                   lessphp config
     * @param boolean $addHash              add hash to filename
     * @param array $variables              variables to pass to lessphp
     * @param array $registerFunctions      functions to pass to lessphp
     */
    public function __construct($file, $path, $cachePath, $conf = array(), $addHash = true, $variables = array(), $registerFunctions = array())
    {
        $this->path = $path.'/';
        $this->cachePath = $cachePath .'/';
        $this->hash = ($addHash) ? '-'.md5($this->path) : '';

        if (!is_dir( PATH_site . $this->cachePath)) {
        	GeneralUtility::mkdir_deep(PATH_site . $this->cachePath);
        }

        $this->file = $file;
        $this->lessc = new \lessc();

        if ( isset($conf['formatter']) && in_array(strtolower($conf['formatter']), array("compressed", "lessjs", "classic")) ) {
            $this->lessc->setFormatter(strtolower($conf['formatter']));
        }

        if ( isset($conf['preserveComments']) ) {
            $this->lessc->setPreserveComments((bool)$conf['preserveComments']);
        }

        if ( count($registerFunctions) ) {
            foreach ( $registerFunctions as $name => $callback) {
                if (strpos($callback, "->") !== FALSE) {
                    $callback = explode("->", $callback);
                }
                $this->lessc->registerFunction($name, $callback);
            }
        }

        if ( count($variables) ) {
            $this->hash = '-'.md5($this->path.serialize($variables)); 
            $this->lessc->setVariables($variables);   
        }

        if ( isset($conf['force']) ) {
            $this->force = (bool)$conf['force'];
        }
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
		if (file_exists($this->path.$this->file)) {

            if (!is_dir( PATH_site . $outPath )) {
                GeneralUtility::mkdir_deep($outPath);
            }

            $this->autoCompileLESS();

            if ( $this->updated || !file_exists(PATH_site . $outPath.'/'.$this->outFileName) ) {
            	file_put_contents( PATH_site . $outPath.'/'.$this->outFileName, $this->compiled);
            }

            return $outPath.'/'.$this->outFileName;

        }

        return false;
    }
    
    /**
     * autoCompileLESS
     * 
     * compile the LESS file, if any of the containig files have been updated since the last compiling process
     */
    private function autoCompileLESS() {
        // load the cache
        $filename = str_replace(".less", "", $this->file).$this->hash;
        $cache_fname = PATH_site . $this->cachePath.$filename.'.'.$this->cacheFileExt;
      	$this->outFileName = $filename . '.css';

        if (file_exists($cache_fname)) {
            $cache = unserialize(file_get_contents($cache_fname));
        } else {
            $cache = $this->path.$this->file;
        }
          
        $new_cache = $this->lessc->cachedCompile($cache, $this->force);  
        //$new_cache = \lessc::cexecute($cache);           
         
        if (!is_array($cache) || $new_cache['updated'] > $cache['updated']) {
            file_put_contents($cache_fname, serialize($new_cache));
            $this->updated = true;
            $this->compiled = $new_cache['compiled'];
            return;
        }
                    
        $this->updated = false;
        $this->compiled = $cache['compiled'];
    }
    
}
