<?php

/**
 * aNAcONDA kICKSTART (ack) [File]
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
 * The \josephtingiris\Ack\File class
 */
class File extends \josephtingiris\Debug
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
     * returns an array of files found in $search_directory matching patters
     * TODO; make better
     */
    public function fileFind($search_directory=null, $search_pattern=null, $search_pattern_append=null, $search_recursion=true)
    {
        /*
         * begin function logic
         */

        $debug_level = 10;

        if (!is_dir($search_directory)) {
            $this->debug("invalid directory $search_directory",$debug_level);
            return array();
        }

        $return_result=array();

        if ($search_pattern == "*") $search_pattern=null;

        $search_separators=array("/","@",":","+","!");
        $valid_separator=false;
        foreach ($search_separators as $search_separator) {
            if (strpos($search_pattern,$search_separator) === false) {
                $valid_separator=true;
            } else continue;
            if ($valid_separator) break;
        }
        if ($search_separator == null) {
            Warning("couldn't determine a valid search separator for $search_pattern");
            return;
        }
        $search_pattern=$search_separator . $search_pattern . $search_separator;
        if ($search_pattern_append != null) $search_pattern.=$search_pattern_append;

        $this->debugValue("search_directory",$debug_level,$search_directory);
        $this->debugValue("search_pattern",$debug_level,$search_pattern);

        $search=array($search_directory);
        while (null !== ($dir = array_pop($search))) {
            if ($dh = opendir($dir)) {
                while (false !== ($file = readdir($dh))) {
                    if($file == '.' || $file == '..') continue; # skip these, altogether
                    $path = $dir . '/' . $file;
                    $this->debugValue("path",$debug_level+10,$path);

                    if (is_dir($path)) {
                        # recursion; add the new directory to the array
                        if ($search_recursion) $search[]=$path;
                    }

                    if (preg_match($search_pattern,$path)) {
                        $return_result[]=$path;
                        $this->debug("search_pattern matched '$path'",$debug_level);
                    }
                }
            } else {
                # failed to open directory
                $this->debug("WARNING could not open directory $dir (permissions?)");
            }

            closedir($dh);
        }

        return $return_result;

        /*
         * end function logic
         */
    }

}
