<?php

/**
 * aNAcONDA kICKSTART (ack) [Log]
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
 * The \josephtingiris\Ack\Log class
 */
class Log extends \josephtingiris\Debug
{
    /*
     * public properties.
     */

    public $Log_File = null;
    public $Log_Level = null;

    /*
     * private properties.
     */

    /*
     * public functions.
     */

    /*
     * private functions.
     */

    public function __construct($debug_level_construct=null, $log_file=null)
    {
        /*
         * begin function logic
         */

        $parent_class = get_parent_class();

        if ($parent_class !== false) {
            parent::__construct($debug_level_construct); // execute parent __construct;
        }

        $this->debug("Class = " . __CLASS__, 20);

        $this->Ack_Alert = new \josephtingiris\Ack\Alert();

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

    public function logMessage($log_message, $log_level=null, $log_file=null)
    {
        /*
         * begin function logic
         */

        if (empty($log_file)) {
            if (!empty($this->Log_File)) {
                $log_file=$this->Log_File;
            } else {
                if (!empty($GLOBALS["Log_File"])) {
                    $log_file=$GLOBALS["Log_File"];
                }
            }
        }
        if (empty($log_file)) {
            $log_file="/tmp/ack.log";
        }

        if (empty($log_level)) {
            if (empty($this->Log_Level)) {
                $log_level="INFO";
            } else {
                $log_level=$this->Log_Level;
            }
        }

        $log_message_header="[" . $log_level . "]";
        $log_message="$log_message_header $log_message";

        $error_log=false;
        if (is_writable(dirname($log_file))) {

            if (!file_exists($log_file)) {
                if (!touch($log_file)) {
                    $error_log=true;
                }
            }

            if (is_writable($log_file) && !$error_log) {
                $fp=fopen($log_file,"a+");
                if ($fp) {
                    fwrite($fp,$this->logTimestamp($log_message) . "\n");
                    fclose($fp);
                } else {
                    $this->Ack_Alert->alertAbort("ERROR opening $log_file",1);
                }
            } else {
                $error_log=true;
            }
        } else {
            $error_log=true;
        }

        if ($error_log) {
            error_log($log_message);
        }

        /*
         * end function logic
         */

    }

    public function logTimestamp($timestamp_message=null,$timestamp_force=true) {

        /*
         * begin function logic
         */

        if ($timestamp_force) {
            if ($timestamp_message != null && $timestamp_message != "") {
                $timestamp_message=date("Y-m-d H:i:s") . " : " . $timestamp_message;
            } else {
                $timestamp_message=date("Y-m-d H:i:s");
            }
        }
        return $timestamp_message;

        /*
         * end function logic
         */

    }

}
