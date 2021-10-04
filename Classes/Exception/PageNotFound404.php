<?php

namespace Sys25\RnBase\Exception;

/**
 *  Copyright notice.
 *
 *  (c) 2017-2021 René Nitzsche <rene@system25.de>
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

/**
 * Wird diese Exception innerhalb einer Action geworfen,
 * wird ein 404 Header gesetzt
 * und das robots Meta Tag NOINDEX.
 *
 * Der Exception sollte eine Nachricht übergeben werden, da diese dann
 * ausgegeben wird.
 */
class PageNotFound404 extends BaseException
{
}
