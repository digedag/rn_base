<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2011 Rene Nitzsche (rene@system25.de)
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

tx_rnbase::load('tx_rnbase_mod_IModule');

/**
 */
interface tx_rnbase_mod_IModHandler
{
    /**
     * Returns a unique ID for this handler. This is used to created the subpart in template.
     * @return string
     */
    public function getSubID();
    /**
     * Returns the label for Handler in SubMenu. You can use a label-Marker.
     * @return string
     */
    public function getSubLabel();
    /**
     * This method is called each time the method func is clicked, to handle request data.
     * @param tx_rnbase_mod_IModule $mod
     */
    public function handleRequest(tx_rnbase_mod_IModule $mod);
    /**
     * Display the user interface for this handler
     * @param string $template the subpart for handler in func template
     * @param tx_rnbase_mod_IModule $mod
     * @param array $options
     */
    public function showScreen($template, tx_rnbase_mod_IModule $mod, $options);
}
