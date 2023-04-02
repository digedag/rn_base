<?php

namespace Sys25\RnBase\Backend\Module;

use Psr\Http\Message\ServerRequestInterface;
use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Frontend\Marker\BaseMarker;
use Sys25\RnBase\Frontend\Marker\FormatUtil;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Utility\Files;
use Sys25\RnBase\Utility\Network;
use Sys25\RnBase\Utility\TYPO3;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2021 Rene Nitzsche (rene@system25.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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

abstract class BaseModFunc implements IModFunc
{
    /* @var $mod IModule */
    protected $mod;

    public function init(IModule $module, $conf)
    {
        $this->mod = $module;
    }

    public function getModuleIdentifier()
    {
        return 'my_module';
    }

    /**
     * Returns the base module.
     *
     * @return IModule
     */
    public function getModule()
    {
        return $this->mod;
    }

    public function main(?ServerRequestInterface $request = null)
    {
        if (TYPO3::isTYPO121OrHigher()) {
            $modFuncFrame = \tx_rnbase::makeInstance(ModFuncFrame::class);

            return $modFuncFrame->render($this, $request);
        }

        return $this->renderOutput();
    }

    protected function renderOutput()
    {
        $out = '';
        $conf = $this->getModule()->getConfigurations();

        $file = Files::getFileAbsFileName($conf->get($this->getConfId().'template'));
        $templateCode = Network::getUrl($file);
        if (!$templateCode) {
            return $conf->getLL('msg_template_not_found').'<br />File: \''.$file.'\'<br />ConfId: \''.$this->getConfId().'template\'';
        }
        $subpart = '###'.strtoupper($this->getFuncId()).'###';
        $template = Templates::getSubpart($templateCode, $subpart);
        if (!$template) {
            return $conf->getLL('msg_subpart_not_found').': '.$subpart;
        }

        $start = microtime(true);
        $memStart = memory_get_usage();
        $out .= $this->getContent($template, $conf, $conf->getFormatter(), $this->getModule()->getFormTool());
        if (BaseMarker::containsMarker($out, 'MOD_')) {
            $markerArr = [];
            $memEnd = memory_get_usage();
            $markerArr['###MOD_PARSETIME###'] = (microtime(true) - $start);
            $markerArr['###MOD_MEMUSED###'] = ($memEnd - $memStart);
            $markerArr['###MOD_MEMSTART###'] = $memStart;
            $markerArr['###MOD_MEMEND###'] = $memEnd;
            $out = Templates::substituteMarkerArrayCached($out, $markerArr);
        }

        return $out;
    }

    /**
     * Kindklassen implementieren diese Methode um den Modulinhalt zu erzeugen.
     *
     * @param string                                     $template
     * @param ConfigurationInterface $configurations
     * @param FormatUtil $formatter
     * @param ToolBox $formTool
     *
     * @return string
     */
    abstract protected function getContent($template, &$configurations, &$formatter, $formTool);

    /**
     * Liefert die ConfId für diese ModFunc.
     *
     * @return string
     */
    public function getConfId()
    {
        return $this->getFuncId().'.';
    }

    /**
     * Jede Modulfunktion sollte über einen eigenen Schlüssel innerhalb des Moduls verfügen. Dieser
     * wird später für die Konfigruration verwendet.
     */
    abstract protected function getFuncId();
}
