<?php

# 20151025, joseph.tingiris@gmail.com, v0.01

# Global (debug)

if (!empty($_GET) && is_array($_GET)) {
    $_GET_lower = array_change_key_case($_GET, CASE_LOWER);
} else {
    $_GET_lower=array();
}

if (isset($_GET_lower['debug'])) {
    $DEBUG=(int)$_GET_lower['debug'];
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

# Functions

function ackAuthorized($ack_mac=null, $ack_ip=null, $ack_config=null) {

    # begin function logic

    global $Debug;

    $ack_mac=ackMac($ack_mac);

    if (!filter_var($ack_ip, FILTER_VALIDATE_IP)) {
        ackLog("invalid ack_ip '$ack_ip'","WARNING");
        $ack_ip=null;
    }

    if (empty($ack_mac) && empty($ack_ip)) {
        ackLog("unauthorized [NULL ack_mac & ack_ip]","ERROR");
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

            if (!$ack_authorized && ackAuthorizedIp($ack_ip, $ack_authorized_ip)) {
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
            "authorized = true",
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

# if an ip is in a cidr range return true else return false
function ackAuthorizedIp($ip, $cidr) {

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
    if (empty($ack_config_attribute)) return null;

    $ack_config=ackFile($ack_config);
    if (empty($ack_config)) return null;

    $Debug->debug("get ack_config_attribute=$ack_config_attribute, ack_config=$ack_config",22);

    $attribute_found=false;

    $ini_stack=parse_ini_file($ack_config,true);
    if (empty($ini_stack)) return null;

    if (!$attribute_found && is_string($ack_config_attribute)) {
        if (isset($ini_stack[$ack_config_attribute]) || array_key_exists($ack_config_attribute,$ini_stack)) $attribute_found=true;
    }

    if (!$attribute_found && is_array($ack_config_attribute) || is_object($ack_config_attribute)) {
        foreach($ack_config_attribute as $get_attribute) {
            if (is_string($get_attribute)) {
                if (isset($ini_stack[$get_attribute]) || array_key_exists($get_attribute,$ini_stack)) $attribute_found=true;
            }
        }
        unset($get_attribute);
    }

    if (!$attribute_found) return null;

    #print_r($ini_stack);

    if (is_string($ack_config_attribute)) {
        $return_string=null;
        if (is_string($ini_stack[$ack_config_attribute])) {
            $return_string=$ini_stack[$ack_config_attribute];
        } else if (is_array($ini_stack[$ack_config_attribute]) || is_object($ini_stack[$ack_config_attribute])) {
            $return_string=end($ini_stack[$ack_config_attribute]);
        }
        return $return_string;
    }

    if (is_array($ack_config_attribute) || is_object($ack_config_attribute)) {
        if (is_object($ack_config_attribute)) {
            $return_stack=object();
        } else $return_stack=array();

        foreach($ack_config_attribute as $get_attribute) {
            foreach($ini_stack as $ini_attribute => $ini_value) {
                #$Debug->debug("get_attribute=$get_attribute, ini_attribute=$ini_attribute",2);
                if ($ini_attribute == $get_attribute) {
                    $return_stack[$ini_attribute]=$ini_value;
                }
            }
            unset($ini_attribute);
            unset($ini_value);
        }
        unset($get_attribute);

        return $return_stack;
    }

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

    if (isset($_SERVER["SCRIPT_NAME"])) {
        $ack_config_modifier.="[".trim($_SERVER["SCRIPT_NAME"])."]";
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

function ackGlobals() {

    # begin function logic

    global $Debug;
    $ack_globals=$GLOBALS;
    ksort($ack_globals);
    foreach ($ack_globals as $ack_global_key => $ack_global_value) {
        if (stristr($ack_global_key,"ack")) {
            $Debug->debugValue($ack_global_key,25);
        }
    }
    unset($ack_global_key, $ack_global_value);

    # end function logic

}

function ackLog($log_message, $log_level="INFO", $log=null) {

    # begin function logic

    if (empty($log)) {
        if (!empty($GLOBALS["Ack_Log"])) $log=$GLOBALS["Ack_Log"];
    }
    if (empty($log)) $log="/tmp/entry.log";

    $log_message_header="[".$log_level."]";
    $log_message="$log_message_header $log_message";

    $error_log=false;
    if (is_writable(dirname($log))) {
        if (is_writable($log)) {
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
            $ack_mac=trim($ack_mac);
        } else {
            $ack_mac=null;
        }
    }

    return $ack_mac;

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

$Debug = new \josephtingiris\Debug();

# debug the $_SERVER variables
if (!empty($_SERVER) && is_array($_SERVER)) {
    $Debug->debug("----------------> _SERVER key/value pairs",10);
    foreach ($_SERVER as $_SERVER_KEY => $_SERVER_VALUE) {
        $Debug->debug("$_SERVER_KEY = $_SERVER_VALUE", 10);
    }
    $Debug->debug("----------------< _SERVER key/value pairs",10);
}

# Global (defaults)

if (empty($Ack_0)) {
    if (!empty($_SERVER["PHP_SELF"])) {
        $Ack_0=$_SERVER["PHP_SELF"];
    } else {
        $Ack_0=__FILE__;
    }
}
if (is_link($Ack_0)) $Ack_0=readlink($Ack_0);

if (empty($Ack_Php)) {
    $Ack_Php=__FILE__;
}

if (is_readable($Ack_Dir . "/etc/ack-abbreviation")) {
    $Ack_Label=trim(file_get_contents($Ack_Dir . "/etc/ack-label"));
} else {
    $Ack_Label="ack";
}

$Ack_Dir=dirname(dirname(realpath(__FILE__)));

$Ack_Aaa_Dir=$Ack_Dir ."/aaa";

$Ack_Aaa_Cache=$Ack_Aaa_Dir."/aaa.cache";

$Ack_Aaa_Ini=$Ack_Aaa_Dir."/000000000000";

$Ack_Anaconda_Client=false;

$Ack_Authorized_Ips=array(
    "127.0.0.0/8",
    "172.22.0.0/16",
    "192.168.0.0/24",
    "10.0.0.0/8",
);

if (is_readable($Ack_Dir . "/etc/ack-domain")) {
    $Ack_Domain=trim(file_get_contents($Ack_Dir . "/etc/ack-domain"));
} else {
    $Ack_Domain="localdomain";
}

if (is_readable($Ack_Dir . "/etc/ack-entity")) {
    $Ack_Entity=trim(file_get_contents($Ack_Dir . "/etc/ack-entity"));
} else {
    $Ack_Entity="anonymous";
}

$Ack_Etc_Dir=$Ack_Dir . "/etc";

$Ack_Id="base";

$Ack_Log_Dir=$Ack_Dir . "/log";
if (!is_writable($Ack_Log_Dir)) {
    $Ack_Log_Dir="/tmp";
}

$Ack_Log=$Ack_Log_Dir."/".str_replace(".php","",basename(__FILE__)).".php.log";

$Ack_Install_Server="localhost";

$Ack_Install_Servers="debug";

$Ack_Local_Ip="127.0.0.1";

$Ack_Local_Interface="eth0";

$Ack_Local_Mac="000000000000";

$Ack_Privacy=$Ack_Etc_Dir."/privacy";

$Ack_Remote_Ip="";

$Ack_Remote_Interface="";

$Ack_Remote_Mac="";

$Ack_Sn="";

$Ack_Uuid=ackUuid();


# HTTP_X_ANACONDA_ARCHITECTURE=x86_64
# HTTP_X_ANACONDA_SYSTEM_RELEASE=CentOS
# HTTP_X_RHN_PROVISIONING_MAC_0=eth0 00:0c:29:2f:64:ad
# HTTP_X_SYSTEM_SERIAL_NUMBER=VMware-56 4d c1 86 1e 45 27 53-0b 17 58 e1 06 2f 64 ad

if (isset($_SERVER["HTTP_X_ANACONDA_ARCHITECTURE"])) {
    $GLOBALS["Ack_Anaconda_Architecture"]=trim($_SERVER["HTTP_X_ANACONDA_ARCHITECTURE"]);
    $GLOBALS["Ack_Anaconda_Client"]=true;
} else {
    $GLOBALS["Ack_Anaconda_Architecture"]="";
}

if (isset($_SERVER["HTTP_X_ANACONDA_SYSTEM_RELEASE"])) {
    $GLOBALS["ANACONDA_SYSTEM_RELEASE"]=trim($_SERVER["HTTP_X_ANACONDA_SYSTEM_RELEASE"]);
    $GLOBALS["Ack_Anaconda_Client"]=true;
} else {
    $GLOBALS["ANACONDA_SYSTEM_RELEASE"]="";
}

if (isset($_SERVER["HTTP_X_PROVISIONING_IP_0"])) {
    if (strpos($_SERVER["HTTP_X_PROVISIONING_IP_0"]," ") !== false) {
        $PROVISIONING_IP_0=explode(" ",$_SERVER["HTTP_X_PROVISIONING_IP_0"]);
        if (isset($PROVISIONING_IP_0[0])) $GLOBALS["Ack_Local_Interface"]=trim($PROVISIONING_IP_0[0]);
        if (isset($PROVISIONING_IP_0[1])) $GLOBALS["Ack_Local_Ip"]=trim($PROVISIONING_IP_0[1]);
    }
    $GLOBALS["Ack_Anaconda_Client"]=true;
}

if (isset($_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"])) {
    if (strpos($_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"]," ") !== false) {
        $RHN_PROVISIONING_MAC_0=explode(" ",$_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"]);
        if (isset($RHN_PROVISIONING_MAC_0[0])) $GLOBALS["Ack_Local_Interface"]=trim($RHN_PROVISIONING_MAC_0[0]);
        if (isset($RHN_PROVISIONING_MAC_0[1])) $GLOBALS["Ack_Local_Mac"]=trim($RHN_PROVISIONING_MAC_0[1]);
    }
    $GLOBALS["Ack_Anaconda_Client"]=true;
}

if (isset($_GET_lower['mac'])) $GLOBALS["Ack_Local_Mac"]=ackMac($_GET_lower['mac']);
if (isset($_GET_lower['provisioning_mac'])) $GLOBALS["Ack_Local_Mac"]=ackMac($_GET_lower['provisioning_mac']);

if (!empty($_SERVER["REMOTE_ADDR"])) $Ack_Remote_Ip=trim($_SERVER["REMOTE_ADDR"]);

$Ack_Local_Mac=strtolower($Ack_Local_Mac);

$Ack_Aaa_Ini=$Ack_Aaa_Dir."/".strtolower($Ack_Local_Mac);

if (isset($_SERVER["HTTP_X_SYSTEM_SERIAL_NUMBER"])) {
    $GLOBALS["Ack_Sn"]=trim($_SERVER["HTTP_X_SYSTEM_SERIAL_NUMBER"]);
    $GLOBALS["Ack_Anaconda_Client"]=true;
}

if  (isset($_SERVER["SERVER_NAME"])) {
    $GLOBALS["Ack_Install_Server"]=trim($_SERVER["SERVER_NAME"]);
    if (stristr($GLOBALS["Ack_Install_Server"],".")) {
        $GLOBALS["Ack_Domain"]=trim(substr($GLOBALS["Ack_Install_Server"],strpos($GLOBALS["Ack_Install_Server"],".")+1));
        if (!empty($GLOBALS["Ack_Domain"])) {
            $GLOBALS["Ack_Install_Servers"]=$GLOBALS["Ack_Domain"]." ".$GLOBALS["Ack_Install_Servers"];
        }
    }
}

if (stristr($GLOBALS["Ack_Install_Server"],"debug.") || stristr($GLOBALS["Ack_Install_Server"],"dev.") || stristr($GLOBALS["Ack_Install_Server"],"dt.") || stristr($GLOBALS["Ack_Install_Server"],"vm.")) {
    $GLOBALS["Ack_Crypt_Pw"]=crypt("password", '$6$ackD');
} else {
    $GLOBALS["Ack_Crypt_Pw"]=crypt($GLOBALS["Ack_Local_Mac"], '$6$ackM');
}

if ($GLOBALS["Ack_Anaconda_Client"] === false) {
    ackLog($GLOBALS["Ack_Remote_Ip"]." is not an anaconda client","NOTICE");
} else {
    ackLog($GLOBALS["Ack_Remote_Ip"]." is an anaconda client (Ack_Anaconda_Architecture=".$GLOBALS["Ack_Anaconda_Architecture"].")","NOTICE");
    ackLog($GLOBALS["Ack_Remote_Ip"]." is an anaconda client (ANACONDA_SYSTEM_RELEASE=".$GLOBALS["ANACONDA_SYSTEM_RELEASE"].")","NOTICE");
    ackLog($GLOBALS["Ack_Remote_Ip"]." is an anaconda client (Ack_Local_Interface=".$GLOBALS["Ack_Local_Interface"].")","NOTICE");
    ackLog($GLOBALS["Ack_Remote_Ip"]." is an anaconda client (Ack_Local_Ip=".$GLOBALS["Ack_Local_Ip"].")","NOTICE");
    ackLog($GLOBALS["Ack_Remote_Ip"]." is an anaconda client (Ack_Local_Mac=".$GLOBALS["Ack_Local_Mac"].")","NOTICE");
    ackLog($GLOBALS["Ack_Remote_Ip"]." is an anaconda client (Ack_Sn=".$GLOBALS["Ack_Sn"].")","NOTICE");
}

$Debug->debug("Ack_Anaconda_Client = ".$Ack_Anaconda_Client,99);
$Debug->debug("Ack_Anaconda_Architecture = ".$Ack_Anaconda_Architecture,99);
$Debug->debug("ANACONDA_SYSTEM_RELEASE = ".$ANACONDA_SYSTEM_RELEASE,99);

?>
