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

/**
 * The \josephtingiris\Ack class contains methods for dynamic anaconda & kickstart.
 */

class Ack extends \josephtingiris\Debug
{

    /*
     * public properties.
     */

    public $_GET_lower = array();
    public $Config_File = null;
    public $Default_Ini_Section = null;
    public $Environment = null;

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

        if (empty($this->Start_Time)) {
            $this->Start_Time = microtime(true);
        }

        if (empty($this->Environment)) {
            $this->Environment = $this->configValue("environment");
        }

        if (empty($this->Default_Ini_Section)) {
            $this->Default_Ini_Section = $this->Environment;
        }

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
     * mysqli constructor
     */
    public function _mysqli($config_file=null, $config_section=null, $abort=true, $alert=true)
    {

        $debug_level = 3;

        $failure_reason = __FUNCTION__ . " ERROR, ";

        if ($config_file == null | ! is_readable($config_file)) {
            $config_section=$this->Default_Ini_Section;
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
            $config_section=$this->Default_Ini_Section;
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
