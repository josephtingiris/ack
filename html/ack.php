<?php $DEBUG=25;

# 20170723, joseph.tingiris@gmail.com

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

# begin Ack.php.include

$Ack_Php="Ack.php";
$Ack_Php_Dir=dirname(__FILE__);
if (is_link($Ack_Php_Dir)) $Ack_Php_Dir=readlink($Ack_Php_Dir);
while (!empty($Ack_Php_Dir) && $Ack_Php_Dir != "/" && is_dir($Ack_Php_Dir)) { # search backwards
    foreach (array($Ack_Php_Dir, $Ack_Php_Dir."/include/debug-php", $Ack_Php_Dir."/include") as $Ack_Php_Source_Dir) {
        $Ack_Php_Source=$Ack_Php_Source_Dir."/".$Ack_Php;
        if (is_readable($Ack_Php_Source)) {
            require_once($Ack_Php_Source);
            break;
        } else {
            unset($Ack_Php_Source);
        }
    }
    if (!empty($Ack_Php_Source)) break;
    $Ack_Php_Dir=dirname("$Ack_Php_Dir");
}
if (empty($Ack_Php_Source)) { echo "$Ack_Php file not found\n"; exit(1); }
unset($Ack_Php_Dir, $Ack_Php);

# end Ack.php.include

# Functions

# Anaconda/Kickstart (AcK)
Function ackIndex() {
global $ACK_LABEL,$CONSOLE,$CNC_SERVER,$ENTITY,$CRYPT_PW,$INSTALL_DOMAIN,$INSTALL_ID,$INSTALL_SERVER,$INSTALL_SERVERS,$LOG_DIR,$LOG_FILE,$ANACONDA_ARCHITECTURE,$ANACONDA_SYSTEM_RELEASE,$PROVISIONING_INTERFACE,$PROVISIONING_MAC,$PROVISIONING_SN,$Ack_Dir, $ACK_CACHE_FILE,$CONFIG_DIR,$Ack_Config,$PROVISIONING_IP_LOCAL,$PROVISIONING_IP_REMOTE,$MEDIA_ALIAS;

if (isset($Ack_Config)) {
    if(stristr($Ack_Config, ":")){
        $Ack_Config =str_replace(":","",$Ack_Config);
    }
}

$ack_debug=ackConfigGet("debug",$Ack_Config);
if (empty($ack_debug)) {
    $ack_debug=0;
} else {
    $ack_debug=(int)$ack_debug;
}

#print_r($ack_hostname);
$ack_hostname=ackConfigGet("hostname",$Ack_Config);
if (empty($ack_hostname) && !empty($PROVISIONING_MAC)) {
    $ack_hostname=strtolower(trim(str_replace(":","",$PROVISIONING_MAC)));
    ackConfigSet("hostname=$ack_hostname",$Ack_Config);
} else {
    if (empty($PROVISIONING_MAC)) {
        $ack_hostname="anonymous";
        ackConfigSet("hostname=$ack_hostname",$Ack_Config);
    }
}

echo "#\n";
echo "# aNAcONDA kICKSTART ($ACK_LABEL) - Automatic Install [$PROVISIONING_MAC] - $Ack_Dir ". realpath(__FILE__);;
if ($ack_debug >= 1) echo " [DEBUG=$ack_debug]";
echo "\n";
echo "#\n";
echo "# $ENTITY - $INSTALL_SERVER (".  date("Y-m-d H:i:s") .")\n";
echo "#\n";
echo "\n";

echo "install\n";
echo "\n";

echo "eula --agreed\n";
echo "\n";

echo "services --enabled=NetworkManager,sshd\n";
echo "\n";

# Specifies the language
echo "lang en_US.UTF-8\n";
echo "\n";

# Specifies the keyboard layout
echo "keyboard --vckeymap=us --xlayouts='us'\n";
echo "\n";

# Forces the text installer to be used (saves time)
#echo "text\n";
#echo "\n";

# Forces the cmdline installer to be used (debugging)
#echo "cmdline\n";
#echo "\n";

# Skips the display of any GUI during install (saves time)
echo "skipx\n";
echo "\n";

# Used with an HTTP install to specify where the install files are located
#$media_alias="/media/".$ANACONDA_SYSTEM_RELEASE."-".$ANACONDA_ARCHITECTURE."/"; // the trailing / is annoying
#$media_alias="/media/".$ANACONDA_SYSTEM_RELEASE."-".$ANACONDA_ARCHITECTURE;
if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == strtolower("on")) {
    $url="\"https://".$INSTALL_SERVER.$MEDIA_ALIAS."\" --noverifyssl";
} else $url="\"http://".$INSTALL_SERVER.$MEDIA_ALIAS."\"";
echo "url --url=$url\n";
echo "\n";

