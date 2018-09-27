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

namespace josephtingiris;

require(dirname(__FILE__)) . "/Autoload.php";

/**
 * The \josephtingiris\Ack class
 */
class Ack extends \josephtingiris\Debug
{
    /*
     * public properties.
     */

    public $Aaa_Cache = null;
    public $Aaa_Dir = null;
    public $Authorized_Ips = array();
    public $Basename = null;
    public $Build_Dir = null;
    public $Component = null;
    public $Config_File = null;
    public $Client_Aaa = null;
    public $Client_Anaconda = null;
    public $Client_Architecture = null;
    public $Client_Authorized = null;
    public $Client_Bootproto = null;
    public $Client_Hostname = null;
    public $Client_Install_Uri = null;
    public $Client_Install_Url = null;
    public $Client_Ip = null;
    public $Client_Ip_0_Address = null;
    public $Client_Ip_0_Interface = null;
    public $Client_Log_Level = null;
    public $Client_Mac = null;
    public $Client_Mac_0_Address = null;
    public $Client_Mac_0_Interface = null;
    public $Client_Password = null;
    public $Client_Ppi = null;
    public $Client_Serial_Number = null;
    public $Client_System_Release = null;
    public $Client_Template_Kickstart = null;
    public $Client_Type = null;
    public $Dir = null;
    public $Dirname = null;
    public $Distribution_Dir = null;
    public $End_Time = null;
    public $Entity = null;
    public $Environment = null;
    public $Etc_Dir = null;
    public $Etc_Dirs = array();
    public $GET_lower = array();
    public $Group = null;
    public $Id = null;
    public $Ini_Section = null;
    public $Install_Server = null;
    public $Install_Servers = array();
    public $Label = null;
    public $Log_Dir = null;
    public $Media_Dir = null;
    public $Network_Interfaces=array();
    public $Privacy = null;
    public $Release_Dir = null;
    public $Ssh_Authorized_Key_Files = array();
    public $Ssh_Authorized_Keys = array();
    public $Start_Time = null;
    public $Timezone = null;
    public $User = null;
    public $Uuid = null;
    public $Vm_Dir = null;
    public $Zero = null;

    // these should probably be static or private
    public $Default_Ip_Address=null; // lowest metric? ipv4 or ipv6 ??
    public $Default_Ip_Cidr=null;
    public $Default_Ip_Interface=null;
    public $Default_Ip_Destination=null;
    public $Default_Ip_Gateway=null;
    public $Default_Ip_Mac=null;
    public $Default_Ip_Netmask=null;
    public $Ipv4_Default_Address=null;
    public $Ipv4_Default_Cidr=null;
    public $Ipv4_Default_Interface=null;
    public $Ipv4_Default_Destination=null;
    public $Ipv4_Default_Gateway=null;
    public $Ipv4_Default_Mac=null;
    public $Ipv4_Default_Netmask=null;
    public $Ipv6_Default_Address=null;
    public $Ipv6_Default_Cidr=null;
    public $Ipv6_Default_Interface=null;
    public $Ipv6_Default_Destination=null;
    public $Ipv6_Default_Gateway=null;
    public $Ipv6_Default_Mac=null;
    public $Ipv6_Default_Netmask=null;

    /*
     * private properties.
     */

    /*
     * public functions.
     */

