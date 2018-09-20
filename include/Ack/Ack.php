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
    public $Ack_Ini_Section = "main";

    /*
     * private properties.
     */

    private $Config_File = null;
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

        //echo "start time = ".$this->Start_Time.$this->br();
        //echo "stop time = ".$this->Stop_Time.$this->br();

    }

    /**
     * mysqli constructor
     */
    public function _mysqli($config_file=null, $config_section=null, $abort=true, $alert=true)
    {

        $debug_level = 3;

        $failure_reason = __FUNCTION__ . " ERROR, ";

        if ($config_file == null | ! is_readable($config_file)) {
            $config_section=$this->Ack_Ini_Section;
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
            $config_section=$this->Ack_Ini_Section;
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

        $this->stop($return_code);

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

        $alert_mail_sent=$this->email($to, $subject, $body, $additional_headers);
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
            $default_config_file="base.ini";
        }

        if (empty($config_file)) {
            $config_file=$default_config_file;
        }

        if (is_readable($config_file)) {
            $this->Config_File="$config_file";
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
    public function configValue($config_key=null, $config_section=null, $config_file=null, $abort=true, $alert=true)
    {

        $debug_level=35;

        $config_file=$this->configFile($config_file,$abort);

        $account = $this->account();
        $this->debugValue("account",$debug_level,$account);

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

        if (!preg_match("/-" . $account . "$/i", $config_section)) {
            // if there is no config section then append the account value
            $config_section .= "-" . $account;
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

        $config_environment = $this->environment();

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
     * a simple (to use), trustworthy email method
     *
     * @param {string|array|object} 'to' email address(es)
     * @param {string|array|object} 'subject' subject; arrays or objects will be merged into a string
     * @param {string|array|object} 'message' message body (parts); if a body (part) contains html tags then content-type html is set
     * @param {string|array|object} 'additional_headers' & custom options, e.g. cc, bcc, reply-to, attachment(s), etc.
     * @param {string|array|object} 'additional_parameters' passed to php mail()
     * @param {boolean} alert on failure
     * @param {boolean} abort on failure
     * @return {boolean} true on success, false on failure
     */
    public function email($to=null, $subject=null, $message=null, $additional_headers=null, $additional_parameters=null, $alert=false, $abort=false)
    {

        $debug_level = 33;

        $this->debug(__FUNCTION__ . "(" . print_r(func_get_args(),true) . ")",$debug_level);

        // note; rfc2822 message header keys are case INSENSITIVE;
        // note; rfc2822 allows
        // only 1 Sender: address
        // multiple From: (mailbox-list)
        // unlimited Comments:

        // set the rfc2822_headers array that will be popoluated and reused
        $rfc2822_headers=array();

        $rfc2822_mailer_version="0.0.1";
        $rfc2822_mailer=preg_replace("/\\\/","-",get_class($this)) . " " . __FUNCTION__ . " [version $rfc2822_mailer_version]";

        // set the rfc2822_domain & rfc2822_user; these will be reused
        if ($this->cli()) {
            // not apache, from is empty so set from to process user
            $rfc2822_domain = gethostname();

            $process_user = posix_getpwuid(posix_geteuid());
            $rfc2822_user=$process_user['name'];

            if (is_null($rfc2822_user) || $rfc2822_user == '') {
                $rfc2822_user="nobody";
            }
        } else {
            if (!empty($_SERVER['HTTP_HOST'])) {
                $rfc2822_domain = $_SERVER['HTTP_HOST'];
                $rfc2822_user = "noreply";
            }
        }

        // create recipients array
        $recipients=array();
        if (!is_array($to) && !is_object($to)) {
            if (!is_null($to) && $to != '') {
                $to = trim((string)$to);
                $recipients = explode(",", $to);
            }
        } else {
            if (is_array($to)) {
                $recipients = $to;
            } else {
                if (is_object($to)) {
                    $recipients = (array)$to;
                }
            }
        }
        $recipients = array_map('trim', $recipients);
        $recipients = array_unique($recipients);
        $this->debugValue("recipients",$debug_level,$recipients);

        // check recipients array
        if (empty($recipients)) {
            $failure_reason = "failure, recipients are empty";
            return $this->failure($failure_reason, false, $abort);
        } else {
            // $to is used again, as an array, later ...
            $to = $recipients;
        }

        // create subject string
        if (is_array($subject) || is_object($subject)) {
            if (is_object($subject)) {
                $subject = (array)$subject;
            }
            $subject = implode($subject, " ");
        }
        $subject = trim($subject);

        // what if someone passes Subject: ? Which one wins ??
        if (!preg_grep("/^Subject:/i",$rfc2822_headers)) {
            $rfc2822_headers[] = "Subject: $subject";
        }

        // debug subject string
        $this->debugValue("subject",$debug_level,$subject);

        // create 'custom' options array
        $options=array();
        if (!is_array($additional_headers) && !is_object($additional_headers)) {
            if (!is_null($additional_headers) && $additional_headers != '') {
                $additional_headers = trim((string)$additional_headers);
                $options = explode(",", $additional_headers);
            }
        } else {
            if (is_array($additional_headers)) {
                $options = $additional_headers;
            } else {
                if (is_object($additional_headers)) {
                    $options = (array)$additional_headers;
                }
            }
        }
        $options = array_map('trim', $options);
        #$options = array_map('strtolower', $options); // DO NOT CONVERT CASE

        // debug options array
        $this->debugValue("options",$debug_level,$options);

        // convert the options to arrays & headers single option headers, rfc2822 'list' sematics (csv)

        // attachments are special; they're not technically headers
        $attachments=array();

        // these are rfc2822 header equivalents
        $custom_headers=array(
            "attachment",
            "bcc",
            "cc",
            "from",
            "replyto",
            "reply-to",
            "reply_to",
            "to",
        );
        $custom_headers=array_map('strtolower', $custom_headers); // rely on lowercase

        // iterate through 'options', find rfc2822 values that conflict with custom_headers & change them to custom header values
        foreach ($options as $option_key => $option_value) {

            // this is here in case a multi dimensional array is passed; ignore it
            if (!is_string($option_value)) {
                continue;
            } else {
                $option_value=trim($option_value);
            }

            $option_value=strtolower($option_value);

            $this->debugValue("option_key=$option_key, option_value",$debug_level,$option_value);

            $option_value_explode = explode("=", $option_value);

            $this->debugValue("option_value_explode",$debug_level,$option_value_explode);

            if (!isset($option_value_explode[0])) {
                continue;
            }

            if (!preg_grep("/$option_value_explode[0]/i",$custom_headers)) {

                // not a custom value; could be an rfc2822 header ... see if there's a dupe that can be consolidated

                $rfc2822_header_strpos = strpos($option_value, ":");

                if ($rfc2822_header_strpos === false) {
                    // there's no = or : in the option; for now don't do anything (this is probably an error, though)
                    continue;
                }

                $rfc2822_header_key = strtolower(trim(substr($option_value,0,$rfc2822_header_strpos)));
                foreach ($custom_headers as $custom_header) {
                    if ($custom_header == $rfc2822_header_key) {

                        // a conflicting rfc2822 header was passed; get the rfc2822 header value
                        $rfc2822_header_value = trim(substr($option_value,$rfc2822_header_strpos+1));

                        $this->debugValue("convert 'custom' option [$option_key] rfc2822 '$rfc2822_header_key'",$debug_level,$rfc2822_header_value);

                        // convert it to a custom header before processing custom_headers (no/null values are checked later)
                        // replace the option_key with the new converted value
                        $options[$option_key] = "$custom_header=$rfc2822_header_value";

                    }
                }
                unset($custom_header);

                unset($option_value_explode, $rfc2822_header_key, $rfc2822_header_value, $rfc2822_header_strpos);

            }
        }
        unset($option_key, $option_value);

        // iterate through 'options' & merge values into a local variable named after custom header values
        foreach ($options as $option) {

            // this is here in case a multi dimensional array is passed; ignore it
            if (!is_string($option)) {
                continue;
            } else {
                $option=trim($option);
            }

            $this->debugValue("option",$debug_level,$option);

            $option_explode = explode("=", $option);

            $this->debugValue("option_explode",$debug_level,$option_explode);

            if (!isset($option_explode[0])) {
                continue;
            }

            if (preg_grep("/$option_explode[0]/i",$custom_headers)) {

                $this->debugValue("option_explode[0]",$debug_level,$option_explode[0]);

                // todo; change this to foreach loop through custom_headers; handle attachments different

                // note; everything EXCEPT attachment is UNSET later; do NOT rely on the case values ! (use what's in rfc2822_headers)
                switch($option_explode[0]) {
                case "attachment":
                    if (isset($option_explode[1]) && is_readable($option_explode[1])) {
                        $attachments[] = $option_explode[1];
                    } else {
                        if (!isset($option_explode[1])) {
                            $failure_reason = "error, attachment filename not set";
                        } else {
                            if (!is_readable($option_explode[1])) {
                                $failure_reason = "error, removed attachment '$option_explode[1]' (not readable)";
                            } else {
                                $failure_reason = "error, removed attachment '$option_explode[1]' (unavailable)";
                            }
                        }

                        $this->failure($failure_reason, false, $abort);

                        // Comments: to rfc2822_headers
                        $rfc2822_headers[] = "Comments: $failure_reason";

                    }
                    break;
                case "cc":
                    if (isset($option_explode[1]) && !is_null($option_explode[1]) && $option_explode[1] != '') {
                        if (!isset($cc)) {
                            $cc = array();
                        }
                        $option_explode_explode = explode(",", $option_explode[1]);
                        $cc = array_merge($cc, $option_explode_explode);
                        $cc = array_unique(array_map('trim', $cc));
                        unset($option_explode_explode);
                    }
                    break;
                case "bcc":
                    if (isset($option_explode[1]) && !is_null($option_explode[1]) && $option_explode[1] != '') {
                        if (!isset($bcc)) {
                            $bcc = array();
                        }
                        $option_explode_explode = explode(",", $option_explode[1]);
                        $bcc = array_merge($bcc, $option_explode_explode);
                        $bcc = array_unique(array_map('trim', $bcc));
                        unset($option_explode_explode);
                    }
                    break;
                case "from":
                    if (isset($option_explode[1]) && !is_null($option_explode[1]) && $option_explode[1] != '') {
                        if (!isset($from)) {
                            $from = array();
                        }
                        $option_explode_explode = explode(",", $option_explode[1]);
                        $from = array_merge($from, $option_explode_explode);
                        $from = array_unique(array_map('trim', $from));
                        unset($option_explode_explode);
                    }
                    break;
                case "replyto":
                case "reply-to":
                case "reply_to":
                    if (isset($option_explode[1]) && !is_null($option_explode[1]) && $option_explode[1] != '') {
                        if (!isset($reply_to)) {
                            $reply_to = array();
                        }
                        $option_explode_explode = explode(",", $option_explode[1]);
                        $reply_to = array_merge($reply_to, $option_explode_explode);
                        $reply_to = array_unique(array_map('trim', $reply_to));
                        unset($option_explode_explode);
                    }
                    break;
                case "to":
                    if (isset($option_explode[1]) && !is_null($option_explode[1]) && $option_explode[1] != '') {
                        if (!isset($to)) {
                            $to = array();
                        }
                        $option_explode_explode = explode(",", $option_explode[1]);
                        $to = array_merge($to, $option_explode_explode);
                        $to = array_unique(array_map('trim', $to));
                        unset($option_explode_explode);
                    }
                    break;
                case "replyto":
                case "reply-to":
                case "reply_to":
                }

            } else {
                // it's not one of custom headers, so pass it through (trimmed)
                $rfc2822_headers[] .= trim($option);
            }
        }
        unset($option);

        // at this point, additional_headers & new options sematics have been merged into unique & expandable local variable arrays
        // also, custom headers that do not conflict with passed rfc2822_headers have been seperated and added to rfc2822_headers

        $this->debugValue("options",$debug_level,$options);

        // now, iterate through custom_headers
        foreach ($custom_headers as $custom_header) {

            // if a local variable (expansion) was set above
            if (!empty($$custom_header)) {
                $this->debugValue("$custom_header",$debug_level,$$custom_header);

                $header_values=null;
                $rfc2822_header = null;

                // then (implode) the values into comma separated values per rfc2822
                foreach ($$custom_header as $header_key => $header_value) {
                    $this->debug("customer_header=$custom_header, header_key=$header_key, header_value=$header_value",$debug_level);
                    $header_values .= "$header_value,";
                }
                unset($$custom_header, $header_key, $header_value); // NOTICE; $$custom_header is being UNSET

                $header_values=rtrim($header_values,",");

                // ucwords is not necessary, here ... rfc2822 explicitly (albeit indirectly) says that keys are case insensitive
                $rfc2822_header = str_replace("_","-",ucwords(strtolower($custom_header))) . ": $header_values\r\n";

                // and add them to the rfc2822_headers array
                $rfc2822_headers[] = $rfc2822_header;
            }

        }

        // at this point there is a de-duped list of rfc2822 headers, empty & duplicate values removed

        // check of rfc2822_headers keys & values
        // todo; better enforcement of rfc2822 for complex/messy calls

        // these are used to determine which values to reformat as rfc2822 address lists
        $rfc2822_address_lists = array(
            "bcc",
            "cc",
            "from",
            "reply-to",
            "to",
        );
        $rfc2822_address_lists=array_map('strtolower', $rfc2822_address_lists); // rely on lowercase

        // rfc2822 header keys can't have certain characters, e.g. :space:, ; ... there are more ... for now, just the logic
        // add rejects matches to this list
        $rfc2822_header_key_rejects = array(
            " ",
            "~",
            "!",
            "@",
            "#",
            "$",
            "%",
            "^",
            "&",
            "*",
            "(",
            ")",
            "+",
            ":",
";",
",",
".",
"?",
"'",
"\"",
"/",
"\\",
        );

        // this loop validates the rfc2822_headers array
        foreach ($rfc2822_headers as $rfc2822_headers_key => $rfc2822_headers_value) {

            $rfc2822_header_strpos = strpos($rfc2822_headers_value, ":");

            // if there's no : in the rfc2822 option; remove it
            if ($rfc2822_header_strpos === false) {
                unset($rfc2822_headers[$rfc2822_headers_key]);

                $failure_reason = "error, removed invalid rfc2822 header [$rfc2822_headers_key] key '$rfc2822_headers_value' (no colon)";
                $this->failure($failure_reason, false, $abort);

                unset($rfc2822_headers_strpos);
                continue;
            }

            $rfc2822_header_key = strtolower(trim(substr($rfc2822_headers_value,0,$rfc2822_header_strpos)));
            $this->debugValue("rfc2822_header_key",$debug_level,$rfc2822_header_key);

            // rfc2822 header keys can't have certain characters, e.g. :space:, ; ...
            foreach ($rfc2822_header_key_rejects as $rfc2822_header_key_reject) {
                $rfc2822_header_key_reject_strpos = strpos($rfc2822_header_key, $rfc2822_header_key_reject);

                // if there's a reject string in the rfc2822 option; remove it
                if ($rfc2822_header_key_reject_strpos !== false) {
                    unset($rfc2822_headers[$rfc2822_headers_key]);

                    $failure_reason = "error, removed invalid rfc2822 header [$rfc2822_headers_key] key '$rfc2822_headers_value' (rejected '$rfc2822_header_key_reject')";
                    $this->failure($failure_reason, false, $abort);

                    unset($rfc2822_header_key_reject_strpos);
                    continue;
                }

            }
            unset($rfc2822_header_key_reject, $rfc2822_header_key_reject_strpos);

            $rfc2822_header_value = trim(substr($rfc2822_headers_value,$rfc2822_header_strpos+1));

            // format address lists
            foreach ($rfc2822_address_lists as $rfc2822_address_list) {

                if (strtolower($rfc2822_header_key) == $rfc2822_address_list) {
                    $rfc2822_address_value=null;
                    $this->debugValue("[$rfc2822_headers_key] $rfc2822_header_key is an address-list",$debug_level,$rfc2822_header_value);
                    $rfc2822_addresses=explode(",",$rfc2822_header_value);

                    foreach ($rfc2822_addresses as $rfc2822_address) {

                        $rfc2822_address = trim($rfc2822_address);

                        // first, check for a < delimiting <name> address
                        $rfc2822_address_strpos = strpos($rfc2822_address, "<");

                        $this->debugValue("[$rfc2822_headers_key] $rfc2822_address",$debug_level,$rfc2822_address);

                        if ($rfc2822_address_strpos === FALSE) {
                            // there's no name component
                            if (!$this->cli()) {

                                // if it's apache then add one only if it's a valid email address

                                if (filter_var($rfc2822_address, FILTER_VALIDATE_EMAIL) === false) {

                                    // this happens if it's called via apache mod_php (adhere to rfc5351)

                                    // do not add the rfc5351 invalid address

                                    $failure_reason = "error, removed invalid $rfc2822_address_list address '$rfc2822_address'";
                                    $this->failure($failure_reason, false, $abort);

                                    // Comments: to rfc2822_headers
                                    $rfc2822_headers[] = "Comments: $failure_reason";

                                } else {

                                    // add the valid rfc5351 address
                                    $rfc2822_address_value .= "$rfc2822_address <$rfc2822_address>,";

                                }

                            }
                        } else {
                            if ($rfc2822_address_strpos == 0) {

                                // definitely invalid; flip it around

                                $rfc2822_address_address = trim(trim(substr($rfc2822_address, 0, strpos($rfc2822_address,">")),"<|>"));
                                $rfc2822_address_name = trim(trim(substr($rfc2822_address, strpos($rfc2822_address,">")),"<|>"));

                            } else {

                                // maybe invalid .. only flip it around if the address component is invalid

                                if ($rfc2822_address_strpos > 0) {

                                    $rfc2822_address_address = trim(trim(substr($rfc2822_address, strpos($rfc2822_address,"<")),"<|>"));
                                    $rfc2822_address_name = trim(trim(substr($rfc2822_address, 0, strpos($rfc2822_address,"<")),"<|>"));
                                    // if there's an @ in the name component but not the address component then flip it around
                                    if (strpos($rfc2822_address_address,"@") == false && strpos($rfc2822_address_name,"@") !== false) {
                                        $rfc2822_address_name = trim(trim(substr($rfc2822_address, strpos($rfc2822_address,"<")),"<|>"));
                                        $rfc2822_address_address = trim(trim(substr($rfc2822_address, 0, strpos($rfc2822_address,"<")),"<|>"));
                                    }

                                }
                            }

                            $this->debugValue("[$rfc2822_headers_key] rfc2822_address_address",$debug_level,$rfc2822_address_address);
                            $this->debugValue("[$rfc2822_headers_key] rfc2822_address_name",$debug_level,$rfc2822_address_name);

                            // note; rfc2822 & rfc5351 standards conflict & may cause confusion.
                            // note; rfc5351 2.3.5 specifies SMTP addresses must have a valid DNS domain & php filter_vars obliges.
                            // note; But, rfc2822 address-list does NOT require DNS domain components.
                            // note; php filter_vars is not rfc2822 compliant. This presents problems when/if sending mail direct from cli.
                            // note; What if I want to send an email with sendmail/postfix via a web page to someone@hostname?
                            // note; Or, to someone@hostname.localdomain ??  rfc2822 (and sendmail/postfix) allow this!

                            if ($this->cli()) {

                                // this happens if it's NOT called via apache mod_php (adhere to rfc2822)

                                if (!is_null($rfc2822_address_address) && $rfc2822_address_address != '') {

                                    // if the address & name components are different, add them both
                                    if (!is_null($rfc2822_address_name) && $rfc2822_address_name != '') {
                                        $rfc2822_address_value .= "$rfc2822_address_name <$rfc2822_address_address>,";
                                    } else {
                                        // clone the address in both components
                                        $rfc2822_address_value .= "$rfc2822_address_address <$rfc2822_address_address>,";
                                    }

                                }

                            } else {

                                // this happens if it's called via apache mod_php (adhere to rfc5351)

                                if (filter_var($rfc2822_address_address, FILTER_VALIDATE_EMAIL) === false) {

                                    // this happens if it's called via apache mod_php (adhere to rfc5351)

                                    // do not add the second component, it's not a valid email address (it should be)

                                    $failure_reason = "error, removed invalid $rfc2822_address_list address '$rfc2822_address' (reversed)";
                                    $this->failure($failure_reason, false, $abort);

                                    // Comments: to rfc2822_headers
                                    $rfc2822_headers[] = "Comments: $failure_reason";

                                } else {

                                    // the second component is a valid rfc5351 address; add it

                                    if (!is_null($rfc2822_address_address) && $rfc2822_address_address != '') {
                                        if (!is_null($rfc2822_address_name) && $rfc2822_address_name != '') {
                                            $rfc2822_address_value .= "$rfc2822_address_name <$rfc2822_address_address>,";
                                        } else {
                                            $rfc2822_address_value .= "$rfc2822_address_address <$rfc2822_address_address>,";
                                        }
                                    }

                                }
                            }
                        }

                        unset($rfc2822_address_address, $rfc2822_address_strpos, $rfc2822_address_name);
                    }
                    unset($rfc2822_address);

                    $rfc2822_address_value=trim($rfc2822_address_value,",");

                    if (!is_null($rfc2822_address_value) && $rfc2822_address_value != '') {

                        $this->debugValue("[$rfc2822_headers_key] rfc2822_address_value",$debug_level,$rfc2822_address_value);

                        // replace the existing address list with the formatted address list
                        $rfc2822_headers[$rfc2822_headers_key] = ucwords(strtolower($rfc2822_address_list)) . ": " . $rfc2822_address_value;
                    } else {
                        // nullify the invalid value
                        $rfc2822_header_value=null;
                    }

                    unset($rfc2822_address, $rfc2822_address_value);

                }

            }
            unset($rfc2822_address_list);

            // if there's an rfc2822 option, but an empty value; remove it
            if (is_null($rfc2822_header_value) || $rfc2822_header_value == '') {
                unset($rfc2822_headers[$rfc2822_headers_key]);

                $failure_reason = "error, removed invalid rfc2822 header [$rfc2822_headers_key] value for '$rfc2822_header_key' (no value)";
                $this->failure($failure_reason, false, $abort);

                unset($rfc2822_headers_strpos);
            }

            $this->debugValue("[$rfc2822_headers_key] $rfc2822_header_key",$debug_level,$rfc2822_header_value);
        }
        unset($rfc2822_headers_key, $rfc2822_headers_value);

        // check From: header, first pass
        $rfc2822_headers_from=preg_grep("/^From:/i",$rfc2822_headers);
        if ($rfc2822_headers_from) {
            $this->debugValue("rfc2822_headers_from",$debug_level,$rfc2822_headers_from);
        } else {
            // there's no From: address, so figure it out and add it to rfc2822_headers
            $rfc2822_from = $rfc2822_user . "@" . $rfc2822_domain;
            $rfc2822_from = "$rfc2822_from <$rfc2822_from>";
            $rfc2822_headers[] = "From: " . $rfc2822_from;
        }

        // (re)set these

        $rfc2822_from=null;
        $rfc2822_sender=null;

        // check From: header, second pass (set sender)
        $rfc2822_headers_from=preg_grep("/^From:/i",$rfc2822_headers);
        if ($rfc2822_headers_from) {
            foreach($rfc2822_headers_from as $rfc2822_header_from) {
                $rfc2822_from=trim(preg_replace("/^From:/i","", $rfc2822_header_from));

                $rfc2822_sender=trim(preg_replace("/^From:/i","", explode(",",$rfc2822_header_from)[0]));
                if (is_null($rfc2822_sender) || $rfc2822_sender == '') {
                    $rfc2822_sender=null; // this should get reset
                } else {
                    $rfc2822_headers[] = "Sender: " . $rfc2822_sender;
                }
                $this->debugValue("Sender: set",$debug_level,$rfc2822_sender);
            }
        } else {
            // no from, can't set sender ... return false
            $failure_reason = "failure, can't determine from address (no sender)";
            return $this->failure($failure_reason, false, $abort);
        }

        $this->debugValue("rfc2822_from",$debug_level,$rfc2822_from);

        // check Sender: header (final)
        $rfc2822_headers_sender=preg_grep("/^Sender:/i",$rfc2822_headers);
        if (!$rfc2822_headers_sender) {
            // no sender, something went wrong ... return false
            $failure_reason = "failure, can't determine sender address (no header)";
            return $this->failure($failure_reason, false, $abort);
        }

        $this->debugValue("rfc2822_sender",$debug_level,$rfc2822_sender);

        // add other generic, but useful (& missing) rfc2822_headers, here ...

        if (!preg_grep("/^Date:/i",$rfc2822_headers)) {
            $rfc2822_headers[] = 'Date: ' . date('r');
        }

        if (!preg_grep("/^Message-id:/i",$rfc2822_headers)) {
            $rfc2822_headers[] = 'Message-id: <' . $this->uuid() . '@' . $rfc2822_domain . '>';
        }

        if (!preg_grep("/^Return-path:/i",$rfc2822_headers)) {
            $rfc2822_headers[] = "Return-path: $rfc2822_sender";
        }

        if (!preg_grep("/^Reply-to:/i",$rfc2822_headers)) {
            $rfc2822_headers[] = "Reply-to: $rfc2822_sender";
        }

        if (!preg_grep("/^Thread-index:/i",$rfc2822_headers)) {
            $rfc2822_headers[] = "Thread-index: " . $this->uuid();
        }

        if (!preg_grep("/^X-priority:/i",$rfc2822_headers)) {
            $rfc2822_headers[] = "X-priority: 3";
        }

        if (!preg_grep("/^X-mailer:/i",$rfc2822_headers)) {
            $rfc2822_headers[] = "X-mailer: " . $rfc2822_mailer;
        }

        // trim, sort, & unique rfc2822_headers
        $rfc2822_headers = array_map('trim', $rfc2822_headers); // this needs happen per rfc2822
        sort($rfc2822_headers); // easier to read in debug output
        $rfc2822_headers = array_unique($rfc2822_headers);

        // at this point ...
        // rfc2822_headers *could* be passed to php mail() EXCEPT for the To: headers ...
        // there's a valid array of attachments (if given)

        // debug rfc2822_headers
        $this->debugValue("rfc282_headers",$debug_level,$rfc2822_headers);

        // debug attachments
        $this->debugValue("attachments",$debug_level,$attachments);

        // create parameters array
        $parameters=array();
        if (!is_array($additional_parameters) && !is_object($additional_parameters)) {
            if (!is_null($additional_parameters) && $additional_parameters != '') {
                $additional_parameters = trim((string)$additional_parameters);
                $parameters = explode(",", $additional_parameters);
            }
        } else {
            if (is_array($additional_parameters)) {
                $parameters = $additional_parameters;
            } else {
                if (is_object($additional_parameters)) {
                    $parameters = (array)$additional_parameters;
                }
            }
        }
        $parameters = array_map('trim', $parameters);

        // if no parameters are passed, then force Return-path with parameter (otherwise, honor passed parameters)
        if (empty($parameters) && !empty($rfc2822_sender)) {

            $rfc2822_sender_address = $rfc2822_sender;

            if (strpos($rfc2822_sender_address,"<") !== false && strpos($rfc2822_sender_address,">") !== false) {
                // sendmail/postfix -f argument wont accept rfc2822 compliant Sender:
                // ideally, Return-path: would only be back to sender's address
                $rfc2822_sender_address = substr($rfc2822_sender_address,strpos($rfc2822_sender_address,"<"));
                $rfc2822_sender_address = substr($rfc2822_sender_address,0,strpos($rfc2822_sender_address,">"));
            }

            if (strpos($rfc2822_sender_address," ") !== false) {
                // sendmail/postfix -f argument only accepts single words
                $rfc2822_sender_address = substr($rfc2822_sender_address,0,strpos($rfc2822_sender_address," "));
            }

            $rfc2822_sender_address = trim(trim($rfc2822_sender_address,"<|>"));

            if (!is_null($rfc2822_sender_address) && $rfc2822_sender_address != '') {
                $parameters[] = "-oi";
                $parameters[] = "-F '$rfc2822_sender'";
                $parameters[] = "-r '$rfc2822_sender_address'";
                $parameters[] = "-f '$rfc2822_sender_address'";
            }
        }

        // debug parameters
        $this->debugValue("parameters",$debug_level,$parameters);

        // check parameters array
        if (empty($parameters)) {
            // optional
        }

        // create body_parts array (this needs to be AFTER processing options)
        $body_parts=array();
        if (!is_array($message) && !is_object($message)) {
            if (!is_null($message) && $message != '') {
                $message = trim((string)$message);
                $body_parts[] = $message;
            }
        } else {
            if (is_array($message)) {
                $body_parts = $message;
            } else {
                if (is_object($message)) {
                    $body_parts = (array)$message;
                }
            }
        }
        $body_parts = array_map('trim', $body_parts);

        // add headers based on body type
        if (!preg_grep("/^Mime-version:/i",$rfc2822_headers)) {
            $rfc2822_headers[] = "Mime-version: 1.0";
        }
        if (!preg_grep("/^Content-type:/i",$rfc2822_headers)) {
            if (count($body_parts) > 1 || !empty($attachments)) {
                // create a unique rfc2822 boundary string
                $rfc2822_boundary = preg_replace("/\\\/","-",get_class($this)) . "-" . $this->uuid();

                $rfc2822_headers[] = "Content-type: multipart/alternative; boundary=$rfc2822_boundary";
            } else {
                $rfc2822_headers[] = "Content-type: text/plain; charset=utf-8";
            }
        }

        $this->debugValue("body_parts",$debug_level,$body_parts);

        // at this point ... rfc2822_headers && attachments arrays should be reliable & trustworthy

        // if this method hasn't already returned due to a parse failure, then use this as a return variable

        $email_sent = false;

        // todo; PHPMailer integration
        // todo; try PHPMailer first, else fall back to php mail() ??

        $php_mailer=false; // permanently disabled, for now
        if ($php_mailer) {

            try {

                $phpmailer = new \PHPMailer;
                $phpmailer->setFrom($from); // can this handle multiple from, & set single sender per rfc2822?
                $phpmailer->addAddress($to); // can this handle multiple comma seperated rfc2822 address-list format?

                $phpmailer->Subject = $subject;
                $phpmailer->Body    = $body . " - PHPMailer"; // what if a boundaried, multipart body is passed?

                #$email_sent=$phpmailer->send());

            } catch (\Exception $e) {
                $php_mail=true;
            }

            if (!$email_sent) {
                // try again with php_mail
                $php_mail=true;
            }

        } // end php_mailer

        $php_mail=true; // enabled; use this first, for now, no external depedencies; php 5.4+ compatible

        if ($php_mail) {

            // note;
            // php 5.4.x mail() will send empty To: & Subject: if they're in additional_headers, which violates rfc2822
            // php 5.4.x mail() will also send null To: & Subject: if called as mail(null, null, ...) (which also violates rfc2822)

            $php_mail_additional_headers = $rfc2822_headers;

            // pull To: & Subject: out of rfc2822_headers and put everything else into php_mail_additional_headers
            foreach ($php_mail_additional_headers as $php_mail_additional_headers_key => $php_mail_additional_headers_value) {
                if (preg_match("/^Subject:/i",$php_mail_additional_headers_value)) {
                    // put what's in rfc2822_headers Subject into $subject
                    $subject = trim(preg_replace("/^Subject:/i","",$php_mail_additional_headers_value));
                    unset($php_mail_additional_headers[$php_mail_additional_headers_key]);
                }
                if (preg_match("/^To:/i",$php_mail_additional_headers_value)) {
                    // put what's in rfc2822_headers To into $To
                    $to = trim(preg_replace("/^To:/i","",$php_mail_additional_headers_value));
                    unset($php_mail_additional_headers[$php_mail_additional_headers_key]);
                }
            }
            unset($php_mail_additional_headers_key, $php_mail_additional_headers_value);

            // last resort; set to to process user; hopefully no bounces
            if (empty($to)) {
                $to = $rfc2822_user;
            }

            $this->debugValue("to",$debug_level,$to);
            $this->debugValue("subject",$debug_level,$subject);

            // todo; reduce this debug level
            $this->debugValue("php_mail_additional_headers",$debug_level-25,$php_mail_additional_headers);

            if (!empty($attachments)) {
                // todo; reduce this debug level
                $this->debugValue("attachments",$debug_level-25,$attachments);
            }

            // put body together
            $body = null;
            foreach ($body_parts as $body_part) {

                // don't add multiparts unless this method set the boundary

                if (!empty($rfc2822_boundary)) {
                    if($body_part != strip_tags($body_part)) {
                        // contains html
                        $this->debugValue("body_part (html)",$debug_level,$body_part);
                        $body .= "--" . $rfc2822_boundary . "\r\n";
                        $body .= "Content-type: text/html; charset=utf-8" . "\r\n";
                    } else {
                        // no html
                        $this->debugValue("body_part (text)",$debug_level,$body_part);
                        $body .= "--" . $rfc2822_boundary . "\r\n";
                        $body .= "Content-type: text/plain; charset=utf-8" . "\r\n";
                    }
                    $body .= "Content-transfer-encoding: 7bit" . "\r\n";
                    $body .= "\r\n";
                }

                $body .= $body_part;
                $body .= "\r\n";

            }

            foreach ($attachments as $attachment) {
                // don't add attachments unless this method set the boundary
                if (!empty($rfc2822_boundary)) {
                    $attachment_size=filesize($attachment);
                    $body .= "--" . $rfc2822_boundary . "\r\n";
                    $body .= "Content-type: application/octet-stream; name=\"" . basename($attachment) . "\"" . "\r\n";
                    $body .= "Content-transfer-encoding: base64" . "\r\n";
                    $body .= "Content-disposition: attachment; filename=\"" . basename($attachment) . "\"; size=$attachment_size" . "\r\n";
                    $body .= "\r\n";
                    $body .= chunk_split(base64_encode(file_get_contents($attachment)));
                }
            }

            if (!empty($rfc2822_boundary)) {
                $body .= "--" . $rfc2822_boundary . "--";
            }

            // todo; reduce this debug level
            $this->debugValue("body",$debug_level-10,$body);

            $email_sent=mail($to, $subject, $body, implode("\r\n", $php_mail_additional_headers), implode(" ", $parameters));
        }

        if ($email_sent) {
            $this->debug("success, email to '$to', subject '$subject' sent!",1);
        } else {
            $failure_reason = "error, email to '$to', subject '$subject' not sent";
            return $this->failure($failure_reason, false, $abort);
        }

        return $email_sent;

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