# Assign a static IP address upon first boot & set the hostname
echo "network --onboot yes --bootproto dhcp --hostname $ack_hostname\n";
echo "\n";
#echo "network --onboot yes --bootproto dhcp --hostname $ack_hostname --ipv6=off\n";
#echo "\n";

# Give the second interface a DHCP address (if you are not using a second interface comment this line out)
#echo "network --device eth1 --bootproto=dhcp\n";
#echo "\n";

# Set the (pre-install) ssh password
echo "rootpw --iscrypted ".$CRYPT_PW."\n";
echo "\n";

# Set the (install) root password
echo "sshpw --username=root ".$CRYPT_PW." --iscrypted\n";
echo "\n";

# Enable the firewall and open port 22 for SSH remote administration
echo "firewall --enabled --ssh\n";
echo "\n";

# Disable selinux
echo "selinux --disabled\n";
echo "\n";

# Setup security and SELinux levels
echo "authconfig --enableshadow --passalgo=sha512\n";
echo "\n";

# Set the timezone
echo "timezone ".$GLOBALS['Default_Timezone']." --isUtc --ntpservers=pool.ntp.org\n";
echo "\n";

# Do NOT Zero out the MBR
#echo "zerombr\n";
#echo "\n";

# Include the dynamically generated 'smartpart' configuration (created by ack-ks)
echo "%include /tmp/smartpart\n";
echo "\n";

# reboot when installation completes
#echo "reboot\n";
#echo "\n";

# Install the Core software packages, groups like "minimal", or individual package names
echo "%packages\n";
echo "@base\n";
echo "@core\n";
echo "kexec-tools\n";
echo "bind-utils\n";
#echo "epel-release\n"; # this will break older versions of centos 7 installing from Everything media
echo "net-tools\n";
echo "nmap\n";
echo "ntp\n";
echo "ntpdate\n";
echo "php-cli\n";
echo "svn\n";
echo "telnet\n";
echo "vim-enhanced\n";
echo "wget\n";
echo "whois\n";
echo "%end\n";
echo "\n";

$ack_ppi=ackPpi();
if (!empty($ack_ppi)) {
    echo "%pre --log=/var/tmp/install.pre.log\n";
    echo $ack_ppi;
    echo "%end\n";
    echo "\n";
    echo "%post --log=/mnt/sysimage/var/tmp/install/install.post.log --nochroot\n";
    echo $ack_ppi;
    echo "%end\n";
    echo "\n";
}

}

