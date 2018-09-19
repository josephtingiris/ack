<?php
/*

aNAcONDA kICKSTART (ack)

Copyright (C) 2015 Joseph Tingiris

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

 */

# Global (debug)

if (!empty($_GET) && is_array($_GET)) {
    $_GET_lower = array_change_key_case($_GET, CASE_LOWER);
} else {
    $_GET_lower=array();
}

if (isset($_GET_lower['debug'])) {
    $DEBUG=(int)$_GET_lower['debug'];
}

if (!empty($_POST) && is_array($_POST)) {
    $_POST_lower = array_change_key_case($_POST, CASE_LOWER);
} else {
    $_POST_lower=array();
}

// _POST_lower['debug'] overrides _GET_lower['debug']
if (isset($_POST_lower['debug'])) {
    $DEBUG=(int)$_POST_lower['debug'];
}

if (empty($Default_Timezone)) {
    $Default_Timezone="America/New_York";
}
date_default_timezone_set($Default_Timezone);

# begin Debug.php.include

$Debug_Php="Debug.php";
$Debug_Php_Dir=dirname(__FILE__);
if (is_link($Debug_Php_Dir)) $Debug_Php_Dir=readlink($Debug_Php_Dir);
while (!empty($Debug_Php_Dir) && $Debug_Php_Dir != "/" && is_dir($Debug_Php_Dir)) { # search backwards
    foreach (array($Debug_Php_Dir, $Debug_Php_Dir."/include/debug-php", $Debug_Php_Dir."/include") as $Debug_Php_Source_Dir) {
        $Debug_Php_Source=$Debug_Php_Source_Dir."/".$Debug_Php;
        if (is_readable($Debug_Php_Source)) {
            require_once($Debug_Php_Source);
            break;
        } else {
            unset($Debug_Php_Source);
        }
    }
    if (!empty($Debug_Php_Source)) break;
    $Debug_Php_Dir=dirname("$Debug_Php_Dir");
}
if (empty($Debug_Php_Source)) { echo "$Debug_Php file not found\n"; exit(1); }
unset($Debug_Php_Dir, $Debug_Php);

# end Debug.php.include

$Debug = new \josephtingiris\Debug();

# Functions

function ackAuthorized($ack_mac=null, $ack_ip=null, $ack_config=null) {

    # begin function logic

    global $Debug;

    $ack_ip=ackIpv4($ack_ip);
    $ack_mac=ackMac($ack_mac);

    if (empty($ack_ip) && empty($ack_mac)) {
        ackLog("unauthorized [NULL ack_ip & ack_mac]","ERROR");
        return false;
    }

    $ack_authorized=false;
    $ack_authorized_date=date("Y-m-d H:i:s"); // TODO; make this date format include timezone (offset)
    $ack_authorized_method=null;

    $ack_config=ackFile($ack_config);
    $ack_config_update=false;

    $Debug->debug("ackAuthorized($ack_mac,$ack_ip,$ack_config)",20);

    // config files are the primary authorization method, check those first

    if (!$ack_authorized && !empty($ack_config)) {

        $ack_authorized_config=ackConfigGet("authorized",$ack_config);
        $Debug->debug("ack_authorized_config = $ack_authorized_config",20);
        if (filter_var($ack_authorized_config, FILTER_VALIDATE_BOOLEAN)) {
            $ack_authorized=$ack_authorized_config;
        } else {
            $ack_authorized=false;
        }

        if ($ack_authorized) {
            $ack_authorized_method="authorized attribute is set to true";
        }

    }

    // ip addresses are the secondary authorization method

    if (!$ack_authorized && !empty($ack_ip)) {

        if (!empty($GLOBALS["Ack_Authorized_Ips"]) && is_array($GLOBALS["Ack_Authorized_Ips"])) {
            $ack_authorized_ips=$GLOBALS["Ack_Authorized_Ips"];
        } else {
            $ack_authorized_ips=array();
        }

        foreach ($ack_authorized_ips as $ack_authorized_ip) {

            if  (!$ack_authorized && $ack_ip == $ack_authorized_ip) { # exact match, e.g. /32
                $ack_authorized_method="exact ip match";
                $ack_authorized=true;
            }

            if (!$ack_authorized && ackIpv4Authorized($ack_ip, $ack_authorized_ip)) {
                $ack_authorized_method="cidr ip match ($ack_authorized_ip)";
                $ack_authorized=true;
            }

            if ($ack_authorized) {
                $ack_config_update=true;
                break;
            }
        }


    }

    if ($ack_config_update && !empty($ack_authorized_method) && !empty($ack_config)) {
        $ack_config_set=array(
            //"authorized = true", // add permanent authorization?
            "authorized_method = \"$ack_authorized_method\"",
            "ip = $ack_ip",
            "mac = $ack_mac",
            "modified = $ack_authorized_date",
        );
        ackConfigSet($ack_config_set,$ack_config);
    }

    if ($ack_authorized) {
        ackLog("authorized [$ack_ip][$ack_mac][$ack_config][$ack_authorized_method]","NOTICE");
        return $ack_authorized;
    } else {
        ackLog("unauthorized [$ack_ip][$ack_mac][$ack_config]","WARNING");
        return false;
    }

    ackLog("unauthorized [$ack_ip][$ack_mac][failsafe]","ERROR");
    return false;

    # end function logic

}

function ackCacheSet($ack_cache_stack=array(),$ack_cache=null) {

    # begin function logic

    if (empty($ack_cache_stack)) return;

    $ack_cache_string=null;

    foreach ($ack_cache_stack as $ack_cache_element) {
        $ack_cache_string.=$ack_cache_element.",";
    }
    $ack_cache_string=trim($ack_cache_string,",");

    if (empty($ack_cache_string)) return;

    $ack_cache_check=ackFile($ack_cache,false);
    if (empty($ack_cache_check)) {
        ackLog("$ack_cache file not writable (check permissions)","FATAL");
        exit(2);
        return;
    }

    if (isset($_SERVER["REMOTE_ADDR"])) {
        $remote_addr=trim($_SERVER["REMOTE_ADDR"]);
    } else {
        $remote_addr=gethostbyname(gethostname());
    }

    $fp=fopen($ack_cache,"a+");
    if ($fp) {
        fwrite($fp,date("Y-m-d H:i:s").",$remote_addr,".$ack_cache_string."\n");
        fclose($fp);
    }

    # end function logic

}

