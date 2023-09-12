<?php

namespace Sys25\RnBase\ExtBaseFluid\Controller;

use Sys25\RnBase\Utility\TYPO3;

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
 * When using this trait you can configure your cache tags like this through TypoScript
 * for the actions of a extbase controller:
 * plugin.ty_my_ext.settings.cacheTags.$lowerCamelCaseControllerNameOmittingController.$lowerCaseActionNameOmittingAction.0 = my_cache_tag
 * example for tx_news: plugin.tx_news.settings.cacheTags.news.detail.0 = my_cache_tag_for_the_news_detail_view.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
trait CacheTagsTrait
{
    public function callActionMethod()
    {
        $this->handleCacheTags();
        parent::callActionMethod();
    }

    protected function handleCacheTags()
    {
        if ($cacheTags = $this->settings['cacheTags'][strtolower($this->request->getControllerName())][$this->request->getControllerActionName()]) {
            TYPO3::getTSFE()->addCacheTags($cacheTags);
        }
    }
}
