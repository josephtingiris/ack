<?php

/**
 * aNAcONDA kICKSTART (ack) [Client]
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
 * The \josephtingiris\Ack\Client class
 */
class Client extends \josephtingiris\Debug
{
    /*
     * public properties.
     */

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

    public function clientProvisioningIPs()
    {
        /*
         * begin function logic
         */

        $provisioning_ips = array();

        # HTTP_X_PROVISIONING_IP_0=eth0 1.2.3.4
        #$_SERVER["HTTP_X_PROVISIONING_IP_0"]="br1 1.2.3.4";
        for ($interface=0; $interface<=10; $interface++) {
            if (isset($_SERVER["HTTP_X_PROVISIONING_IP_".$interface])) {
                if (strpos($_SERVER["HTTP_X_PROVISIONING_IP_".$interface]," ") !== false) {
                    $provisioning_ips[$interface]=explode(" ",$_SERVER["HTTP_X_PROVISIONING_IP_".$interface]);
                }
            } else {
                break;
            }
        }

        return $provisioning_ips;

        /*
         * end function logic
         */
    }

    public function clientAnaconda()
    {
        /*
         * begin function logic
         */

        $anaconda = false;

        if (!empty($this->clientAnacondaArchitecture())) {
            $anaconda = true;
        }

        if (!empty($this->clientAnacondaSystemRelease())) {
            $anaconda = true;
        }

        if (!empty($this->clientRHNProvisioningMACs())) {
            $anaconda = true;
        }

        return $anaconda;

        /*
         * end function logic
         */
    }

    public function clientAnacondaArchitecture()
    {
        /*
         * begin function logic
         */

        $architecture = null;

        # HTTP_X_ANACONDA_ARCHITECTURE=x86_64
        if (isset($_SERVER["HTTP_X_ANACONDA_ARCHITECTURE"])) {
            $architecture=trim($_SERVER["HTTP_X_ANACONDA_ARCHITECTURE"]);
        }

        return $architecture;

        /*
         * end function logic
         */
    }

    public function clientAnacondaSystemRelease()
    {
        /*
         * begin function logic
         */

        $system_release = null;

        # HTTP_X_ANACONDA_SYSTEM_RELEASE=CentOS
        if (isset($_SERVER["HTTP_X_ANACONDA_SYSTEM_RELEASE"])) {
            $system_release=trim($_SERVER["HTTP_X_ANACONDA_SYSTEM_RELEASE"]);
        }

        return $system_release;

        /*
         * end function logic
         */
    }

    public function clientAnacondaSystemSerialNumber()
    {
        /*
         * begin function logic
         */

        $serial_number = null;

        # HTTP_X_SYSTEM_SERIAL_NUMBER=VMware-56 4d c1 86 1e 45 27 53-0b 17 58 e1 06 2f 64 ad
        if (isset($_SERVER["HTTP_X_SYSTEM_SERIAL_NUMBER"])) {
            $serial_number=trim($_SERVER["HTTP_X_SYSTEM_SERIAL_NUMBER"]);
        }

        return $serial_number;

        /*
         * end function logic
         */
    }

    /**
     * output a client kickstart config
     */
    public function clientKickstart()
    {
        /*
         * begin function logic
         */

        $Ack = new \josephtingiris\Ack();

        $this->propertiesPrint();

        $ack_index_header=null;
        $ack_index_header.="#";
        $ack_index_header.="\n";
        $ack_index_header.="#";
        if (!empty($Ack->Entity)) {
            $ack_index_header.=" ".$Ack->Entity;
        }
        $ack_index_header.=" kickstart";
        if (!empty($Ack->Label)) {
            $ack_index_header.=" (".$Ack->Label.")";
        }
        $ack_index_header.=" - Install Server [".$Ack->Install_Server."]";
        $ack_index_header.=" - Template [".$Ack->Client_Kickstart_Template."]";
        if (!empty($Ack->Client_Debug)) {
            $ack_index_header.=" - [DEBUG=".$Ack->Client_Debug."]";
        }
        $ack_index_header.="\n";
        $ack_index_header.="#";
        $ack_index_header.="\n";
        $ack_index_header.="# [".  date("Y-m-d H:i:s") ."]";
        $ack_index_header.=" - config";
        if (!empty($Ack->Client_IP)) {
            $ack_index_header.=" - ".$Ack->Client_IP;
        }
        if (!empty($Ack->Client_IP_Address_0) && $Ack->Client_IP_Address_0 != $Ack->Client_IP) {
            $ack_index_header.=" - ".$Ack->Client_IP_Address_0;
        }
        if (!empty($Ack->Client_MAC)) {
            $ack_index_header.=" - ".$Ack->Client_MAC;
        }
        $ack_index_header.=" - start";
        $ack_index_header.="\n";
        $ack_index_header.="#";
        $ack_index_header.="\n";

        echo $ack_index_header;

        $ack_client_template_kickstart_contents=$this->clientKickstartTemplate($Ack->Client_Kickstart_Template);

        echo $ack_client_template_kickstart_contents;

        #print_r($_SERVER);

        $ack_index_footer=null;
        $ack_index_footer.="\n";
        $ack_index_footer.="\n";
        $ack_index_footer.="#";
        $ack_index_footer.="\n";
        $ack_index_footer.="# [".  date("Y-m-d H:i:s") ."]";
        $ack_index_footer.=" - config";
        if (!empty($Ack->Client_IP)) {
            $ack_index_footer.=" - ".$Ack->Client_IP;
        }
        if (!empty($Ack->Client_IP_Address_0) && $Ack->Client_IP_Address_0 != $Ack->Client_IP) {
            $ack_index_footer.=" - ".$Ack->Client_IP_Address_0;
        }
        if (!empty($Ack->Client_MAC)) {
            $ack_index_footer.=" - ".$Ack->Client_MAC;
        }
        $ack_index_footer.=" - end";
        $ack_index_footer.="\n";
        $ack_index_footer.="#";
        $ack_index_footer.="\n";

        echo $ack_index_footer;

        /*
         * end function logic
         */
    }

