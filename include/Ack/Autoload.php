<?php

/**
 * aNAcONDA kICKSTART (ack) [component]
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

/*
 *
 * nothing should preceed namespace, except declarations;
 * see http://php.net/manual/en/language.namespaces.definition.php
 *
 * PSR-0: Autoloading Standard (Deprecated, DO NOT USE)
 * see http://www.php-fig.org/psr/psr-0/
 * see http://www.php-fig.org/psr/psr-4/
 *
 * PSR-1: Basic Coding Standard
 * see http://www.php-fig.org/psr/psr-1/
 *
 * PSR-2: Coding Style Guide
 * see http://www.php-fig.org/psr/psr-2/
 *
 * PSR-3: Logger Interface
 * see http://www.php-fig.org/psr/psr-3/
 * use \Monolog\Logger as Logger; # psr-3
 * use \Monolog\Handler\StreamHandler as StreamHandler; # psr-3
 *
 * PSR-4: Autoloader
 * see http://www.php-fig.org/psr/psr-4/
 * require composer autoload.php; ClassLoader implements a PSR-0, PSR-4 and classmap class loader.
 *
 */

# begin composer psr-4 autoloader

if (empty($Autoload_Enabled)) {
    $Autoload_Php="autoload.php";
    $Autoload_Enabled=false;
    $Autoload_Paths=array(dirname(__FILE__), getcwd());
    foreach ($Autoload_Paths as $Autoload_Path) {
        if ($Autoload_Enabled) break;
        while(strlen($Autoload_Path) > 0) {
            if ($Autoload_Path == ".") $Autoload_Path=getcwd();
            if ($Autoload_Path == "/") break;
            if (is_readable($Autoload_Path . "/vendor/" . $Autoload_Php) && !is_dir($Autoload_Path . "/vendor/" . $Autoload_Php)) {
                $Autoload_Enabled=true;
                require_once($Autoload_Path . "/vendor/" . $Autoload_Php);
                break;
            } else {
                $Autoload_Path=dirname($Autoload_Path);
            }
        }
    }
    if (!$Autoload_Enabled) { echo "$Autoload_Php file not found\n"; exit(1); }
    unset($Autoload_Php, $Autoload_Path);
}

# end composer psr-4 autoloader

/*
 *
 * vendor class depdendent psr-0 & psr-4 root/unnamed namespace aliases MAY be used
 * however, missing namespace aliases SHOULD NOT cause PHP Fatal (class not found) errors
 *
 */

/*
 *
 * functions
 *
 */

/*
 *
 * main
 *
 */