function ackPpi($ppi_file=null) {
    global $ACK_LABEL,$CONSOLE,$CNC_SERVER,$ENTITY,$CRYPT_PW,$INSTALL_DOMAIN,$INSTALL_ID,$INSTALL_SERVER,$INSTALL_SERVERS,$LOG_DIR,$LOG_FILE,$ANACONDA_ARCHITECTURE,$ANACONDA_SYSTEM_RELEASE,$PROVISIONING_INTERFACE,$PROVISIONING_MAC,$PROVISIONING_SN,$Ack_Dir,$ACK_CACHE_FILE,$CONFIG_DIR,$Ack_Config,$PROVISIONING_IP_LOCAL,$PROVISIONING_IP_REMOTE;

    if (empty($ppi_file)) $ppi_file="../sbin/ack-ppi";

    $ppi_return=null;
    if (is_readable($ppi_file)) {

        #
        # get aaa specific config variables
        #

        $aaa_console=ackConfigGet("console",$Ack_Config);
        if (empty($aaa_console)) {
            $aaa_console=$CONSOLE;
        }

        $aaa_debug=ackConfigGet("debug",$Ack_Config);
        if (empty($aaa_debug)) {
            $aaa_debug=0;
        } else {
            $aaa_debug=(int)$aaa_debug;
        }

        $aaa_install_disk_percent=ackConfigGet("install_disk_percent",$Ack_Config);
        if (empty($aaa_install_disk_percent)) {
            $aaa_install_disk_percent=0;
        } else {
            $aaa_install_disk_percent=(int)$aaa_install_disk_percent;
        }
        if (empty($aaa_install_disk_percent) || $aaa_install_disk_percent == 0) $aaa_install_disk_percent=100;

        #
        # get contents of etc authorized keys
        #

        $ssh_authorized_key_files=array("",
            $Ack_Dir."/etc/authorized_keys",
            "");

        $ssh_authorized_keys="";
        foreach ($ssh_authorized_key_files as $ssh_authorized_key_file) {
            if (is_readable($ssh_authorized_key_file)) {
                $ssh_authorized_keys.=trim(file_get_contents($ssh_authorized_key_file));
            }
        }

        # unique keys, only
        $ssh_authorized_keys_stack=explode(PHP_EOL,$ssh_authorized_keys);
        $ssh_authorized_keys_stack=array_unique($ssh_authorized_keys_stack);
        usort($ssh_authorized_keys_stack, "strcasecmp");
        $ssh_authorized_keys=implode("\n",$ssh_authorized_keys_stack);

        #
        # get contents of ppi_file & replace hashes with config variables
        #

        $ppi_return=file_get_contents($ppi_file);
        if ($ppi_return !== false) {
            if ($aaa_debug >= 1) $ppi_return=str_replace("DEBUG=0","DEBUG=$aaa_debug",$ppi_return);
            # make this preg_replace, not just 100 percent
            if ($aaa_install_disk_percent >= 1) $ppi_return=str_replace("INSTALL_DISK_PERCENT=100","INSTALL_DISK_PERCENT=$aaa_install_disk_percent",$ppi_return);
            $ppi_return=str_replace("##ACK_LABEL##",$ACK_LABEL,$ppi_return);
            $ppi_return=str_replace("##CNC_SERVER##",$CNC_SERVER,$ppi_return);
            $ppi_return=str_replace("##CONSOLE##",$aaa_console,$ppi_return);
            $ppi_return=str_replace("##INSTALL_ID##",$INSTALL_ID,$ppi_return);
            $ppi_return=str_replace("##INSTALL_DOMAIN##",$INSTALL_DOMAIN,$ppi_return);
            $ppi_return=str_replace("##INSTALL_SERVER##",$INSTALL_SERVER,$ppi_return);
            $ppi_return=str_replace("##INSTALL_SERVERS##",$INSTALL_SERVERS,$ppi_return);
            $ppi_return=str_replace("##INSTALL_SSH_AUTHORIZED_KEYS##",$ssh_authorized_keys,$ppi_return);

            $ppi_return.="\n";
            $ppi_return.="# rm /tmp/ack-ppi; curl --write-out %{http_code} --silent -k https://$INSTALL_SERVER/?ack-ppi -o /tmp/ack-ppi; bash /tmp/ack-ppi";
            $ppi_return.="\n";
        }
    } else {
        $ppi_return.="\n";
        $ppi_return.="# ack-ppi file not found";
        $ppi_return.="\n";
        ackLog("$ppi_file not found from ".getcwd()."","WARNING");
    }

    return $ppi_return;
}

#
# Main Logic
#