    public function __construct($debug_level_construct = null)
    {
        /*
         * begin function logic
         */

        $parent_class = get_parent_class();

        if ($parent_class !== false) {
            parent::__construct($debug_level_construct); // execute parent __construct;
        }

        $this->debug("Class = " . __CLASS__, 20);

        // Start_Time; first
        if (empty($this->Start_Time)) {
            $this->Start_Time = microtime(true);
        }

        $this->Ack_Config = new \josephtingiris\Ack\Config;
        $this->Ack_Client = new \josephtingiris\Ack\Client;
        $this->Ack_Network = new \josephtingiris\Ack\Network;
        $this->Ack_Server = new \josephtingiris\Ack\Server;
        $this->Ack_Variant = new \josephtingiris\Ack\Variant;

        // Zero
        if (empty($this->Zero)) {
            if (empty($_SERVER["Zero"])) {
                if (empty($_SERVER["SCRIPT_FILENAME"])) {
                    $this->Zero=__FILE__;
                } else {
                    $this->Zero=$_SERVER["SCRIPT_FILENAME"];
                }
            } else {
                $this->Zero=$_SERVER["Zero"];
            }
        }
        $this->Zero=realpath($this->Zero);

        // Basename
        $this->Basename=basename($this->Zero);

        // Component
        if (strpos($this->Basename,"-") !== false) {
            $basename_exploded=explode("-",$this->Basename);
            if (isset($basename_exploded[0]) && $basename_exploded[0] == 'ack') {
                $basename_shift=array_shift($basename_exploded);
                $this->Component=implode('-',$basename_exploded);
            }
            unset($Basename_Exploded, $basename_shift);
        }
        if (!isset($this->Component)) {
            $this->Component=$this->Basename;
        }

        // Config_File
        $this->Config_File=$this->Ack_Config->filename($this->Config_File);

        // Dir
        if (empty($this->Dir) || !is_readable($this->Dir)) {
            $dir_path=dirname(realpath(__FILE__));
            while(strlen($dir_path) > 0) {
                if ($dir_path == ".") {
                    $dir_path=getcwd();
                }
                if ($dir_path == "/") {
                    $dir_path=dirname(realpath(__FILE__));
                    break;
                }
                if (is_dir($dir_path . "/aaa") && is_dir($dir_path . "/html") && (is_dir($dir_path . "/media") || is_dir($dir_path . "/vendor"))) {
                    break;
                }
                $dir_path=dirname($dir_path);
            }
            $this->Dir=$dir_path;
            unset($dir_path);
        }

        // Dirname
        $this->Dirname=dirname($this->Zero);

        // Label
        if (empty($this->Label)) {
            $this->Label= "ack";
        }

        // Etc_Dir
        if (empty($this->Etc_Dir)) {
            $this->Etc_Dir=$this->Dir . "/etc";
        }

        // Config_File
        if (empty($this->Config_File)) {
            if (is_readable($this->Etc_Dir . "/" . $this->Label . ".ini")) {
                $this->Config_File="$this->Etc_Dir" . "/" . $this->Label . ".ini";
            }
        }

        /*
         *
         * initial (server) properties that can be set via (server) config file (i.e. ack.ini)
         *
         */

        // Environment
        if (empty($this->Environment)) {
            $this->Environment = $this->Ack_Config->value("environment");
            if (empty($this->Environment)) {
                $this->Environment = $this->Ack_Server->environment();
            }
        }
        $this->Ack_Config->Environment = $this->Environment;

        // Ini_Section
        if (empty($this->Ini_Section)) {
            $this->Ini_Section = $this->Environment;
        }
        $this->Ack_Config->Ini_Section = $this->Ini_Section;

        // Aaa_Dir
        if (empty($this->Aaa_Dir)) {
            $this->Aaa_Dir=$this->Ack_Config->value("aaa_dir");
            if (empty($this->Aaa_Dir)) {
                $this->Aaa_Dir=$this->Dir . "/aaa";
            }
        }

        // Aaa_Cache
        if (empty($this->Aaa_Cache)) {
            $this->Aaa_Cache=$this->Ack_Config->value("aaa_cache");
            if (empty($this->Aaa_Cache)) {
                $this->Aaa_Cache=$this->Aaa_Dir . "/aaa.cache";
            }
        }

        // Authorized_Ips
        if (empty($this->Authorized_Ips)) {
            $this->Authorized_Ips = $this->Ack_Config->value("authorized_ips");
            if (empty($this->Authorized_Ips)) {
                $this->Authorized_Ips = array(
                    "127.0.0.0/8",
                );
            }
        }

        // Entity
        if (empty($this->Entity)) {
            $this->Entity = $this->Ack_Config->value("entity");
            if (empty($this->Entity)) {
                $this->Entity = "anonymous";
            }
        }

        // Etc_Dirs
        if (empty($this->Etc_Dirs)) {
            $this->Etc_Dirs = $this->Ack_Config->value("etc_dirs");
            if (empty($this->Etc_Dirs)) {
                $this->Etc_Dirs[] = "/etc/ack";
                $this->Etc_Dirs[] = "/etc";
                $this->Etc_Dirs[] = $this->Etc_Dir;
            }
        }
        $this->Etc_Dirs=array_unique($this->Etc_Dirs);

        // Group
        if (empty($this->Group)) {
            $this->Group = $this->Ack_Config->value("group");
            if (empty($this->Group)) {
                $this->Group = "root";
            }
        }

        // Id
        if (empty($this->Id)) {
            $this->Id=$this->Ack_Config->value("id");
            if (empty($this->Id)) {
                $this->Id= "base";
            }
        }

        // Install_Server
        if (empty($this->Install_Server)) {
            $this->Install_Server=$this->Ack_Config->value("install_server");
            if (empty($this->Install_Server)) {
                $this->Install_Server= "localhost";
            }
        }

        // Install_Servers
        if (empty($this->Install_Servers)) {
            $this->Install_Servers = $this->Ack_Config->value("install_servers");
            if (empty($this->Install_Servers)) {
                $this->Install_Servers[] = $this->Install_Server;
            }
        }
        $this->Install_Servers=array_unique($this->Install_Servers);

        // Log_Dir
        if (empty($this->Log_Dir)) {
            $this->Log_Dir=$this->Ack_Config->value("log_dir");
            if (empty($this->Log_Dir)) {
                $this->Log_Dir=$this->Dir . "/log";
            }
        }

        // Media_Dir
        if (empty($this->Media_Dir)) {
            $this->Media_Dir=$this->Ack_Config->value("media_dir");
            if (empty($this->Media_Dir)) {
                $this->Media_Dir=$this->Dir . "/media";
            }
        }
        if (!is_readable($this->Media_Dir)) {
            $this->Media_Dir="/var/tmp";
        }

        // Build_Dir
        if (empty($this->Build_Dir)) {
            $this->Build_Dir=$this->Ack_Config->value("build_dir");
            if (empty($this->Build_Dir)) {
                $this->Build_Dir=$this->Media_Dir . "/build";
            }
        }
        if (!is_writable($this->Build_Dir)) {
            $this->Build_Dir="/var/tmp";
        }

        // Distribution_Dir
        if (empty($this->Distribution_Dir)) {
            $this->Distribution_Dir=$this->Ack_Config->value("dirstribution_dir");
            if (empty($this->Distribution_Dir)) {
                $this->Distribution_Dir=$this->Media_Dir . "/distribution";
            }
        }
        if (!is_writable($this->Distribution_Dir)) {
            $this->Distribution_Dir="/var/tmp";
        }

        // Release_Dir
        if (empty($this->Release_Dir)) {
            $this->Release_Dir=$this->Ack_Config->value("release_dir");
            if (empty($this->Release_Dir)) {
                $this->Release_Dir=$this->Media_Dir . "/release";
            }
        }
        if (!is_writable($this->Release_Dir)) {
            $this->Release_Dir="/var/tmp";
        }

        // Vm_Dir
        if (empty($this->Vm_Dir)) {
            $this->Vm_Dir=$this->Ack_Config->value("vm_dir");
            if (empty($this->Vm_Dir)) {
                $this->Vm_Dir=$this->Media_Dir . "/vm";
            }
        }
        if (!is_writable($this->Vm_Dir)) {
            $this->Vm_Dir="/var/tmp";
        }

        // Network_Interfaces
        if (empty($this->Network_Interfaces)) {
            $this->Network_Interfaces = $this->Ack_Config->value("network_interfaces"); // TODO; document the use of this
            if (empty($this->Network_Interfaces)) {
                $this->Network_Interfaces = $this->Ack_Network->interfaces();
            }
        }

        // Privacy
        if (empty($this->Privacy)) {
            $this->Privacy=$this->Ack_Config->value("privacy");
            if (empty($this->Privacy)) {
                $this->Privacy="Private Property";
            }
        }

        // Ssh_Authorized_Key_Files
        if (empty($this->Ssh_Authorized_Key_Files)) {
            $this->Ssh_Authorized_Key_Files = $this->Ack_Config->value("ssh_authorized_key_files");
            if (empty($this->Ssh_Authorized_Key_Files)) {
                $this->Ssh_Authorized_Key_Files[] = $this->Etc_Dir . "/ack-authorized_keys";
            }
        }
        $this->Ssh_Authorized_Key_Files=array_unique($this->Ssh_Authorized_Key_Files);
        usort($this->Ssh_Authorized_Key_Files, "strcasecmp");

        // Ssh_Authorized_Keys
        if (empty($this->Ssh_Authorized_Keys)) {
            $this->Ssh_Authorized_Keys = $this->Ack_Config->value("ssh_authorized_keys");
            if (empty($this->Ssh_Authorized_Keys)) {
                $this->Ssh_Authorized_Keys = $this->Ssh_Authorized_Keys;
            }
        }
        if (!empty($this->Ssh_Authorized_Key_Files) && is_array($this->Ssh_Authorized_Key_Files)) {
            foreach ($this->Ssh_Authorized_Key_Files as $ssh_authorized_key_file) {
                if (is_readable($ssh_authorized_key_file)) {
                    foreach(file($ssh_authorized_key_file) as $ssh_authorized_key_file_line) {
                        $this->Ssh_Authorized_Keys[] = trim($ssh_authorized_key_file_line);
                    }
                    unset($ssh_authorized_key_file_line);
                }
            }
            unset($ssh_authorized_key_file);
        }
        $this->Ssh_Authorized_Keys=array_unique($this->Ssh_Authorized_Keys);
        usort($this->Ssh_Authorized_Keys, "strcasecmp");

        // Timezone; date() is used throughout; ensure a valid timezone is set
        if(!ini_get('date.timezone')) {

            $this->Timezone = $this->Ack_Config->value("timezone");
            if (empty($this->Timezone)) {
                $this->Timezone=@date_default_timezone_get();
            }
            if (empty($this->Timezone)) {
                $this->Timezone= getenv('TZ');
            }
            if (!in_array($this->Timezone, \DateTimeZone::listIdentifiers())) {
                $this->Timezone= "UTC";
            }
            $GLOBALS["TZ"]=$this->Timezone;
            date_default_timezone_set($this->Timezone);

        }

        // User
        if (empty($this->User)) {
            $this->User = $this->Ack_Config->value("user");
            if (empty($this->User)) {
                $this->User = "root";
            }
        }

        // Uuid
        if (empty($this->Uuid)) {
            $this->Uuid = $this->Ack_Config->value("uuid");
            if (empty($this->Uuid)) {
                $this->Uuid = $this->Ack_Variant->uuid();
            }
        }

        /*
         *
         * initial client properties (some can be set via client config file, e.g. aaa/[mac])
         *
         */

        // Client_Anaconda
        if (empty($this->Client_Anaconda)) {
            $this->Client_Anaconda = false;
        }

        // Client_Architecture
        if (empty($this->Client_Architecture)) {
            $this->Client_Architecture = null;
        }

        // Client_Authorized
        if (empty($this->Client_Authorized)) {
            $this->Client_Authorized = false;
        }

        if (empty($this->Client_Bootproto)) {
            $this->Client_Bootproto = "dhcp";
        }

        if (empty($this->Client_Install_Uri)) {
            $this->Client_Install_Uri = null;
        }

        if (empty($this->Client_Install_Url)) {
            $this->Client_Install_Url = null;
        }

        if (empty($this->Client_Hostname)) {
            $this->Client_Hostname = "localhost";
        }
        if (empty($this->Client_Ip)) {
            $this->Client_Ip = "127.0.0.1";
        }

        if (empty($this->Client_Ip_0_Address)) {
            $this->Client_Ip_0_Address = $this->Client_Ip;
        }

        if (empty($this->Client_Ip_0_Interface)) {
            $this->Client_Ip_0_Interface = "eth0";
        }

        if (empty($this->Client_Log_Level)) {
            $this->Client_Log_Level = "INFO";
        }

        if (empty($this->Client_Mac)) {
            $this->Client_Mac = "000000000000";
        }

        if (empty($this->Client_Aaa)) {
            $this->Client_Aaa = $this->Aaa_Dir . "/" . $this->Client_Mac;
        }

        if (empty($this->Client_Mac_0_Address)) {
            $this->Client_Mac_0_Address = $this->Client_Mac;
        }

        if (empty($this->Client_Mac_0_Interface)) {
            $this->Client_Mac_0_Interface = $this->Client_Ip_0_Interface;
        }

        if (empty($this->Client_Password)) {
            $this->Client_Password = $this->Client_Mac;
        }

        if (empty($this->Client_Ppi)) {
            $this->Client_Ppi = null;
        }

        if (empty($this->Client_Serial_Number)) {
            $this->Client_Serial_Number = null;
        }

        if (empty($this->Client_System_Release)) {
            $this->Client_System_Release = null;
        }

        if (empty($this->Client_Template_Kickstart)) {
            $this->Client_Template_Kickstart = null;
        }

        if (empty($this->Client_Type)) {
            $this->Client_Type = null;
        }

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

        $this->Stop_Time = microtime(true);

        /*
         * end function logic
         */
    }

    /*
     * private functions.
     */
}
