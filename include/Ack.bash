#!/bin/bash

# This is a bash include file; common ack globals & functions
#
# 20160313, joseph.tingiris@gmail.com

PATH=/bin:/usr/bin:/sbin:/usr/sbin

# begin Debug.bash.include

Debug_Bash="Debug.bash"
Debug_Bash_Dir="$(dirname $(readlink -e ${BASH_SOURCE}))"
while [ "${Debug_Bash_Dir}" != "" ] && [ "${Debug_Bash_Dir}" != "/" ]; do # search backwards
    for Debug_Bash_Source_Dir in ${Debug_Bash_Dir} ${Debug_Bash_Dir}/include/debug-bash ${Debug_Bash_Dir}/include; do
        Debug_Bash_Source=${Debug_Bash_Source_Dir}/${Debug_Bash}
        if [ -r "${Debug_Bash_Source}" ]; then
            source "${Debug_Bash_Source}"
            break
        else
            unset Debug_Bash_Source
        fi
    done
    if [ "${Debug_Bash_Source}" != "" ]; then break; fi
    Debug_Bash_Dir=$(dirname "${Debug_Bash_Dir}")
done
if [ "${Debug_Bash_Source}" == "" ]; then echo "${Debug_Bash} file not found"; exit 1; fi
unset Debug_Bash_Dir Debug_Bash

# end Debug.bash.include

# GLOBAL_VARIABLES

Media_Architectures=()
Media_Architectures+=(i386)
Media_Architectures+=(x86_64)

Media_Candidates=()
Media_Candidates+=(beta)
Media_Candidates+=(dev)
Media_Candidates+=(local)
Media_Candidates+=(qa)
Media_Candidates+=(stg)

Media_Distributions=()
Media_Distributions+=(centos)
Media_Distributions+=(fedora)
Media_Distributions+=(rhel)

Media_Platforms=()
Media_Platforms+=(ack)
Media_Platforms+=(bin)
Media_Platforms+=(server)
Media_Platforms+=(workstation)

Zero=$0
if [ "${Zero}" == "-bash" ]; then Zero=${BASH_SOURCE}; fi
if [ -f "${Zero}" ]; then
    Zero=$(readlink -e "${Zero}")
fi
export Zero

# begin Debug.bash.include

# begin Ack.php.include

Ack_Php="Ack.php"
Ack_Php_Dir="$(dirname $(readlink -e ${BASH_SOURCE}))"
while [ "${Ack_Php_Dir}" != "" ] && [ "${Ack_Php_Dir}" != "/" ]; do # search backwards
    for Ack_Php_Source_Dir in ${Ack_Php_Dir}/include/Ack ${Ack_Php_Dir}/include ${Ack_Php_Dir}; do
        Ack_Php_Source=${Ack_Php_Source_Dir}/${Ack_Php}
        if [ -r "${Ack_Php_Source}" ]; then
            # run the php class to export Ack.php globals to export shell environment
            eval "$(/bin/env php <<< "<?php require_once('${Ack_Php_Source}'); \$Ack = new \josephtingiris\Ack(0); \$Ack->propertiesPrint(true,'Ack');?>")"
            break
        else
            unset Ack_Php_Source
        fi
    done
    if [ "${Ack_Php_Source}" != "" ]; then break; fi
    Ack_Php_Dir=$(dirname "${Ack_Php_Dir}")
done
if [ "${Ack_Php_Source}" == "" ]; then echo "${Ack_Php} file not found"; exit 1; fi
unset Ack_Php_Dir Ack_Php

# end Ack.php.include

# Functions