function ackConfigGet($ack_config_attribute,$ack_config=null) {

    # begin function logic

    global $Debug;

    if (is_string($ack_config_attribute)) $ack_config_attribute=trim($ack_config_attribute);
    if (empty($ack_config_attribute)) {
        return null;
    }

    $ack_config=ackFile($ack_config);
    if (empty($ack_config)) {
        return null;
    }

    $debug_level=22;
    $debug_string="get ack_config=$ack_config, attribute=$ack_config_attribute";

    $attribute_found=false;

    $ini_stack=parse_ini_file($ack_config,true);
    if (empty($ini_stack)) {
        $debug_string.=" (empty ini)";
        $Debug->debug($debug_string,$debug_level);
        return null;
    }

    if (!$attribute_found && is_string($ack_config_attribute)) {
        if (isset($ini_stack[$ack_config_attribute]) || array_key_exists($ack_config_attribute,$ini_stack)) {
            $attribute_found=true;
        }
    }

    if (!$attribute_found && is_array($ack_config_attribute) || is_object($ack_config_attribute)) {
        foreach($ack_config_attribute as $get_attribute) {
            if (is_string($get_attribute)) {
                if (isset($ini_stack[$get_attribute]) || array_key_exists($get_attribute,$ini_stack)) {
                    $attribute_found=true;
                }
            }
        }
        unset($get_attribute);
    }

    if (!$attribute_found) {
        $debug_string.=" (attribute not found)";
        $Debug->debug($debug_string,$debug_level);
        return null;
    }

    if (is_string($ack_config_attribute)) {
        $return_string=null;
        if (is_string($ini_stack[$ack_config_attribute])) {
            $return_string=$ini_stack[$ack_config_attribute];
        } else if (is_array($ini_stack[$ack_config_attribute]) || is_object($ini_stack[$ack_config_attribute])) {
            $return_string=end($ini_stack[$ack_config_attribute]);
        }
        $debug_string.=" ($return_string)";
        $Debug->debug($debug_string,$debug_level);
        return $return_string;
    }

    if (is_array($ack_config_attribute) || is_object($ack_config_attribute)) {
        if (is_object($ack_config_attribute)) {
            $return_stack=object();
        } else $return_stack=array();

        foreach($ack_config_attribute as $get_attribute) {
            foreach($ini_stack as $ini_attribute => $ini_value) {
                $debug_string.=", get_attribute=$get_attribute, ini_attribute=$ini_attribute";
                if ($ini_attribute == $get_attribute) {
                    $return_stack[$ini_attribute]=$ini_value;
                }
            }
            unset($ini_attribute);
            unset($ini_value);
        }
        unset($get_attribute);

        $Debug->debug($debug_string,$debug_level);
        return $return_stack;
    }

    $Debug->debug($debug_string,$debug_level);
    return null;

    # end function logic

}

function ackConfigSet($ack_config_attribute,$ack_config=null) {

    # begin function logic

    global $Debug;

    if (is_string($ack_config_attribute)) $ack_config_attribute=trim($ack_config_attribute);
    if (empty($ack_config_attribute)) return null;

    $ack_config=ackFile($ack_config);
    if (empty($ack_config)) return null;

    if (!is_writable($ack_config)) {
        ackLog("$ack_config file not writable","ERROR");
        return null;
    }

    $ack_config_stack=array();

    if (is_string($ack_config_attribute)) {
        $Debug->debug("set ack_config_attribute=$ack_config_attribute, ack_config=$ack_config",2);
        if (strpos($ack_config_attribute,"=") !== false) {
            $ini_attribute=trim(substr($ack_config_attribute,0,strpos($ack_config_attribute,"=")));
            $ini_value=trim(substr($ack_config_attribute,strpos($ack_config_attribute,"=")+1));
            $ack_config_stack[$ini_attribute]=$ini_value;
        } else {
            $ack_config_stack[$ack_config_attribute]=null;
        }
    } else if (is_array($ack_config_attribute) || is_object($ack_config_attribute)) {
        foreach($ack_config_attribute as $attribute_value) {
            if (!is_string($attribute_value)) continue;
            $Debug->debug("set ack_config_attribute=$attribute_value, ack_config=$ack_config",2);
            if (strpos($attribute_value,"=") !== false) {
                $ini_attribute=trim(substr($attribute_value,0,strpos($attribute_value,"=")));
                $ini_value=trim(substr($attribute_value,strpos($attribute_value,"=")+1));
                if (strtolower($ini_value) == "false" || strtolower($ini_value) == "off") $ini_value="false";
                if (strtolower($ini_value) == "true" || strtolower($ini_value) == "on") $ini_value="true";
                $ack_config_stack[$ini_attribute]=$ini_value;
            } else {
                $ack_config_stack[$attribute_value]=null;
            }
        }
        unset($attribute_value);
    }

    if (empty($ack_config_stack)) {
        ackLog("ack_config_stack is null","WARNING");
        return null;
    }

    $ack_config_date=date("Y-m-d H:i:s");

    $ack_config_modifier="[$ack_config_date]";

    if (isset($_SERVER["REQUEST_SCHEME"])) {
        $ack_config_modifier.="[".trim($_SERVER["REQUEST_SCHEME"])."]";
    }

    if (isset($_SERVER["SERVER_NAME"])) {
        $ack_config_modifier.="[".trim($_SERVER["SERVER_NAME"])."]";
    }

    if (isset($_SERVER["SCRIPT_FILENAME"])) {
        $ack_config_modifier.="[".trim($_SERVER["SCRIPT_FILENAME"])."]";
    }

    if (isset($_SERVER["REMOTE_ADDR"])) {
        $ack_config_modifier.="[".trim($_SERVER["REMOTE_ADDR"])."]";
    }

    $ack_config_entry="modifier = $ack_config_modifier\n";

    foreach ($ack_config_stack as $ini_attribute => $ini_value) {
        #$ack_config_modified="modified[$ini_attribute] = ".$ack_config_date."\n";
        #$ack_config_entry.=$ack_config_modified;
        $ini_attribute = strtolower($ini_attribute);
        $ack_config_entry.="$ini_attribute = $ini_value\n";
    }
    unset($ini_attribute);
    unset($ini_value);

    if (empty($ack_config_entry) || strpos($ack_config_entry," = ") == 0) {
        ackLog("ack_config_entry is null","WARNING");
        return null;
    }
    $fp=fopen($ack_config,"a+");
    if ($fp) {
        fwrite($fp,$ack_config_entry);
        fclose($fp);
    }

    # end function logic

}