    /**
     * output a client kickstart include
     */
    public function clientKickstartInclude($include_file=null)
    {
        /*
         * begin function logic
         */

        if (!is_readable($include_file)) {
            return null;
        }

        $Ack = new \josephtingiris\Ack();

        $include_contents=$Ack->ackKeyReplace(file_get_contents($include_file));

        return $include_contents;

        /*
         * end function logic
         */
    }

    /**
     * output a parsed client kickstart template config
     */
    public function clientKickstartTemplate($template_file=null)
    {
        /*
         * begin function logic
         */

        if (!is_readable($template_file)) {
            return null;
        }

        $Ack = new \josephtingiris\Ack();

        $client_kickstart_template_contents=$Ack->ackKeyReplace(file_get_contents($template_file),true);

        # if necessary, correct UTC timezone
        if (preg_match("/\ntimezone(.*)UTC/i",$client_kickstart_template_contents)) {
            if (!preg_match("/\ntimezone(.*)UTC --utc/i",$client_kickstart_template_contents)) {
                $client_kickstart_template_contents=preg_replace("/\ntimezone UTC/","\ntimezone UTC --utc",$client_kickstart_template_contents);
            }
        }

        $client_kickstart_template="";

        $client_kickstart_template.="\n\n# template $template_file - start\n\n\n";
        if (preg_match("/##ACK_PPI##/i",$client_kickstart_template_contents)) {
            $client_kickstart_include_ppi=$this->clientKickstartInclude($Ack->Client_Kickstart_Include_PPI);
            if (!empty($client_kickstart_include_ppi)) {
                $client_kickstart_include_pre="%pre --log=/var/tmp/ack.pre.log\n";
                $client_kickstart_include_pre.=$client_kickstart_include_ppi;
                $client_kickstart_include_pre.="%end\n";
                $client_kickstart_include_pre.="\n";
                $client_kickstart_include_post="%post --log=/mnt/sysimage/var/tmp/ack/ack.post.log --nochroot\n";
                $client_kickstart_include_post.=$client_kickstart_include_ppi;
                $client_kickstart_include_post.="%end\n";
                $client_kickstart_include_post.="\n";

                $client_kickstart_include_ppi= "%include /tmp/smartpart\n\n";
                $client_kickstart_include_ppi.=$client_kickstart_include_pre;
                $client_kickstart_include_ppi.=$client_kickstart_include_post;
                $client_kickstart_include_ppi.="\n";
                $client_kickstart_include_basename=basename($Ack->Client_Kickstart_Include_PPI);
                $client_kickstart_include_ppi.="\n\n# rm /tmp/$client_kickstart_include_basename; curl --write-out %{http_code} --silent -k https://".$Ack->Install_Server."/?include=$client_kickstart_include_basename&debug=10 -o /tmp/$client_kickstart_include_basename; bash /tmp/$client_kickstart_include_basename\n\n";
            }
            $client_kickstart_template_contents=str_replace("##ACK_PPI##",$client_kickstart_include_ppi,$client_kickstart_template_contents);
        } else {
            if (preg_match("/##ACK_PREINSTALL##/i",$client_kickstart_template_contents)) {
            }
            if (preg_match("/##ACK_POSTINSTALL##/i",$client_kickstart_template_contents)) {
            }
        }

        $client_kickstart_template.=$client_kickstart_template_contents;
        $client_kickstart_template.="\n\n# template $template_file - end\n";

        return $client_kickstart_template;

        /*
         * end function logic
         */
    }

    public function clientRHNProvisioningMACs()
    {
        /*
         * begin function logic
         */

        $provisinging_macs = array();

        # HTTP_X_RHN_PROVISIONING_MAC_0=eth0 00:0c:29:2f:64:ad
        #$_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"]="br1 00:0c:29:2f:64:ad";
        for ($interface=0; $interface<=10; $interface++) {
            if (isset($_SERVER["HTTP_X_RHN_PROVISIONING_MAC_".$interface])) {
                if (strpos($_SERVER["HTTP_X_RHN_PROVISIONING_MAC_".$interface]," ") !== false) {
                    $provisinging_macs[$interface]=explode(" ",$_SERVER["HTTP_X_RHN_PROVISIONING_MAC_".$interface]);
                }
            } else {
                break;
            }
        }

        return $provisinging_macs;

        /*
         * end function logic
         */
    }

}
