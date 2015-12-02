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

class ParseLessRecess extends ParseLessAbstract {

    protected $compileFile;

    /**
     * Init
     * 
     */
    protected function init($conf)
    {
       if ( count($this->registerFunctions) ) {
            // this actually does not work without lessphp
            throw new Exception("It's currently not possible to register functions with this compiler. Remove the functions from the configuration or change the compiler to lessphp.");
            
        }

        if ( count($this->variables) ) {
            $this->hash = '-'.md5($this->path.serialize($this->variables)); 
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
       $compileFile = $this->path.$this->file;
       $modifiedFile = false;

       if ( count($this->variables) ) {
            $modifiedFile = PATH_site . $this->cachePath.str_replace(".css", '.'.$this->cacheFileExt.'.less', $this->outFileName);
            $content = file_get_contents($compileFile);
            $this->hash = '-'.md5($this->path.serialize($this->variables)); 

            foreach ($this->variables as $key => $value) {
                 $content .= "\r\n@".$key.": ".$value.";";
             } 
        }
       
        if ($modifiedFile) {
            file_put_contents($modifiedFile, $content);
            $compileFile = $modifiedFile; 
        }

        $cmd =  \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('recess');
        if (!$cmd) {
            throw new Exception("Error hile compiling LESS: recess cannot be executed.");  
        }

        $cli  = $cmd . " ". escapeshellarg($compileFile)." --compile --compress > ". escapeshellarg(PATH_site.$outPath.'/'.$this->outFileName);

        $lastLine = \TYPO3\CMS\Core\Utility\CommandUtility::exec($cli);

        return $outPath.'/'.$this->outFileName;
    }
    
}
