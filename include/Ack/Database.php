<?php

/**
 * aNAcONDA kICKSTART (ack) [Database]
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
 * The \josephtingiris\Ack\Database class
 */
class Database extends \josephtingiris\Debug
{
    /*
     * public properties.
     */

    public $Config_File = null;
    public $Ini_Section = null;

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

        $this->Ack_Config = new \josephtingiris\Ack\Config($debug_level_construct);

        // Config_File
        $this->Config_File=$this->Ack_Config->configFilename($this->Config_File);

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
     * mysqli
     */
    public function databaseMySQLi($config_file=null, $config_section=null, $abort=true, $alert=true)
    {
        /*
         * begin function logic
         */

        $debug_level = 3;

        $failure_reason = __FUNCTION__ . " ERROR, ";

        if ($config_file == null | !is_readable($config_file)) {
            $config_section=$this->Ini_Section;
        }

        $mysqli_config=$this->databaseMySQLiConfig($config_file, $config_section, $abort, $alert);

        $mysqli = @new \mysqli($mysqli_config["mysql_host"],$mysqli_config["mysql_user"],$mysqli_config["mysql_pass"],$mysqli_config["mysql_db"], $mysqli_config["mysql_port"]);

        if (mysqli_connect_errno()) {
            $failure_reason="ERROR: connection to host='" . $mysqli_config["mysql_host"] . "', db = '" . $mysqli_config["mysql_db"] . "' failed";
            return $this->alertFail($failure_reason, $abort, $alert);
        }

        return $mysqli;
    }

    /**
     * returns _myslqi constructor values as an array
     */
    public function databaseMySQLiConfig($config_file=null, $config_section=null, $abort=true, $alert=true)
    {
        /*
         * begin function logic
         */

        $debug_level = 33;

        $failure_reason = __FUNCTION__ . " ERROR, ";

        if ($config_file == null | ! is_readable($config_file)) {
            $config_section=$this->Ini_Section;
        }

        $config_file=$this->Ack_Config->configFilename($this->Config_File);

        $this->debugValue(__FUNCTION__ . "() config_file",$debug_level,$config_file);
        $this->debugValue(__FUNCTION__ . "() config_section",$debug_level,$config_section);

        $config_section_values = $this->Ack_Config->configValue("",$config_section);
        if (empty($config_section_values)) {
            $failure_reason="ERROR: $config_file section $config_section is empty";
            return $this->alertFail($failure_reason, $abort, $alert);
        }

        $return_values=array();
        $required_config_values=array("mysql_host","mysql_port","mysql_db","mysql_user","mysql_pass");
        foreach ($required_config_values as $required_config_value) {
            $return_values[$required_config_value]=$this->Ack_Config->configValue("$required_config_value",$config_section);
            $this->debugValue("$required_config_value",$debug_level,$return_values[$required_config_value]);
        }

        return $return_values;

        /*
         * end function logic
         */
    }

}
