<?php
namespace ONM\Less\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

require_once( 'ParseLessAbstract.php' );


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

class ParseLessPhp extends ParseLessAbstract {

    private $lessc;

    const VERSION_LEGACY = "legacy";
    const VERSION_CURRENT = "current";

    /**
     * Init
     * 
     */
    protected function init($conf)
    {
        if ( isset($conf['force']) ) {
            $this->force = (bool)$conf['force'];
        }

        switch ($conf['version']) {
            case self::VERSION_LEGACY:
                $version = self::VERSION_LEGACY;
                break;

            case self::VERSION_CURRENT:
            default:
                $version = self::VERSION_CURRENT;
                break;
        }


        require_once( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('onm_less') . 'Classes/Contrib/lessphp-'.$version.'/lessc.inc.php' );
        $this->lessc = new \lessc();

        if ( isset($conf['formatter']) && in_array(strtolower($conf['formatter']), array("compressed", "lessjs", "classic")) ) {
            $this->lessc->setFormatter(strtolower($conf['formatter']));
        }

        if ( isset($conf['preserveComments']) ) {
            $this->lessc->setPreserveComments((bool)$conf['preserveComments']);
        }

        if ( count($this->registerFunctions) ) {
            foreach ( $this->registerFunctions as $name => $callback) {
                if (strpos($callback, "->") !== FALSE) {
                    $callback = explode("->", $callback);
                }
                $this->lessc->registerFunction($name, $callback);
            }
        }

        if ( count($this->variables) ) {
            $this->hash = '-'.md5($this->path.serialize($this->variables)); 
            $this->lessc->setVariables($this->variables);   
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
    protected function autoCompileLESS() 
    {
        // load the cache
        $cache_fname = PATH_site . $this->cachePath.str_replace(".css", '.'.$this->cacheFileExt, $this->outFileName);
      	

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
