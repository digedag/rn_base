<?php

namespace Sys25\RnBase\Frontend\View;

use Sys25\RnBase\Utility\Files;

/***************************************************************
* Copyright notice
*
* (c) 2007-2019 René Nitzsche <rene@system25.de>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

abstract class AbstractView
{
    protected $pathToTemplates;

    protected $templateFile;

    /**
     * Set the path of the template directory.
     *
     * You can make use the syntax EXT:myextension/somepath.
     * It will be evaluated to the absolute path by tx_rnbase_util_Files::getFileAbsFileName()
     *
     * @param string path to the directory containing the php templates
     */
    public function setTemplatePath($pathToTemplates)
    {
        $this->pathToTemplates = $pathToTemplates;
    }

    /**
     * Set the path of the template file.
     *
     * You can make use the syntax EXT:myextension/template.php
     *
     * @param string path to the file used as templates
     */
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = $templateFile;
    }

    /**
     * Returns the template to use.
     * If TemplateFile is set, it is preferred. Otherwise
     * the filename is build from pathToTemplates, the templateName and $extension.
     *
     * @param string name of template
     * @param string file extension to use
     *
     * @return string complete filename of template
     */
    public function getTemplate($templateName, $extension = '.php', $forceAbsPath = 0)
    {
        if (strlen($this->templateFile) > 0) {
            return ($forceAbsPath) ? Files::getFileAbsFileName($this->templateFile) : $this->templateFile;
        }
        $path = $this->pathToTemplates;
        $path .= '/' == substr($path, -1, 1) ? $templateName : '/'.$templateName;
        $extLen = strlen($extension);
        $path .= substr($path, $extLen * -1, $extLen) == $extension ? '' : $extension;

        return $path;
    }
}
