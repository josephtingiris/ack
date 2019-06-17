#!/bin/bash

# This is a bash include file; common ack globals & functions
#
# 20160313, joseph.tingiris@gmail.com

PATH=/bin:/usr/bin:/sbin:/usr/sbin

# begin Debug.bash.include

Debug_Bash="Debug.bash"
Debug_Bash_Dir="$(dirname $(readlink -e $BASH_SOURCE))"
while [ "$Debug_Bash_Dir" != "" ] && [ "$Debug_Bash_Dir" != "/" ]; do # search backwards
    for Debug_Bash_Source_Dir in $Debug_Bash_Dir $Debug_Bash_Dir/include/debug-bash $Debug_Bash_Dir/include; do
        Debug_Bash_Source=${Debug_Bash_Source_Dir}/${Debug_Bash}
        if [ -r "${Debug_Bash_Source}" ]; then
            source "${Debug_Bash_Source}"
            break
        else
            unset Debug_Bash_Source
        fi
    done
    if [ "$Debug_Bash_Source" != "" ]; then break; fi
    Debug_Bash_Dir=$(dirname "$Debug_Bash_Dir")
done
if [ "$Debug_Bash_Source" == "" ]; then echo "$Debug_Bash file not found"; exit 1; fi
unset Debug_Bash_Dir Debug_Bash

# end Debug.bash.include

# GLOBAL_VARIABLES

Zero=$0
if [ "$Zero" == "-bash" ]; then Zero=$BASH_SOURCE; fi
if [ -f "$Zero" ]; then
    Zero=$(readlink -e "$Zero")
fi
export Zero

# begin Debug.bash.include

# begin Ack.php.include

Ack_Php="Ack.php"
Ack_Php_Dir="$(dirname $(readlink -e $BASH_SOURCE))"
while [ "$Ack_Php_Dir" != "" ] && [ "$Ack_Php_Dir" != "/" ]; do # search backwards
    for Ack_Php_Source_Dir in $Ack_Php_Dir/include/Ack $Ack_Php_Dir/include $Ack_Php_Dir; do
        Ack_Php_Source=${Ack_Php_Source_Dir}/${Ack_Php}
        if [ -r "${Ack_Php_Source}" ]; then
            # run the php class to export Ack.php globals to export shell environment
            eval "$(/bin/env php <<< "<?php require_once('${Ack_Php_Source}'); \$Ack = new \josephtingiris\Ack; \$Ack->properties(true,'Ack');?>")"
            break
        else
            unset Ack_Php_Source
        fi
    done
    if [ "$Ack_Php_Source" != "" ]; then break; fi
    Ack_Php_Dir=$(dirname "$Ack_Php_Dir")
done
if [ "$Ack_Php_Source" == "" ]; then echo "$Ack_Php file not found"; exit 1; fi
unset Ack_Php_Dir Ack_Php

# end Ack.php.include

# Functions

function ackEtc() {
    local ack_name="$1"
    local -l ack_cat="$2"

    for ack_etc in $Ack_Etcs; do
        if [ "$ack_etc" != "" ]; then
            if [ -r "${ack_etc}/${ack_name}" ]; then
                if [ "$ack_cat" == "cat" ]; then
                    cat "${ack_etc}/${ack_name}"
                else
                    echo "$ack_etc/$ack_name"
                fi
                unset ack_etc
                return
            fi
        else
            if [ -d "$ack_name" ]; then
                echo "$ack_name"
                unset ack_etc
                return
            fi
        fi
    done
}

function ackStart() {

    # begin function logic

    debugFunction $@

    debugFunction $@

    # end function logic
}

function ackStop() {

    # begin function logic

    debugFunction $@

    debugFunction $@

    exit 

    # end function logic
}

# Main

debugValue PATH 50

Ack_Globals=$(set | grep -i ^Ack | grep = | awk -F= '{print $1}' | sort -u)
for Ack_Global in $Ack_Globals; do
    debugValue ${Ack_Global} 25
done
unset Ack_Globals

Ack_Group_Exists=$(cat /etc/group | grep ^$Ack_Group:)
if [ "$Ack_Group_Exists" == "" ]; then
    aborting "group '$Ack_Group' not found" 1
fi

Ack_User_Exists=$(cat /etc/passwd | grep ^$Ack_User:)
if [ "$Ack_User_Exists" == "" ]; then
    aborting "user '$Ack_User' not found" 1
fi

