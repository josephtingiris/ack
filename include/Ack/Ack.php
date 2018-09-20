<?php

/**
 * 
 * aNAcONDA kICKSTART (ack)
 * 
 * Copyright (C) 2015 Joseph Tingiris
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * @author      Current authors: Joseph Tingiris <joseph.tingiris@gmail.com>
 *                               (next author)
 *
 *              Original author: Joseph Tingiris <joseph.tingiris@gmail.com>
 *
 * @license     https://opensource.org/licenses/GPL-3.0
 *
 * @version     0.2.0
 */

namespace josephtingiris\Ack;

/**
 * The \josephtingiris\Ack class contains methods for dynamic anaconda & kickstart.
 */

class Ack extends \josephtingiris\Debug
{

    /*
     * public properties.
     */

    public $_GET_lower = array();

    /*
     * private properties.
     */

    /*
     * public functions.
     */

    public function __construct($debug_level_construct = null)
    {

        $this->debug("Class = " . __CLASS__, 20);

        // date() is used throughout; ensure a timezone is set

        if(!ini_get('date.timezone')) {
            $TZ=@date_default_timezone_get();
            if (empty($TZ)) {
                $TZ= getenv('TZ');
            }
            if (empty($TZ)) {
                date_default_timezone_set('UTC');
            } else {
                date_default_timezone_set($TZ);
            }
        }

        if (empty($this->Start_Time)) {
            $this->Start_Time = microtime(true);
        }

    }

    public function __destruct()
    {

        $this->Stop_Time = microtime(true);

        //echo "start time = ".$this->Start_Time.$this->br();
        //echo "stop time = ".$this->Stop_Time.$this->br();

    }

}
