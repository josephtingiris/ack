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

/*
 *
 * nothing should preceed namespace, except declarations;
 * see http://php.net/manual/en/language.namespaces.definition.php
 *
 * PSR-0: Autoloading Standard (Deprecated, DO NOT USE)
 * see http://www.php-fig.org/psr/psr-0/
 * see http://www.php-fig.org/psr/psr-4/
 *
 * PSR-1: Basic Coding Standard
 * see http://www.php-fig.org/psr/psr-1/
 *
 * PSR-2: Coding Style Guide
 * see http://www.php-fig.org/psr/psr-2/
 *
 * PSR-3: Logger Interface
 * see http://www.php-fig.org/psr/psr-3/
 * use \Monolog\Logger as Logger; # psr-3
 * use \Monolog\Handler\StreamHandler as StreamHandler; # psr-3
 *
 * PSR-4: Autoloader
 * see http://www.php-fig.org/psr/psr-4/
 * require composer autoload.php; ClassLoader implements a PSR-0, PSR-4 and classmap class loader.
 *
 */

# begin composer psr-4 autoloader

if (empty($Autoload_Enabled)) {
    $Autoload_Php="autoload.php";
    $Autoload_Enabled=false;
    $Autoload_Paths=array(dirname(__FILE__), getcwd());
    foreach ($Autoload_Paths as $Autoload_Path) {
        if ($Autoload_Enabled) break;
        while(strlen($Autoload_Path) > 0) {
            if ($Autoload_Path == ".") $Autoload_Path=getcwd();
            if ($Autoload_Path == "/") break;
            if (is_readable($Autoload_Path . "/vendor/" . $Autoload_Php) && !is_dir($Autoload_Path . "/vendor/" . $Autoload_Php)) {
                $Autoload_Enabled=true;
                require_once($Autoload_Path . "/vendor/" . $Autoload_Php);
                break;
            } else {
                $Autoload_Path=dirname($Autoload_Path);
            }
        }
    }
    if (!$Autoload_Enabled) { echo "$Autoload_Php file not found\n"; exit(1); }
    unset($Autoload_Php, $Autoload_Path);
}

# end composer psr-4 autoloader

/*
 *
 * vendor class depdendent psr-0 & psr-4 root/unnamed namespace aliases MAY be used
 * however, missing namespace aliases SHOULD NOT cause PHP Fatal (class not found) errors
 *
 */

