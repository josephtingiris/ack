<?php

/**
 * aNAcONDA kICKSTART (ack) [Config]
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
 * The \josephtingiris\Ack\Config class
 */
class Config extends \josephtingiris\Debug
{
    /*
     * public properties.
     */

    public $Config_File = null;
    public $Environment = null;
    public $Ini_Section = null;

    /*
     * private properties.
     */

    private $Config_Values = array();

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

        $this->Ack_Alert = new \josephtingiris\Ack\Alert;
        $this->Ack_Server = new \josephtingiris\Ack\Server;

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
     * validates the presence of and returns the config file in use as a string
     */
    public function configFilename($config_file=null, $abort=true)
    {
        /*
         * begin function logic
         */

        $debug_level=35;

        if (defined('CONFIG_FILE')) {
            $default_config_file=CONFIG_FILE;
        } else {
            $default_config_file="ack.ini";
        }

        if (empty($config_file)) {
            $config_file=$default_config_file;
        }

        if (is_readable($config_file)) {
            $this->Config_File=realpath("$config_file");
        } else {
            // search for config_file, backwards from the location of this __FILE__ (in /etc/)
            $config_found=0;
            $config_paths=array(dirname(__FILE__), getcwd());
            foreach ($config_paths as $config_path) {
                while(strlen($config_path) > 0) {
                    $this->debugValue("search config_path",$debug_level,$config_path);
                    if ($config_path == ".") $config_path=getcwd();
                    if ($config_path == "/") break;
                    if (is_readable($config_path . "/etc/" . $config_file) && !is_dir($config_path . "/etc/" . $config_file)) {
                        $config_found=1;
                        $this->Config_File=$config_path . "/etc/" . $config_file;
                        $this->debugValue("found etc config file",$debug_level,$this->Config_File);
                        break;
                    } else {
                        $config_path=dirname($config_path);
                    }
                }
                if ($config_found == 1) break;
            }
            unset($config_path);
        }

        if (!is_readable($this->Config_File) || !is_file($this->Config_File)) {
            if ($abort) {
                $this->Ack_Alert->alertAbort("ERROR: $config_file file not readable",1);
            } else {
                $this->debug("ERROR: $config_file file not readable");
            }
        }

        $this->debugValue(__FUNCTION__,$debug_level,$this->Config_File);
        return $this->Config_File;

        /*
         * end function logic
         */
    }

    /**
     * returns the config file configValue(s), or null
     */
    public function configValue($config_key=null, $config_section=null, $config_file=null, $abort=false, $alert=false)
    {
        /*
         * begin function logic
         */

        $debug_level=35;

        $config_file=$this->configFilename($config_file,$abort);

        $this->debugValue("config file",$debug_level,$config_file);
        $this->debugValue("config key",$debug_level,$config_key);

        $config_message = "searching $config_file";

        if (is_readable($config_file) && is_file($config_file)) {
            $this->Config_Values = parse_ini_file($config_file,true);
        }

        // if there are no config values then abort or return nothing
        if (empty($this->Config_Values)) {
            if ($abort) {
                $this->Ack_Alert->alertAbort("ERROR: $config_file is empty",1);
            } else {
                $this->debug("ERROR: $config_file is empty",0);
                return null;
            }
        }

        $config_section = trim($config_section);
        if (empty($config_section)) {
            $config_section = $this->Ini_Section;
        }

        $this->debugValue("config section",$debug_level,$config_section);

        // if there is no specific config key given then return all [section] values; let the caller figure out what to do
        if (empty($config_key)) {
            if (empty($this->Config_Values[$config_section])) {
                $this->debugValue(__FUNCTION__,$debug_level,$this->Config_Values);
                return $this->Config_Values;
            } else {
                if (empty($config_section)) {
                    $this->debugValue(__FUNCTION__,$debug_level,$this->Config_Values);
                    return $this->Config_Values;
                } else {
                    $this->debugValue(__FUNCTION__,$debug_level,$this->Config_Values[$config_section]);
                    return $this->Config_Values[$config_section];
                }
            }
        } else {
            $config_message .= " for '$config_key'";
        }

        #print_r($this->Config_Values);

        // create an array of sections to be searched algorithmically

        $config_sections = array();
        if ($config_section != null) {
            array_push($config_sections,$config_section); // always search the given scope first
            $sections=explode("-",$config_section);
            foreach ($sections as $section) {
                array_pop($sections); // remove the last element from the given scope & search that next
                if ($sections == null) {
                    break;
                }
                array_push($config_sections,implode($sections,"-"));
            }
            unset($sections, $section);
        }
        array_push($config_sections, null); // always search the global scope (last)

        #print_r($config_sections);

        $config_environment = $this->Environment;
        if (empty($config_environment)) {
            $config_environment = $this->Ack_Server->serverEnvironment();
        }

        $config_value=null;

        // search the config sections in order; return first key found
        foreach ($config_sections as $config_section) {
            if ($config_value == null) {
                if ($config_section == null) {
                    // search Global section
                    if (isset($this->Config_Values[$config_environment][$config_key])) {
                        $config_value = $this->Config_Values[$config_environment][$config_key];
                        $config_message .= " found in global section as '$config_environment" . "[$config_key]' with value '" . print_r($config_value,true) . "'";
                        break; // Global environment key match
                    }
                    if (isset($this->Config_Values[$config_key])) {
                        $config_value = $this->Config_Values[$config_key];
                        $config_message .= " found in global section as '$config_key' with value '" . print_r($config_value,true) . "'";
                        break; // Global key match
                    }
                } else {
                    // $config_section != null; search config_section
                    if (isset($this->Config_Values[$config_section][$config_environment][$config_key])) {
                        $config_value = $this->Config_Values[$config_section][$config_environment][$config_key];
                        $config_message .= " found in " . "[$config_section]" . " section as '$config_environment" . "[$config_key]' with value '" . print_r($config_value,true) . "'";
                        break; // config_section environment key match
                    }
                    if (isset($this->Config_Values[$config_section][$config_key])) {
                        $config_value = $this->Config_Values[$config_section][$config_key];
                        $config_message .= " found in " . "[$config_section]" . " section as '$config_key' with value '" . print_r($config_value,true) . "'";
                        break;
                    }
                }
            } else {
                // $config_value != null
                break;
            }

        }

        #$this->debug("section = $config_section, environment = $config_environment, key = $config_key, value = $config_value",$debug_level);
        #print_r($this->Config_Values);

        if ($config_value == null && $abort) {
            $aborting_message="ERROR: $config_file has no value";
            if ($config_section != null) {
                $aborting_message .= " in section [$config_section]";
            }
            $aborting_message .= " for '$config_key'";
            $this->alertAbort("$aborting_message",3);
        } else {
            $this->debug($config_message,$debug_level);
        }

        return $config_value;

        /*
         * end function logic
         */
    }

}