function ackExec($cmd, &$stdout=null, &$stderr=null) {
    $cmd = escapeshellcmd($cmd);
    $proc = proc_open($cmd,[
        1 => ['pipe','w'],
        2 => ['pipe','w'],
    ],$pipes);
    $stdout = trim(stream_get_contents($pipes[1]));
    fclose($pipes[1]);
    $stderr = trim(stream_get_contents($pipes[2]));
    fclose($pipes[2]);
    return proc_close($proc);
}

function ackFile($ack_file=null, $ack_ini=true) {

    # begin function logic

    global $Debug;

    $Debug->debug("ack_file=$ack_file",50);
    if (!empty($ack_file) && is_string($ack_file)) {
        $ack_file=str_replace("-", '',$ack_file);
        $ack_file=str_replace(":", '',$ack_file);
        $ack_file=trim($ack_file);
    } else {
        $ack_file=null;
    }
    $Debug->debug("ack_file=$ack_file",50);

    if (empty($ack_file)) {
        ackLog("ack_file '$ack_file' is invalid, set to null","ERROR");
        return null;
    }

    if (!file_exists($ack_file)) {
        ackLog("$ack_file file not found, touching","INFO");
        if (touch($ack_file)) {
            ackLog ("$ack_file touched successfully","NOTICE");
        } else {
            ackLog("$ack_file could not be touched","ERROR");
            return null;
        }
    }

    if (!is_readable($ack_file)) {
        ackLog("$ack_file file not readable","ERROR");
        return null;
    }

    if ($ack_ini) {
        $ini_stack=@parse_ini_file($ack_file,true);
        if ($ini_stack === false) {
            ackLog("$ack_file file readable, but contains an ini syntax error","ERROR");
            return null;
        }
    }

    return $ack_file;

    # end function logic

}

function ackGlobals($bash=false) {

    # begin function logic

    global $Debug;

    $ack_globals=array();

    $ack_export="";

    foreach ($GLOBALS as $ack_global_key => $ack_global_value) {
        if (is_string($ack_global_key)) {
            if (preg_match("/^ack/i",$ack_global_key)) {
                if ($bash) {
                    if (is_string($ack_global_value)) {
                        $ack_export.= "export $ack_global_key=\"".trim($ack_global_value)."\"\n";
                    } else {
                        if (is_array($ack_global_value) || is_object($ack_global_value)) {
                            $ack_export.= "export $ack_global_key=(";
                            foreach($ack_global_value as $ack_global_value_value) {
                                if (is_string($ack_global_value_value)) {
                                    $ack_export.= "\"".trim($ack_global_value_value)."\" ";
                                }
                            }
                            $ack_export=trim($ack_export);
                            $ack_export.= ")\n";
                        }
                    }
                } else {
                    $ack_globals[$ack_global_key]=$ack_global_value;
                }
            }
        }
    }
    unset($ack_global_key, $ack_global_value);


    if ($bash) {
        if (!empty($_SERVER["PATH"]) && !empty($GLOBALS["Ack_Path"])) {
            $ack_path=$GLOBALS["Ack_Path"];
            $ack_export.= "export PATH=\"$ack_path\"\n";
        }
        echo $ack_export;
        exit(0);
    } else {
        ksort($ack_globals);
        foreach ($ack_globals as $ack_global_key => $ack_global_value) {
            $Debug->debugValue($ack_global_key,9);
        }
    }

    # end function logic

}

function ackGlobalsReplace($ack_content=null) {

    # begin function logic

    global $Debug;

    $ack_content_replace=$ack_content;
    $ack_replace_key=null;

    if (is_string($ack_content)) {

        $ack_globals=$GLOBALS;
        ksort($ack_globals);
        foreach ($ack_globals as $ack_global_key => $ack_global_value) {
            if (is_string($ack_global_value)) {
                if (preg_match("/^ack/i",$ack_global_key)) {
                    $ack_replace_key="##".strtoupper($ack_global_key)."##";
                    $ack_content_replace=str_replace($ack_replace_key,$ack_global_value,$ack_content_replace);
                }
            }
        }
        unset($ack_global_key, $ack_global_value, $ack_replace_key);

    }

    return $ack_content_replace;

    # end function logic

}

# optionally convert an integer (or hex) to a decimal IP, validate it, & return it (or null)
function ackIpv4($ip=null, $int_ip=false, $hex_ip=false, $little_endian=false) {
    if ($ip == null) return null;

    $return_ip=$ip;

    if ($hex_ip) {
        if ($little_endian) {
            $return_ip=hexdec(implode('',array_reverse(str_split($ip,2)))); // little to big endian
        } else {
            // big endian
            $return_ip=hexdec($ip);
        }
        if (!empty($return_ip)) {
            $int_ip=true;
        }
    }

    if ($int_ip) {
        $return_ip=long2ip($return_ip);
    }

    if (!filter_var($return_ip, FILTER_VALIDATE_IP)) {
        ackLog("invalid ack_ip '$ip'","WARNING");
        $return_ip=null;
    }

    return $return_ip;
}