function ackEtc() {
    local ack_name="$1"
    local -l ack_cat="$2"

    for ack_etc in ${Ack_Etcs}; do
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

# This function is an attempt to formulate a consistent, repeatable URI naming structure for various
# Enterprise Linux distrubtions that have changed naming conventions over the course of many releases.
# Essentially; CentOS, Fedora, RedHat (et al) have different naming schemes for virutally the same things.

# If a URI name *can* be formulated then it will be output, otherwise the original input will be output.

# distribution (centos, fedora, rhel); ${Media_Distributions}
# major version (first set of 'numbered' strings, i.e. 1, 1.2, 1.2.3, v1234, etc)
# minor version (release) [optional] (second set of 'numbered' strings)
# platform (bin, server, workstation) [optional] ${Media_Platforms}
# candidate (beta) [optional] ${Media_Candidates}
# extras [optional] (can be anything, including tertiary set of 'numbered' strings)
# architecture (i386, x86_64) ${Media_Architectures}

# http(s)://<hostname>/<distribution>/<major>[.minor]/[platform]/[candidate]/[extras]/<architecture>

function ackURI() {
    local uri_arg="$1"

    if [ "$uri_arg" == "" ]; then return 1; fi

    local uri_ext="$2"

    local debug_level=30

    debugValue uri_arg ${debug_level}

    debugValue uri_ext ${debug_level}

    local uri
    local uris=()
    if [ -d "$uri_arg" ]; then
        while read uri; do
            uris+=("$uri")
        done <<< "$(find "${uri_arg}/" -maxdepth 1 -type f)"
        unset uri
    else
        debugValue uri_arg $debug_level file
        uris+=("$uri_arg")
    fi

    local uri
    for uri in ${uris[@]}; do
        debugValue uri_basename $((${debug_level}+20))

        local uri_basename=$(basename $uri)

        local uri_dashes
        uri_dashes=${uri_basename//[^-]}

        local uri_distribution media_distribution
        local uri_major_version
        local uri_minor_version
        local uri_platform media_platform
        local uri_candidate media_candidate
        local uri_architecture media_architecture
        local uri_extras

        local uri_cut uri_cut_dash uri_cut_found

        local uri_return

        if [ ${#uri_dashes} -gt 1 ]; then

            if [ ${#uri_ext} -gt 0 ]; then
                if [[ "${uri_basename,,}" =~ .${uri_ext}$ ]]; then
                    uri_basename="${uri_basename%"${uri_basename##*[!.${uri_ext}]}"}" # trim trailing .${uri_ext}
                fi
            fi

            debugValue uri_basename $((${debug_level}+5)) ${#uri_dashes}

            uri_cut=${uri_basename,,}
            for (( d=0; d<=${#uri_dashes}; d++)); do
                uri_cut_dash=${uri_cut%%-*}
                uri_cut_found=1

                debugValue uri_cut_dash $((${debug_level}+15)) ${uri_cut}

                if [ ${#uri_distribution} -eq 0 ]; then
                    for media_distribution in ${Media_Distributions[@]}; do
                        if [ "${media_distribution}" == "${uri_cut_dash}" ]; then
                            uri_distribution=${uri_cut_dash}
                            uri_cut_found=0
                            break
                        fi
                    done
                    if [ ${uri_cut_found} -eq 0 ]; then 
                        debugValue uri_distribution ${debug_level+3}
                        uri_cut=${uri_cut#*-}
                        continue
                    fi
                fi

                if [ ${#uri_architecture} -eq 0 ]; then
                    for media_architecture in ${Media_Architectures[@]}; do
                        if [ "${media_architecture}" == "${uri_cut_dash}" ]; then
                            uri_architecture=${uri_cut_dash}
                            uri_cut_found=0
                            break
                        fi
                    done
                    if [ ${uri_cut_found} -eq 0 ]; then 
                        debugValue uri_architecture ${debug_level+3}
                        uri_cut=${uri_cut#*-}
                        continue
                    fi
                fi

                if [ ${#uri_platform} -eq 0 ]; then
                    for media_platform in ${Media_Platforms[@]}; do
                        if [ "${media_platform}" == "${uri_cut_dash}" ]; then
                            uri_platform=${uri_cut_dash}
                            uri_cut_found=0
                            break
                        fi
                    done
                    if [ ${uri_cut_found} -eq 0 ]; then 
                        debugValue uri_platform ${debug_level+3}
                        uri_cut=${uri_cut#*-}
                        continue
                    fi
                fi

                if [ ${#uri_candidate} -eq 0 ]; then
                    for media_candidate in ${Media_Candidates[@]}; do
                        if [ "${media_candidate}" == "${uri_cut_dash}" ]; then
                            uri_candidate=${uri_cut_dash}
                            uri_cut_found=0
                            break
                        fi
                    done
                    if [ ${uri_cut_found} -eq 0 ]; then 
                        debugValue uri_candidate ${debug_level+3}
                        uri_cut=${uri_cut#*-}
                        continue
                    fi
                fi

                if [[ ${uri_cut_dash} =~ ^[0-9]+(\.[0-9]+)* ]]; then
                    if [ ${#uri_major_version} -eq 0 ]; then
                        uri_major_version=${uri_cut_dash}
                        uri_cut=${uri_cut#*-}
                        continue
                    fi
                    if [ ${#uri_minor_version} -eq 0 ]; then
                        uri_minor_version+=.${uri_cut_dash}
                    else
                        uri_minor_version+=-${uri_cut_dash}
                    fi
                    debugValue uri_major_version ${debug_level+3}
                    debugValue uri_minor_version ${debug_level+3}
                    uri_cut=${uri_cut#*-}
                    continue
                fi

                if [ ${uri_cut_found} -ne 0 ]; then
                    uri_extras+=${uri_cut_dash}-
                fi

                uri_cut=${uri_cut#*-}
            done

            debugValue uri_extras $((${debug_level}+7))

            if [ ${#uri_distribution} -gt 0 ]; then

                uri_return="/"

                uri_distribution="${uri_distribution%"${uri_distribution##*[!-]}"}" # trim trailing -
                uri_return+="${uri_distribution}/"

                if [ ${#uri_major_version} -gt 0 ]; then
                    uri_major_version="${uri_major_version%"${uri_major_version##*[!.]}"}" # trim trailing .
                    uri_return+="${uri_major_version}"
                fi

                if [ ${#uri_minor_version} -gt 0 ]; then
                    uri_minor_version="${uri_minor_version%"${uri_minor_version##*[!-]}"}" # trim trailing -
                    uri_minor_version="${uri_minor_version%"${uri_minor_version##*[!.]}"}" # trim trailing .
                    uri_return+="${uri_minor_version}"
                fi

                if [ ${#uri_major_version} -gt 0 ] || [ ${#uri_minor_version} -gt 0 ]; then
                    uri_return+="/"
                fi

                if [ ${#uri_platform} -gt 0 ]; then
                    uri_platform="${uri_platform%"${uri_platform##*[!-]}"}" # trim trailing -
                    uri_return+="${uri_platform}/"
                fi

                if [ ${#uri_candidate} -gt 0 ]; then
                    uri_candidate="${uri_candidate%"${uri_candidate##*[!-]}"}" # trim trailing -
                    uri_return+="${uri_candidate}/"
                fi

                if [ ${#uri_extras} -gt 0 ]; then
                    uri_extras="${uri_extras%"${uri_extras##*[!-]}"}" # trim trailing -
                    uri_return+="${uri_extras}/"
                fi

                if [ ${#uri_architecture} -gt 0 ]; then
                    uri_architecture="${uri_architecture%"${uri_architecture##*[!-]}"}" # trim trailing -
                    uri_return+="${uri_architecture}"
                fi

                uri_return+="/"

                uri_return=${uri_return//\/\//\/}

            else
                uri_return=${uri_basename}
            fi

        else
            uri_return=${uri_basename}
        fi

        printf "${uri_return}\n"

        unset -v uri_distribution media_distribution
        unset -v uri_major_version
        unset -v uri_minor_version
        unset -v uri_platform media_platform
        unset -v uri_candidate media_candidate
        unset -v uri_architecture media_architecture
        unset -v uri_extras

        unset -v uri_cut uri_cut_dash uri_cut_found

        unset -v uri_return

    done

}

# Main

debugValue PATH 50

Ack_Globals=$(set | grep -i ^Ack | grep = | awk -F= '{print $1}' | sort -u)
for Ack_Global in ${Ack_Globals}; do
    debugValue ${Ack_Global} 25
done
unset Ack_Globals

Ack_Group_Exists=$(cat /etc/group | grep ^${Ack_Group}:)
if [ "${Ack_Group_Exists}" == "" ]; then
    aborting "group '${Ack_Group}' not found" 1
fi

Ack_User_Exists=$(cat /etc/passwd | grep ^${Ack_User}:)
if [ "${Ack_User_Exists}" == "" ]; then
    aborting "user '${Ack_User}' not found" 1
fi
