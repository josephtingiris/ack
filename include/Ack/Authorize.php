<?php

/**
 * aNAcONDA kICKSTART (ack) [Authorize]
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
 * The \josephtingiris\Ack\Authorize class
 */
class Authorize extends \josephtingiris\Debug
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

        $this->Ack_Network = new \josephtingiris\Ack\Network($debug_level_construct);

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
     * authorize a client
     */
    public function authorize($ack_mac=null, $ack_ip=null, $ack_config=null) {

        /*
         * begin function logic
         */

        global $Ack;

        $ack_ip=$this->Ack_Network->networkIPv4($ack_ip);
        $ack_mac=$this->Ack_Network->networkMAC($ack_mac);

        if (empty($ack_ip) && empty($ack_mac)) {
            ackLog("unauthorized [NULL ack_ip & ack_mac]","ERROR");
            return false;
        }

        $ack_authorized=false;
        $ack_authorized_date=date("Y-m-d H:i:s"); // TODO; make this date format include timezone (offset)
        $ack_authorized_method=null;

        $ack_config=$ack_config;
        $ack_config_update=false;

        $Ack->debug("ackAuthorized($ack_mac,$ack_ip,$ack_config)",20);

        // config files are the primary authorization method, check those first

        if (!$ack_authorized && !empty($ack_config)) {

            $ack_authorized_config=ackConfigGet("authorized",$ack_config);
            $Ack->debug("ack_authorized_config = $ack_authorized_config",20);
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

        /*
         * end function logic
         */

    }

}
