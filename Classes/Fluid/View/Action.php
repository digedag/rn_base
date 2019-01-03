<?php
namespace Sys25\RnBase\Fluid\View;

/***************************************************************
 * Copyright notice
 *
 * (c) René Nitzsche <rene@system25.de>
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
 * Sys25\RnBase\Fluid\View$Action
 *
 * View class for actions based on tx_rnbase_action_BaseIOC
 *
 * @package         TYPO3
 * @subpackage      rn_base
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Action extends \tx_rnbase_view_Base
{

    /**
     * @param string $templateName
     * @param \tx_rnbase_configurations $configurations
     * @return string
     * @throws \Exception
     */
    public function render($templateName, $configurations)
    {
        $extensionKey = $configurations->getExtensionKey();
        if (strlen($extensionKey) === 0) {
            throw new \Exception('The extension key yould not be resolved. Please check your typoscript configuration.');
        }

        $view = $this->initializeView(
            $templateName,
            $this->getTypoScriptConfigurationForFluid($extensionKey, $configurations),
            $configurations
        );

        $rnbaseViewData = $configurations->getViewData();

        // add variables
        $variables = $configurations->getKeyNames('variables.');
        if (!empty($variables) && is_array($variables)) {
            foreach ($variables as $variable) {
                $view->assign(
                    $variable,
                    $configurations->get(
                        'variables.' . $variable,
                        true
                    )
                );
            }
        }
        $view->assignMultiple((array) $rnbaseViewData);

        $out = $view->render();
        if (
            ($filter = $rnbaseViewData->offsetGet('filter')) &&
            is_object($filter) &&
            method_exists($filter, 'parseTemplate') &&
            ($configurationId = $this->getConfigurationId())
        ) {
            $out = $rnbaseViewData->offsetGet('filter')->parseTemplate(
                $out, $configurations->getFormatter(), $configurationId
            );
        }

        return trim($out);
    }

    /**
     * @param string $extensionKey
     * @param \Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @return array
     */
    protected function getTypoScriptConfigurationForFluid(
        $extensionKey, \Tx_Rnbase_Configuration_ProcessorInterface $configurations
    ) {
        $typoScriptConfiguration = $this->getDefaultTypoScriptConfigurationForFluid($extensionKey);

        $typoScriptConfiguration['settings'] = \tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
            $typoScriptConfiguration['settings'], (array) $configurations->get('settings.')
        );

        $typoScriptConfiguration['view'] = \tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
            $typoScriptConfiguration['view'], (array) $configurations->get('view.')
        );

        // support for old path configuration
        $oldPaths = array('templateRootPath', 'layoutRootPath', 'partialRootPath');
        foreach ($oldPaths as $oldPath) {
            if ($typoScriptConfiguration['view'][$oldPath]) {
                $typoScriptConfiguration['view'][$oldPath . 's.'][0] = $typoScriptConfiguration['view'][$oldPath];
            }
        }

        // support "templatePath" configuration like tx_rnbase_view_Base::getTemplate()
        if (strlen($configurations->get('templatePath')) !== 0) {
            $typoScriptConfiguration['view']['templateRootPaths.'][0] = $configurations->get('templatePath');
        }

        return $typoScriptConfiguration;
    }


    /**
     * @param string $extensionKey
     * @return array
     */
    protected function getDefaultTypoScriptConfigurationForFluid($extensionKey)
    {
        $typoScriptConfiguration['settings'] = array();

        $resourcesPath = 'EXT:' . $extensionKey . '/Resources/Private/';
        $typoScriptConfiguration['view']['templateRootPaths.'][0] = $resourcesPath . 'Templates/';
        $typoScriptConfiguration['view']['layoutRootPaths.'][0] = $resourcesPath . 'Layouts/';
        $typoScriptConfiguration['view']['partialRootPaths.'][0] = $resourcesPath . 'Partials/';

        return $typoScriptConfiguration;
    }

    /**
     * @param string $templateName
     * @param array $typoScriptConfigurationForFluid
     * @param \Tx_Rnbase_Configuration_ProcessorInterface $configurations
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected function initializeView(
        $templateName, $typoScriptConfigurationForFluid, \Tx_Rnbase_Configuration_ProcessorInterface $configurations
    ) {
        $view = Factory::getViewInstance($configurations, $typoScriptConfigurationForFluid);
        $view->setPartialRootPaths($typoScriptConfigurationForFluid['view']['partialRootPaths.']);
        $view->setLayoutRootPaths($typoScriptConfigurationForFluid['view']['layoutRootPaths.']);
        $view->setTemplateRootPaths($typoScriptConfigurationForFluid['view']['templateRootPaths.']);

        if ($this->templateFile) {
            $view->setTemplatePathAndFilename(\tx_rnbase_util_Files::getFileAbsFileName($this->templateFile));
        } else {
            $view->setTemplate($templateName);
        }

        if ($configurationId = $this->getConfigurationId()) {
            $view->assign('confId', $configurationId);
        }

        return $view;
    }

    /**
     * @return string
     */
    protected function getConfigurationId()
    {
        $configurationId = '';
        if (is_object($controller = $this->getController())) {
            if (method_exists($controller, 'getConfId')) {
                $configurationId = $controller->getConfId();
            }
        }

        return $configurationId;
    }
}
