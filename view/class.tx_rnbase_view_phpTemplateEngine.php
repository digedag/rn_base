<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 *  Original version:
 *  (c) 2006 Elmar Hinz
 *  Contact: elmar.hinz@team-red.net
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * Depends on: none
 *
 * @author René Nitzsche <rene@system25.de>
 */

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_view_Base');

/**
 * Base class for all views
 */
class tx_rnbase_view_phpTemplateEngine extends tx_rnbase_view_Base {

  function tx_rnbase_view_phpTemplateEngine() {
  }

  /**
   * Render the PHP template, translate and return the output as string
   *
   * The ".php" suffix is added in this function.
   * Call this function after the $pathToTemplates is set.
   * The return value is the rendered result of the template, followed by translation.
   * It is typically a (x)html string, but can be used for any other text based format.
   *
   * @param	string		name of template file without the ".php" suffix
   * @param	tx_rnbase_configurations	configuration instance
   * @return	string		typically an (x)html string
   */
  function render($view, $configurations){
    
    $link = $configurations->createLink();

    // Die ViewData bereitstellen
    $viewData =& $configurations->getViewData();

    $formatter = tx_rnbase::makeInstance('tx_rnbase_util_FormatUtil', $configurations);
//t3lib_div::debug($formatter);

    $path = $this->getTemplate($view);
    // Für den PHP Include benötigen wir den absoluten Pfad
    $path = t3lib_div::getFileAbsFileName($path);

    ob_start();
    include($path);
    $out = ob_get_clean();
    return $out;
  }


}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/view/class.tx_rnbase_view_phpTemplateEngine.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rn_base/view/class.tx_rnbase_view_phpTemplateEngine.php']);
}