# return an array with an inteface's address(es), cidr, & mask, or optionally a string of the (first) address, cidr, or mask
function ackIpv4Address($interface=null, $address=false, $cidr=false, $mask=false) {

    $interface=trim($interface);
    if (empty($interface)) {
        return null;
    }

    global $Debug;

    $return_address=null;
    $return_addresses=array();
    $return_cidr=null;
    $return_cidrs=array();
    $return_mask=null; // TODO convert cidr to mask
    $return_masks=array();

    $ip_cmd="ip -4 -o addr show $interface";
    $ip_rc=ackExec($ip_cmd,$ip_stdout,$ip_stderr);
    if ($ip_rc == 0) {
        // success
        $ip_stdout_lines=explode("\n",$ip_stdout);
        if (!empty($ip_stdout_lines)) {
            foreach($ip_stdout_lines as $ip_stdout_line) {
                $ip_stdout_line=trim($ip_stdout_line);
                if ($ip_stdout_line == null || $ip_stdout_line == "") {
                    continue;
                }
                $ip_explode_line=explode(" ",$ip_stdout_line);
                if (isset($ip_explode_line[1]) && $ip_explode_line[1] == "$interface") {
                    // this line matches $interface
                    if (isset($ip_explode_line[6]) && strpos($ip_explode_line[6],"/") !== false) {
                        list ($ip_address, $ip_cidr) = explode('/', $ip_explode_line[6]);

                        $ip_address=ackIpv4($ip_address);
                        if (!empty($ip_address)) {
                            $Debug->debugValue("ip_address",15,$ip_address);
                            if ($address && !$cidr && !$mask) {
                                return $ip_address;
                            } else {
                                $Debug->debugValue($ip_explode_line,15);
                            }
                        }

                        $ip_cidr=trim($ip_cidr);
                        $ip_cidr=(int)$ip_cidr;
                        if (isset($ip_cidr) && is_int($ip_cidr)) {
                            $Debug->debugValue("ip_cidr",15,$ip_cidr);
                            if (!$address && $cidr && !$mask) {
                                return $ip_cidr;
                            } else {
                                $Debug->debugValue($ip_explode_line,15);
                            }
                        }
                        if (isset($ip_cidr) && is_int($ip_cidr)) {
                            $Debug->debugValue("ip_cidr",15,$ip_cidr);
                            if (!$address && !$cidr && $mask) {
                                return ackIpv4Netmask($ip_cidr);
                            } else {
                                $Debug->debugValue($ip_explode_line,15);
                            }
                        }

                    }
                }
            }
            unset($ip_explode_line, $ip_stdout_line);
        }
        exit(0);
    } else {
        // failure
        ackLog("'$ip_cmd' failed","ERROR");
    }
}

# if an ip is in a cidr range return true else return false
function ackIpv4Authorized($ip, $cidr) {

    # begin function logic

    if (strpos($cidr,"/") !== false) {
        list ($subnet, $bits) = explode('/', $cidr);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned

        return ($ip & $mask) == $subnet;
    }

    return false;

    # end function logic

}

function ackIpv4Netmask($ip) {

    if (strpos($ip, '/') !== false) {
        $cidr = substr ($ip, strpos ($ip, '/') + 1) * 1;
    } else {
        $ip = trim($ip);
        $cidr = (int)$ip;
    }

    $netmask = str_split (str_pad (str_pad ('', $cidr, '1'), 32, '0'), 8);

    foreach ($netmask as &$element)
        $element = bindec ($element);

    return join ('.', $netmask);

}

