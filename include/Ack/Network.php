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
    function networkInterfaces($interface=null)
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
    function networkIPv4($ip=null, $int_ip=false, $hex_ip=false, $little_endian=false) {
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
            $Ack_Log = new \josephtingiris\Ack\Log;
            $Ack_Log->message("invalid ack_ip '$ip'","WARNING");
        if (!filter_var($return_ip, FILTER_VALIDATE_IP)) {
            $this->Ack_Log->message("invalid ack_ip '$ip'","WARNING");
            $return_ip=null;
        }

        return $return_ip;
    }

    # return an array with an inteface's address(es), cidr, & mask, or optionally a string of the (first) address, cidr, or mask
    function ipv4Address($interface=null, $address=false, $cidr=false, $mask=false) {

        $interface=trim($interface);
        if (empty($interface)) {
            return null;
        }

        global $Ack;

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

                            $ip_address=networkIPv4($ip_address);
                            if (!empty($ip_address)) {
                                $Ack->debugValue("ip_address",15,$ip_address);
                                if ($address && !$cidr && !$mask) {
                                    return $ip_address;
                                } else {
                                    $Ack->debugValue($ip_explode_line,15);
                                }
                            }

                            $ip_cidr=trim($ip_cidr);
                            $ip_cidr=(int)$ip_cidr;
                            if (isset($ip_cidr) && is_int($ip_cidr)) {
                                $Ack->debugValue("ip_cidr",15,$ip_cidr);
                                if (!$address && $cidr && !$mask) {
                                    return $ip_cidr;
                                } else {
                                    $Ack->debugValue($ip_explode_line,15);
                                }
                            }
                            if (isset($ip_cidr) && is_int($ip_cidr)) {
                                $Ack->debugValue("ip_cidr",15,$ip_cidr);
                                if (!$address && !$cidr && $mask) {
                                    return ipv4Netmask($ip_cidr);
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
            $this->Ack_Log->message("'$ip_cmd' failed","ERROR");
        }
    }

    # if an ip is in a cidr range return true else return false
    function ipv4Authorized($ip, $cidr) {

        /*
         * begin function logic
         */

        if (strpos($cidr,"/") !== false) {
            list ($subnet, $bits) = explode('/', $cidr);
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

    function ipv4Netmask($ip) {

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

    /**
     * validate & return a formatted mac address
     */
    function mac($mac=null,$mac_ip=null) {

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
