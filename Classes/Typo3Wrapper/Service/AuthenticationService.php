<?php

namespace Sys25\RnBase\Typo3Wrapper\Service;

/*
 *  Copyright notice.
 *
 *  (c) 2019 René Nitzsche <rene@system25.de>
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
 */

use Sys25\RnBase\Utility\TYPO3;

if (TYPO3::isTYPO95OrHigher()) {
    class AuthenticationService extends \TYPO3\CMS\Core\Authentication\AuthenticationService
    {
    }
} else {
    /**
     * Wrapper für \TYPO3\CMS\Sv\AuthenticationService seit TYPO3 6.x.
     *
     * @author          Hannes Bochmann <rene@system25.de>
     * @license         http://www.gnu.org/licenses/lgpl.html
     *                  GNU Lesser General Public License, version 3 or later
     */
    class AuthenticationService extends \TYPO3\CMS\Sv\AuthenticationService
    {
    }
}
