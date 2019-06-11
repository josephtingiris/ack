<?php

/**
 * aNAcONDA kICKSTART (ack) [Network]
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
 * The \josephtingiris\Ack\Network class
 */
class Network extends \josephtingiris\Debug
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

        $this->Ack_Log = new \josephtingiris\Ack\Log();
        $this->Ack_Sub = new \josephtingiris\Ack\Sub();

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
     * return valid interface(s), or null
     */
    public function networkInterfaces($interface=null)
    {
        /*
         * begin function logic
         */

        $interfaces=array();

        if (is_readable("/sys/class/net") && is_dir("/sys/class/net")) {
            $sys_class_net=array_diff(scandir("/sys/class/net"), array('..', '.'));
        }

        if (empty($interface)) {
            $interfaces=$sys_class_net;
        } else {
            // TODO; handle single or multiple *given* interfaces
        }

        return $interfaces;

        /*
         * end function logic
         */
    }

    # optionally convert an integer (or hex) to a decimal IP, validate it, & return it (or null)
    public function networkIPv4($ip=null, $int_ip=false, $hex_ip=false, $little_endian=false) {
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
        $this->Ack_Log->logMessage("invalid ack_ip '$ip'","WARNING");
        if (!filter_var($return_ip, FILTER_VALIDATE_IP)) {
            $this->Ack_Log->logMessage("invalid ack_ip '$ip'","WARNING");
            $return_ip=null;
        }

        return $return_ip;
    }

    # return an array with an inteface's address(es), prefix, & mask, or optionally a string of the (first) address, prefix, or mask
    public function networkIPv4Address($interface=null, $address=false, $prefix=false, $mask=false) {

        $interface=trim($interface);
        if (empty($interface)) {
            return null;
        }

        global $Ack;

        $return_address=null;
        $return_addresses=array();
        $return_prefix=null;
        $return_prefixes=array();
        $return_mask=null; // TODO convert prefix to mask
        $return_masks=array();

        $ip_cmd="ip -4 -o addr show $interface";
        $ip_rc=subExec($ip_cmd,$ip_stdout,$ip_stderr);
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
                            list ($ip_address, $ip_prefix) = explode('/', $ip_explode_line[6]);

                            $ip_address=networkIPv4($ip_address);
                            if (!empty($ip_address)) {
                                $Ack->debugValue("ip_address",15,$ip_address);
                                if ($address && !$prefix && !$mask) {
                                    return $ip_address;
                                } else {
                                    $Ack->debugValue($ip_explode_line,15);
                                }
                            }

                            $ip_prefix=trim($ip_prefix);
                            $ip_prefix=(int)$ip_prefix;
                            if (isset($ip_prefix) && is_int($ip_prefix)) {
                                $Ack->debugValue("ip_prefix",15,$ip_prefix);
                                if (!$address && $prefix && !$mask) {
                                    return $ip_prefix;
                                } else {
                                    $Ack->debugValue($ip_explode_line,15);
                                }
                            }
                            if (isset($ip_prefix) && is_int($ip_prefix)) {
                                $Ack->debugValue("ip_prefix",15,$ip_prefix);
                                if (!$address && !$prefix && $mask) {
                                    return networkIPv4Netmask($ip_prefix);
                                } else {
                                    $Ack->debugValue($ip_explode_line,15);
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
            $this->Ack_Log->logMessage("'$ip_cmd' failed","ERROR");
        }
    }

    # if an ip is in a prefix range return true else return false
    public function networkIPv4InRange($ip, $prefix) {

        /*
         * begin function logic
         */

        if (strpos($prefix,"/") !== false) {
            list ($subnet, $bits) = explode('/', $prefix);
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned

            return ($ip & $mask) == $subnet;
        }

        return false;

        /*
         * end function logic
         */

    }

    public function networkIPv4Netmask($ip) {

        if (strpos($ip, '/') !== false) {
            $prefix = substr ($ip, strpos ($ip, '/') + 1) * 1;
        } else {
            $ip = trim($ip);
            $prefix = (int)$ip;
        }

        $netmask = str_split (str_pad (str_pad ('', $prefix, '1'), 32, '0'), 8);

        foreach ($netmask as &$element)
            $element = bindec ($element);

        return join ('.', $netmask);

    }

    public function networkIPv4Route($destination_ip=null, $field=null) {

        if (is_null($destination_ip)) {
            return;
        }

        $destination_ip=gethostbyname($destination_ip);
        if (!filter_var($destination_ip, FILTER_VALIDATE_IP)) {
            $destination_ip="1";
        }

        if (!filter_var($destination_ip, FILTER_VALIDATE_IP) && $destination_ip != "1") {
            return;
        }

        if (is_null($field)) {
            $field=strtolower($field);
        }

        $this->debugValue("destination_ip",22,$destination_ip);
        $this->debugValue("field",22,$field);

        $destination_route=null;
        $return_string=null;

        // fields
        $source_gateway=null;
        $source_interface=null;
        $source_ip=null;
        $source_mac=null;
        $source_netmask=null;
        $source_network=null;
        $source_prefix=null;

        $ip_route_get_cmd="ip -4 -o route get $destination_ip";
        $ip_route_get_rc=$this->Ack_Sub->subExec($ip_route_get_cmd,$ip_route_get_stdout,$ip_route_get_stderr);
        if ($ip_route_get_rc === 0) {
            $ip_route_get_stdout_lines=explode("\n",$ip_route_get_stdout);
            if (!empty($ip_route_get_stdout_lines)) {

                // source interface, gateway, & source
                foreach($ip_route_get_stdout_lines as $ip_route_get_stdout_line) {
                    $this->debug($ip_route_get_stdout_line,24);

                    // source interface
                    $ip_route_get_stdout_line_dev=explode("dev",$ip_route_get_stdout_line);
                    if (isset($ip_route_get_stdout_line_dev[1])) {
                        $ip_route_get_stdout_line_dev=explode(" ",$ip_route_get_stdout_line_dev[1]);
                    }

                    if (isset($ip_route_get_stdout_line_dev[1])) {
                        $this->debug($ip_route_get_stdout_line_dev[1],44);
                        $source_interface=$ip_route_get_stdout_line_dev[1];
                    }

                    // source gateway
                    $ip_route_get_stdout_line_via=explode("via",$ip_route_get_stdout_line);
                    if (isset($ip_route_get_stdout_line_via[1])) {
                        $ip_route_get_stdout_line_via=explode(" ",$ip_route_get_stdout_line_via[1]);
                    }

                    if (isset($ip_route_get_stdout_line_via[1])) {
                        $this->debug($ip_route_get_stdout_line_via[1],44);
                        $source_gateway=$ip_route_get_stdout_line_via[1];
                    }

                    // source ip
                    $ip_route_get_stdout_line_src=explode("src",$ip_route_get_stdout_line);
                    if (isset($ip_route_get_stdout_line_src[1])) {
                        $ip_route_get_stdout_line_src=explode(" ",$ip_route_get_stdout_line_src[1]);
                    }

                    if (isset($ip_route_get_stdout_line_src[1])) {
                        $this->debug($ip_route_get_stdout_line_src[1],44);
                        $source_ip=$ip_route_get_stdout_line_src[1];
                    }

                }
            }
        }

        if (is_null($source_gateway)) {
            if (!is_null($source_ip) && !empty($source_ip)) {
                $source_gateway=$source_ip;
            }
        }

        if (!is_null($source_interface) && !empty($source_interface) && is_readable("/sys/class/net/$source_interface/address")) {
            $source_mac=$this->networkMAC(file_get_contents("/sys/class/net/$source_interface/address"));

            $ip_route_show_cmd="ip -4 -o route show dev $source_interface scope link";
            $ip_route_show_rc=$this->Ack_Sub->subExec($ip_route_show_cmd,$ip_route_show_stdout,$ip_route_show_stderr);
            if ($ip_route_show_rc === 0) {

                $ip_route_show_stdout_lines=explode("\n",$ip_route_show_stdout);
                if (!empty($ip_route_show_stdout_lines)) {
                    foreach($ip_route_show_stdout_lines as $ip_route_show_stdout_line) {
                        $this->debug($ip_route_show_stdout_line,25);

                        // source network
                        $ip_route_show_stdout_line_network=preg_split("/[[:space:]]/",$ip_route_show_stdout_line);
                        if (isset($ip_route_show_stdout_line_network[0])) {
                            $ip_route_show_stdout_line_network=explode("/",$ip_route_show_stdout_line_network[0]);
                        }

                        if (isset($ip_route_show_stdout_line_network[0])) {
                            $this->debug($ip_route_show_stdout_line_network[0],44);
                            $source_network=$ip_route_show_stdout_line_network[0];
                        }

                        // source prefix
                        $ip_route_show_stdout_line_prefix=preg_split("/[[:space:]]/",$ip_route_show_stdout_line);
                        if (isset($ip_route_show_stdout_line_prefix[0])) {
                            $ip_route_show_stdout_line_prefix=explode("/",$ip_route_show_stdout_line_prefix[0]);
                        }

                        if (isset($ip_route_show_stdout_line_prefix[1])) {
                            $this->debug($ip_route_show_stdout_line_prefix[1],44);
                            $source_prefix=$ip_route_show_stdout_line_prefix[1];
                        }

                        // source netmask

                        $source_netmask=$this->networkIPv4Netmask($source_prefix);
                    }
                }

            }

        }

        if ($destination_ip == "1") {
            if (is_null($source_prefix)) {
                $source_prefix="0";
            }
            $destination_ip="0.0.0.0";
        }

        $debug_level=13;
        $this->debugValue($destination_ip,$debug_level,"destination_ip");
        $this->debugValue($source_gateway,$debug_level,"source_gateway");
        $this->debugValue($source_interface,$debug_level,"source_interface");
        $this->debugValue($source_ip,$debug_level,"source_ip");
        $this->debugValue($source_mac,$debug_level,"source_mac");
        $this->debugValue($source_netmask,$debug_level,"source_netmask");
        $this->debugValue($source_network,$debug_level,"source_network");
        $this->debugValue($source_prefix,$debug_level,"source_prefix");

        if (is_null($field) || empty($field)) {
            $return_string="$destination_ip,$source_gateway,$source_interface,$source_ip,$source_mac,$source_netmask,$source_network,$source_prefix";
        } else {
            if ($field == "destination" || $field == "destination_ip") {
                $return_string=$destination_ip;
            }

            if ($field == "gateway" || $field == "source_gateway" || $field == "default_gateway" || $field == "gw") {
                $return_string=$source_gateway;
            }

            if ($field == "interface" || $field == "source_interface") {
                $return_string=$source_interface;
            }

            if ($field == "address" || $field == "source" || $field == "source_ip") {
                $return_string=$source_ip;
            }

            if ($field == "mac" || $field == "source_mac") {
                $return_string=$source_mac;
            }

            if ($field == "netmask" || $field == "source_netmask") {
                $return_string=$source_netmask;
            }

            if ($field == "network" || $field == "source_network") {
                $return_string=$source_network;
            }

            if ($field == "prefix" || $field == "source_prefix") {
                $return_string=$source_prefix;
            }

        }

        $this->debugValue($return_string,$debug_level,"return_string");
        return $return_string;
    }

    /**
     * validate & return a formatted mac address
     */
    public function networkMAC($mac=null,$mac_ip=null) {

        /*
         * begin function logic
         */

        if (!empty($mac)) {
            if (preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/', $mac) == 1) {
                $mac=str_replace("-", '',$mac);
                $mac=str_replace(":", '',$mac);
            } else {
                $mac=null;
            }
        } else {
            if (empty($mac_ip)) {
                $mac=implode('', str_split(substr(md5(mt_rand()), 0, 12), 2)); // random mac
            } else {
                $mac_ip_md5="12".md5($mac_ip);
                $mac=substr($mac_ip_md5,0,12);
            }
        }

        $mac=trim($mac);
        $mac=strtolower($mac);

        return $mac;

        /*
         * end function logic
         */

    }

}
