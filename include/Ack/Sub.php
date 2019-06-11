<?php

/**
 * aNAcONDA kICKSTART (ack) [Sub]
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
 * The \josephtingiris\Ack\Sub class
 */
class Sub extends \josephtingiris\Debug
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

        $this->Ack_Alert = new \josephtingiris\Ack\Alert();

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
     * returns curl output as an array
     */
    public function subCurl($url=null, $curl_options=null, $abort=false, $alert=false)
    {
        /*
         * begin function logic
         */

        $debug_level = 25;

        $failure_reason = __FUNCTION__ . " ERROR, ";

        if (is_null($url)) {
            $failure_reason .= "url is null";
            return $this->Ack_Alert->alertFail($failure_reason, $abort, $alert);
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
                    $this->Ack_Alert->alertFail($failure_reason, $abort, $alert);
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

        /*
         * end function logic
         */
    }

    /**
     * non-blocking (async) exec
     */
    public function subExec($cmd, &$stdout=null, &$stderr=null) {
        $cmd = escapeshellcmd($cmd);
        $proc = proc_open($cmd,[
            1 => ['pipe','w'],
            2 => ['pipe','w'],
        ],$pipes);
        $stdout .= trim(stream_get_contents($pipes[1]));
        fclose($pipes[1]);
        $stderr .= trim(stream_get_contents($pipes[2]));
        fclose($pipes[2]);
        return proc_close($proc);
    }

    /**
     * return a RFC 4122 compliant universally unique identifier
     */
    public function subUUID()
    {
        /*
         * begin function logic
         */

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

        /*
         * end function logic
         */
    }

}
