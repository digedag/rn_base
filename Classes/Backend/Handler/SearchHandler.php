<?php

namespace Sys25\RnBase\Backend\Handler;

use Exception;
use Sys25\RnBase\Backend\Form\ToolBox;
use Sys25\RnBase\Backend\Lister\AbstractLister;
use Sys25\RnBase\Backend\Module\BaseModule;
use Sys25\RnBase\Backend\Module\IModHandler;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Domain\Model\DataModel;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Utility\Strings;
use tx_rnbase;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016-2021 René Nitzsche <rene@system25.de>
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

/**
 * Abstract search handler.
 *
 * @author Michael Wagner
 */
abstract class SearchHandler implements IModHandler
{
    /**
     * The current mod.
     *
     * @var BaseModule
     */
    private $module;

    /**
     * The options object for the handler.
     *
     * @var DataModel
     */
    private $options = [];

    /**
     * Returns the module.
     *
     * @return IModule
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Returns an instance of form tool from the module.
     *
     * @return ToolBox
     */
    protected function getFormTool()
    {
        return $this->getModule()->getFormTool();
    }

    /**
     * Returns the options.
     *
     * @param string $key
     *
     * @return DataModel
     */
    protected function getOptions($key = null)
    {
        if (null === $key) {
            return $this->options;
        }

        return $this->options->getProperty($key);
    }

    /**
     * The class for the lister.
     *
     * @return string
     */
    abstract protected function getListerClass();

    /**
     * Returns a unique ID for this handler.
     * This is used to created the subpart in template.
     * It is recommended the childclass extends this method!
     *
     * @return string
     */
    public function getSubModuleId()
    {
        $modId = str_replace('\\', '_', get_class($this));
        $modId = Strings::underscoredToLowerCamelCase($modId);

        return $modId;
    }

    /**
     * Returns a unique ID for this handler.
     * This is used to created the subpart in template.
     * This maps to the new getSubModuleId method.
     *
     * @return string
     */
    // @codingStandardsIgnoreStart (interface/abstract mistake)
    public function getSubID()
    {
        // @codingStandardsIgnoreEnd
        return $this->getSubModuleId();
    }

    /**
     * Returns the handler options.
     *
     * @param IModule $mod
     * @param array                  $options
     */
    protected function init(
        IModule $mod, array &$options = []
    ) {
        $this->module = $mod;

        $options['pid'] = $mod->getPid();

        $this->options = DataModel::getInstance($options);

        $this->prepare();
    }

    /**
     * Prepares the handler.
     */
    protected function prepare()
    {
    }

    /**
     * Prepares the marker arrays.
     * Can be overriden by the child handler to extend the marker arrays.
     *
     * @param string $template
     * @param array  $markerArray
     * @param array  $subpartArray
     * @param array  $wrappedSubpartArray
     *
     * @return string
     */
    protected function prepareMarkerArrays(
        $template,
        array &$markerArray,
        array &$subpartArray,
        array &$wrappedSubpartArray
    ) {
        $markerArray['###ADDITIONAL###'] = '';

        return $template;
    }

    /**
     * Display the user interface for this handler.
     *
     * @param string                $template The subpart for handler in func template
     * @param IModule $mod
     * @param array                 $options
     *
     * @return string
     */
    // @codingStandardsIgnoreStart (interface/abstract mistake)
    public function showScreen(
        $template,
        IModule $mod,
        $options
    ) {
        // @codingStandardsIgnoreEnd
        $this->init($mod, $options);

        $markerArray = $subpartArray = $wrappedSubpartArray = [];

        $this->prepareMarkerArrays(
            $template,
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray
        );

        $template = $this->showSearch(
            $template,
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray
        );

        return Templates::substituteMarkerArrayCached(
            $template,
            $markerArray
        );
    }

    /**
     * Base listing.
     *
     * @param string $template
     * @param array  $markerArray
     * @param array  $subpartArray
     * @param array  $wrappedSubpartArray
     *
     * @return string
     */
    protected function showSearch(
        $template,
        ?array &$markerArray = null,
        ?array &$subpartArray = null,
        ?array &$wrappedSubpartArray = null
    ) {
        /* @var $searcher Tx_Hpsplaner_Backend_Searcher_* */
        $searcher = $this->getLister();

        $markerArray = array_merge(
            $markerArray,
            $searcher->renderListMarkers()
        );

        if ($this->getOptions()->hasBaseTableName()) {
            $markerArray['###ADDITIONAL###'] .= $this->getFormTool()->createNewLink(
                $this->getOptions()->getBaseTableName(),
                $this->getOptions()->getPid(),
                $this->getOptions()->getNewEntryLabel() ?: '###LABEL_BUTTON_NEW_OBJECT###'
            );
        }

        return $template;
    }

    /**
     * Creates the lister instance.
     *
     * @return AbstractLister
     */
    protected function getLister()
    {
        $lister = tx_rnbase::makeInstance(
            $this->getListerClass(),
            $this->getModule(),
            $this->getOptions()
        );

        if (!$lister instanceof AbstractLister) {
            throw new Exception('The lister "'.get_class($lister).'" has to extend "Sys25\RnBase\Backend\Lister\AbstractLister"');
        }

        return $lister;
    }

    /**
     * This method is called each time the method func is clicked,
     * to handle request data.
     *
     * @param IModule $mod
     *
     * @return string|null With error message
     */
    public function handleRequest(IModule $mod)
    {
        return null;
    }
}