if (isset($GLOBALS["ACK_ENTITY"])) {
    $ENTITY=$GLOBALS["ACK_ENTITY"];
} else {
    if (isset($BASE_COMPANY)) {
        $ENTITY=$BASE_COMPANY;
    } else {
        $ENTITY="Personal Use";
    }
}

$Ack_Dir=dirname(dirname(realpath(__FILE__)));
$CONFIG_DIR=$Ack_Dir."/aaa";

$ACK_CACHE_FILE=$CONFIG_DIR."/aaa.cache";
$Debug->debug(__FILE__.", ACK_CACHE_FILE=$ACK_CACHE_FILE",10);

$Ack_Config=$ACK_CACHE_FILE;

$PROVISIONING_IP_LOCAL="127.0.0.1";
$PROVISIONING_IP_REMOTE="0.0.0.0";

$ETC_DIR=$Ack_Dir."/etc";

$LOG_DIR=$Ack_Dir."/log";
if (!is_writable($LOG_DIR)) $LOG_DIR="/tmp";
$LOG_FILE=$LOG_DIR."/".str_replace(".php","",basename(__FILE__)).".log";
$Debug->debug(__FILE__.", LOG_FILE=$LOG_FILE",10);

$PRIVACY_FILE=$ETC_DIR."/privacy";

ackGlobals();

$Ack_Authorized=ackAuthorized($Ack_Local_Mac,$Ack_Remote_Ip,$Ack_Aaa_Ini);
if ($Ack_Authorized === false) {
    ackLog($Ack_Remote_Ip." is unauthorized [".$Ack_Local_Mac."]","WARNING");
    ackCacheSet(array($Ack_Local_Mac,"unauthorized"),$Ack_Aaa_Cache);
    echo "\nunauthorized";
    exit(1);
} else {
    ackCacheSet(array($Ack_Local_Mac,"authorized"),$Ack_Aaa_Cache);
}

if (is_readable("../etc/ack-label")) {
    $ACK_LABEL=trim(file_get_contents("../etc/ack-label"));
} else {
    $ACK_LABEL="ack";
}

$CNC_SERVER="localhost";
$CONSOLE="tty0";
$CONSOLE="ttyS0,115200n8";
$ANACONDA_CLIENT=false;
$INSTALL_ID="base";
$INSTALL_DOMAIN="localdomain";
$INSTALL_SERVER="localhost";
$INSTALL_SERVERS="debug";
$PROVISIONING_INTERFACE="lo";
$PROVISIONING_MAC="000000000000";
$PROVISIONING_SN="unknown";

# HTTP_X_ANACONDA_ARCHITECTURE=x86_64
# HTTP_X_ANACONDA_SYSTEM_RELEASE=CentOS
# HTTP_X_RHN_PROVISIONING_MAC_0=eth0 00:0c:29:2f:64:ad
# HTTP_X_SYSTEM_SERIAL_NUMBER=VMware-56 4d c1 86 1e 45 27 53-0b 17 58 e1 06 2f 64 ad

$ANACONDA_ARCHITECTURE="x86_64";
if (isset($_SERVER["HTTP_X_ANACONDA_ARCHITECTURE"])) {
    $ANACONDA_ARCHITECTURE=trim($_SERVER["HTTP_X_ANACONDA_ARCHITECTURE"]);
    $ANACONDA_CLIENT=true;
}

$ANACONDA_SYSTEM_RELEASE="CentOS";
if (isset($_SERVER["HTTP_X_ANACONDA_SYSTEM_RELEASE"])) {
    $ANACONDA_SYSTEM_RELEASE=trim($_SERVER["HTTP_X_ANACONDA_SYSTEM_RELEASE"]);
    $ANACONDA_CLIENT=true;
}

