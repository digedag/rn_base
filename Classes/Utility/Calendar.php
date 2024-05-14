<?php

namespace Sys25\RnBase\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2021 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
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

define('CALENDAR_JANUARY', 0);
define('CALENDAR_FEBRUARY', 1);

define('CALENDAR_YEAR', 1);
define('CALENDAR_MONTH', 2);
define('CALENDAR_HOUR', 10);
define('CALENDAR_MINUTE', 12);
define('CALENDAR_SECOND', 13);
/*
 * Field number for get and set indicating the day of the month. This is a synonym for DATE. The first day of the month has value 1.
 */
define('CALENDAR_DAY_OF_MONTH', 5);
define('CALENDAR_DAY_OF_YEAR', 6);
define('CALENDAR_WEEK_OF_MONTH', 7);
define('CALENDAR_WEEK_OF_YEAR', 8);

/**
 * Simple Implementation of a calendar.
 */
class Calendar
{
    public $_time; // Die Zeit des Kalenders
    protected $_seconds;
    private $_clearHash;

    public function __construct()
    {
        $this->_time = time();
        $this->_init();
    }

    /**
     * Gets this Calendar's current time.
     */
    public function getTime()
    {
        return $this->_time;
    }

    public function setTime($timestamp)
    {
        $this->_time = $timestamp;
    }

    /**
     * Date Arithmetic function. Adds the specified (signed) amount of time to the given time
     * field, based on the calendar's rules.
     *
     * @param $field - the time field
     * @param $amount - the amount of date or time to be added to the field
     */
    public function add($field, $amount)
    {
        // Bis zur Woche können direkt die Sekunden aufaddiert werden
        if (array_key_exists($field, $this->_seconds)) {
            $this->_time = $this->_time + $this->_seconds[$field] * $amount;

            return;
        }

        $key = $this->_clearHash[$field];
        if ($key) {
            $date = getdate($this->_time);
            $date[$key] += $amount;
            $this->_time = $this->_mktime($date);
        }
    }

    /**
     * Clears the value in the given time field.
     */
    public function clear($field = 0)
    {
        if (0 == $field) {
            $this->_time = 0;

            return;
        }

        $date = getdate($this->_time);
        $date[$this->_clearHash[$field]] = 0;
        $this->_time = $this->_mktime($date);
    }

    /**
     * Erstellt den Timestamp aus dem Datumsarray.
     */
    private function _mktime($dateArr)
    {
        return mktime(
            $dateArr['hours'],
            $dateArr['minutes'],
            $dateArr['seconds'],
            $dateArr['mon'],
            $dateArr['mday'],
            $dateArr['year']
        );
    }

    private function _init()
    {
        $this->_seconds = [CALENDAR_SECOND => 1,
            CALENDAR_MINUTE => 60,
            CALENDAR_HOUR => 60 * 60,
            CALENDAR_DAY_OF_MONTH => 86400,
            CALENDAR_DAY_OF_YEAR => 86400,
            CALENDAR_WEEK_OF_MONTH => 86400 * 7,
            CALENDAR_WEEK_OF_YEAR => 86400 * 7, ];

        $this->_clearHash = [CALENDAR_SECOND => 'seconds',
            CALENDAR_MINUTE => 'minutes',
            CALENDAR_HOUR => 'hours',
            CALENDAR_DAY_OF_MONTH => 'mday',
            CALENDAR_DAY_OF_YEAR => 'mday',
            CALENDAR_MONTH => 'mon',
            CALENDAR_YEAR => 'year', ];
    }

    private function _getSeconds($field)
    {
        return $this->_seconds[$field];
    }
}
