<?php

namespace Sys25\RnBase\ExtBaseFluid\ViewHelper;

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

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Utility\Language;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Sys25\RnBase\ExtBaseFluid\ViewHelper\TranslateViewHelper.
 *
 * {namespace rn=Sys25\RnBase\ExtBaseFluid\ViewHelper}
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
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class TranslateViewHelper extends AbstractViewHelper
{
    /**
     * Initialize ViewHelper arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', 'The key to Translate.', false, null);
    }

    /**
     * Translates the label.
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
     * Translates the label.
     *
     * @param array                     $arguments
     * @param \Closure                  $renderChildrenClosure
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

        if (null === $key) {
            $key = trim((string) $renderChildrenClosure());
        }

        // first try to translate from the rn base controller configuration
        if (
            $renderingContext instanceof RenderingContext
            && $renderingContext->getViewHelperVariableContainer()->getView()->getConfigurations() instanceof ConfigurationInterface
        ) {
            return $renderingContext->getViewHelperVariableContainer()->getView()->getConfigurations()->getLL($key);
        }

        // otherwise translate to the typo3 language service
        return Language::sL($key);
    }
}
