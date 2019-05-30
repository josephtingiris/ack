<?php

/**
 * aNAcONDA kICKSTART (ack) [Alert]
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
 * The \josephtingiris\Ack\Alert class
 */
class Alert extends \josephtingiris\Debug
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
     * aborts (exits, dies) with a given message & return code
     */
    public function alertAbort($aborting_message=null, $return_code=1, $alert=false)
    {
        /*
         * begin function logic
         */

        $aborting = "aborting";
        if ($alert) {
            $aborting .= " & alerting";
        }
        $aborting .= ", $aborting_message ... ($return_code)";
        $aborting=$this->timeStamp($aborting);

        echo $this->serverBr();
        echo "$aborting" . $this->serverBr();;
        echo $this->serverBr();

        if ($alert) {
            $this->alertMail($subject="!! ABORT !!", $body=$aborting . "\r\n\r\n");
        }

        exit($return_code);

        /*
         * end function logic
         */
    }

    /**
     * output a debug message & return false or abort
     */
    public function alertFail($failure_reason=null, $abort=false, $alert=false)
    {
        /*
         * begin function logic
         */

        $debug_level = 3;

        if ($failure_reason == null) {
            $failure_reason = "failure";
        }

        $failure_reason = trim($failure_reason);
        $failure_reason = trim($failure_reason,",");

        if ($abort) {
            $this->alertAbort($failure_reason,1,$alert);
        } else {
            if ($alert) {
                $this->alertMail($failure_reason);
            } else {
                $this->debug($failure_reason, $debug_level);
            }
            return false;
        }

        /*
         * end function logic
         */
    }

    /**
     * sends an alert email with a given message
     */
    public function alertMail($subject=null, $body=null, $to=null, $from=null, $cc=null)
    {
        /*
         * begin function logic
         */

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
            return $this->alertFail($failure_reason, false, false);
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

        $ack_email = new \josephtingiris\Ack\Email;
        $alert_mail_sent=$ack_email->email($to, $subject, $body, $additional_headers);
        if (!$alert_mail_sent) {
            // todo; slack?
            error_log("alertMail FAIL (not sent) subject=$subject, to=$to, from=$from, cc=$cc");
        }

        /*
         * end function logic
         */
    }

    /**
     * fail (aborts) with a non-zero return code & sends an alert email with a given message
     */
    public function alertMailAbort($subject=null, $body=null, $to=null, $from=null, $cc=null)
    {
        /*
         * begin function logic
         */

        $this->debug("alertMailAbort subject=$subject, to=$to, from=$from, cc=$cc", 0);

        $this->alertMail($subject,$body,$to,$from,$cc);

        if (empty($body)) {
            if (empty($subject)) {
                $body = __FUNCTION__;
            } else {
                $body = $subject;
            }
        }

        $this->alertAbort($body, 255);

        /*
         * end function logic
         */
    }

}