/**
 * The \josephtingiris\Ack class contains methods for dynamic anaconda & kickstart.
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

    private $Config_Values = array();

    /*
     * public functions.
     */

    public function __construct($debug_level_construct = null)
    {

        $parent_class = get_parent_class();

        if ($parent_class !== false) {
            parent::__construct($debug_level_construct); // execute parent __construct;
        }

        $this->debug("Class = " . __CLASS__, 20);

        // Start_Time; first
        if (empty($this->Start_Time)) {
            $this->Start_Time = microtime(true);
        }

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
            $this->Environment = $this->configValue("environment");
            if (empty($this->Environment)) {
                $this->Environment = "code";
            }
        }

        // Ini_Section
        if (empty($this->Ini_Section)) {
            $this->Ini_Section = $this->Environment;
        }

        // Aaa_Dir
        if (empty($this->Aaa_Dir)) {
            $this->Aaa_Dir=$this->configValue("aaa_dir");
            if (empty($this->Aaa_Dir)) {
                $this->Aaa_Dir=$this->Dir . "/aaa";
            }
        }

        // Aaa_Cache
        if (empty($this->Aaa_Cache)) {
            $this->Aaa_Cache=$this->configValue("aaa_cache");
            if (empty($this->Aaa_Cache)) {
                $this->Aaa_Cache=$this->Aaa_Dir . "/aaa.cache";
            }
        }

        // Authorized_Ips
        if (empty($this->Authorized_Ips)) {
            $this->Authorized_Ips = $this->configValue("authorized_ips");
            if (empty($this->Authorized_Ips)) {
                $this->Authorized_Ips = array(
                    "127.0.0.0/8",
                );
            }
        }

        // Entity
        if (empty($this->Entity)) {
            $this->Entity = $this->configValue("entity");
            if (empty($this->Entity)) {
                $this->Entity = "anonymous";
            }
        }

        // Etc_Dirs
        if (empty($this->Etc_Dirs)) {
            $this->Etc_Dirs = $this->configValue("etc_dirs");
            if (empty($this->Etc_Dirs)) {
                $this->Etc_Dirs[] = "/etc/ack";
                $this->Etc_Dirs[] = "/etc";
                $this->Etc_Dirs[] = $this->Etc_Dir;
            }
        }
        $this->Etc_Dirs=array_unique($this->Etc_Dirs);

        // Group
        if (empty($this->Group)) {
            $this->Group = $this->configValue("group");
            if (empty($this->Group)) {
                $this->Group = "root";
            }
        }

        // Id
        if (empty($this->Id)) {
            $this->Id=$this->configValue("id");
            if (empty($this->Id)) {
                $this->Id= "base";
            }
        }

        // Install_Server
        if (empty($this->Install_Server)) {
            $this->Install_Server=$this->configValue("install_server");
            if (empty($this->Install_Server)) {
                $this->Install_Server= "localhost";
            }
        }

        // Install_Servers
        if (empty($this->Install_Servers)) {
            $this->Install_Servers = $this->configValue("install_servers");
            if (empty($this->Install_Servers)) {
                $this->Install_Servers[] = $this->Install_Server;
            }
        }
        $this->Install_Servers=array_unique($this->Install_Servers);

        // Log_Dir
        if (empty($this->Log_Dir)) {
            $this->Log_Dir=$this->configValue("log_dir");
            if (empty($this->Log_Dir)) {
                $this->Log_Dir=$this->Dir . "/log";
            }
        }

        // Media_Dir
        if (empty($this->Media_Dir)) {
            $this->Media_Dir=$this->configValue("media_dir");
            if (empty($this->Media_Dir)) {
                $this->Media_Dir=$this->Dir . "/media";
            }
        }
        if (!is_readable($this->Media_Dir)) {
            $this->Media_Dir="/var/tmp";
        }

        // Build_Dir
        if (empty($this->Build_Dir)) {
            $this->Build_Dir=$this->configValue("build_dir");
            if (empty($this->Build_Dir)) {
                $this->Build_Dir=$this->Media_Dir . "/build";
            }
        }
        if (!is_writable($this->Build_Dir)) {
            $this->Build_Dir="/var/tmp";
        }

        // Distribution_Dir
        if (empty($this->Distribution_Dir)) {
            $this->Distribution_Dir=$this->configValue("dirstribution_dir");
            if (empty($this->Distribution_Dir)) {
                $this->Distribution_Dir=$this->Media_Dir . "/distribution";
            }
        }
        if (!is_writable($this->Distribution_Dir)) {
            $this->Distribution_Dir="/var/tmp";
        }

        // Release_Dir
        if (empty($this->Release_Dir)) {
            $this->Release_Dir=$this->configValue("release_dir");
            if (empty($this->Release_Dir)) {
                $this->Release_Dir=$this->Media_Dir . "/release";
            }
        }
        if (!is_writable($this->Release_Dir)) {
            $this->Release_Dir="/var/tmp";
        }

        // Vm_Dir
        if (empty($this->Vm_Dir)) {
            $this->Vm_Dir=$this->configValue("vm_dir");
            if (empty($this->Vm_Dir)) {
                $this->Vm_Dir=$this->Media_Dir . "/vm";
            }
        }
        if (!is_writable($this->Vm_Dir)) {
            $this->Vm_Dir="/var/tmp";
        }

        // Network_Interfaces
        if (empty($this->Network_Interfaces)) {
            $this->Network_Interfaces = $this->configValue("network_interfaces"); // TODO; document the use of this
            if (empty($this->Network_Interfaces)) {
                $this->Network_Interfaces = $this->networkInterfaces();
            }
        }

        // Privacy
        if (empty($this->Privacy)) {
            $this->Privacy=$this->configValue("privacy");
            if (empty($this->Privacy)) {
                $this->Privacy="Private Property";
            }
        }

        // Ssh_Authorized_Key_Files
        if (empty($this->Ssh_Authorized_Key_Files)) {
            $this->Ssh_Authorized_Key_Files = $this->configValue("ssh_authorized_key_files");
            if (empty($this->Ssh_Authorized_Key_Files)) {
                $this->Ssh_Authorized_Key_Files[] = $this->Etc_Dir . "/ack-authorized_keys";
            }
        }
        $this->Ssh_Authorized_Key_Files=array_unique($this->Ssh_Authorized_Key_Files);
        usort($this->Ssh_Authorized_Key_Files, "strcasecmp");

        // Ssh_Authorized_Keys
        if (empty($this->Ssh_Authorized_Keys)) {
            $this->Ssh_Authorized_Keys = $this->configValue("ssh_authorized_keys");
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

            $this->Timezone = $this->configValue("timezone");
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
            $this->User = $this->configValue("user");
            if (empty($this->User)) {
                $this->User = "root";
            }
        }

        // Uuid
        if (empty($this->Uuid)) {
            $this->Uuid = $this->configValue("uuid");
            if (empty($this->Uuid)) {
                $this->Uuid = $this->uuid();
            }
        }

        /*
         *
         * initial client properties (some can be set via client config file, e.g. aaa/[mac])
         *
         */

        if (empty($this->Client_Anaconda)) {
            $this->Client_Anaconda = false;
        }

        if (empty($this->Client_Architecture)) {
            $this->Client_Architecture = null;
        }

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

    }

    public function __destruct()
    {

        $parent_class = get_parent_class();

        if ($parent_class !== false) {
            parent::__destruct(); // execute parent __destruct;
        }

        $this->Stop_Time = microtime(true);

    }

    /**
     * mysqli
     */
    public function _mysqli($config_file=null, $config_section=null, $abort=true, $alert=true)
    {

        $debug_level = 3;

        $failure_reason = __FUNCTION__ . " ERROR, ";

        if ($config_file == null | ! is_readable($config_file)) {
            $config_section=$this->Ini_Section;
        }

        $mysqli_config=$this->_mysqliConfig($config_file, $config_section, $abort, $alert);

        $mysqli = @new \mysqli($mysqli_config["mysql_host"],$mysqli_config["mysql_user"],$mysqli_config["mysql_pass"],$mysqli_config["mysql_db"], $mysqli_config["mysql_port"]);

        if (mysqli_connect_errno()) {
            $failure_reason="ERROR: connection to host='" . $mysqli_config["mysql_host"] . "', db = '" . $mysqli_config["mysql_db"] . "' failed";
            return $this->failure($failure_reason, $abort, $alert);
        }

        return $mysqli;
    }

    /**
     * returns _myslqi constructor values as an array
     */
    public function _mysqliConfig($config_file=null, $config_section=null, $abort=true, $alert=true)
    {
        $debug_level = 33;

        $failure_reason = __FUNCTION__ . " ERROR, ";

        if ($config_file == null | ! is_readable($config_file)) {
            $config_section=$this->Ini_Section;
        }

        $config_file=$this->configFile();

        $this->debugValue(__FUNCTION__ . "() config_file",$debug_level,$config_file);
        $this->debugValue(__FUNCTION__ . "() config_section",$debug_level,$config_section);

        $config_section_values = $this->configValue("",$config_section);
        if (empty($config_section_values)) {
            $failure_reason="ERROR: $config_file section $config_section is empty";
            return $this->failure($failure_reason, $abort, $alert);
        }

        $return_values=array();
        $required_config_values=array("mysql_host","mysql_port","mysql_db","mysql_user","mysql_pass");
        foreach ($required_config_values as $required_config_value) {
            $return_values[$required_config_value]=$this->configValue("$required_config_value",$config_section);
            $this->debugValue("$required_config_value",$debug_level,$return_values[$required_config_value]);
        }

        return $return_values;
    }

    /**
     * aborts (exits, dies) with a given message & return code
     */
    public function aborting($aborting_message=null, $return_code=1, $alert=false)
    {

        $aborting = "aborting";
        if ($alert) {
            $aborting .= " & alerting";
        }
        $aborting .= ", $aborting_message ... ($return_code)";
        $aborting=$this->timeStamp($aborting);

        echo $this->br();
        echo "$aborting" . $this->br();;
        echo $this->br();

        if ($alert) {
            $this->alertMail($subject="!! ABORT !!", $body=$aborting . "\r\n\r\n");
        }

        exit($return_code);

    }

    /**
     * fail (aborts) with a non-zero return code & sends an alert email with a given message
     */
    public function alertFail($subject=null, $body=null, $to=null, $from=null, $cc=null)
    {

        $this->debug("alertFail subject=$subject, to=$to, from=$from, cc=$cc", 0);

        $this->alertMail($subject,$body,$to,$from,$cc);

        if (empty($body)) {
            if (empty($subject)) {
                $body = __FUNCTION__;
            } else {
                $body = $subject;
            }
        }

        $this->aborting($body, 255);

    }

    /**
     * sends an alert email with a given message
     */
    public function alertMail($subject=null, $body=null, $to=null, $from=null, $cc=null)
    {

        $debug_level = 0;

        if (empty($to) && !empty($GLOBALS["Alert_To"])) {
            $to=$GLOBALS["Alert_To"];
        }

        if (empty($to) && !empty($GLOBALS["alert_to"])) {
            $to=$GLOBALS["alert_to"];
        }

        if (empty($from) && !empty($GLOBALS["Alert_From"])) {
            $from=$GLOBALS["Alert_From"];
        }

        if (empty($from) && !empty($GLOBALS["alert_from"])) {
            $from=$GLOBALS["alert_from"];
        }

        if (empty($cc) && !empty($GLOBALS["Alert_Cc"])) {
            $cc=$GLOBALS["Alert_Cc"];
        }

        if (empty($cc) && !empty($GLOBALS["Alert_CC"])) {
            $cc=$GLOBALS["Alert_CC"];
        }

        if (empty($cc) && !empty($GLOBALS["alert_cc"])) {
            $cc=$GLOBALS["alert_cc"];
        }

        $this->debug("alertMail subject=$subject, to=$to, from=$from, cc=$cc", 0);

        if (empty($to) || empty($from)) {
            $process_user = posix_getpwuid(posix_geteuid());

            if (empty($to))
                $to=$process_user['name'];

            if (empty($from)) {
                $from=$process_user['name'];
            }
        }

        if (empty($to) || empty($from)) {
            $failure_reason = "error, either to or from address are empty";
            return $this->failure($failure_reason, false, false);
        }

        $hostname=gethostname();

        if (!empty($body)) {
            $body = trim($body);
            $body .= "\r\n\r\n";
        }

        $body .= "hostname         = " . $hostname . "\r\n";
        $body .= "from             = " . $from . "\r\n";
        $body .= "to               = " . $to . "\r\n";

        if (!empty($cc)) {
            $carbon_copies = preg_split( "/(,| )/", $cc, -1, PREG_SPLIT_NO_EMPTY );
            $carbon_copies = array_unique($carbon_copies);
        }

        if (!empty($carbon_copies)) {
            foreach ($carbon_copies as $carbon_copy) {
                $carbon_copy=trim($carbon_copy);
                if (!empty($carbon_copy)) {
                    $body .= "cc               = " . $carbon_copy . "\r\n";
                }
            }
        }

        $backtrace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
        $backtrace_count=count($backtrace);
        $body .= "\r\n";
        $body .= "backtrace count  = " . $backtrace_count . "\r\n";

        # get the root caller
        if (!empty($backtrace[$backtrace_count]["file"])) {
            $backtrace_caller=$backtrace[$backtrace_count]["file"];
        } else {
            if (!empty($backtrace[$backtrace_count-1]["file"])) {
                $backtrace_caller=$backtrace[$backtrace_count-1]["file"];
            } else {
                if (!empty($backtrace[0]["file"])) {
                    $backtrace_caller=$backtrace[0]["file"];
                } else {
                    $backtrace_caller="?? unknown ??";
                }
            }
        }
        $body .= "backtrace caller = " . $backtrace_caller . "\r\n";

        $alert_subject="!! ALERT !! from $hostname:$backtrace_caller";
        if (!empty($subject)) {
            $subject=$alert_subject . " [$subject]";
        }

        $this->debug("MAIL subject=$subject, to=$to, from=$from, cc=$cc", 25);

        $additional_headers=array();
        if (!empty($carbon_copies)) {
            foreach ($carbon_copies as $carbon_copy) {
                $additional_headers[] = "Cc: $carbon_copy";
            }
        }

        $ack_email = new \josephtingiris\Ack\Email;
        $alert_mail_sent=$ack_email->email($to, $subject, $body, $additional_headers);
        if (!$alert_mail_sent) {
            // todo; slack?
            error_log("alertMail FAIL (not sent) subject=$subject, to=$to, from=$from, cc=$cc");
        }

    }

    /**
     * outputs a string with an appropriate line break
     */
    public function br($input=null)
    {

        if ($this->cli()) {
            //echo "this is being run via cli";
            $br = "$input\n";
        } else {
            //echo "this is being run via apache";
            $br = "$input<br />\n";
        }

        return $br;
    }

    /**
     * outputs a boolean if it's *not* running under a web server
     */
    public function cli()
    {

        if (!empty($_SERVER['SERVER_NAME'])) {
            return false;
        }

        return true;

    }

    /**
     * validates the presence of and returns the config file in use as a string
     */
    public function configFile($config_file=null, $abort=true)
    {

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
                $this->aborting("ERROR: $config_file file not readable",1);
            } else {
                $this->debug("ERROR: $config_file file not readable");
            }
        }

        $this->debugValue(__FUNCTION__,$debug_level,$this->Config_File);
        return $this->Config_File;

    }

    /**
     * returns the config file value(s), or null
     */
    public function configValue($config_key=null, $config_section=null, $config_file=null, $abort=false, $alert=false)
    {

        $debug_level=35;

        $config_file=$this->configFile($config_file,$abort);

        $this->debugValue("config file",$debug_level,$config_file);
        $this->debugValue("config key",$debug_level,$config_key);

        $config_message = "searching $config_file";

        if (is_readable($config_file) && is_file($config_file)) {
            $this->Config_Values = parse_ini_file($config_file,true);
        }

        // if there are no config values then abort or return nothing
        if (empty($this->Config_Values)) {
            if ($abort) {
                $this->aborting("ERROR: $config_file is empty",1);
            } else {
                $this->debug("ERROR: $config_file is empty",0);
                return null;
            }
        }

        $config_section = trim($config_section);

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
            $this->aborting("$aborting_message",3);
        } else {
            $this->debug($config_message,$debug_level);
        }

        return $config_value;

    }

    /**
     * returns curl output as an array
     */
    public function curl($url=null, $curl_options=null, $abort=false, $alert=false)
    {

        $debug_level = 25;

        $failure_reason = __FUNCTION__ . " ERROR, ";

        if (is_null($url)) {
            $failure_reason .= "url is null";
            return $this->failure($failure_reason, $abort, $alert);
        }

        $this->debugValue("curl_init", $debug_level, $url);

        ob_start();

        $curl_init = curl_init($url);

        // embedded options; $curl_options can override

        // follow redirects
        curl_setopt($curl_init, CURLOPT_FOLLOWLOCATION, true);

        // respect RFC 7231 for POST redirects
        curl_setopt($curl_init, CURLOPT_POSTREDIR, 3); // correct bitmask for 4, other (too) ?

        // allow invalid certs
        curl_setopt($curl_init, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_init, CURLOPT_SSL_VERIFYPEER, false);

        // return transfer output
        curl_setopt($curl_init, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_init, CURLOPT_SSL_VERIFYPEER, false);

        // set timeout
        curl_setopt($curl_init, CURLOPT_CONNECTTIMEOUT, 30);

        // set user agent
        curl_setopt($curl_init, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");

        // set passed curl_options
        if (!is_null($curl_options) && is_array($curl_options)) {
            foreach ($curl_options as $curl_option => $curl_option_value) {

                $this->debug("setting passed curl option $curl_option to " . var_export($curl_option_value, true), $debug_level);

                if (@curl_setopt($curl_init, $curl_option, $curl_option_value) !== true) {
                    // problem setting option
                    $failure_reason .= "curl_setop failed setting curl option '$curl_option'";
                    $this->failure($failure_reason, $abort, $alert);
                }
            }
        }

        $curl_output = curl_exec($curl_init);

        $curl_output = trim($curl_output);

        $this->debugValue("curl_output", $debug_level, $curl_output);

        $curl_error = curl_error($curl_init);

        $curl_response_code = curl_getinfo($curl_init, CURLINFO_HTTP_CODE);

        curl_close($curl_init);

        ob_end_flush();

        return array('output' => $curl_output, 'error' => $curl_error, 'response_code' => $curl_response_code);
    }

    /**
     * output a debug message & return false or abort
     */
    public function failure($failure_reason=null, $abort=false, $alert=false)
    {
        $debug_level = 3;

        if ($failure_reason == null) {
            $failure_reason = "failure";
        }

        $failure_reason = trim($failure_reason);
        $failure_reason = trim($failure_reason,",");

        if ($abort) {
            $this->aborting($failure_reason,1,$alert);
        } else {
            if ($alert) {
                $this->alertMail($failure_reason);
            } else {
                $this->debug($failure_reason, $debug_level);
            }
            return false;
        }
    }

    /**
     * returns an array of files found in $search_directory matching patters
     * TODO; make better
     */
    public function fileFind($search_directory=null, $search_pattern=null, $search_pattern_append=null, $search_recursion=true) {

        $debug_level = 10;

        if (!is_dir($search_directory)) {
            $this->debug("invalid directory $search_directory",$debug_level);
            return array();
        }

        $return_result=array();

        if ($search_pattern == "*") $search_pattern=null;

        $search_separators=array("/","@",":","+","!");
        $valid_separator=false;
        foreach ($search_separators as $search_separator) {
            if (strpos($search_pattern,$search_separator) === false) {
                $valid_separator=true;
            } else continue;
            if ($valid_separator) break;
        }
        if ($search_separator == null) {
            Warning("couldn't determine a valid search separator for $search_pattern");
            return;
        }
        $search_pattern=$search_separator . $search_pattern . $search_separator;
        if ($search_pattern_append != null) $search_pattern.=$search_pattern_append;

        $this->debugValue("search_directory",$debug_level,$search_directory);
        $this->debugValue("search_pattern",$debug_level,$search_pattern);

        $search=array($search_directory);
        while (null !== ($dir = array_pop($search))) {
            if ($dh = opendir($dir)) {
                while (false !== ($file = readdir($dh))) {
                    if($file == '.' || $file == '..') continue; # skip these, altogether
                    $path = $dir . '/' . $file;
                    $this->debugValue("path",$debug_level+10,$path);

                    if (is_dir($path)) {
                        # recursion; add the new directory to the array
                        if ($search_recursion) $search[]=$path;
                    }

                    if (preg_match($search_pattern,$path)) {
                        $return_result[]=$path;
                        $this->debug("search_pattern matched '$path'",$debug_level);
                    }
                }
            } else {
                # failed to open directory
                $this->debug("WARNING could not open directory $dir (permissions?)");
            }

            closedir($dh);
        }

        return $return_result;

    }

    /**
     * return valid interface(s), or null
     */
    function networkInterfaces($interface=null) {
        $interfaces_return=array();

        if (is_readable("/sys/class/net") && is_dir("/sys/class/net")) {
            $interfaces=array_diff(scandir("/sys/class/net"), array('..', '.'));
        }

        if (empty($interface)) {
            $interfaces_return=$interfaces;
        } else {
            // TODO; handle single or multiple *given* interfaces
        }

        return $interfaces_return;
    }

    /**
     * print public properties
     */
    function properties($bash=false) {

        /*
         * begin function logic
         */

        $debug_level = 7;

        $ack_properties=array();

        $ack_export="";

        $reflect = new \ReflectionClass($this);
        $reflections = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach($reflections as $reflection) {
            if (isset($reflection->name) && isset($reflection->class) && $reflection->class == __CLASS__) {
                $property = $reflection->name;
                if ($bash) {
                    if (is_string($this->$property)) {
                        $ack_export.= "export Ack_$property=\"" . trim($this->$property) . "\"\n";
                    } else {
                        if (is_array($this->$property) || is_object($this->$property)) {
                            $ack_export.= "export Ack_$property=(";
                            foreach ($this->$property as $ack_properties_key => $ack_properties_value) {
                                if (is_string($ack_properties_value)) {
                                    $ack_export.= "\"" . trim($ack_properties_value) . "\" ";
                                }
                            }
                            unset($ack_properties_key, $ack_properties_value);
                            $ack_export=trim($ack_export);
                            $ack_export.= ")\n";
                        }
                    }
                } else {
                    $ack_properties[$property]=$this->$property;
                }
                unset($property);
            }
        }

        if ($bash) {
            if (!empty($_SERVER["PATH"])) {
                if (!empty($this->Path)) {
                    $ack_path=$this->Path.":".$_SERVER["PATH"];
                } else {
                    $ack_path=$_SERVER["PATH"];
                }
                $ack_export.= "export PATH=\"$ack_path\"\n";
            }
            printf("%s\n", $ack_export);;
            exit(0);
        } else {
            if (!empty($_SERVER) && is_array($_SERVER)) {
                $this->debug("----------------> _SERVER key/value pairs",50);
                foreach ($_SERVER as $_SERVER_KEY => $_SERVER_VALUE) {
                    $this->debugValue("$_SERVER_KEY",50,$_SERVER_VALUE);
                }
                $this->debug("----------------< _SERVER key/value pairs",50);
            }
            ksort($ack_properties);
            foreach ($ack_properties as $ack_properties_key => $ack_properties_value) {
                $this->debugValue($ack_properties_key,9,$ack_properties_value);
            }
            unset($ack_properties_key, $ack_properties_value);
        }

        /*
         * end function logic
         */

    }

    /**
     * Display a usage message and stop
     */
    public function usage($note=null)
    {

        if (!empty($note)) {
            echo "NOTE:" . $this->br();
            echo $this->br();
            echo "$note" . $this->br();
            echo $this->br();
        }

        exit(255);

    }

    /**
     * return a RFC 4122 compliant universally unique identifier
     */
    public function uuid()
    {

        $random_string=openssl_random_pseudo_bytes(16);
        $time_low=bin2hex(substr($random_string, 0, 4));
        $time_mid=bin2hex(substr($random_string, 4, 2));
        $time_hi_and_version=bin2hex(substr($random_string, 6, 2));
        $clock_seq_hi_and_reserved=bin2hex(substr($random_string, 8, 2));
        $node=bin2hex(substr($random_string, 10, 6));
        $time_hi_and_version=hexdec($time_hi_and_version);
        $time_hi_and_version=$time_hi_and_version >> 4;
        $time_hi_and_version=$time_hi_and_version | 0x4000;
        $clock_seq_hi_and_reserved=hexdec($clock_seq_hi_and_reserved);
        $clock_seq_hi_and_reserved=$clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved=$clock_seq_hi_and_reserved | 0x8000;

        return sprintf("%08s-%04s-%04x-%04x-%012s", $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);

    }

    /*
     * private functions.
     */
}