$MEDIA_DIRS=array("/media/loop","/media/iso");
$MEDIA_BUILDS=array("Everything-1804","Everything-1503-01"); # first found wins
$MEDIA_VERS=array("7","6");
foreach ($MEDIA_DIRS as $MEDIA_DIR) {
    if (!empty($MEDIA_ALIAS)) break;
    foreach ($MEDIA_BUILDS as $MEDIA_BUILD) {
        if (!empty($MEDIA_ALIAS)) break;
        foreach ($MEDIA_VERS as $MEDIA_VER) {
            $MEDIA_CHECK=$ANACONDA_SYSTEM_RELEASE."-".$MEDIA_VER."-".$ANACONDA_ARCHITECTURE."-".$MEDIA_BUILD;
            // todo, use curl instead ...
            if (is_readable($MEDIA_DIR."/".$MEDIA_CHECK."/.treeinfo")) {
                $MEDIA_ALIAS="/media/".$MEDIA_CHECK;
                break;
            }
        }
    }
}
if (empty($MEDIA_ALIAS)) {
    echo "error, can't find boot media for $ANACONDA_SYSTEM_RELEASE-$ANACONDA_ARCHITECTURE";
    #exit(2);
}

if (isset($_SERVER["HTTP_X_PROVISIONING_IP_0"])) {
    if (strpos($_SERVER["HTTP_X_PROVISIONING_IP_0"]," ") !== false) {
        $PROVISIONING_IP_0=explode(" ",$_SERVER["HTTP_X_PROVISIONING_IP_0"]);
        if (isset($PROVISIONING_IP_0[0])) $PROVISIONING_INTERFACE=trim($PROVISIONING_IP_0[0]);
        if (isset($PROVISIONING_IP_0[1])) $PROVISIONING_IP_LOCAL=trim($PROVISIONING_IP_0[1]);
    }
    $ANACONDA_CLIENT=true;
}

