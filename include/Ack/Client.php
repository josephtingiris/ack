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

        $architecture=$this->clientAnacondaArchitecture();
        if (!empty($architecture)) {
            $anaconda = true;
        }

        $system_release=$this->clientAnacondaSystemRelease();
        if (!empty($system_release)) {
            $anaconda = true;
        }

        $provisioning_macs=$this->clientRHNProvisioningMACs();
        if (!empty($provisioning_macs)) {
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
        $ack_index_header.=" aNAcONDA kICKSTART";
        if (!empty($Ack->Label)) {
            $ack_index_header.=" (".$Ack->Label.")";
        }
        $ack_index_header.=" - Install Server [".$Ack->Install_Server."]";
        if (!empty($Ack->Client_Debug)) {
            $ack_index_header.=" - [DEBUG=".$Ack->Client_Debug."]";
        }
        $ack_index_header.="\n";
        $ack_index_header.="#";
        $ack_index_header.="\n";
        $ack_index_header.="# [".  date("Y-m-d H:i:s") ."]";
        $ack_index_header.=" - client";
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
        $ack_index_footer.=" - client";
        if (!empty($Ack->Client_IP)) {
            $ack_index_footer.=" - ".$Ack->Client_IP;
        }
        if (!empty($Ack->Client_IP_Address_0) && $Ack->Client_IP_Address_0 != $Ack->Client_IP) {
            $ack_index_footer.=" - ".$Ack->Client_IP_Address_0;
        }
        if (!empty($Ack->Client_MAC)) {
            $ack_index_footer.=" - ".$Ack->Client_MAC;
        }
        $ack_index_footer.=" - finish";
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

        if (empty($include_contents)) {
            return;
        }

        if ($Ack->is_debug()) {
            $include_basename=basename($include_file);
            $include_contents.="\n\n# rm -f /tmp/$include_basename; curl --write-out %{http_code} --silent -k \"".$Ack->Client_Install_URL."/?include=$include_basename&build=".$Ack->Client_Build_URI."&install=".$Ack->Client_Install_URI;
            if ($Ack->Client_Recovery == 0) {
                $include_contents.="&recovery";
            }
            $include_contents.="&debug=10\" -o /tmp/$include_basename; bash /tmp/$include_basename\n";
        }

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

        $client_kickstart_template="\n\n# [".  date("Y-m-d H:i:s") ."]";
        $client_kickstart_template.="- template $template_file - begin\n\n\n";

        if (preg_match("/##ACK_PPI##/i",$client_kickstart_template_contents)) {

            $client_kickstart_include=null;
            $client_kickstart_include_ppi=null;

            if (is_readable($Ack->Client_Kickstart_Include_PPI)) {
                $client_kickstart_include=$this->clientKickstartInclude($Ack->Client_Kickstart_Include_PPI);

                if (!empty($client_kickstart_include)) {

                    # a hack; only include smartpart if the pre include has it
                    if (strstr($client_kickstart_include,"/tmp/smartpart")) {
                        $client_kickstart_include_pre="%include /tmp/smartpart\n\n";
                    } else {
                        $client_kickstart_include_pre=null;
                    }

                    $client_kickstart_include_pre.="%pre --log=/var/tmp/ack.pre.log\n";
                    $client_kickstart_include_pre.=$client_kickstart_include;
                    $client_kickstart_include_pre.="%end\n";
                    $client_kickstart_include_pre.="\n";

                    $client_kickstart_include_post="%post --log=/mnt/sysimage/var/tmp/ack/ack.post.log --nochroot\n";
                    $client_kickstart_include_post.=$client_kickstart_include;
                    $client_kickstart_include_post.="%end\n";
                    $client_kickstart_include_post.="\n";

                    $client_kickstart_include_ppi.=$client_kickstart_include_pre;
                    $client_kickstart_include_ppi.=$client_kickstart_include_post;
                    $client_kickstart_include_ppi.="\n";

                }
            }

            $client_kickstart_template_contents=str_replace("##ACK_PPI##",$client_kickstart_include_ppi,$client_kickstart_template_contents);
        } else {

            // %pre
            if (preg_match("/##ACK_PRE##/i",$client_kickstart_template_contents)) {
                $client_kickstart_include=null;
                $client_kickstart_include_pre=null;

                if (is_readable($Ack->Client_Kickstart_Include_Pre)) {
                    $client_kickstart_include=$this->clientKickstartInclude($Ack->Client_Kickstart_Include_Pre);

                    if (!empty($client_kickstart_include)) {

                        # a hack; only include smartpart if the pre include has it
                        if (strstr($client_kickstart_include,"/tmp/smartpart")) {
                            $client_kickstart_include_pre.="%include /tmp/smartpart\n\n";
                        }

                        $client_kickstart_include_pre.="%pre --log=/var/tmp/ack.pre.log\n";
                        $client_kickstart_include_pre.=$client_kickstart_include;
                        $client_kickstart_include_pre.="%end\n";
                        $client_kickstart_include_pre.="\n";

                    }
                }

                $client_kickstart_template_contents=str_replace("##ACK_PRE##",$client_kickstart_include_pre,$client_kickstart_template_contents);
            }

            // %pre-install
            if (preg_match("/##ACK_PREINSTALL##/i",$client_kickstart_template_contents)) {
                $client_kickstart_include=null;
                $client_kickstart_include_preinstall=null;

                if (is_readable($Ack->Client_Kickstart_Include_Preinstall)) {
                    $client_kickstart_include=$this->clientKickstartInclude($Ack->Client_Kickstart_Include_Preinstall);

                    if (!empty($client_kickstart_include)) {

                        $client_kickstart_include_preinstall.="%pre-install --log=/var/tmp/ack.pre-install.log\n";
                        $client_kickstart_include_preinstall.=$client_kickstart_include;
                        $client_kickstart_include_preinstall.="%end\n";
                        $client_kickstart_include_preinstall.="\n";

                    }
                }

                $client_kickstart_template_contents=str_replace("##ACK_PREINSTALL##",$client_kickstart_include_preinstall,$client_kickstart_template_contents);
            }

            // %post
            if (preg_match("/##ACK_POST##/i",$client_kickstart_template_contents)) {
                $client_kickstart_include=null;
                $client_kickstart_include_post=null;

                if (is_readable($Ack->Client_Kickstart_Include_Post)) {
                    $client_kickstart_include=$this->clientKickstartInclude($Ack->Client_Kickstart_Include_Post);

                    if (!empty($client_kickstart_include)) {
                        $client_kickstart_include_post.="%post --log=/var/tmp/ack.post.log\n";
                        $client_kickstart_include_post.=$client_kickstart_include;
                        $client_kickstart_include_post.="%end\n";
                        $client_kickstart_include_post.="\n";
                    }

                }

                $client_kickstart_template_contents=str_replace("##ACK_POST##",$client_kickstart_include_post,$client_kickstart_template_contents);
            }

        }

        $client_kickstart_template.=$client_kickstart_template_contents;
        $client_kickstart_template.="\n\n# [".  date("Y-m-d H:i:s") ."]";
        $client_kickstart_template.="- template $template_file - end\n";

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

        $provisioning_macs = array();

        # HTTP_X_RHN_PROVISIONING_MAC_0=eth0 00:0c:29:2f:64:ad
        #$_SERVER["HTTP_X_RHN_PROVISIONING_MAC_0"]="br1 00:0c:29:2f:64:ad";
        for ($interface=0; $interface<=10; $interface++) {
            if (isset($_SERVER["HTTP_X_RHN_PROVISIONING_MAC_".$interface])) {
                if (strpos($_SERVER["HTTP_X_RHN_PROVISIONING_MAC_".$interface]," ") !== false) {
                    $provisioning_macs[$interface]=explode(" ",$_SERVER["HTTP_X_RHN_PROVISIONING_MAC_".$interface]);
                }
            } else {
                break;
            }
        }

        return $provisioning_macs;

        /*
         * end function logic
         */
    }

}