function ackLog($log_message, $log_level="INFO", $log=null) {

    # begin function logic

    global $Debug;

    if (empty($log)) {
        if (!empty($GLOBALS["Ack_Log"])) $log=$GLOBALS["Ack_Log"];
    }
    if (empty($log)) $log="/tmp/ack.log";

    $log_message_header="[".$log_level."]";
    $log_message="$log_message_header $log_message";

    $error_log=false;
    if (is_writable(dirname($log))) {

        if (!file_exists($log)) {
            if (!touch($log)) {
                $error_log=true;
            }
        }

        if (is_writable($log) && !$error_log) {
            $fp=fopen($log,"a+");
            if ($fp) {
                fwrite($fp,ackTimestamp($log_message)."\n");
                fclose($fp);
            } else {
                aborting("ERROR opening $log",1);
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

    # end function logic
}

# validate & return a formatted mac address
function ackMac($ack_mac=null) {

    # begin function logic

    if (!empty($ack_mac)) {
        if (preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/', $ack_mac) == 1) {
            $ack_mac=str_replace("-", '',$ack_mac);
            $ack_mac=str_replace(":", '',$ack_mac);
        } else {
            $ack_mac=null;
        }
    } else {
        if (empty($GLOBALS["Ack_Client_Ip"])) {
            $ack_mac=implode('', str_split(substr(md5(mt_rand()), 0, 12), 2)); // random mac
        } else {
            $ack_client_ip_md5=md5($GLOBALS["Ack_Client_Ip"]);
            $ack_mac=substr($ack_client_ip_md5,0,12);
        }
    }

    $ack_mac=trim($ack_mac);
    $ack_mac=strtolower($ack_mac);

    return $ack_mac;

    # end function logic

}

function ackPath($ack_dir=null) {

    # begin function logic

    $path=array();
    if (!empty($_SERVER["path"])) {
        $path=explode(":",$_SERVER["PATH"]);
    }

    $ack_path="";

    if (is_dir($ack_dir)) {

        $ack_files=array_diff(scandir($ack_dir), array('..', '.'));
        foreach($ack_files as $ack_file) {
            if ($ack_file == "bin" || $ack_file == "sbin") {
                if (is_dir($ack_dir."/".$ack_file)) {
                    if (!in_array($ack_dir."/".$ack_file,$path)) {
                        $ack_path.=$ack_dir."/".$ack_file.":";
                    }
                    continue;
                }
            } else {
                if (is_dir($ack_dir."/".$ack_file."/bin")) {
                    if (!in_array($ack_dir."/".$ack_file."/bin",$path)) {
                        $ack_path.=$ack_dir."/".$ack_file."/bin:";
                    }
                    continue;
                } else {
                    if (is_dir($ack_dir."/".$ack_file."/sbin")) {
                        if (!in_array($ack_dir."/".$ack_file."/sbin",$path)) {
                            $ack_path.=$ack_dir."/".$ack_file."/sbin:";
                        }
                        continue;
                    }
                }
            }
        }
    }

    $ack_required_paths=array(
        "/bin",
        "/usr/bin",
        "/sbin",
        "/usr/sbin",
        "./",
    );
    foreach ($ack_required_paths as $ack_required_path) {
        $ack_path.=$ack_required_path.":";
    }

    if (!empty($path)) {
        $ack_path_exploded=explode(":",$ack_path);
        foreach ($path as $pathdir) {
            if (!in_array($pathdir,$ack_path_exploded)) {
                $ack_path.=$pathdir.":";
            }
        }
    }

    $ack_path=trim($ack_path,":");

    putenv("PATH=$ack_path");

    return $ack_path;

    # end function logic

}

function ackRevoke() {

    # begin function logic

    global $sourcedir;
    $revoke_date=strtotime(date("Y-m-d H:i:s"));
    $time=strtotime('now');
    $files=scandir($sourcedir);
    foreach($files as $file) {
        if (stristr($file,":")) {
            $revoke_status=ackConfigGet("revoke",$sourcedir.$file);
            if ($revoke_status != "revoked") {
                $revoke_time=strtotime($revoke_status);
                if ($revoke_time < $revoke_date) {
                    $ack_config=array("revoke = revoked","authorized = false");
                    ackConfigSet($ack_config,$sourcedir.$file);
                }
            }
        }
    }

    # end function logic

}

function ackTemplate($template_name=null, $template_content=false, $template_uri=null) {

    # begin function logic

    global $Debug;

    if ($template_name == null || $template_name == '') {
        return null;
    }

    $preg_matches=array("/^\//");
    if (preg_match($preg_matches,$template_uri))
    $Debug->debugValue("template_uri",8,$template_uri);

    $template_found=null;

    if (is_readable($template_name)) {

        // an exact match was found

        $template_found=$template_name;

    } else {

        // search sepcific directories

        $template_file=null;
        $template_files=array();

        if (!empty($GLOBALS["Ack_Client_Mac"])) {
            array_push($template_files, $GLOBALS["Ack_Client_Mac"]."/".$template_name);
            array_push($template_files, $GLOBALS["Ack_Client_Mac"]."/".$template_name."-".$GLOBALS["Ack_Client_Mac"]);
            array_push($template_files, $template_name."-".$GLOBALS["Ack_Client_Mac"]);
        }

        array_push($template_files, $template_name); // last on the stack

        if (!empty($GLOBALS["Ack_Etc_Dirs"]) && is_array($GLOBALS["Ack_Etc_Dirs"])) {
            foreach ($template_files as $template_file) {
                $Debug->debugValue("template_file",8,$template_file);
                foreach ($GLOBALS["Ack_Etc_Dirs"] as $ack_etc_dir) {
                    $template_tmp=$ack_etc_dir."/".$template_file;
                    if (is_readable($template_tmp) && filesize($template_tmp) >= 1) {
                        $template_found=realpath($template_tmp);
                        break;
                    }
                }
                if ($template_found != null) {
                    break;
                }
            }
        }
    }

    $Debug->debugValue("template_found",8,$template_found);

    if (empty($template_content)) {

        // only return the template found (or null)

        return $template_found;

    } else {

        // return the contents of the template found with globals replaced (or null)

        if ($template_found == null) {
            return $template_found;
        } else {

            $template_contents=null;

            $template_contents=file_get_contents($template_found);
            if ($template_contents !== false) {
                $template_contents=ackGlobalsReplace($template_contents);
            }

            $template_contents=trim($template_contents);

            if (empty($template_contents)) {
                $template_contents=null;
            }

            return $template_contents;
        }

    }

    # end function logic

}

function ackTimestamp($timestamp_message=null,$timestamp_force=true) {

    # begin function logic

    if ($timestamp_force) {
        if ($timestamp_message != null && $timestamp_message != "") {
            $timestamp_message=date("Y-m-d H:i:s")." : ".$timestamp_message;
        } else {
            $timestamp_message=date("Y-m-d H:i:s");
        }
    }
    return $timestamp_message;

    # end function logic

}

function ackUuid() {

    # begin function logic

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

    # end function logic

}

#
# Main Logic
#

# debug the $_SERVER variables
if (!empty($_SERVER) && is_array($_SERVER)) {
    $Debug->debug("----------------> _SERVER key/value pairs",50);
    foreach ($_SERVER as $_SERVER_KEY => $_SERVER_VALUE) {
        $Debug->debugValue("$_SERVER_KEY",50,$_SERVER_VALUE);
    }
    $Debug->debug("----------------< _SERVER key/value pairs",50);
}

# Global (defaults)

if (empty($_SERVER["Ack_0"])) {
    if (!empty($_SERVER["SCRIPT_FILENAME"])) {
        $Ack_0=$_SERVER["SCRIPT_FILENAME"];
    } else {
        $Ack_0=__FILE__;
    }
} else {
    $Ack_0=$_SERVER["Ack_0"];
}
$Ack_0=realpath($Ack_0);

$Ack_Basename=basename($Ack_0);

if (strpos($Ack_Basename,"-") !== false) {
    $Ack_Basename_Exploded=explode("-",$Ack_Basename);
    if (isset($Ack_Basename_Exploded[0]) && $Ack_Basename_Exploded[0] == 'ack') {
        $Ack_Basename_Shift=array_shift($Ack_Basename_Exploded);
        $Ack_Component=implode('-',$Ack_Basename_Exploded);
    }
    unset($Ack_Basename_Exploded, $Ack_Basename_Shift);
}

if (!isset($Ack_Component)) {
    $Ack_Component=$Ack_Basename;
}

# assuming this file is in include/ (or another ack subdirectory)
$Ack_Dir=dirname(dirname(realpath(__FILE__)));

$Ack_Dirname=dirname($Ack_0);

$Ack_Aaa_Dir=$Ack_Dir ."/aaa";

$Ack_Etc_Dir=$Ack_Dir . "/etc";

$Ack_Etc_Dirs=array(
    "/etc/ack",
    "/etc",
    $Ack_Etc_Dir,
);

$Ack_Aaa_Cache=$Ack_Aaa_Dir."/aaa.cache";

$Ack_Authorized_Ips=array(
    "127.0.0.0/8",
    "172.22.0.0/16",
    "192.168.0.0/24",
    "10.0.0.0/8",
);

$Ack_Client_Aaa=$Ack_Aaa_Dir."/000000000000";

$Ack_Client_Anaconda=false;

$Ack_Client_Architecture=null;

$Ack_Client_Authorized=false;

$Ack_Client_Bootproto="dhcp";

$Ack_Client_Hostname=null;

$Ack_Client_Install_Uri=null;

$Ack_Client_Install_Url=null;

$Ack_Client_Ip=null;

$Ack_Client_Ip_0_Interface=null;

$Ack_Client_Ip_0_Address=null;

$Ack_Client_Log_Level="INFO";

$Ack_Client_Mac=null;

$Ack_Client_Mac_0_Interface=null;

$Ack_Client_Mac_0_Address=null;

$Ack_Client_Password=null;

$Ack_Client_Ppi=false;

$Ack_Client_Serial_Number=null;

$Ack_Client_System_Release=null;

$Ack_Client_Template_Kickstart=null;

$Ack_Client_Type=null;

if (is_readable($Ack_Dir . "/etc/ack-entity")) {
    $Ack_Entity=trim(file_get_contents($Ack_Dir . "/etc/ack-entity"));
} else {
    $Ack_Entity="anonymous";
}

// lowest metric? ipv4 or ipv6 ??
$Ack_Default_Ip_Address=null;
$Ack_Default_Ip_Cidr=null;
$Ack_Default_Ip_Interface=null;
$Ack_Default_Ip_Destination=null;
$Ack_Default_Ip_Gateway=null;
$Ack_Default_Ip_Mac=null;
$Ack_Default_Ip_Netmask=null;

$Ack_Interfaces=array();
if (is_readable("/sys/class/net") && is_dir("/sys/class/net")) {
    $Ack_Interfaces=array_diff(scandir("/sys/class/net"), array('..', '.'));
}

if (empty($_SERVER["Ack_User"])) {
    $Ack_User="root";
} else {
    $Ack_User=$_SERVER["Ack_User"];
}

if (empty($_SERVER["Ack_Group"])) {
    $Ack_Group="root";
} else {
    $Ack_Group=$_SERVER["Ack_Group"];
}

$Ack_Ipv4_Default_Address=null;
$Ack_Ipv4_Default_Cidr=null;
$Ack_Ipv4_Default_Interface=null;
$Ack_Ipv4_Default_Destination=null;
$Ack_Ipv4_Default_Gateway=null;
$Ack_Ipv4_Default_Mac=null;
$Ack_Ipv4_Default_Netmask=null;

$Ack_Ipv6_Default_Address=null;
$Ack_Ipv6_Default_Cidr=null;
$Ack_Ipv6_Default_Interface=null;
$Ack_Ipv6_Default_Destination=null;
$Ack_Ipv6_Default_Gateway=null;
$Ack_Ipv6_Default_Mac=null;
$Ack_Ipv6_Default_Netmask=null;

$Ack_Id="base";

if (is_readable($Ack_Dir . "/etc/ack-label")) {
    $Ack_Label=trim(file_get_contents($Ack_Dir . "/etc/ack-label"));
}
if (empty($Ack_Label)) {
    $Ack_Label="ack";
}

$Ack_Install_Server="localhost";

$Ack_Install_Servers="debug";

$Ack_Log_Dir=$Ack_Dir . "/log";
if (!is_writable($Ack_Log_Dir)) {
    $Ack_Log_Dir="/tmp";
}

$Ack_Log=$Ack_Log_Dir."/".$Ack_Label.".log";

$Ack_Media_Dir=$Ack_Dir . "/media";
if (!is_readable($Ack_Media_Dir)) {
    $Ack_Media_Dir="/var/tmp";
}

$Ack_Build_Dir=$Ack_Media_Dir . "/build";
if (!is_writable($Ack_Build_Dir)) {
    $Ack_Build_Dir="/var/tmp";
}

$Ack_Distribution_Dir=$Ack_Media_Dir . "/distribution";
if (!is_writable($Ack_Distribution_Dir)) {
    $Ack_Distribution_Dir="/var/tmp";
}

$Ack_Release_Dir=$Ack_Media_Dir . "/release";
if (!is_writable($Ack_Release_Dir)) {
    $Ack_Release_Dir="/var/tmp";
}

$Ack_Virt_Dir=$Ack_Media_Dir . "/vm";
if (!is_writable($Ack_Virt_Dir)) {
    $Ack_Virt_Dir="/var/tmp";
}

$Ack_Privacy=$Ack_Etc_Dir."/privacy";

$Ack_Ssh_Authorized_Key_Files=array();
if (is_readable($Ack_Etc_Dir."/ack-authorized_keys")) {
    array_push($Ack_Ssh_Authorized_Key_Files,$Ack_Etc_Dir."/ack-authorized_keys");
}

$Ack_Ssh_Authorized_Keys=array();
foreach ($Ack_Ssh_Authorized_Key_Files as $Ack_Ssh_Authorized_Key_File) {
    if (is_readable($Ack_Ssh_Authorized_Key_File)) {
        foreach(file($Ack_Ssh_Authorized_Key_File) as $line) {
            array_push($Ack_Ssh_Authorized_Keys,$line);
        }
        unset($line);
    }
}
# unique keys, only
$Ack_Ssh_Authorized_Keys=array_unique($Ack_Ssh_Authorized_Keys);
usort($Ack_Ssh_Authorized_Keys, "strcasecmp");

$Ack_Uuid=ackUuid();

# These are http headers set by Anaconda, e.g.
#
# HTTP_X_ANACONDA_ARCHITECTURE=x86_64
# HTTP_X_ANACONDA_SYSTEM_RELEASE=CentOS
# HTTP_X_RHN_PROVISIONING_MAC_0=eth0 00:0c:29:2f:64:ad
# HTTP_X_SYSTEM_SERIAL_NUMBER=VMware-56 4d c1 86 1e 45 27 53-0b 17 58 e1 06 2f 64 ad

if (isset($_SERVER["HTTP_X_ANACONDA_ARCHITECTURE"])) {
    $Ack_Client_Architecture=trim($_SERVER["HTTP_X_ANACONDA_ARCHITECTURE"]);
}
if (empty($Ack_Client_Architecture)) {
    $Ack_Client_Architecture=null;
} else {
    $Ack_Client_Anaconda=true;
}

if (isset($_SERVER["HTTP_X_ANACONDA_SYSTEM_RELEASE"])) {
    $Ack_Client_System_Release=trim($_SERVER["HTTP_X_ANACONDA_SYSTEM_RELEASE"]);
}
if (empty($Ack_Client_System_Release)) {
    $Ack_Client_System_Release=null;
} else {
    $Ack_Client_Anaconda=true;
}

if (isset($_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"])) {
    if (strpos($_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"]," ") !== false) {
        $Ack_Client_Provisioning_Mac_0=explode(" ",$_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"]);
        if (isset($Ack_Client_Provisioning_Mac_0[0])) $Ack_Client_Mac_0_Interface=trim($Ack_Client_Provisioning_Mac_0[0]);
        if (isset($Ack_Client_Provisioning_Mac_0[1])) $Ack_Client_Mac_0_Address=trim($Ack_Client_Provisioning_Mac_0[1]);
        unset($Ack_Client_Provisioning_Mac_0);
    }
    $Ack_Client_Anaconda=true;
}
if (empty($Ack_Client_Mac_0_Address)) {
    $Ack_Client_Mac_0_Address=null;
} else {
    $Ack_Client_Anaconda=true;
}
if (empty($Ack_Client_Mac_0_Interface)) {
    $Ack_Client_Mac_0_Interface=null;
} else {
    $Ack_Client_Anaconda=true;
}

if (isset($_SERVER["HTTP_X_SYSTEM_SERIAL_NUMBER"])) {
    $Ack_Client_Serial_Number=trim($_SERVER["HTTP_X_SYSTEM_SERIAL_NUMBER"]);
    $Ack_Client_Anaconda=true;
}
if (empty($Ack_Client_Serial_Number)) {
    $Ack_Client_Serial_Number=null;
} else {
    $Ack_Client_Anaconda=true;
}

# These are http headers set by ack-ppi, e.g.
#
# HTTP_X_ACK_PROVISIONING_IP_0=eth0 1.2.3.4

if (isset($_SERVER["HTTP_X_ACK_PROVISIONING_IP_0"])) {
    if (strpos($_SERVER["HTTP_X_ACK_PROVISIONING_IP_0"]," ") !== false) {
        $Ack_Client_Provisioning_Ip_0=explode(" ",$_SERVER["HTTP_X_ACK_PROVISIONING_IP_0"]);
        if (isset($Ack_Client_Provisioning_Ip_0[0])) $Ack_Client_Ip_0_Interface=trim($Ack_Client_Provisioning_Ip_0[0]);
        if (isset($Ack_Client_Provisioning_Ip_0[1])) $Ack_Client_Ip_0_Address=trim($Ack_Client_Provisioning_Ip_0[1]);
        unset($Ack_Client_Provisioning_Ip_0);
    }
    $GLOBALS["Ack_Client_Anaconda"]=true;
}
if (empty($Ack_Client_Ip_0_Address)) {
    $Ack_Client_Ip_0_Address=null;
} else {
    $Ack_Client_Ppi=true;
}
if (empty($Ack_Client_Ip_0_Interface)) {
    $Ack_Client_Ip_0_Interface=null;
} else {
    $Ack_Client_Ppi=true;
}

# These are server variables that are automatically set, or could be set via the environment

// this will be the value of the client's NAT IP, if the client is NAT'ing.  otherwise it will match Ack_Client_Ip_0_Address or be null
if (!empty($_SERVER["REMOTE_ADDR"])) {
    $Ack_Client_Ip=trim($_SERVER["REMOTE_ADDR"]);
} else {
    if (!empty($Ack_Client_Ip_0_Address)) {
        $Ack_Client_Ip=$Ack_Client_Ip_0_Address;
    } else {
        $Ack_Client_Ip=null;
    }
}

# These are request variables that can be set by GET/POST

// Notes:
//
// * _REQUEST_lower should always override (what's set via) http headers
// * _POST_lower should always override _GET_lower


if (!empty($_GET_lower["install"])) {
    $Ack_Client_Install_Uri=$_GET_lower["install"];
}
if (!empty($_POST_lower["install"])) {
    $Ack_Client_Install_Uri=$_POST_lower["install"];
}
if (empty($Ack_Client_Install_Uri)) {
    $Ack_Client_Install_Uri=null;
}

if (isset($_GET_lower["mac"])) {
    $Ack_Client_Mac=ackMac($_GET_lower["mac"]);
}
if (isset($_POST_lower["mac"])) {
    $Ack_Client_Mac=ackMac($_POST_lower["mac"]);
}
if (empty($Ack_Client_Mac) && !empty($Ack_Client_Mac_0_Address)) {
    $Ack_Client_Mac=ackMac($Ack_Client_Mac_0_Address);
} else {
    if (empty($Ack_Client_Mac)) {
        $Ack_Client_Mac=ackMac();
    }
}

if (!empty($Ack_Client_Mac)) {
    $Ack_Client_Aaa=$Ack_Aaa_Dir."/".$Ack_Client_Mac;
}

if (isset($_GET_lower["hostname"])) {
    $Ack_Client_Hostname=$_GET_lower["hostname"];
}
if (isset($_POST_lower["hostname"])) {
    $Ack_Client_Hostname=$_POST_lower["hostname"];
}
if (empty($Ack_Client_Hostname)) {
    $Ack_Client_Hostname=$Ack_Client_Mac;
}

# variables used for replacements

if  (isset($_SERVER["HTTP_HOST"])) {
    $Ack_Install_Server=trim($_SERVER["HTTP_HOST"]);
    if (stristr($Ack_Install_Server,".")) {
        $Ack_Domain=trim(substr($Ack_Install_Server,strpos($Ack_Install_Server,".")+1));
        if (!empty($Ack_Domain)) {
            $Ack_Install_Servers=$Ack_Domain." ".$Ack_Install_Servers;
        }
    }
} else {
    if (is_readable($Ack_Dir . "/etc/ack-domain")) {
        $Ack_Domain=trim(file_get_contents($Ack_Dir . "/etc/ack-domain"));
        $Ack_Install_Server=$Ack_Label.".".$Ack_Domain;
    } else {
        $Ack_Domain="localdomain";
        $Ack_Install_Server="localhost";
    }
}

if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == strtolower("on")) {
    $Ack_Install_Server_Url="https://".$Ack_Install_Server."/";
} else {
    $Ack_Install_Server_Url="http://".$Ack_Install_Server."/";
}

$Ack_Client_Install_Url=$Ack_Install_Server_Url;
if (!empty($Ack_Client_Install_Uri)) {
    // TODO; debt
    if (preg_match("/-kickstart$/",$Ack_Client_Install_Uri)) {
        // strip off the /<IP>-kickstart suffix
        $Ack_Client_Install_Uri=dirname($Ack_Client_Install_Uri);
    }
    if (is_readable($Ack_Media_Dir."/".$Ack_Client_Install_Uri."/TRANS.TBL")) {
        $Ack_Client_Install_Url=$Ack_Install_Server_Url.str_replace("//","/","/media/".$Ack_Client_Install_Uri);
    }
}

// the root password (is the lowercase mac address without the colons)
$Ack_Client_Password=crypt($Ack_Client_Mac, '$6$ackM');

$Debug->debugValue($Ack_Client_Install_Uri,5);
$Ack_Client_Template_Kickstart=ackTemplate("ack-template-kickstart",false,$Ack_Client_Install_Uri);

// TODO crude, improve (use ip -4 -o r s) ... ipv4 set default interface globals
if (is_readable("/proc/net/route")) {
    foreach(file("/proc/net/route") as $route) {
        $route_explode=explode("\t",$route);
        if (isset($route_explode[1]) && $route_explode[1] == "00000000") {
            $Debug->debugValue("route",15);
            $Debug->debugValue("route_explode",15);
            if (isset($route_explode[0])) { // Iface
                $Ack_Ipv4_Default_Interface=trim($route_explode[0]);
            }
            if (isset($route_explode[1])) { // Destination
                $Ack_Ipv4_Default_Destination=ackIpv4($route_explode[1],true,true,true);
            }
            if (isset($route_explode[2])) { // Gateway
                $Ack_Ipv4_Default_Gateway=ackIpv4($route_explode[2],true,true,true);
            }
            #if (isset($route_explode[3])) { // Flags
            #if (isset($route_explode[4])) { // Use
            #if (isset($route_explode[5])) { // Metric
            #if (isset($route_explode[6])) { // Mask
            #if (isset($route_explode[7])) { // MTU
            #if (isset($route_explode[8])) { // Window
            #if (isset($route_explode[9])) { // IRTT
        }
    }
    unset($route, $route_explode, $route_ip_hex, $route_ip_dec);
}

if (!empty($Ack_Ipv4_Default_Interface)) {
    $Ack_Ipv4_Default_Address=ackIpv4Address($Ack_Ipv4_Default_Interface,true,false,false);
    $Ack_Ipv4_Default_Cidr=ackIpv4Address($Ack_Ipv4_Default_Interface,false,true,false);
    $Ack_Ipv4_Default_Netmask=ackIpv4Address($Ack_Ipv4_Default_Interface,false,false,true);
    if (is_readable("/sys/class/net/".$Ack_Ipv4_Default_Interface."/address")) {
        $Ack_Ipv4_Default_Mac=file_get_contents("/sys/class/net/".$Ack_Ipv4_Default_Interface."/address");
    }
}

// prefer ipv4, for now ...

if (!empty($Ack_Ipv4_Default_Address) && empty($Ack_Default_Address)) {
    $Ack_Default_Ip_Address=$Ack_Ipv4_Default_Address;
    $Ack_Default_Ip_Cidr=$Ack_Ipv4_Default_Cidr;
    $Ack_Default_Ip_Interface=$Ack_Ipv4_Default_Interface;
    $Ack_Default_Ip_Destination=$Ack_Ipv4_Default_Destination;
    $Ack_Default_Ip_Gateway=$Ack_Ipv4_Default_Gateway;
    $Ack_Default_Ip_Mac=$Ack_Ipv4_Default_Mac;
    $Ack_Default_Ip_Netmask=$Ack_Ipv4_Default_Netmask;
}

// TODO add (use ip -6 -o r s) ... set ipv6 default interface globals


if (!empty($Ack_Ipv6_Default_Address) && empty($Ack_Default_Address)) {
    $Ack_Default_Ip_Address=$Ack_Ipv6_Default_Address;
    $Ack_Default_Ip_Cidr=$Ack_Ipv6_Default_Cidr;
    $Ack_Default_Ip_Interface=$Ack_Ipv6_Default_Interface;
    $Ack_Default_Ip_Destination=$Ack_Ipv6_Default_Destination;
    $Ack_Default_Ip_Gateway=$Ack_Ipv6_Default_Gateway;
    $Ack_Default_Ip_Mac=$Ack_Ipv6_Default_Mac;
    $Ack_Default_Ip_Netmask=$Ack_Ipv6_Default_Netmask;
}

# log some stuff ...

if ($Ack_Client_Anaconda) {
    $Ack_Client_Type="anaconda";
    if ($Ack_Client_Ppi) {
        $Ack_Client_Type.=" & ack-ppi";
    }
} else {
    if ($Ack_Client_Ppi) {
        $Ack_Client_Type="ack-ppi";
        $Ack_Client_Log_Level="WARNING";
    } else {
        $Ack_Client_Type="unknown";
        $Ack_Client_Log_Level="ERROR";
    }
}

$Ack_Path=ackPath($Ack_Dir);

$Ack_Timezone=$Default_Timezone;

if (filter_var($Ack_Client_Ip, FILTER_VALIDATE_IP)) {
    ackLog($Ack_Client_Ip." is an $Ack_Client_Type client (Ack_Client_Aaa=".$Ack_Client_Aaa.")","INFO");
    ackLog($Ack_Client_Ip." is an $Ack_Client_Type client (Ack_Client_Architecture=".$Ack_Client_Architecture.")","INFO");
    ackLog($Ack_Client_Ip." is an $Ack_Client_Type client (Ack_Client_Ip=".$Ack_Client_Ip.")","INFO");
    ackLog($Ack_Client_Ip." is an $Ack_Client_Type client (Ack_Client_Ip_0_Address=".$Ack_Client_Ip_0_Address.")","INFO");
    ackLog($Ack_Client_Ip." is an $Ack_Client_Type client (Ack_Client_Ip_0_Interface=".$Ack_Client_Ip_0_Interface.")","INFO");
    ackLog($Ack_Client_Ip." is an $Ack_Client_Type client (Ack_Client_Mac=".$Ack_Client_Mac.")","INFO");
    ackLog($Ack_Client_Ip." is an $Ack_Client_Type client (Ack_Client_Mac_0_Address=".$Ack_Client_Mac_0_Address.")","INFO");
    ackLog($Ack_Client_Ip." is an $Ack_Client_Type client (Ack_Client_Mac_0_Interface=".$Ack_Client_Mac_0_Interface.")","INFO");
    ackLog($Ack_Client_Ip." is an $Ack_Client_Type client (Ack_Client_Serial_Number=".$Ack_Client_Serial_Number.")","INFO");
    ackLog($Ack_Client_Ip." is an $Ack_Client_Type client (Ack_Client_System_Release=".$Ack_Client_System_Release.")","INFO");
}

?>
