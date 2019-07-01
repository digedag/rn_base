<?php
namespace Sys25\RnBase\Fluid\ViewHelper;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * Sys25\RnBase\Fluid\ViewHelper\TranslateViewHelper
 *
 * {namespace rn=Sys25\RnBase\Fluid\ViewHelper}
 *
 * Usage to render label.description
 *     Inline:
 *         {varWithLabelDescriptionKey -> rn:translate()}
 *         {f:format.raw(value: 'label.description') -> rn:translate()}
 *     Tag:
 *         <rn:translate key="label.description" />
 *     Childs:
 *         <rn:translate>label.description</rn:translate>
 *
 * @package TYPO3
 * @subpackage rn_base
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class TranslateViewHelper extends AbstractViewHelper
{
    /**
     * Initialize ViewHelper arguments
     *
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', 'The key to Translate.', false, null);
    }

    /**
     * Translates the label
     *
     * @return string
     *
     * @throws \Exception
     */
    public function render()
    {
        return static::renderStatic(
            [
                'key' => $this->arguments['key'],
            ],
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * Translates the label
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $key = $arguments['key'];

        if ($key === null) {
            $key = trim((string) $renderChildrenClosure());
        }

        // first try to translate from the rn base controller configuration
        if ((
            $renderingContext instanceof RenderingContext &&
            $renderingContext->getViewHelperVariableContainer()->getView()->getConfigurations() instanceof \Tx_Rnbase_Configuration_Processor
        )) {
            return $renderingContext->getViewHelperVariableContainer()->getView()->getConfigurations()->getLL($key);
        }

        // otherwise translate to the typo3 language service
        return \tx_rnbase_util_Lang::sL($key);
    }
}
