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

# be careful changing the order of these

Ack_0=$0
if [ "$Ack_0" == "-bash" ]; then Ack_0=$BASH_SOURCE; fi
if [ -f "$Ack_0" ]; then
    Ack_0=$(readlink -e "$Ack_0")
fi
Ack_Basename=$(basename $Ack_0)

if [ "$Ack_Component" == "" ]; then
    if [ "${Ack_Basename:0:3}" == "ack" ]; then
        Ack_Component=$(echo "$Ack_Basename" | awk -F- '{print $2}' | awk -F. '{print $1}')
    else
        Ack_Component=$(echo "$Ack_Basename" | awk -F- '{print $1}' | awk -F. '{print $1}')
    fi
fi
if [ "$Ack_Component" == "" ]; then
    Ack_Component=$(echo "$Ack_Basename" | awk -F- '{print $1}' | awk -F. '{print $1}')
fi

Ack_Dirname=$(dirname $(realpath $(dirname $Ack_0)))

if [ "$Ack_Dir" == "" ]; then Ack_Dir=$Ack_Dirname; fi

if [ "$Ack_Dirname" == "." ]; then Ack_Dirname=$(pwd); fi
Ack_Dirname=$(echo "$Ack_Dirname" | sed -e '/\/bin$/s///g' -e '/\/sbin$/s///g')

if [ "$Ack_Exit" == "" ]; then declare -i Ack_Exit=0; fi

if [ "$Ack_Include" == "" ]; then Ack_Include="$BASH_SOURCE"; fi

if [ "$Ack_Component" != "" ]; then Ack_Component_DIR="${Ack_Dir}/${Ack_Component}"; else Ack_Component_DIR="$Ack_Dir"; fi

if [ "$Ack_Local_Dir" == "" ]; then
    if [ -d "${Ack_Dir}/local" ]; then
        Ack_Local_Dir="${Ack_Dir}/local"
    else
        if [ -h "${Ack_Dir}/local" ]; then
            Ack_Local_Dir="${Ack_Dir}/local"
        else
            Ack_Local_Dir="${Ack_Dir}"
        fi
    fi
fi

if [ "$Ack_Backup_Dir" == "" ]; then
    Ack_Backup_Dir="${Ack_Local_Dir}/backup"
    if [ ! -d "$Ack_Backup_Dir" ]; then
        mkdir -p "$Ack_Backup_Dir"
    fi
fi

if [ "$Ack_Etcs" == "" ]; then Ack_Etcs="${Ack_Local_Dir}/${Ack_Component}/etc ${Ack_Component_DIR}/etc ${Ack_Local_Dir}/etc ${Ack_Dir}/etc /etc"; fi

if [ "$Ack_Accounts" == "" ]; then
    if [ -d "$Ack_Dir/account" ]; then
        Ack_Accounts=$(echo $(find "$Ack_Dir/account" | grep -v "$Ack_Dir/account$" | awk -F\/ '{print $NF}') | awk '{print $1}')
    fi
fi

if [ "$Ack_Interfaces" == "" ]; then Ack_Interfaces=$(echo $(ip -o link list | egrep -ve '\ lo' | awk -F: '{print $2}' | sort -u)); fi

if [ "$Ack_Gateway" == "" ]; then Ack_Gateway=$(netstat -rn | grep ^0.0.0.0\ | awk '{print $2}' | head -1); fi

if [ "$Ack_Gateway_Interface" == "" ]; then Ack_Gateway_Interface=$(netstat -rn | grep ^0.0.0.0\ | awk '{print $NF}' | head -1); fi
Ack_Gateway_Interface_Ipv4=$(ip -o addr list $Ack_Gateway_Interface 2> /dev/null | grep -o -E "([0-9]{1,3}[\.]){3}[0-9]{1,3}" | awk 'NR==1{print $1}' | grep -v [a-z] | grep -v [A-Z])
Ack_Gateway_Interface_Mac=$(ip -o link list $Ack_Gateway_Interface 2> /dev/null | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}'| awk 'NR==1{print tolower($1)}' | sed -e '/:/s///g')

if [ "$Ack_Group" == "" ]; then Ack_Group="root"; fi

if [ "$Ack_User" == "" ]; then Ack_User="root"; fi

if [ "$Ack_Provisioning_Interface" == "" ]; then Ack_Provisioning_Interface="$Ack_Gateway_Interface"; fi

if [ "$Ack_Provisioning_Mac" == "" ]; then Ack_Provisioning_Mac="$Ack_Gateway_Interface_Mac"; fi

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

function ackPath() {
    local ack_path="./:"
    Ack_Path=$ack_path
    if [ -d $Ack_Dir ]; then
        local ack_bin
        local ack_bins=$(find "$Ack_Dir" -maxdepth 4 -type d -name bin -o -name sbin | sort)
        for ack_bin in $ack_bins; do
            ack_path+="$ack_bin:"
        done
    fi

    ack_path+=":$PATH"

    OIFS=$IFS
    IFS=":"
    for dir_path in $ack_path; do
        if [ "$dir_path" == "" ]; then continue; fi
        if [ ! -d "$dir_path" ]; then continue; fi
        Ack_Path+="$dir_path:"
    done
    IFS=$OIFS

    export Ack_Path
    PATH=$(echo $Ack_Path | sed -e '/:$/s///g')
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

ackPath

debugValue PATH 50

Ack_Label=$(ackEtc ack-label cat)
if [ "$Ack_Label" == "" ]; then
    Ack_Label="ack"
fi

Ack_Domain=$(ackEtc ack-domain cat)
if [ "$Ack_Domain" == "" ]; then
    Ack_Domain="localdomain"
fi

Ack_Entity=$(ackEtc ack-entity cat)
if [ "$Ack_Entity" == "" ]; then
    Ack_Entity="Personal Use"
fi


Ack_Group_Exists=$(cat /etc/group | grep ^$Ack_Group:)
if [ "$Ack_Group_Exists" == "" ]; then
    aborting "group '$Ack_Group' not found" 1
fi

Ack_User_Exists=$(cat /etc/passwd | grep ^$Ack_User:)
if [ "$Ack_User_Exists" == "" ]; then
    aborting "user '$Ack_User' not found" 1
fi

# this should be last
Ack_Globals=$(set | grep -i ^Ack | grep = | awk -F= '{print $1}' | sort -u)
for Ack_Global in $Ack_Globals; do
    debugValue ${Ack_Global} 25
done
