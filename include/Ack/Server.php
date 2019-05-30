<?php

/**
 * aNAcONDA kICKSTART (ack) [Server]
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

require(dirname(__FILE__)) . "/Autoload.php";

/**
 * The \josephtingiris\Ack\Server class
 */
class Server extends \josephtingiris\Debug
{
    /*
     * public properties.
     */

    /*
     * private properties.
     */

    /*
     * public functions.
     */

    /*
     * private functions.
     */

    public function __construct($debug_level_construct=null)
    {
        /*
         * begin function logic
         */

        $parent_class = get_parent_class();

        if ($parent_class !== false) {
            parent::__construct($debug_level_construct); // execute parent __construct;
        }

        $this->debug("Class = " . __CLASS__, 20);

        /*
         * end function logic
         */
    }

    public function __destruct()
    {
        /*
         * begin function logic
         */

        $parent_class = get_parent_class();

        if ($parent_class !== false) {
            parent::__destruct(); // execute parent __destruct;
        }

        /*
         * end function logic
         */
    }

    /**
     * outputs a string with an appropriate line break
     */
    public function serverCR($input=null)
    {
        /*
         * begin function logic
         */

        if ($this->serverCLI()) {
            //echo "this is being run via cli";
            $br = "$input\n";
        } else {
            //echo "this is being run via apache";
            $br = "$input<br />\n";
        }

        return $br;

        /*
         * end function logic
         */
    }

    /**
     * outputs a boolean if it's *not* running under a web server
     */
    public function serverCLI()
    {
        /*
         * begin function logic
         */

        if (!empty($_SERVER['SERVER_NAME'])) {
            return false;
        }

        return true;

        /*
         * end function logic
         */
    }

    /**
     * outputs a string with a machine's environment
     */
    public function serverEnvironment()
    {
        /*
         * begin function logic
         */

        $return_environment = "code";

        // TODO; add environment logic

        return $return_environment;;

        /*
         * end function logic
         */
    }

    /**
     * Display a usage message and stop
     */
    public function serverUsage($note=null)
    {
        /*
         * begin function logic
         */

        if (!empty($note)) {
            echo "NOTE:" . $this->serverCR();
            echo $this->serverCR();
            echo "$note" . $this->serverCR();
            echo $this->serverCR();
        }

        exit(255);

        /*
         * end function logic
         */
    }

}