if (isset($_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"])) {
    if (strpos($_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"]," ") !== false) {
        $RHN_PROVISIONING_MAC_0=explode(" ",$_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"]);
        if (isset($RHN_PROVISIONING_MAC_0[0])) $PROVISIONING_INTERFACE=trim($RHN_PROVISIONING_MAC_0[0]);
        if (isset($RHN_PROVISIONING_MAC_0[1])) $PROVISIONING_MAC=trim($RHN_PROVISIONING_MAC_0[1]);
    }
    $ANACONDA_CLIENT=true;
}

if (isset($_SERVER["REMOTE_ADDR"])) $PROVISIONING_IP_REMOTE=trim($_SERVER["REMOTE_ADDR"]);

if (isset($_GET_lower["mac"])) $PROVISIONING_MAC=$_GET_lower["mac"];

if (isset($_GET_lower["provisioning_mac"])) $PROVISIONING_MAC=$_GET_lower["provisioning_mac"];

$PROVISIONING_MAC=strtolower($PROVISIONING_MAC);

$Ack_Config=$CONFIG_DIR."/".strtolower($PROVISIONING_MAC);

if (isset($_SERVER["HTTP_X_SYSTEM_SERIAL_NUMBER"])) {
    $PROVISIONING_SN=trim($_SERVER["HTTP_X_SYSTEM_SERIAL_NUMBER"]);
    $ANACONDA_CLIENT=true;
}

if  (isset($_SERVER["SERVER_NAME"])) {
    $INSTALL_SERVER=trim($_SERVER["SERVER_NAME"]);
}

$CNC_SERVER=str_replace("ack.","cnc.",$INSTALL_SERVER);

if (stristr($INSTALL_SERVER,"debug.") || stristr($INSTALL_SERVER,"dev.") || stristr($INSTALL_SERVER,"dt.") || stristr($INSTALL_SERVER,"qa.") || stristr($INSTALL_SERVER,"vm.")) {
    if (is_readable($ETC_DIR."/password.sha512")) {
        $CRYPT_PW=file_get_contents($ETC_DIR."password.sha512");
    } else {
        $CRYPT_PW=crypt("K1ckst4rt!", '$6$ackD');
    }
    if (empty($CRYPT_PW)) {
        echo "error, bad sha512";
        exit(2);
    }
    $INSTALL_DOMAIN=trim(substr($INSTALL_SERVER,strpos($INSTALL_SERVER,".")+1));
    #$INSTALL_DOMAIN=trim(substr($INSTALL_DOMAIN,strpos($INSTALL_DOMAIN,".")+1));
} else {
    $INSTALL_DOMAIN=trim($INSTALL_SERVER);
    $CRYPT_PW=crypt($PROVISIONING_MAC, '$6$ackP');
}

if (!empty($INSTALL_DOMAIN)) {
    $INSTALL_SERVERS="$INSTALL_DOMAIN $INSTALL_SERVERS";
}

header("Content-Type: text/plain;charset=UTF-8");

if ($ANACONDA_CLIENT === false) {
    ackLog("$PROVISIONING_IP_REMOTE is not an anaconda client","WARNING");
    #echo Private_Property();
} else {
    ackLog("$PROVISIONING_IP_REMOTE is an anaconda client (ANACONDA_ARCHITECTURE=$ANACONDA_ARCHITECTURE)","NOTICE");
    ackLog("$PROVISIONING_IP_REMOTE is an anaconda client (ANACONDA_SYSTEM_RELEASE=$ANACONDA_SYSTEM_RELEASE)","NOTICE");
    ackLog("$PROVISIONING_IP_REMOTE is an anaconda client (PROVISIONING_INTERFACE=$PROVISIONING_INTERFACE)","NOTICE");
    ackLog("$PROVISIONING_IP_REMOTE is an anaconda client (PROVISIONING_IP_LOCAL=$PROVISIONING_IP_LOCAL)","NOTICE");
    ackLog("$PROVISIONING_IP_REMOTE is an anaconda client (PROVISIONING_MAC=$PROVISIONING_MAC)","NOTICE");
    ackLog("$PROVISIONING_IP_REMOTE is an anaconda client (PROVISIONING_SN=$PROVISIONING_SN)","NOTICE");
}

$Debug->debug("ACK_LABEL=$ACK_LABEL",25);
$Debug->debug("CNC_SERVER=$CNC_SERVER",25);
$Debug->debug("CONFIG_DIR=$CONFIG_DIR",25);
$Debug->debug("CONSOLE=$CONSOLE",25);
$Debug->debug("CRYPT_PW=$CRYPT_PW",25);
$Debug->debug("ENTITY=$ENTITY",25);
$Debug->debug("LOG_DIR=$LOG_DIR",25);
$Debug->debug("LOG_FILE=$LOG_FILE",25);
$Debug->debug("ANACONDA_ARCHITECTURE=$ANACONDA_ARCHITECTURE",25);
$Debug->debug("ANACONDA_SYSTEM_RELEASE=$ANACONDA_SYSTEM_RELEASE",25);
$Debug->debug("Ack_Dir=$Ack_Dir",25);
$Debug->debug("ACK_CACHE_FILE=$ACK_CACHE_FILE",25);
$Debug->debug("Ack_Config=$Ack_Config",25);
$Debug->debug("PROVISIONING_INTERFACE=$PROVISIONING_INTERFACE",25);
$Debug->debug("PROVISIONING_IP_LOCAL=$PROVISIONING_IP_LOCAL",25);
$Debug->debug("PROVISIONING_IP_REMOTE=$PROVISIONING_IP_REMOTE",25);
$Debug->debug("PROVISIONING_MAC=$PROVISIONING_MAC",25);
$Debug->debug("PROVISIONING_SN=$PROVISIONING_SN",25);

ackCacheSet(array($PROVISIONING_MAC,$PROVISIONING_SN,$PROVISIONING_INTERFACE,$ANACONDA_ARCHITECTURE,$ANACONDA_SYSTEM_RELEASE),$ACK_CACHE_FILE);

if (isset($_GET['ack-ppi'])) {
    echo ackPpi();
} else {
    if (isset($_GET['init']) && $_GET['init']=="ack") {
        echo date("YmdHis") ."\n";
    } else {
        ackIndex();
    }
}

?>
