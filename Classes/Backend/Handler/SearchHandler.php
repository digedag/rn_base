<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 René Nitzsche <rene@system25.de>
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

tx_rnbase::load('tx_rnbase_mod_IModHandler');

/**
 * Abstract search handler.
 *
 * @author Michael Wagner
 */
abstract class Tx_Rnbase_Backend_Handler_SearchHandler implements tx_rnbase_mod_IModHandler
{
    /**
     * The current mod.
     *
     * @var tx_rnbase_mod_BaseModule
     */
    private $module = null;

    /**
     * The options object for the handler.
     *
     * @var Tx_Rnbase_Domain_Model_Data
     */
    private $options = [];

    /**
     * Returns the module.
     *
     * @return tx_rnbase_mod_IModule
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Returns an instance of form tool from the module.
     *
     * @return tx_rnbase_util_FormTool
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
     * @return Tx_Rnbase_Domain_Model_Data
     */
    protected function getOptions(
        $key = null
    ) {
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
        tx_rnbase::load('Tx_Rnbase_Utility_Strings');
        $modId = str_replace('\\', '_', static::class);
        $modId = Tx_Rnbase_Utility_Strings::underscoredToLowerCamelCase($modId);

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
     * @param \tx_rnbase_mod_IModule $mod
     */
    protected function init(
        tx_rnbase_mod_IModule $mod,
        array &$options = []
    ) {
        $this->module = $mod;

        $options['pid'] = $mod->getPid();

        tx_rnbase::load('Tx_Rnbase_Domain_Model_Data');
        $this->options = Tx_Rnbase_Domain_Model_Data::getInstance($options);

        $this->prepare();
    }

    /**
     * Prepares the handler.
     */
    protected function prepare()
    {
//         ($this->getOptions()
//             ->setPid($this->getModule()->getPid())
//             ->setBaseTableName('tt_content')
//         );
    }

    /**
     * Prepares the marker arrays.
     * Can be overriden by the child handler to extend the marker arrays.
     *
     * @param string $template
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
     * @param array                 $options
     *
     * @return string
     */
    // @codingStandardsIgnoreStart (interface/abstract mistake)
    public function showScreen(
        $template,
        tx_rnbase_mod_IModule $mod,
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

        return tx_rnbase_util_Templates::substituteMarkerArrayCached(
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
        array &$markerArray = null,
        array &$subpartArray = null,
        array &$wrappedSubpartArray = null
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
     * @return Tx_Rnbase_Backend_Lister_AbstractLister
     */
    protected function getLister()
    {
        $lister = tx_rnbase::makeInstance(
            $this->getListerClass(),
            $this->getModule(),
            $this->getOptions()
        );

        if (!$lister instanceof Tx_Rnbase_Backend_Lister_AbstractLister) {
            throw new Exception('The likster "' . get_class($lister) . '" has to extend "Tx_Rnbase_Backend_Lister_AbstractLister"');
        }

        return $lister;
    }

    /**
     * This method is called each time the method func is clicked,
     * to handle request data.
     *
     * @return string|null With error message
     */
    public function handleRequest(tx_rnbase_mod_IModule $mod)
    {
        return null;
    }
}
